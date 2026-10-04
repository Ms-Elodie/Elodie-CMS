<?php

require_once __DIR__ . '/security.php';
uag_start_session();

$username = $_SESSION['pending_totp_setup'] ?? null;
$settingUp = is_string($username);
$managingCodes = ($_GET['mode'] ?? '') === 'recovery'
    && is_string($_SESSION['_login'] ?? null)
    && is_string($_SESSION['_pass'] ?? null);

if (!$settingUp && !$managingCodes) {
    http_response_code(403);
    exit('Accès refusé.');
}

$user = uag_user($username ?? $_SESSION['_login']);
if ($user === null) {
    http_response_code(403);
    exit('Compte administrateur introuvable.');
}
if (!$settingUp
    && (!is_string($_SESSION['_pass'] ?? null)
        || !hash_equals($user['password_hash'], $_SESSION['_pass'])
        || !hash_equals($user['username'], $_SESSION['_login']))) {
    http_response_code(403);
    exit('Session administrateur invalide.');
}

$error = '';
$codes = [];
if ($settingUp) {
    if ((int) $user['totp_enabled'] === 1) {
        http_response_code(403);
        exit('L’authentification à deux facteurs est déjà activée.');
    }
    if (!isset($_SESSION['totp_setup_secret']) || !is_string($_SESSION['totp_setup_secret'])) {
        $_SESSION['totp_setup_secret'] = uag_base32_encode(random_bytes(20));
    }
    $secret = $_SESSION['totp_setup_secret'];
    $label = rawurlencode('Elodie CMS:' . $username);
    $issuer = rawurlencode('Elodie CMS');
    $authenticatorUri = "otpauth://totp/{$label}?secret={$secret}&issuer={$issuer}&algorithm=SHA1&digits=6&period=30";

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        uag_require_valid_csrf_token();
        if (uag_login_rate_limited($username)) {
            http_response_code(429);
            exit('Trop de tentatives. Réessayez dans 15 minutes.');
        }
        $submittedCode = $_POST['code'] ?? null;
        $counter = is_string($submittedCode)
            ? uag_totp_counter_for_code($secret, trim($submittedCode))
            : null;
        if ($counter === null) {
            uag_record_login_failure($username);
            $error = 'Code incorrect. Vérifiez l’heure de votre téléphone et réessayez.';
        } else {
            $codes = uag_generate_recovery_codes();
            uag_enable_totp($username, $secret, $counter, $codes);
            uag_clear_login_failures($username);
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
        exit('L’authentification à deux facteurs n’est pas activée.');
    }
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        uag_require_valid_csrf_token();
        if (uag_login_rate_limited($username)) {
            http_response_code(429);
            exit('Trop de tentatives. Réessayez dans 15 minutes.');
        }
        $submittedCode = $_POST['code'] ?? null;
        $codeValid = is_string($submittedCode)
            && uag_accept_totp_code($username, $user['totp_secret'], trim($submittedCode));
        if (!$codeValid && (!is_string($submittedCode)
            || !uag_consume_recovery_code($username, $submittedCode))) {
            uag_record_login_failure($username);
            $error = 'Code incorrect.';
        } else {
            uag_clear_login_failures($username);
            $codes = uag_generate_recovery_codes();
            uag_store_recovery_codes($username, $codes);
        }
    }
}

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sécurité Elodie CMS</title>
    <style>
        body { color: #333; font: 16px sans-serif; margin: 2rem auto; max-width: 38rem; padding: 0 1rem; }
        code, input { font: 1rem monospace; }
        input { box-sizing: border-box; max-width: 100%; padding: .5rem; width: 18rem; }
        li { margin: .4rem 0; }
        .error { color: #a00; }
        .secret { overflow-wrap: anywhere; }
    </style>
</head>
<body>
    <h1><?= $settingUp ? 'Configurer Google Authenticator' : 'Codes de secours' ?></h1>
    <?php if ($error !== ''): ?>
        <p class="error"><?= uag_escape($error) ?></p>
    <?php endif; ?>

    <?php if ($codes !== []): ?>
        <p>Enregistrez ces codes maintenant. Ils ne seront plus affichés et chacun ne fonctionne qu’une seule fois.</p>
        <ul>
            <?php foreach ($codes as $code): ?>
                <li><code><?= uag_escape($code) ?></code></li>
            <?php endforeach; ?>
        </ul>
        <p><a href="index.php">Continuer vers l’administration</a></p>
    <?php elseif ($settingUp): ?>
        <p>Dans votre application d’authentification, ajoutez un compte avec cette clé (ou copiez l’URI dans une application compatible) :</p>
        <p class="secret"><strong>Clé secrète :</strong> <code><?= uag_escape($secret) ?></code></p>
        <p class="secret"><strong>URI :</strong> <code><?= uag_escape($authenticatorUri) ?></code></p>
        <form method="post">
            <?= uag_csrf_input() ?>
            <label for="code">Saisissez le code à 6 chiffres généré par l’application</label><br>
            <input id="code" name="code" inputmode="numeric" autocomplete="one-time-code" pattern="[0-9]{6}" maxlength="6" required>
            <button type="submit">Activer et afficher mes codes de secours</button>
        </form>
    <?php else: ?>
        <p>Confirmez votre identité avec un code de l’application ou un code de secours actuel. Les anciens codes de secours seront remplacés.</p>
        <form method="post">
            <?= uag_csrf_input() ?>
            <label for="code">Code d’authentification ou code de secours</label><br>
            <input id="code" name="code" autocomplete="one-time-code" maxlength="32" required>
            <button type="submit">Générer de nouveaux codes</button>
        </form>
    <?php endif; ?>
</body>
</html>
