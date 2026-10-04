<?php

require_once __DIR__ . '/security.php';
elodie_cms_start_session();
require_once __DIR__ . '/config.php';
include __DIR__ . '/langues.php';

$username = $_SESSION['pending_totp_setup'] ?? null;
$settingUp = is_string($username);
$managingCodes = ($_GET['mode'] ?? '') === 'recovery'
    && is_string($_SESSION['_login'] ?? null)
    && is_string($_SESSION['_pass'] ?? null);

if ($managingCodes) {
    $username = $_SESSION['_login'];
    require_once __DIR__ . '/fonctions.php';
    $page = 'recovery';
}

if (!$settingUp && !$managingCodes) {
    http_response_code(403);
    exit(elodie_cms_ui('access_denied'));
}

$user = elodie_cms_user($username ?? $_SESSION['_login']);
if ($user === null) {
    http_response_code(403);
    exit(elodie_cms_ui('account_missing'));
}
if (!$settingUp
    && (!is_string($_SESSION['_pass'] ?? null)
        || !hash_equals($user['password_hash'], $_SESSION['_pass'])
        || !hash_equals($user['username'], $_SESSION['_login']))) {
    http_response_code(403);
    exit(elodie_cms_ui('invalid_admin_session'));
}

$error = '';
$codes = [];
if ($settingUp) {
    if ((int) $user['totp_enabled'] === 1) {
        http_response_code(403);
        exit(elodie_cms_ui('totp_already_enabled'));
    }
    if (!isset($_SESSION['totp_setup_secret']) || !is_string($_SESSION['totp_setup_secret'])) {
        $_SESSION['totp_setup_secret'] = elodie_cms_base32_encode(random_bytes(20));
    }
    $secret = $_SESSION['totp_setup_secret'];
    $label = rawurlencode('Elodie CMS:' . $username);
    $issuer = rawurlencode('Elodie CMS');
    $authenticatorUri = "otpauth://totp/{$label}?secret={$secret}&issuer={$issuer}&algorithm=SHA1&digits=6&period=30";

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        elodie_cms_require_valid_csrf_token();
        if (elodie_cms_login_rate_limited($username)) {
            http_response_code(429);
            exit(elodie_cms_ui('too_many_attempts'));
        }
        $submittedCode = $_POST['code'] ?? null;
        $counter = is_string($submittedCode)
            ? elodie_cms_totp_counter_for_code($secret, trim($submittedCode))
            : null;
        if ($counter === null) {
            elodie_cms_record_login_failure($username);
            $error = elodie_cms_ui('incorrect_code');
        } else {
            $codes = elodie_cms_generate_recovery_codes();
            elodie_cms_enable_totp($username, $secret, $counter, $codes);
            elodie_cms_clear_login_failures($username);
            $_SESSION['_login'] = $username;
            $_SESSION['_pass'] = $_SESSION['pending_password_hash'];
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            unset(
                $_SESSION['pending_totp_setup'],
                $_SESSION['pending_password_hash'],
                $_SESSION['totp_setup_secret']
            );
        }
    }
} else {
    if ((int) $user['totp_enabled'] !== 1 || !is_string($user['totp_secret'])) {
        http_response_code(403);
        exit(elodie_cms_ui('totp_disabled'));
    }
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        elodie_cms_require_valid_csrf_token();
        if (elodie_cms_login_rate_limited($username)) {
            http_response_code(429);
            exit(elodie_cms_ui('too_many_attempts'));
        }
        $submittedCode = $_POST['code'] ?? null;
        $codeValid = is_string($submittedCode)
            && elodie_cms_accept_totp_code($username, $user['totp_secret'], trim($submittedCode));
        if (!$codeValid && (!is_string($submittedCode)
            || !elodie_cms_consume_recovery_code($username, $submittedCode))) {
            elodie_cms_record_login_failure($username);
            $error = elodie_cms_ui('incorrect_code');
        } else {
            elodie_cms_clear_login_failures($username);
            $codes = elodie_cms_generate_recovery_codes();
            elodie_cms_store_recovery_codes($username, $codes);
        }
    }
}

?>
<!DOCTYPE html>
<html lang="<?= elodie_cms_escape($GLOBALS['elodieCmsLanguage']) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= elodie_cms_escape(elodie_cms_ui('security_title')) ?></title>
    <link rel="stylesheet" href="mobile.css">
</head>
<body class="<?= $managingCodes ? 'admin' : 'auth-page' ?>">
    <?php if ($managingCodes): ?>
        <?php include __DIR__ . '/includes/topbar.php'; ?>
        <div class="admin-layout">
            <?php include __DIR__ . '/includes/menu.php'; ?>
            <main class="admin-main" id="contenu2">
            <div class="admin-page-heading"><p class="admin-eyebrow"><?= elodie_cms_escape(elodie_cms_ui('administration')) ?></p><h1><?= elodie_cms_escape(elodie_cms_ui('recovery_codes')) ?></h1></div>
            <section class="admin-content">
    <?php else: ?>
        <main class="auth-shell">
        <a class="auth-brand" href="index.php">Elodie CMS</a>
        <section class="auth-card">
        <p class="admin-eyebrow"><?= elodie_cms_escape(elodie_cms_ui('private_area')) ?></p>
        <h1><?= elodie_cms_escape(elodie_cms_ui('setup_authenticator')) ?></h1>
    <?php endif; ?>
    <?php if ($error !== ''): ?>
        <p class="setup-error" role="alert"><?= elodie_cms_escape($error) ?></p>
    <?php endif; ?>

    <?php if ($codes !== []): ?>
        <p><?= elodie_cms_escape(elodie_cms_ui('save_codes')) ?></p>
        <ul>
            <?php foreach ($codes as $code): ?>
                <li><code><?= elodie_cms_escape($code) ?></code></li>
            <?php endforeach; ?>
        </ul>
        <p><a href="index.php"><?= elodie_cms_escape(elodie_cms_ui('continue_admin')) ?></a></p>
    <?php elseif ($settingUp): ?>
        <p><?= elodie_cms_escape(elodie_cms_ui('auth_setup_help')) ?></p>
        <p class="secret"><strong><?= elodie_cms_escape(elodie_cms_ui('secret_key')) ?></strong> <code><?= elodie_cms_escape($secret) ?></code></p>
        <p class="secret"><strong><?= elodie_cms_escape(elodie_cms_ui('uri')) ?></strong> <code><?= elodie_cms_escape($authenticatorUri) ?></code></p>
        <form method="post">
            <?= elodie_cms_csrf_input() ?>
            <label for="code"><?= elodie_cms_escape(elodie_cms_ui('enter_six_digit_code')) ?></label><br>
            <input id="code" name="code" inputmode="numeric" autocomplete="one-time-code" pattern="[0-9]{6}" maxlength="6" required>
            <button type="submit"><?= elodie_cms_escape(elodie_cms_ui('enable_show_codes')) ?></button>
        </form>
    <?php else: ?>
        <p><?= elodie_cms_escape(elodie_cms_ui('confirm_identity')) ?></p>
        <form method="post">
            <?= elodie_cms_csrf_input() ?>
            <label for="code"><?= elodie_cms_escape(elodie_cms_ui('auth_or_backup_code')) ?></label><br>
            <input id="code" name="code" autocomplete="one-time-code" maxlength="32" required>
            <button type="submit"><?= elodie_cms_escape(elodie_cms_ui('generate_codes')) ?></button>
        </form>
    <?php endif; ?>
    <?php if ($managingCodes): ?>
            </section>
            </main>
        </div>
    <?php else: ?>
        </section>
        </main>
    <?php endif; ?>
</body>
</html>
