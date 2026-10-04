<?php

require_once __DIR__ . '/admin/security.php';
uag_start_session();

if (uag_is_installed()) {
    http_response_code(404);
    exit('Installation déjà effectuée.');
}

$languages = ['fr', 'en', 'es', 'nl'];
$values = [
    'title' => '',
    'language' => 'fr',
    'author' => '',
    'comments' => 'off',
    'pagination' => 'on',
    'site_url' => '',
    'login' => '',
    'password' => '',
    'rewriting' => 'on2',
    'admin_link' => 'off',
    'date_format' => 'on',
];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    uag_require_valid_csrf_token();

    foreach ($values as $key => $default) {
        if ($key === 'password') {
            continue;
        }
        $posted = $_POST[$key] ?? $default;
        if (!is_string($posted)) {
            $errors[] = 'Les données du formulaire sont invalides.';
            continue;
        }
        $values[$key] = trim($posted);
    }
    $password = $_POST['password'] ?? null;
    if (!is_string($password)) {
        $errors[] = 'Le mot de passe est invalide.';
        $password = '';
    }

    if (!in_array($values['language'], $languages, true)) {
        $errors[] = 'La langue sélectionnée est invalide.';
    }
    if (!in_array($values['pagination'], ['on', 'off'], true)
        || !in_array($values['rewriting'], ['on', 'on2', 'off'], true)
        || !in_array($values['admin_link'], ['on', 'off'], true)
        || !in_array($values['comments'], ['on', 'off'], true)
        || !in_array($values['date_format'], ['on', 'off'], true)) {
        $errors[] = 'Une option de configuration est invalide.';
    }
    if ($values['title'] === '' || strlen($values['title']) > 480
        || $values['author'] === '' || strlen($values['author']) > 480
        || $values['login'] === '' || strlen($values['login']) > 480) {
        $errors[] = 'Le titre, le nom du responsable et le login sont obligatoires (120 caractères maximum).';
    }
    if (strlen($password) < 12 || strlen($password) > 72) {
        $errors[] = 'Le mot de passe doit contenir entre 12 et 72 octets.';
    }

    $siteUrl = $values['site_url'];
    $siteParts = parse_url($siteUrl);
    if (!filter_var($siteUrl, FILTER_VALIDATE_URL)
        || !is_array($siteParts)
        || !in_array($siteParts['scheme'] ?? '', ['http', 'https'], true)
        || empty($siteParts['host'])
        || isset($siteParts['user'])
        || isset($siteParts['pass'])
        || isset($siteParts['query'])
        || isset($siteParts['fragment'])) {
        $errors[] = 'L’adresse du site doit être une URL HTTP ou HTTPS valide.';
    }

    if ($errors === []) {
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        $settings = array_fill(0, 32, '');
        $settings[0] = htmlentities($values['title'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $settings[1] = $values['language'];
        $settings[2] = htmlentities($values['author'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $settings[3] = $values['comments'];
        $settings[4] = $values['pagination'];
        $settings[5] = rtrim($siteUrl, '/');
        $settings[6] = htmlentities($values['login'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $settings[7] = $passwordHash;
        $settings[8] = $values['rewriting'];
        $settings[9] = $values['admin_link'];
        $settings[10] = $values['date_format'];
        $encodedSettings = array_map('base64_encode', $settings);
        $encodedSettings[] = '';
        uag_write_encoded_configuration($encodedSettings);

        header('Location: index.php');
        exit();
    }
} else {
    $serverName = $_SERVER['SERVER_NAME'] ?? 'localhost';
    if (!is_string($serverName) || !preg_match('/^[a-zA-Z0-9.-]+$/', $serverName)) {
        $serverName = 'localhost';
    }
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $values['site_url'] = $scheme . '://' . $serverName;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Installation de Elodie CMS</title>
    <style>
        body { color: #444; font: 16px sans-serif; margin: 2rem auto; max-width: 42rem; padding: 0 1rem; }
        label { display: block; margin-top: 1rem; }
        input, select { box-sizing: border-box; max-width: 100%; padding: .5rem; width: 100%; }
        .error { color: #a00; }
    </style>
</head>
<body>
    <h1>Installation de Elodie CMS</h1>
    <?php foreach ($errors as $error): ?>
        <p class="error"><?= uag_escape($error) ?></p>
    <?php endforeach; ?>
    <form method="post" action="install.php">
        <?= uag_csrf_input() ?>
        <label for="title">Titre du site</label>
        <input id="title" name="title" maxlength="120" required value="<?= uag_escape($values['title']) ?>">
        <label for="language">Langue</label>
        <select id="language" name="language">
            <?php foreach ($languages as $language): ?>
                <option value="<?= uag_escape($language) ?>" <?= $values['language'] === $language ? 'selected' : '' ?>><?= uag_escape($language) ?></option>
            <?php endforeach; ?>
        </select>
        <label for="author">Responsable</label>
        <input id="author" name="author" maxlength="120" required value="<?= uag_escape($values['author']) ?>">
        <label for="comments">Commentaires internes</label>
        <select id="comments" name="comments">
            <option value="off" <?= $values['comments'] === 'off' ? 'selected' : '' ?>>Désactivés</option>
            <option value="on" <?= $values['comments'] === 'on' ? 'selected' : '' ?>>Activés</option>
        </select>
        <label for="pagination">Pagination</label>
        <select id="pagination" name="pagination">
            <option value="on" <?= $values['pagination'] === 'on' ? 'selected' : '' ?>>Activée</option>
            <option value="off" <?= $values['pagination'] === 'off' ? 'selected' : '' ?>>Désactivée</option>
        </select>
        <label for="site_url">Adresse du site</label>
        <input id="site_url" name="site_url" type="url" required value="<?= uag_escape($values['site_url']) ?>">
        <label for="login">Login administrateur</label>
        <input id="login" name="login" maxlength="120" required autocomplete="username" value="<?= uag_escape($values['login']) ?>">
        <label for="password">Mot de passe (12 à 72 octets)</label>
        <input id="password" name="password" type="password" minlength="12" maxlength="72" required autocomplete="new-password">
        <label for="rewriting">Réécriture des URL</label>
        <select id="rewriting" name="rewriting">
            <option value="on2" <?= $values['rewriting'] === 'on2' ? 'selected' : '' ?>>Activée</option>
            <option value="off" <?= $values['rewriting'] === 'off' ? 'selected' : '' ?>>Désactivée</option>
        </select>
        <label for="admin_link">Lien vers l’administration</label>
        <select id="admin_link" name="admin_link">
            <option value="on" <?= $values['admin_link'] === 'on' ? 'selected' : '' ?>>Visible</option>
            <option value="off" <?= $values['admin_link'] === 'off' ? 'selected' : '' ?>>Masqué</option>
        </select>
        <label for="date_format">Format de date</label>
        <select id="date_format" name="date_format">
            <option value="on" <?= $values['date_format'] === 'on' ? 'selected' : '' ?>>Littéral</option>
            <option value="off" <?= $values['date_format'] === 'off' ? 'selected' : '' ?>>Numérique</option>
        </select>
        <p><button type="submit">Installer</button></p>
    </form>
</body>
</html>
