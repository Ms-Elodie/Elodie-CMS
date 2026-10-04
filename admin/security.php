<?php

require_once __DIR__ . '/storage.php';
require_once __DIR__ . '/../lang/interface.php';

function elodie_cms_version(): string
{
    return '1.01 Cat';
}

function elodie_cms_release_version(): string
{
    return '1.01';
}

function elodie_cms_start_session(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    session_set_cookie_params([
        'path' => '/',
        'httponly' => true,
        'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'samesite' => 'Lax',
    ]);
    session_start();
}

function elodie_cms_csrf_token(): string
{
    elodie_cms_start_session();

    if (!isset($_SESSION['csrf_token']) || !is_string($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function elodie_cms_csrf_input(): string
{
    return '<input type="hidden" name="csrf_token" value="' .
        htmlspecialchars(elodie_cms_csrf_token(), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') .
        '">';
}

function elodie_cms_has_valid_csrf_token(): bool
{
    elodie_cms_start_session();
    $submitted = $_POST['csrf_token'] ?? null;

    return is_string($submitted)
        && isset($_SESSION['csrf_token'])
        && is_string($_SESSION['csrf_token'])
        && hash_equals($_SESSION['csrf_token'], $submitted);
}

function elodie_cms_require_valid_csrf_token(): void
{
    if (!elodie_cms_has_valid_csrf_token()) {
        http_response_code(403);
        exit(elodie_cms_ui('invalid_csrf'));
    }
}

function elodie_cms_store_password_hash(string $passwordHash): void
{
    $configuration = elodie_cms_read_encoded_configuration();
    if (count($configuration) < 8) {
        throw new RuntimeException('La configuration du CMS est incomplète.');
    }

    $username = base64_decode($configuration[6], true);
    if ($username === false) {
        throw new RuntimeException('Le login administrateur est invalide.');
    }
    elodie_cms_update_user_password(
        html_entity_decode($username, ENT_QUOTES | ENT_HTML5, 'UTF-8'),
        $passwordHash
    );
}

function elodie_cms_base32_encode(string $data): string
{
    $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
    $binary = '';
    foreach (str_split($data) as $character) {
        $binary .= str_pad(decbin(ord($character)), 8, '0', STR_PAD_LEFT);
    }

    $encoded = '';
    foreach (str_split($binary, 5) as $chunk) {
        $encoded .= $alphabet[bindec(str_pad($chunk, 5, '0'))];
    }

    return $encoded;
}

function elodie_cms_base32_decode(string $encoded): string
{
    $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
    $binary = '';
    foreach (str_split(strtoupper(rtrim($encoded, '='))) as $character) {
        $value = strpos($alphabet, $character);
        if ($value === false) {
            throw new InvalidArgumentException('Secret TOTP invalide.');
        }
        $binary .= str_pad(decbin($value), 5, '0', STR_PAD_LEFT);
    }

    $decoded = '';
    foreach (str_split($binary, 8) as $byte) {
        if (strlen($byte) === 8) {
            $decoded .= chr(bindec($byte));
        }
    }

    return $decoded;
}

function elodie_cms_totp_code_for_counter(string $secret, int $counter): string
{
    $counterBytes = pack('N2', intdiv($counter, 4294967296), $counter & 0xffffffff);
    $hash = hash_hmac('sha1', $counterBytes, elodie_cms_base32_decode($secret), true);
    $offset = ord($hash[19]) & 0x0f;
    $binaryCode = unpack('N', substr($hash, $offset, 4))[1] & 0x7fffffff;

    return str_pad((string) ($binaryCode % 1000000), 6, '0', STR_PAD_LEFT);
}

function elodie_cms_totp_counter_for_code(string $secret, string $submittedCode, ?int $timestamp = null): ?int
{
    if (!preg_match('/^[0-9]{6}$/', $submittedCode)) {
        return null;
    }

    $now = $timestamp ?? time();
    $currentCounter = intdiv($now, 30);
    foreach ([-1, 0, 1] as $counterOffset) {
        $counter = $currentCounter + $counterOffset;
        if (hash_equals(elodie_cms_totp_code_for_counter($secret, $counter), $submittedCode)) {
            return $counter;
        }
    }

    return null;
}

function elodie_cms_verify_totp(string $secret, string $submittedCode, ?int $timestamp = null): bool
{
    return elodie_cms_totp_counter_for_code($secret, $submittedCode, $timestamp) !== null;
}

function elodie_cms_accept_totp_code(string $username, string $secret, string $submittedCode): bool
{
    $counter = elodie_cms_totp_counter_for_code($secret, trim($submittedCode));
    if ($counter === null) {
        return false;
    }

    $statement = elodie_cms_database()->prepare(
        'UPDATE users SET totp_last_counter = :counter
         WHERE username = :username AND totp_secret = :secret
            AND (totp_last_counter IS NULL OR totp_last_counter < :counter)'
    );
    $statement->execute([
        'counter' => $counter,
        'username' => $username,
        'secret' => $secret,
    ]);

    return $statement->rowCount() === 1;
}

function elodie_cms_generate_recovery_codes(): array
{
    $codes = [];
    for ($index = 0; $index < 10; $index++) {
        $codes[] = strtoupper(bin2hex(random_bytes(5)));
    }

    return $codes;
}

function elodie_cms_store_recovery_codes(string $username, array $codes): void
{
    $database = elodie_cms_database();
    $database->beginTransaction();
    try {
        $delete = $database->prepare('DELETE FROM recovery_codes WHERE username = :username');
        $delete->execute(['username' => $username]);
        $insert = $database->prepare(
            'INSERT INTO recovery_codes (username, code_hash) VALUES (:username, :code_hash)'
        );
        foreach ($codes as $code) {
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

function elodie_cms_consume_recovery_code(string $username, string $submittedCode): bool
{
    $database = elodie_cms_database();
    $statement = $database->prepare(
        'SELECT code_hash FROM recovery_codes WHERE username = :username AND used_at IS NULL'
    );
    $statement->execute(['username' => $username]);
    foreach ($statement->fetchAll(PDO::FETCH_COLUMN) as $codeHash) {
        if (!password_verify(strtoupper(trim($submittedCode)), $codeHash)) {
            continue;
        }

        $consume = $database->prepare(
            'UPDATE recovery_codes SET used_at = :used_at
             WHERE username = :username AND code_hash = :code_hash AND used_at IS NULL'
        );
        $consume->execute([
            'used_at' => gmdate('c'),
            'username' => $username,
            'code_hash' => $codeHash,
        ]);
        return $consume->rowCount() === 1;
    }

    return false;
}

function elodie_cms_login_rate_limited(string $username): bool
{
    $remoteAddress = is_string($_SERVER['REMOTE_ADDR'] ?? null) ? $_SERVER['REMOTE_ADDR'] : '';
    $accountSubject = hash('sha256', 'account|' . strtolower($username));
    $ipSubject = hash('sha256', 'ip|' . $remoteAddress);
    $database = elodie_cms_database();
    $prune = $database->prepare('DELETE FROM login_attempts WHERE attempted_at < :cutoff');
    $prune->execute(['cutoff' => time() - 900]);
    $count = $database->prepare(
        'SELECT COUNT(*) FROM login_attempts WHERE subject_hash = :subject_hash AND attempted_at >= :cutoff'
    );
    $count->execute(['subject_hash' => $accountSubject, 'cutoff' => time() - 900]);
    $accountAttempts = (int) $count->fetchColumn();
    $count->execute(['subject_hash' => $ipSubject, 'cutoff' => time() - 900]);

    return $accountAttempts >= 10 || (int) $count->fetchColumn() >= 20;
}

function elodie_cms_record_login_failure(string $username): void
{
    $statement = elodie_cms_database()->prepare(
        'INSERT INTO login_attempts (subject_hash, attempted_at) VALUES (:subject_hash, :attempted_at)'
    );
    $remoteAddress = is_string($_SERVER['REMOTE_ADDR'] ?? null) ? $_SERVER['REMOTE_ADDR'] : '';
    foreach ([
        hash('sha256', 'account|' . strtolower($username)),
        hash('sha256', 'ip|' . $remoteAddress),
    ] as $subjectHash) {
        $statement->execute(['subject_hash' => $subjectHash, 'attempted_at' => time()]);
    }
}

function elodie_cms_clear_login_failures(string $username): void
{
    $statement = elodie_cms_database()->prepare(
        'DELETE FROM login_attempts WHERE subject_hash = :subject_hash'
    );
    $statement->execute([
        'subject_hash' => hash('sha256', 'account|' . strtolower($username)),
    ]);
}

function elodie_cms_escape(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function elodie_cms_escape_xml(string $value): string
{
    return htmlspecialchars($value, ENT_XML1 | ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function elodie_cms_escape_legacy_text(string $value): string
{
    return elodie_cms_escape(html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
}

function elodie_cms_post_string(string $key): string
{
    $value = $_POST[$key] ?? null;
    if (!is_string($value)) {
        throw new InvalidArgumentException('Champ de formulaire invalide.');
    }

    return trim($value);
}

function elodie_cms_valid_article_date(string $year, string $month, string $day): bool
{
    if (!ctype_digit($year) || !ctype_digit($month) || !ctype_digit($day)) {
        return false;
    }

    $yearValue = (int) $year;
    return $yearValue >= 1900
        && $yearValue <= 2100
        && checkdate((int) $month, (int) $day, $yearValue);
}

function elodie_cms_reencode_uploaded_image(string $sourcePath, string $mimeType): ?array
{
    $imageData = file_get_contents($sourcePath);
    if ($imageData === false) {
        throw new RuntimeException('Impossible de lire l’image envoyée.');
    }
    $image = @imagecreatefromstring($imageData);
    if (!$image instanceof GdImage) {
        return null;
    }

    $outputFormat = match ($mimeType) {
        'image/jpeg' => ['jpg', 'jpeg'],
        'image/png', 'image/bmp' => ['png', 'png'],
        'image/gif' => ['gif', 'gif'],
        default => null,
    };
    if ($outputFormat === null) {
        imagedestroy($image);
        return null;
    }

    $temporaryPath = tempnam(sys_get_temp_dir(), 'elodie-image-');
    if ($temporaryPath === false) {
        imagedestroy($image);
        throw new RuntimeException('Impossible de préparer le traitement de l’image.');
    }

    $written = match ($outputFormat[1]) {
        'jpeg' => imagejpeg($image, $temporaryPath, 90),
        'gif' => imagegif($image, $temporaryPath),
        default => imagepng($image, $temporaryPath, 6),
    };
    imagedestroy($image);
    if (!$written) {
        unlink($temporaryPath);
        throw new RuntimeException('Impossible de réencoder l’image envoyée.');
    }

    return ['path' => $temporaryPath, 'extension' => $outputFormat[0]];
}

function elodie_cms_article_date_parts(string $date): ?array
{
    if (!preg_match('/\A(\d{4})-(\d{2})-(\d{2})\z/', $date, $matches)
        || !elodie_cms_valid_article_date($matches[1], $matches[2], $matches[3])) {
        return null;
    }

    return ['year' => $matches[1], 'month' => $matches[2], 'day' => $matches[3]];
}

function elodie_cms_valid_http_url(string $url): bool
{
    $parts = parse_url($url);
    return filter_var($url, FILTER_VALIDATE_URL) !== false
        && is_array($parts)
        && in_array($parts['scheme'] ?? '', ['http', 'https'], true)
        && !empty($parts['host'])
        && !isset($parts['user'])
        && !isset($parts['pass']);
}

function elodie_cms_valid_url_setting(string $url): bool
{
    if ($url === '') {
        return true;
    }

    if (preg_match('~^https?://~i', $url)) {
        return elodie_cms_valid_http_url($url);
    }

    return !preg_match('/[\x00-\x20\\\\]/', $url)
        && !preg_match('/^[a-z][a-z0-9+.-]*:/i', $url)
        && !str_starts_with($url, '//');
}

function elodie_cms_valid_menu_url(string $url): bool
{
    if (preg_match('/\Amailto:/i', $url)) {
        return filter_var(substr($url, 7), FILTER_VALIDATE_EMAIL) !== false;
    }

    return elodie_cms_valid_url_setting($url);
}

function elodie_cms_sanitize_article_html(string $html): string
{
    if (!class_exists(DOMDocument::class)) {
        throw new RuntimeException('L’extension PHP DOM est nécessaire pour sécuriser les articles.');
    }

    $document = new DOMDocument('1.0', 'UTF-8');
    $previous = libxml_use_internal_errors(true);
    $loaded = $document->loadHTML(
        '<?xml encoding="UTF-8"><div id="elodie-cms-article">' . $html . '</div>',
        LIBXML_NONET | LIBXML_HTML_NODEFDTD
    );
    libxml_clear_errors();
    libxml_use_internal_errors($previous);

    if (!$loaded) {
        return elodie_cms_escape($html);
    }

    $container = $document->getElementById('elodie-cms-article');
    if (!$container) {
        return elodie_cms_escape($html);
    }

    $allowedTags = [
        'a', 'b', 'blockquote', 'br', 'code', 'div', 'em', 'h2', 'h3', 'h4',
        'hr', 'i', 'img', 'li', 'ol', 'p', 'pre', 's', 'strong', 'u', 'ul',
    ];
    $removedTags = [
        'audio', 'button', 'embed', 'form', 'iframe', 'input', 'link',
        'math', 'meta', 'object', 'script', 'source', 'style', 'svg',
        'textarea', 'video',
    ];

    $legacyEmoji = [
        ':)' => '🙂',
        ':(' => '🙁',
        'XD' => '😆',
        ':D' => '😄',
        ':p' => '😛',
        ':o' => '😮',
        '<3' => '❤️',
    ];
    $cleanNode = static function (DOMNode $node) use (&$cleanNode, $allowedTags, $removedTags, $legacyEmoji, $document): void {
        foreach (iterator_to_array($node->childNodes) as $child) {
            if ($child instanceof DOMElement) {
                $tag = strtolower($child->tagName);
                if (in_array($tag, $removedTags, true)) {
                    $child->parentNode?->removeChild($child);
                    continue;
                }

                $cleanNode($child);
                if (!in_array($tag, $allowedTags, true)) {
                    while ($child->firstChild) {
                        $child->parentNode?->insertBefore($child->firstChild, $child);
                    }
                    $child->parentNode?->removeChild($child);
                    continue;
                }

                $allowedAttributes = match ($tag) {
                    'a' => ['href', 'title', 'target'],
                    'img' => ['src', 'alt', 'title', 'width', 'height'],
                    'blockquote', 'div', 'h2', 'h3', 'h4', 'p' => ['style'],
                    default => [],
                };

                foreach (iterator_to_array($child->attributes) as $attribute) {
                    if (!in_array(strtolower($attribute->name), $allowedAttributes, true)) {
                        $child->removeAttribute($attribute->name);
                    }
                }

                if ($child->hasAttribute('style')) {
                    $style = trim($child->getAttribute('style'));
                    if (preg_match('/^text-align\s*:\s*(left|center|right|justify)\s*;?$/i', $style, $matches) === 1) {
                        $child->setAttribute('style', 'text-align: ' . strtolower($matches[1]));
                    } else {
                        $child->removeAttribute('style');
                    }
                }

                if ($tag === 'a') {
                    $href = $child->getAttribute('href');
                    if ($href !== '' && !preg_match('~^(https?://|mailto:|/|#|\\./)~i', $href)) {
                        $child->removeAttribute('href');
                    }
                    if (strtolower($child->getAttribute('target')) === '_blank') {
                        $child->setAttribute('rel', 'noopener noreferrer');
                    } else {
                        $child->removeAttribute('target');
                    }
                }

                if ($tag === 'img') {
                    $src = $child->getAttribute('src');
                    $legacyImageEmoji = [
                        'content.png' => '🙂',
                        'embarrassed.png' => '🙁',
                        'grin.png' => '😆',
                        'laughing.png' => '😄',
                        'yuck.png' => '😛',
                        'gasp.png' => '😮',
                        'hearteyes.png' => '❤️',
                    ];
                    $legacyImageName = strtolower(basename(parse_url($src, PHP_URL_PATH) ?: ''));
                    if (str_contains(strtolower($src), '/smileys/')
                        && isset($legacyImageEmoji[$legacyImageName])) {
                        $child->parentNode?->replaceChild(
                            $document->createTextNode($legacyImageEmoji[$legacyImageName]),
                            $child
                        );
                        continue;
                    }
                    if (!preg_match('~^(https?://|/|\\./)~i', $src)) {
                        $child->parentNode?->removeChild($child);
                        continue;
                    }
                    foreach (['width', 'height'] as $dimension) {
                        $value = $child->getAttribute($dimension);
                        if ($value !== '' && (!ctype_digit($value) || (int) $value > 2000)) {
                            $child->removeAttribute($dimension);
                        }
                    }
                }
            } elseif ($child instanceof DOMText) {
                $child->data = preg_replace_callback(
                    '/(?<![\p{L}\p{N}])(:\)|:\(|XD|:D|:p|:o|<3)(?![\p{L}\p{N}])/u',
                    static function (array $match) use ($legacyEmoji): string {
                        return $legacyEmoji[$match[0]];
                    },
                    $child->data
                ) ?? $child->data;
            } else {
                $cleanNode($child);
            }
        }
    };

    $cleanNode($container);

    $result = '';
    foreach ($container->childNodes as $child) {
        $result .= $document->saveHTML($child);
    }

    return $result;
}
