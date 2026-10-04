<?php
require_once __DIR__ . '/security.php';

if (!elodie_cms_is_installed()) {
    header('Location: ../install.php');
    exit();
}

require_once __DIR__ . '/config.php';
$tableau = elodie_cms_read_encoded_configuration();
include __DIR__ . '/langues.php';
require_once __DIR__ . '/fonctions.php';
?>
<!DOCTYPE html>
<html lang="<?= elodie_cms_escape($GLOBALS['elodieCmsLanguage']) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= elodie_cms_escape(elodie_cms_ui('login_title')) ?> - Elodie CMS</title>
    <link rel="stylesheet" href="mobile.css">
</head>
<body class="auth-page">
    <main class="auth-shell">
        <a class="auth-brand" href="../index2.php">Elodie CMS</a>
        <section class="auth-card">
            <p class="admin-eyebrow"><?= elodie_cms_escape(elodie_cms_ui('private_area')) ?></p>
            <h1><?= elodie_cms_escape(elodie_cms_ui('login_title')) ?></h1>
            <p class="auth-help"><?= elodie_cms_escape(elodie_cms_ui('login_help')) ?></p>
            <?php connexion_blog(); ?>
        </section>
    </main>
</body>
</html>
