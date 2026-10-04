<?php

require_once __DIR__ . '/security.php';
require __DIR__ . '/verif.php';
include __DIR__ . '/langues.php';
require_once __DIR__ . '/fonctions.php';
require_once __DIR__ . '/updater.php';
$page = 'update';

$checked = ($_GET['check'] ?? '') === '1';
$message = '';
$updateResult = '';
$releaseUrl = '';
$latestVersion = '';
$hasUpdate = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    elodie_cms_require_valid_csrf_token();
    if (($_POST['install'] ?? '') !== '1') {
        http_response_code(400);
        exit(elodie_cms_ui('invalid_form'));
    }
    try {
        $release = elodie_cms_fetch_latest_release();
        if (version_compare($release['version'], elodie_cms_release_version(), '<=')) {
            throw new RuntimeException(elodie_cms_ui('update_no_update'));
        }
        $backupPath = elodie_cms_update_install_release($release['version'], $release['url']);
        $_SESSION['elodie_cms_update_result'] = sprintf(elodie_cms_ui('update_complete'), $backupPath);
    } catch (RuntimeException $exception) {
        $_SESSION['elodie_cms_update_result'] = $exception->getMessage();
    }
    header('Location: update.php?check=1');
    exit();
}

elodie_cms_start_session();
$updateResult = is_string($_SESSION['elodie_cms_update_result'] ?? null)
    ? $_SESSION['elodie_cms_update_result']
    : '';
unset($_SESSION['elodie_cms_update_result']);

if ($checked) {
    try {
        $release = elodie_cms_fetch_latest_release();
        $latestVersion = $release['version'];
        $releaseUrl = 'https://github.com/Ms-Elodie/Elodie-CMS/releases/tag/'
            . rawurlencode($release['tag']);
        $hasUpdate = version_compare($latestVersion, elodie_cms_release_version(), '>');
        $message = $hasUpdate
            ? elodie_cms_ui('update_available')
            : elodie_cms_ui('up_to_date');
    } catch (RuntimeException $exception) {
        $message = $exception->getMessage();
    }
}

?>
<!DOCTYPE html>
<html lang="<?= elodie_cms_escape($GLOBALS['elodieCmsLanguage']) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= elodie_cms_escape(elodie_cms_ui('updates')) ?> - Elodie CMS</title>
    <link rel="stylesheet" href="mobile.css">
</head>
<body class="admin">
    <?php include __DIR__ . '/includes/topbar.php'; ?>
    <div class="admin-layout">
        <?php include __DIR__ . '/includes/menu.php'; ?>
        <main class="admin-main" id="contenu2">
        <div class="admin-page-heading"><p class="admin-eyebrow"><?= elodie_cms_escape(elodie_cms_ui('administration')) ?></p><h1><?= elodie_cms_escape(elodie_cms_ui('updates')) ?></h1></div>
        <section class="admin-content update-panel">
        <h2><?= elodie_cms_escape(elodie_cms_ui('updates_title')) ?></h2>
        <p><?= elodie_cms_escape(elodie_cms_ui('updates_help')) ?></p>
        <form method="get" action="update.php">
            <button type="submit" name="check" value="1"><?= elodie_cms_escape(elodie_cms_ui('check_github')) ?></button>
        </form>
        <?php if ($checked): ?>
            <p role="status"><?= elodie_cms_escape($message) ?></p>
            <?php if ($latestVersion !== ''): ?>
                <p><?= elodie_cms_escape(sprintf(elodie_cms_ui('installed_version'), elodie_cms_version(), $latestVersion)) ?></p>
            <?php endif; ?>
            <?php if ($updateResult !== ''): ?>
                <p role="status"><?= elodie_cms_escape($updateResult) ?></p>
            <?php endif; ?>
            <?php if ($hasUpdate && $_SERVER['REQUEST_METHOD'] !== 'POST'): ?>
                <p><?= elodie_cms_escape(elodie_cms_ui('update_confirmation')) ?></p>
                <form method="post" action="update.php">
                    <?= elodie_cms_csrf_input() ?>
                    <input type="hidden" name="install" value="1">
                    <button type="submit"><?= elodie_cms_escape(elodie_cms_ui('update_button')) ?></button>
                </form>
                <p><a href="<?= elodie_cms_escape($releaseUrl) ?>" target="_blank" rel="noopener noreferrer"><?= elodie_cms_escape(sprintf(elodie_cms_ui('view_update'), $latestVersion)) ?></a></p>
            <?php endif; ?>
        <?php endif; ?>
        <p><a href="https://github.com/Ms-Elodie/Elodie-CMS/releases" target="_blank" rel="noopener noreferrer"><?= elodie_cms_escape(elodie_cms_ui('all_releases')) ?></a></p>
    </section>
        </main>
    </div>
</body>
</html>
