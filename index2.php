<?php

require_once __DIR__ . '/admin/security.php';
require 'admin/fonctions.php'; 

function lire_array($fichier)
{
return elodie_cms_read_encoded_configuration();
}
$fichier='admin/configuration.txt'; 
$tableau=array();
$tableau=lire_array($fichier);
error_reporting(0);

if (!elodie_cms_is_installed()) {
    header('Location: install.php');
    exit();
}

ob_start('ob_gzhandler'); register_shutdown_function('ob_end_flush');

$allnews = elodie_cms_read_news(__DIR__ . '/news.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['comment_submit'] ?? '') === '1') {
    if (!elodie_cms_comments_enabled()) {
        http_response_code(404);
        exit('Les commentaires sont désactivés.');
    }
    elodie_cms_require_valid_csrf_token();
    $articleId = public_article_id($allnews);
    if ($articleId === null || !array_key_exists($articleId, $allnews)) {
        http_response_code(404);
        exit('Article introuvable.');
    }
    $author = elodie_cms_post_string('author');
    $body = trim(elodie_cms_post_string('body'));
    if ($author === '' || strlen($author) > 120 || preg_match('/[\x00-\x1F\x7F]/', $author)
        || $body === '' || strlen($body) > 5000
        || preg_match('//u', $author) !== 1 || preg_match('//u', $body) !== 1
        || preg_match('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', $body)) {
        http_response_code(400);
        exit('Le nom ou le commentaire est invalide (120 et 5 000 octets maximum).');
    }
    $remoteAddress = is_string($_SERVER['REMOTE_ADDR'] ?? null) ? $_SERVER['REMOTE_ADDR'] : '';
    if (!elodie_cms_add_comment($articleId, $author, $body, $remoteAddress)) {
        http_response_code(429);
        exit('Limite de commentaires atteinte. Réessayez dans quelques minutes.');
    }
    header('Location: index2.php?module=articles&page=' . max(1, (int) ($_GET['page'] ?? 1)) . '&comment=sent');
    exit();
}

$nb_messagetotal = count($allnews);

$nbPages = ceil($nb_messagetotal / 1);

$requestedPage = $_GET['page'] ?? null;
$page = is_string($requestedPage) && ctype_digit($requestedPage)
    ? max(0, min($nbPages - 1, (int) $requestedPage - 1))
    : 0;
$liste_news = array_slice($allnews, $page, 1);

$language = base64_decode($tableau[1] ?? '', true);
if (!in_array($language, ['fr', 'en', 'es', 'nl'], true)) {
    $language = 'fr';
}
include __DIR__ . '/lang/' . $language . '-lang.php';

echo'<!DOCTYPE html><!-- Systeme de Pagination Par Qwerty : http://etudiant-libre.fr.nf/ --> <html lang="'.elodie_cms_escape($language).'"><head>';

switch (is_string($_GET['module'] ?? null) ? $_GET['module'] : '')
{

case 'articles': tarticles(); break;

case 'erreurs': terreurs(); break;

default : tprofil();
}

echo'
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="apple-mobile-web-app-capable" content="yes" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<meta name="Generator" content="Elodie CMS '.elodie_cms_escape(elodie_cms_version()).'" />
<link rel="alternate" type="application/rss+xml" title="flux rss" href="rss.php" />
<link rel="stylesheet" type="text/css" href="'.base64_decode($tableau[5]).'/style.css" />';

if (base64_decode($tableau[31])=='') {
echo'<link rel="shortcut icon" type="image/x-icon" href="'.base64_decode($tableau[5]).'/Favicon.ico" sizes="16x16" />
<link rel="icon" type="image/x-icon" href="'.base64_decode($tableau[5]).'/Favicon.ico" sizes="16x16" />'
;}
else {
echo'<link rel="shortcut icon" type="image/x-icon" href="'.base64_decode($tableau[31]).'" sizes="16x16" />
<link rel="icon" type="image/x-icon" href="'.base64_decode($tableau[31]).'" sizes="16x16" />'
;}



echo'</head>';

echo'<body class="home blog"><div id="wrapper"><header id="header">';

if (base64_decode($tableau[26])=='') {echo '<h1 id="site-title">'.base64_decode($tableau[0]).'</h1>';}
else if (base64_decode($tableau[27])=='') {echo '<h1 id="site-title"><img src="'.base64_decode($tableau[26]).'" alt="'.base64_decode($tableau[0]).'"></h1>';}
else {echo '<h1 id="site-title"><img src="'.base64_decode($tableau[26]).'" alt="'.base64_decode($tableau[27]).'"></h1>';}

echo'</header><div id="content"><article style="min-height:270px !important;">

<style type="text/css">
article{
min-height:0px !important;
};
</style>';

switch (is_string($_GET['module'] ?? null) ? $_GET['module'] : '')
{

case 'articles': articles(); comments(); break;

case 'erreurs': erreurs(); break;

default : profil();
}

echo'</article>';

echo'<div id="piedpage">';

echo '<div class="older-posts">';

if (base64_decode($tableau[4])=='on') {krsort($allnews);}
else if (base64_decode($tableau[4])=='off') {};

foreach($allnews as $i => $news) { 

$i2 = $i + 1 ;

echo'<a href="'.base64_decode($tableau[5]).'/index2.php?module=articles&page=' . $i2 . '" style="padding:5px;margin-left:10px;">' . $i2 . '</a>';

}

echo'</div></div>';

echo'</div></div></div>';

echo'</body></html>';
 
?>