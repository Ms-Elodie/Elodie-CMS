<?php
require_once __DIR__ . '/security.php';

if (!elodie_cms_is_installed()) {
    header('Location: ../install.php');
    exit();
}

$legacyPage = $_GET['page'] ?? '';
$legacyPage = is_string($legacyPage) ? $legacyPage : '';
$viewPages = ['liste', 'ajouter', 'editer', 'images', 'configuration'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $destination = 'index.php';
    if (in_array($legacyPage, $viewPages, true)) {
        $destination .= '?page=' . rawurlencode($legacyPage);
        if (isset($_GET['id']) && is_string($_GET['id']) && ctype_digit($_GET['id'])) {
            $destination .= '&id=' . rawurlencode($_GET['id']);
        }
    } elseif ($legacyPage === 'blog') {
        $destination = '../index2.php';
    }
    header('Location: ' . $destination);
    exit();
}

require __DIR__ . '/verif.php';
elodie_cms_require_valid_csrf_token();
include __DIR__ . '/langues.php';
require __DIR__ . '/fonctions.php';

switch ($legacyPage) {
    case 'supprimer':
        supprimer_news();
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
    case 'ajouter':
        ajout_news();
        break;
    case 'editer':
        editer_news();
        break;
    default:
        http_response_code(400);
        exit('Action administrative inconnue.');
}
