<?php

require_once __DIR__ . '/admin/security.php';
elodie_cms_start_session();

if (elodie_cms_is_installed()) {
    http_response_code(404);
    $GLOBALS['elodieCmsLanguage'] = 'fr';
    require_once __DIR__ . '/lang/interface.php';
    exit(elodie_cms_ui('install_done'));
}

$languages = [
    'fr' => 'Français',
    'en' => 'English',
    'es' => 'Español',
    'nl' => 'Nederlands',
    'de' => 'Deutsch',
    'it' => 'Italiano',
    'pt' => 'Português',
];
$requestedLanguage = $_POST['language'] ?? 'fr';
$GLOBALS['elodieCmsLanguage'] = is_string($requestedLanguage)
    && in_array($requestedLanguage, ['de', 'en', 'es', 'fr', 'it', 'nl', 'pt'], true)
        ? $requestedLanguage
        : 'fr';
require_once __DIR__ . '/lang/interface.php';
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
    elodie_cms_require_valid_csrf_token();

    foreach ($values as $key => $default) {
        if ($key === 'password') {
            continue;
        }
        $posted = $_POST[$key] ?? $default;
        if (!is_string($posted)) {
            $errors[] = elodie_cms_ui('invalid_form');
            continue;
        }
        $values[$key] = trim($posted);
    }
    $password = $_POST['password'] ?? null;
    if (!is_string($password)) {
        $errors[] = elodie_cms_ui('invalid_password');
        $password = '';
    }

    if (!array_key_exists($values['language'], $languages)) {
        $errors[] = elodie_cms_ui('invalid_language');
    }
    if (!in_array($values['pagination'], ['on', 'off'], true)
        || !in_array($values['rewriting'], ['on', 'on2', 'off'], true)
        || !in_array($values['admin_link'], ['on', 'off'], true)
        || !in_array($values['comments'], ['on', 'off'], true)
        || !in_array($values['date_format'], ['on', 'off'], true)) {
        $errors[] = elodie_cms_ui('invalid_option');
    }
    if ($values['title'] === '' || strlen($values['title']) > 480
        || $values['author'] === '' || strlen($values['author']) > 480
        || $values['login'] === '' || strlen($values['login']) > 480) {
        $errors[] = elodie_cms_ui('install_required');
    }
    if (strlen($password) < 12 || strlen($password) > 72) {
        $errors[] = elodie_cms_ui('password_length');
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
        $errors[] = elodie_cms_ui('invalid_site_url');
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
        elodie_cms_write_encoded_configuration($encodedSettings);

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
<html lang="<?= elodie_cms_escape($GLOBALS['elodieCmsLanguage']) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= elodie_cms_escape(elodie_cms_ui('install_title')) ?> <?= elodie_cms_escape(elodie_cms_version()) ?></title>
    <link rel="stylesheet" href="admin/mobile.css">
</head>
<body class="setup-page">
    <main class="setup-shell">
    <header class="setup-heading">
        <p class="admin-eyebrow"><?= elodie_cms_escape(elodie_cms_ui('welcome')) ?></p>
        <h1><?= elodie_cms_escape(elodie_cms_ui('install_title')) ?> <span><?= elodie_cms_escape(elodie_cms_version()) ?></span></h1>
        <p><?= elodie_cms_escape(elodie_cms_ui('install_intro')) ?></p>
    </header>
    <section class="setup-card">
    <?php foreach ($errors as $error): ?>
        <p class="setup-error" role="alert"><?= elodie_cms_escape($error) ?></p>
    <?php endforeach; ?>
    <form method="post" action="install.php">
        <?= elodie_cms_csrf_input() ?>
        <div class="setup-grid">
        <div class="setup-field">
        <label for="title"><?= elodie_cms_escape(elodie_cms_ui('site_title')) ?></label>
        <input id="title" name="title" maxlength="120" required value="<?= elodie_cms_escape($values['title']) ?>">
        </div>
        <div class="setup-field">
        <label for="language"><?= elodie_cms_escape(elodie_cms_ui('language')) ?></label>
        <select id="language" name="language">
            <?php foreach ($languages as $language => $languageName): ?>
                <option value="<?= elodie_cms_escape($language) ?>" <?= $values['language'] === $language ? 'selected' : '' ?>><?= elodie_cms_escape($languageName) ?></option>
            <?php endforeach; ?>
        </select>
        </div>
        <div class="setup-field">
        <label for="author"><?= elodie_cms_escape(elodie_cms_ui('manager')) ?></label>
        <input id="author" name="author" maxlength="120" required value="<?= elodie_cms_escape($values['author']) ?>">
        </div>
        <div class="setup-field">
        <label for="comments"><?= elodie_cms_escape(elodie_cms_ui('internal_comments')) ?></label>
        <select id="comments" name="comments">
            <option value="off" <?= $values['comments'] === 'off' ? 'selected' : '' ?>><?= elodie_cms_escape(elodie_cms_ui('disabled')) ?></option>
            <option value="on" <?= $values['comments'] === 'on' ? 'selected' : '' ?>><?= elodie_cms_escape(elodie_cms_ui('enabled')) ?></option>
        </select>
        </div>
        <div class="setup-field">
        <label for="pagination"><?= elodie_cms_escape(elodie_cms_ui('pagination')) ?></label>
        <select id="pagination" name="pagination">
            <option value="on" <?= $values['pagination'] === 'on' ? 'selected' : '' ?>><?= elodie_cms_escape(elodie_cms_ui('enabled')) ?></option>
            <option value="off" <?= $values['pagination'] === 'off' ? 'selected' : '' ?>><?= elodie_cms_escape(elodie_cms_ui('disabled')) ?></option>
        </select>
        </div>
        <div class="setup-field">
        <label for="site_url"><?= elodie_cms_escape(elodie_cms_ui('site_address')) ?></label>
        <input id="site_url" name="site_url" type="url" required value="<?= elodie_cms_escape($values['site_url']) ?>">
        </div>
        <div class="setup-field">
        <label for="login"><?= elodie_cms_escape(elodie_cms_ui('admin_login')) ?></label>
        <input id="login" name="login" maxlength="120" required autocomplete="username" value="<?= elodie_cms_escape($values['login']) ?>">
        </div>
        <div class="setup-field">
        <label for="password"><?= elodie_cms_escape(elodie_cms_ui('password_minimum')) ?></label>
        <input id="password" name="password" type="password" minlength="12" maxlength="72" required autocomplete="new-password">
        </div>
        <div class="setup-field">
        <label for="rewriting"><?= elodie_cms_escape(elodie_cms_ui('url_rewriting')) ?></label>
        <select id="rewriting" name="rewriting">
            <option value="on2" <?= $values['rewriting'] === 'on2' ? 'selected' : '' ?>><?= elodie_cms_escape(elodie_cms_ui('enabled')) ?></option>
            <option value="off" <?= $values['rewriting'] === 'off' ? 'selected' : '' ?>><?= elodie_cms_escape(elodie_cms_ui('disabled')) ?></option>
        </select>
        </div>
        <div class="setup-field">
        <label for="admin_link"><?= elodie_cms_escape(elodie_cms_ui('admin_link')) ?></label>
        <select id="admin_link" name="admin_link">
            <option value="on" <?= $values['admin_link'] === 'on' ? 'selected' : '' ?>><?= elodie_cms_escape(elodie_cms_ui('visible')) ?></option>
            <option value="off" <?= $values['admin_link'] === 'off' ? 'selected' : '' ?>><?= elodie_cms_escape(elodie_cms_ui('hidden')) ?></option>
        </select>
        </div>
        <div class="setup-field">
        <label for="date_format"><?= elodie_cms_escape(elodie_cms_ui('date_format')) ?></label>
        <select id="date_format" name="date_format">
            <option value="on" <?= $values['date_format'] === 'on' ? 'selected' : '' ?>><?= elodie_cms_escape(elodie_cms_ui('literal')) ?></option>
            <option value="off" <?= $values['date_format'] === 'off' ? 'selected' : '' ?>><?= elodie_cms_escape(elodie_cms_ui('numeric')) ?></option>
        </select>
        </div>
        </div>
        <button class="setup-submit" type="submit"><?= elodie_cms_escape(elodie_cms_ui('install_button')) ?></button>
    </form>
    </section>
    </main>
</body>
</html>
