<?php

require_once __DIR__ . '/security.php';
uag_start_session();

// on inclu la page de config
include 'config.php';
include 'langues.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !uag_has_valid_csrf_token()) {
    http_response_code(403);
    exit('Jeton de sécurité invalide.');
}

$login = $_POST['login'] ?? null;
$password = $_POST['mdp'] ?? null;
if (!is_string($login) || !is_string($password) || $login === '' || $password === '') {
    include 'connexion.php';
    exit();
}

$login = trim($login);
if (strlen($login) > 120 || strlen($password) > 4096) {
    uag_record_login_failure(substr($login, 0, 120));
    http_response_code(400);
    exit('Identifiant ou mot de passe invalide.');
}
if (uag_login_rate_limited($login)) {
    http_response_code(429);
    exit('Trop de tentatives. Réessayez dans 15 minutes.');
}

$user = uag_user($login);
$passwordHash = $user['password_hash'] ?? '';
$legacyPassword = str_starts_with($passwordHash, 'legacy-sha1:');
if ($legacyPassword) {
    $passwordValid = hash_equals(
        substr($passwordHash, strlen('legacy-sha1:')),
        sha1($password . $salt)
    );
} else {
    $passwordValid = ((password_get_info($passwordHash)['algoName'] ?? '') !== 'bcrypt'
        || strlen($password) <= 72)
        && password_verify($password, $passwordHash);
}
if ($passwordValid && $legacyPassword && strlen($password) <= 72) {
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
    uag_update_user_password($login, $passwordHash);
}

if (!hash_equals($_admin_login, $login) || !$passwordValid || $user === null) {
    uag_record_login_failure($login);
    include 'connexion.php';
    exit();
}

if ((int) $user['totp_enabled'] === 1) {
    $code = $_POST['code'] ?? null;
    $codeValid = is_string($code)
        && is_string($user['totp_secret'])
        && uag_accept_totp_code($login, $user['totp_secret'], trim($code));
    if (!$codeValid && (!is_string($code) || !uag_consume_recovery_code($login, $code))) {
        uag_record_login_failure($login);
        http_response_code(401);
        include 'connexion.php';
        exit();
    }
    uag_complete_login($login, $passwordHash);
}

if ((int) ($user['totp_enabled'] ?? 0) !== 1) {
    session_regenerate_id(true);
    $_SESSION['pending_totp_setup'] = $login;
    $_SESSION['pending_password_hash'] = $passwordHash;
    uag_clear_login_failures($login);
    header('Location: mfa.php');
    exit();
}

function uag_complete_login(string $login, string $passwordHash): void
{
    uag_clear_login_failures($login);
    session_regenerate_id(true);
    $_SESSION['_login'] = $login;
    $_SESSION['_pass'] = $passwordHash;
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    header('Location: index.php');
    exit();
}

?>
