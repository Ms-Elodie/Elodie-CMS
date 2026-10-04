<?php
require_once __DIR__ . '/security.php';

if (!elodie_cms_is_installed()) {
    header('Location: ../install.php');
    exit();
}

require __DIR__ . '/verif.php';
include __DIR__ . '/langues.php';
require __DIR__ . '/fonctions.php';
require __DIR__ . '/pages.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    elodie_cms_require_valid_csrf_token();
}

$requestedPage = $_GET['page'] ?? '';
$page = is_string($requestedPage) ? $requestedPage : '';
$pageTitles = [
    '' => elodie_cms_ui('dashboard'),
    'liste' => Articles,
    'ajouter' => Ecrire,
    'editer' => Editer,
    'images' => Images,
    'configuration' => Configuration,
    'theme' => elodie_cms_ui('theme_title'),
    'pages' => elodie_cms_ui('pages_title'),
    'page-new' => elodie_cms_ui('page_new'),
    'page-edit' => elodie_cms_ui('page_edit'),
    'supprimer' => Articles,
    'upload' => Images,
    'delete' => Images,
];
if (!array_key_exists($page, $pageTitles)) {
    http_response_code(404);
    $page = '';
}
if ($page === 'editer'
    && (!is_string($_GET['id'] ?? null) || !ctype_digit($_GET['id']))) {
    header('Location: index.php?page=liste');
    exit();
}
if ($page === 'page-edit'
    && (!is_string($_GET['id'] ?? null) || !ctype_digit($_GET['id']))) {
    header('Location: index.php?page=pages');
    exit();
}
if (in_array($page, ['configuration', 'theme'], true) && $_SERVER['REQUEST_METHOD'] === 'POST'
    && ($_GET['id'] ?? '') === '2') {
    if ($page === 'configuration') {
        configuration();
        exit();
    }
}
if ($page === 'theme' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    theme_configuration();
    exit();
}

$pageTitle = $pageTitles[$page];
$activePage = match ($page) {
    'supprimer' => 'liste',
    'upload', 'delete' => 'images',
    'page-new', 'page-edit' => 'pages',
    default => $page,
};
?>
<!DOCTYPE html>
<html lang="<?= elodie_cms_escape($GLOBALS['elodieCmsLanguage']) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="<?= elodie_cms_escape(elodie_cms_ui('administration')) ?> Elodie CMS">
    <title><?= elodie_cms_escape((string) $pageTitle) ?> - Elodie CMS</title>
    <link rel="stylesheet" href="mobile.css">
    <script src="js/article-editor.js" defer></script>
    <script src="js/menu-editor.js" defer></script>
    <script src="js/theme-editor.js" defer></script>
</head>
<body class="admin">
    <?php include __DIR__ . '/includes/topbar.php'; ?>
    <div class="admin-layout">
        <?php include __DIR__ . '/includes/menu.php'; ?>
        <main class="admin-main" id="contenu2">
            <div class="admin-page-heading">
                <p class="admin-eyebrow"><?= elodie_cms_escape(elodie_cms_ui('administration')) ?></p>
                <h1><?= elodie_cms_escape((string) $pageTitle) ?></h1>
            </div>
            <section class="admin-content">
                <?php if (in_array($page, ['configuration', 'theme', 'pages'], true) && ($_GET['saved'] ?? '') === '1'): ?>
                    <p class="admin-notice" role="status"><?= elodie_cms_escape(elodie_cms_ui('settings_saved')) ?></p>
                <?php endif; ?>
                <?php
                switch ($page) {
                    case 'liste':
                        liste_news();
                        break;
                    case 'supprimer':
                        supprimer_news();
                        break;
                    case 'ajouter':
                        ajout_news();
                        break;
                    case 'editer':
                        editer_news();
                        break;
                    case 'images':
                        formulaire_images();
                        images();
                        break;
                    case 'upload':
                        envoyer_images();
                        break;
                    case 'delete':
                        supprimer_images();
                        break;
                    case 'configuration':
                        configuration();
                        break;
                    case 'theme':
                        theme_configuration();
                        break;
                    case 'pages':
                    case 'page-new':
                    case 'page-edit':
                        elodie_cms_admin_pages();
                        break;
                    default:
                        accueil();
                }
                ?>
            </section>
        </main>
    </div>
</body>
</html>
