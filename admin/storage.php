<?php

function elodie_cms_database(): PDO
{
    static $database;
    if ($database instanceof PDO) {
        return $database;
    }
    if (!in_array('sqlite', PDO::getAvailableDrivers(), true)) {
        throw new RuntimeException('L’extension PDO SQLite est nécessaire pour Elodie CMS.');
    }

    $dataDirectory = dirname(__DIR__) . '/data';
    if (!is_dir($dataDirectory) && !mkdir($dataDirectory, 0700, true) && !is_dir($dataDirectory)) {
        throw new RuntimeException('Impossible de créer le répertoire de données SQLite.');
    }
    if (!is_writable($dataDirectory)) {
        throw new RuntimeException('Le répertoire de données SQLite n’est pas accessible en écriture.');
    }
    if (DIRECTORY_SEPARATOR !== '\\' && !chmod($dataDirectory, 0700)) {
        throw new RuntimeException('Impossible de restreindre les permissions du répertoire SQLite.');
    }

    $databasePath = $dataDirectory . '/elodie-cms.sqlite';
    $legacyDatabasePath = $dataDirectory . '/uag.sqlite';
    if (!is_file($databasePath) && is_file($legacyDatabasePath)) {
        $legacyDatabase = new PDO('sqlite:' . $legacyDatabasePath, null, null, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ]);
        $quotedDatabasePath = $legacyDatabase->quote($databasePath);
        if (!is_string($quotedDatabasePath)) {
            throw new RuntimeException('Impossible de préparer la migration de la base SQLite historique.');
        }
        $legacyDatabase->exec('VACUUM INTO ' . $quotedDatabasePath);
        unset($legacyDatabase);
    }

    $database = new PDO('sqlite:' . $databasePath, null, null, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
    if (DIRECTORY_SEPARATOR !== '\\' && !chmod($databasePath, 0600)) {
        throw new RuntimeException('Impossible de restreindre les permissions de la base SQLite.');
    }
    $database->exec('PRAGMA foreign_keys = ON');
    $database->exec('PRAGMA busy_timeout = 5000');
    $database->exec(
        'CREATE TABLE IF NOT EXISTS settings (
            setting_key INTEGER PRIMARY KEY,
            setting_value TEXT NOT NULL
        )'
    );
    $database->exec(
        'CREATE TABLE IF NOT EXISTS articles (
            id INTEGER PRIMARY KEY,
            title TEXT NOT NULL,
            day TEXT NOT NULL,
            month TEXT NOT NULL,
            year TEXT NOT NULL,
            content TEXT NOT NULL,
            excerpt TEXT NOT NULL,
            rating TEXT NOT NULL
        )'
    );
    $database->exec(
        'CREATE TABLE IF NOT EXISTS users (
            username TEXT PRIMARY KEY,
            password_hash TEXT NOT NULL,
            totp_secret TEXT,
            totp_enabled INTEGER NOT NULL DEFAULT 0,
            totp_last_counter INTEGER
        )'
    );
    $database->exec(
        'CREATE TABLE IF NOT EXISTS recovery_codes (
            username TEXT NOT NULL REFERENCES users(username) ON DELETE CASCADE ON UPDATE CASCADE,
            code_hash TEXT NOT NULL,
            used_at TEXT,
            PRIMARY KEY (username, code_hash)
        )'
    );
    $database->exec(
        'CREATE TABLE IF NOT EXISTS login_attempts (
            id INTEGER PRIMARY KEY,
            subject_hash TEXT NOT NULL,
            attempted_at INTEGER NOT NULL
        )'
    );
    $database->exec('CREATE INDEX IF NOT EXISTS login_attempts_subject_time ON login_attempts(subject_hash, attempted_at)');
    $database->exec(
        'CREATE TABLE IF NOT EXISTS comments (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            article_id INTEGER NOT NULL,
            author TEXT NOT NULL,
            body TEXT NOT NULL,
            status TEXT NOT NULL DEFAULT \'pending\' CHECK (status IN (\'pending\', \'approved\')),
            created_at TEXT NOT NULL
        )'
    );
    $database->exec('CREATE INDEX IF NOT EXISTS comments_article_status ON comments(article_id, status, id)');
    $database->exec(
        'CREATE TABLE IF NOT EXISTS comment_rate_limits (
            id INTEGER PRIMARY KEY,
            remote_hash TEXT NOT NULL,
            attempted_at INTEGER NOT NULL
        )'
    );
    $database->exec('CREATE INDEX IF NOT EXISTS comment_rate_limits_remote_time ON comment_rate_limits(remote_hash, attempted_at)');
    elodie_cms_migrate_legacy_data($database);
    elodie_cms_disable_legacy_comment_service($database);

    return $database;
}

function elodie_cms_disable_legacy_comment_service(PDO $database): void
{
    if ($database->query('SELECT 1 FROM settings WHERE setting_key = -2')->fetchColumn()) {
        return;
    }

    $database->beginTransaction();
    try {
        $statement = $database->prepare(
            'INSERT INTO settings (setting_key, setting_value) VALUES (3, :value)
             ON CONFLICT(setting_key) DO UPDATE SET setting_value = excluded.setting_value'
        );
        $statement->execute(['value' => 'off']);
        $marker = $database->prepare(
            'INSERT INTO settings (setting_key, setting_value) VALUES (-2, :value)'
        );
        $marker->execute(['value' => 'comments-disabled-by-default']);
        $database->commit();
    } catch (Throwable $exception) {
        if ($database->inTransaction()) {
            $database->rollBack();
        }
        throw $exception;
    }
}

function elodie_cms_migrate_legacy_data(PDO $database): void
{
    $alreadyMigrated = $database->query("SELECT 1 FROM settings WHERE setting_key = -1")->fetchColumn();
    if ($alreadyMigrated) {
        return;
    }

    $configurationPath = __DIR__ . '/configuration.txt';
    $newsPath = dirname(__DIR__) . '/news.php';
    $legacySettings = [];
    if (is_file($configurationPath)) {
        $contents = file_get_contents($configurationPath);
        if ($contents === false) {
            throw new RuntimeException('Impossible de lire la configuration historique.');
        }
        foreach (explode('-', $contents) as $index => $value) {
            $decoded = base64_decode($value, true);
            if ($decoded === false) {
                throw new RuntimeException('La configuration historique est invalide et n’a pas été modifiée.');
            }
            if ($index < 32) {
                $legacySettings[$index] = $decoded;
            }
        }
    }

    $legacyArticles = [];
    if (is_file($newsPath)) {
        $contents = file_get_contents($newsPath);
        $serialized = $contents === false ? false : base64_decode($contents, true);
        if ($serialized === false) {
            throw new RuntimeException('Le fichier historique des articles est invalide et n’a pas été modifié.');
        }
        $legacyArticles = unserialize($serialized, ['allowed_classes' => false]);
        if (!is_array($legacyArticles)) {
            throw new RuntimeException('Le fichier historique des articles est invalide et n’a pas été modifié.');
        }
        foreach (array_keys($legacyArticles) as $id) {
            if ((!is_int($id) && !(is_string($id) && ctype_digit($id))) || (int) $id < 0) {
                throw new RuntimeException('Un identifiant d’article historique est invalide.');
            }
        }
        foreach ($legacyArticles as $article) {
            if (!is_array($article)) {
                throw new RuntimeException('Un article historique est invalide et la migration a été annulée.');
            }
            foreach (['titre', 'jour', 'mois', 'annee', 'contenu', 'chapo', 'note'] as $field) {
                if (!is_string($article[$field] ?? null)) {
                    throw new RuntimeException('Un article historique est invalide et la migration a été annulée.');
                }
            }
        }
    }

    $database->beginTransaction();
    try {
        $settingInsert = $database->prepare('INSERT INTO settings (setting_key, setting_value) VALUES (:key, :value)');
        foreach ($legacySettings as $key => $value) {
            $settingInsert->execute(['key' => $key, 'value' => $value]);
        }

        if (isset($legacySettings[6], $legacySettings[7]) && $legacySettings[6] !== '' && $legacySettings[7] !== '') {
            $passwordHash = str_starts_with($legacySettings[7], '$')
                ? $legacySettings[7]
                : 'legacy-sha1:' . $legacySettings[7];
            $userInsert = $database->prepare(
                'INSERT INTO users (username, password_hash) VALUES (:username, :password_hash)'
            );
            $userInsert->execute([
                'username' => html_entity_decode($legacySettings[6], ENT_QUOTES | ENT_HTML5, 'UTF-8'),
                'password_hash' => $passwordHash,
            ]);
        }

        $articleInsert = $database->prepare(
            'INSERT INTO articles (id, title, day, month, year, content, excerpt, rating)
             VALUES (:id, :title, :day, :month, :year, :content, :excerpt, :rating)'
        );
        foreach ($legacyArticles as $id => $article) {
            $articleInsert->execute([
                'id' => (int) $id,
                'title' => $article['titre'],
                'day' => $article['jour'],
                'month' => $article['mois'],
                'year' => $article['annee'],
                'content' => $article['contenu'],
                'excerpt' => $article['chapo'],
                'rating' => $article['note'],
            ]);
        }

        $settingInsert->execute(['key' => -1, 'value' => 'legacy-imported']);
        $database->commit();
    } catch (Throwable $exception) {
        if ($database->inTransaction()) {
            $database->rollBack();
        }
        throw $exception;
    }
}

function elodie_cms_read_encoded_configuration(): array
{
    $configuration = array_fill(0, 32, '');
    $statement = elodie_cms_database()->query('SELECT setting_key, setting_value FROM settings WHERE setting_key >= 0');
    foreach ($statement as $row) {
        $configuration[(int) $row['setting_key']] = base64_encode($row['setting_value']);
    }

    return $configuration;
}

function elodie_cms_write_encoded_configuration(array $values): void
{
    if (count($values) < 32) {
        throw new InvalidArgumentException('La configuration doit contenir 32 valeurs.');
    }
    $settings = [];
    foreach (range(0, 31) as $index) {
        $value = $values[$index] ?? null;
        if (!is_string($value)) {
            throw new InvalidArgumentException('La configuration contient une valeur invalide.');
        }
        $decoded = base64_decode($value, true);
        if ($decoded === false) {
            throw new InvalidArgumentException('La configuration contient une valeur invalide.');
        }
        $settings[$index] = $decoded;
    }

    $database = elodie_cms_database();
    $database->beginTransaction();
    try {
        $previousSetting = $database->query('SELECT setting_value FROM settings WHERE setting_key = 6')->fetchColumn();
        if (is_string($previousSetting) && $previousSetting !== '') {
            $previousUsername = html_entity_decode($previousSetting, ENT_QUOTES | ENT_HTML5, 'UTF-8');
            $newUsername = html_entity_decode($settings[6], ENT_QUOTES | ENT_HTML5, 'UTF-8');
            if ($previousUsername !== $newUsername) {
                $rename = $database->prepare(
                    'UPDATE users SET username = :new_username, password_hash = :password_hash
                     WHERE username = :old_username'
                );
                $rename->execute([
                    'new_username' => $newUsername,
                    'password_hash' => $settings[7],
                    'old_username' => $previousUsername,
                ]);
            }
        }

        $statement = $database->prepare(
            'INSERT INTO settings (setting_key, setting_value) VALUES (:key, :value)
             ON CONFLICT(setting_key) DO UPDATE SET setting_value = excluded.setting_value'
        );
        foreach ($settings as $key => $value) {
            $statement->execute(['key' => $key, 'value' => $value]);
        }

        if ($settings[6] !== '' && $settings[7] !== '') {
            $user = $database->prepare(
                'INSERT INTO users (username, password_hash) VALUES (:username, :password_hash)
                 ON CONFLICT(username) DO UPDATE SET password_hash = excluded.password_hash'
            );
            $user->execute([
                'username' => html_entity_decode($settings[6], ENT_QUOTES | ENT_HTML5, 'UTF-8'),
                'password_hash' => $settings[7],
            ]);
        }
        $database->commit();
    } catch (Throwable $exception) {
        if ($database->inTransaction()) {
            $database->rollBack();
        }
        throw $exception;
    }
}

function elodie_cms_user(string $username): ?array
{
    $statement = elodie_cms_database()->prepare('SELECT * FROM users WHERE username = :username');
    $statement->execute(['username' => $username]);
    $user = $statement->fetch();

    return $user === false ? null : $user;
}

function elodie_cms_enable_totp(string $username, string $secret, int $lastCounter, array $recoveryCodes): void
{
    $database = elodie_cms_database();
    $database->beginTransaction();
    try {
        $user = $database->prepare(
            'UPDATE users SET totp_secret = :secret, totp_enabled = 1,
                totp_last_counter = :last_counter WHERE username = :username'
        );
        $user->execute(['secret' => $secret, 'last_counter' => $lastCounter, 'username' => $username]);
        if ($user->rowCount() !== 1) {
            throw new RuntimeException('Impossible d’activer l’authentification à deux facteurs.');
        }

        $delete = $database->prepare('DELETE FROM recovery_codes WHERE username = :username');
        $delete->execute(['username' => $username]);
        $insert = $database->prepare(
            'INSERT INTO recovery_codes (username, code_hash) VALUES (:username, :code_hash)'
        );
        foreach ($recoveryCodes as $code) {
            $insert->execute([
                'username' => $username,
                'code_hash' => password_hash($code, PASSWORD_DEFAULT),
            ]);
        }
        $database->commit();
    } catch (Throwable $exception) {
        if ($database->inTransaction()) {
            $database->rollBack();
        }
        throw $exception;
    }
}

function elodie_cms_update_user_password(string $username, string $passwordHash): void
{
    $database = elodie_cms_database();
    $database->beginTransaction();
    try {
        $statement = $database->prepare(
            'UPDATE users SET password_hash = :password_hash WHERE username = :username'
        );
        $statement->execute(['password_hash' => $passwordHash, 'username' => $username]);
        if ($statement->rowCount() !== 1) {
            throw new RuntimeException('Impossible de mettre à jour le mot de passe administrateur.');
        }

        $configuration = $database->prepare(
            'INSERT INTO settings (setting_key, setting_value) VALUES (7, :password_hash)
             ON CONFLICT(setting_key) DO UPDATE SET setting_value = excluded.setting_value'
        );
        $configuration->execute(['password_hash' => $passwordHash]);
        $database->commit();
    } catch (Throwable $exception) {
        if ($database->inTransaction()) {
            $database->rollBack();
        }
        throw $exception;
    }
}

function elodie_cms_read_news(string $legacyPath = ''): array
{
    $articles = [];
    $statement = elodie_cms_database()->query(
        'SELECT id, title, day, month, year, content, excerpt, rating FROM articles ORDER BY id ASC'
    );
    foreach ($statement as $row) {
        $articles[(int) $row['id']] = [
            'titre' => $row['title'],
            'jour' => $row['day'],
            'mois' => $row['month'],
            'annee' => $row['year'],
            'contenu' => $row['content'],
            'chapo' => $row['excerpt'],
            'note' => $row['rating'],
        ];
    }

    return $articles;
}

function elodie_cms_write_news(string $legacyPath, array $articles): void
{
    $database = elodie_cms_database();
    $database->beginTransaction();
    try {
        $database->exec('DELETE FROM articles');
        $statement = $database->prepare(
            'INSERT INTO articles (id, title, day, month, year, content, excerpt, rating)
             VALUES (:id, :title, :day, :month, :year, :content, :excerpt, :rating)'
        );
        foreach ($articles as $id => $article) {
            if ((!is_int($id) && !(is_string($id) && ctype_digit($id)))
                || (int) $id < 0
                || !is_array($article)) {
                throw new InvalidArgumentException('Un article à enregistrer est invalide.');
            }
            foreach (['titre', 'jour', 'mois', 'annee', 'contenu', 'chapo', 'note'] as $field) {
                if (!is_string($article[$field] ?? null)) {
                    throw new InvalidArgumentException('Un article à enregistrer est invalide.');
                }
            }
            $statement->execute([
                'id' => (int) $id,
                'title' => $article['titre'],
                'day' => $article['jour'],
                'month' => $article['mois'],
                'year' => $article['annee'],
                'content' => $article['contenu'],
                'excerpt' => $article['chapo'],
                'rating' => $article['note'],
            ]);
        }
        $database->exec('DELETE FROM comments WHERE article_id NOT IN (SELECT id FROM articles)');
        $database->commit();
    } catch (Throwable $exception) {
        if ($database->inTransaction()) {
            $database->rollBack();
        }
        throw $exception;
    }
}

function elodie_cms_is_installed(): bool
{
    $settings = elodie_cms_read_encoded_configuration();
    $username = base64_decode($settings[6] ?? '', true);
    $passwordHash = base64_decode($settings[7] ?? '', true);

    return $username !== false && $username !== ''
        && $passwordHash !== false && $passwordHash !== '';
}

function elodie_cms_comments_enabled(): bool
{
    $statement = elodie_cms_database()->query('SELECT setting_value FROM settings WHERE setting_key = 3');

    return $statement->fetchColumn() === 'on';
}

function elodie_cms_add_comment(int $articleId, string $author, string $body, string $remoteAddress): bool
{
    $database = elodie_cms_database();
    $remoteHash = hash('sha256', 'comment|' . $remoteAddress);
    $cutoff = time() - 900;
    $database->beginTransaction();
    try {
        $prune = $database->prepare('DELETE FROM comment_rate_limits WHERE attempted_at < :cutoff');
        $prune->execute(['cutoff' => $cutoff]);
        $count = $database->prepare(
            'SELECT COUNT(*) FROM comment_rate_limits
             WHERE remote_hash = :remote_hash AND attempted_at >= :cutoff'
        );
        $count->execute(['remote_hash' => $remoteHash, 'cutoff' => $cutoff]);
        if ((int) $count->fetchColumn() >= 5) {
            $database->commit();
            return false;
        }

        $attempt = $database->prepare(
            'INSERT INTO comment_rate_limits (remote_hash, attempted_at)
             VALUES (:remote_hash, :attempted_at)'
        );
        $attempt->execute(['remote_hash' => $remoteHash, 'attempted_at' => time()]);
        $comment = $database->prepare(
            'INSERT INTO comments (article_id, author, body, created_at)
             VALUES (:article_id, :author, :body, :created_at)'
        );
        $comment->execute([
            'article_id' => $articleId,
            'author' => $author,
            'body' => $body,
            'created_at' => gmdate('c'),
        ]);
        $database->commit();
        return true;
    } catch (Throwable $exception) {
        if ($database->inTransaction()) {
            $database->rollBack();
        }
        throw $exception;
    }
}

function elodie_cms_comments_for_article(int $articleId): array
{
    $statement = elodie_cms_database()->prepare(
        'SELECT author, body, created_at FROM comments
         WHERE article_id = :article_id AND status = \'approved\' ORDER BY id ASC'
    );
    $statement->execute(['article_id' => $articleId]);

    return $statement->fetchAll();
}

function elodie_cms_list_comments(string $status): array
{
    if (!in_array($status, ['pending', 'approved'], true)) {
        throw new InvalidArgumentException('Le statut des commentaires est invalide.');
    }
    $statement = elodie_cms_database()->prepare(
        'SELECT id, article_id, author, body, status, created_at FROM comments
         WHERE status = :status ORDER BY id DESC'
    );
    $statement->execute(['status' => $status]);

    return $statement->fetchAll();
}

function elodie_cms_set_comment_status(int $commentId, string $status): void
{
    if (!in_array($status, ['pending', 'approved'], true)) {
        throw new InvalidArgumentException('Le statut des commentaires est invalide.');
    }
    $statement = elodie_cms_database()->prepare(
        'UPDATE comments SET status = :status WHERE id = :id'
    );
    $statement->execute(['status' => $status, 'id' => $commentId]);
    if ($statement->rowCount() !== 1) {
        throw new RuntimeException('Le commentaire demandé est introuvable.');
    }
}

function elodie_cms_delete_comment(int $commentId): void
{
    $statement = elodie_cms_database()->prepare('DELETE FROM comments WHERE id = :id');
    $statement->execute(['id' => $commentId]);
    if ($statement->rowCount() !== 1) {
        throw new RuntimeException('Le commentaire demandé est introuvable.');
    }
}
