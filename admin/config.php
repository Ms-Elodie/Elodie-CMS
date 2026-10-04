<?php

require_once __DIR__ . '/security.php';

function lire_array($fichier)
{
    return elodie_cms_read_encoded_configuration();
}

$tableau = elodie_cms_read_encoded_configuration();
$salt = 'BwGk15l8WX';
$storedPassword = base64_decode($tableau[7] ?? '', true);
$storedLogin = base64_decode($tableau[6] ?? '', true);
$_admin_pass = $storedPassword === false ? '' : $storedPassword;
$_admin_login = $storedLogin === false
    ? ''
    : html_entity_decode($storedLogin, ENT_QUOTES | ENT_HTML5, 'UTF-8');
?>
