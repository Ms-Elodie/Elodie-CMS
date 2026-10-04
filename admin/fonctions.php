<?php
require_once __DIR__ . '/security.php';

/* BLOG */

function lien1()   {

$fichier='admin/configuration.txt';
$tableau=array();
$tableau=lire_array($fichier);

echo'<iframe src="'.base64_decode($tableau[23]).'" style="min-width:100%;min-height:550px !important;background:black !important;background-image:none;"></iframe>

<a href="'.base64_decode($tableau[23]).'" target="cwindow"></a>';

}

function lien2()   {

$fichier='admin/configuration.txt';
$tableau=array();
$tableau=lire_array($fichier);

echo'<iframe src="'.base64_decode($tableau[25]).'" style="min-width:100%;min-height:550px !important;background:black !important;background-image:none;"></iframe>

<a href="'.base64_decode($tableau[25]).'" target="cwindow"></a>';

}

function RSS()   {

$fichier='admin/configuration.txt';
$tableau=array();
$tableau=lire_array($fichier);

echo'<iframe src="'.base64_decode($tableau[5]).'/rss.php" style="min-width:100%;min-height:550px !important;background:black !important;background-image:none;"></iframe>

<a href="'.base64_decode($tableau[5]).'/rss.php" target="cwindow"></a>';

}

function blog2()   {

$fichier='admin/configuration.txt';
$tableau=array();
$tableau=lire_array($fichier);

echo'

<iframe src="'.base64_decode($tableau[5]).'/index2.php" style="min-width:100%;min-height:550px !important;background:black !important;background-image:none;"></iframe>

<a href="'.base64_decode($tableau[5]).'/index2.php" target="cwindow"></a>

';

}

function blog()   {

$fichier='configuration.txt';
$tableau=array();
$tableau=lire_array($fichier);

echo'

<iframe src="'.base64_decode($tableau[5]).'/index2.php" style="min-width:100%;min-height:550px !important;background:black !important;background-image:none;"></iframe>

<a href="'.base64_decode($tableau[5]).'/index2.php" target="cwindow"></a>';

}

/* erreurs */

function terreurs()   {

include ('admin/includes/config1.php');

echo'<title>'.base64_decode($tableau[0]).' - 404</title>';

}

function erreurs()   {

include ('admin/includes/config1.php');

echo'<h2>404</h2><center><img src="'.base64_decode($tableau[5]).'/404.gif" alt="404" width="180px"></center><div id="article" style="padding-left:10px"><br/><h1>'.error.'</h1>';

}

/* Articles */

function tarticles()  {

include ('admin/includes/config1.php');

ob_start('ob_gzhandler'); register_shutdown_function('ob_end_flush');

$allnews = elodie_cms_read_news(__DIR__ . '/../news.php');

$nb_messagetotal = count($allnews);

$nbPages = ceil($nb_messagetotal / 1);

$requestedPage = $_GET['page'] ?? null;
$page = is_string($requestedPage) && ctype_digit($requestedPage)
    ? max(0, min($nbPages - 1, (int) $requestedPage - 1))
    : 0;

$liste_news = array_slice($allnews, max(0, $page ?? 0), 1);

if(!empty($liste_news)) { foreach($liste_news as $id => $news) {

echo'<title>'.elodie_cms_escape_legacy_text(base64_decode($tableau[0])).' - '.elodie_cms_escape_legacy_text($news['titre']).'</title><meta name="Description" content="'.elodie_cms_escape_legacy_text($news['chapo']).'">';	} }

else { echo'<title>'.base64_decode($tableau[0]).' - '.Informations.'</title><meta name="Description" content="'.PasdeNews.'">'; };

}

function articles()  {

include ('admin/includes/config1.php');

ob_start('ob_gzhandler'); register_shutdown_function('ob_end_flush');

$allnews = elodie_cms_read_news(__DIR__ . '/../news.php');

$nb_messagetotal = count($allnews);

$nbPages = ceil($nb_messagetotal / 1);

$requestedPage = $_GET['page'] ?? null;
$page = is_string($requestedPage) && ctype_digit($requestedPage)
    ? max(0, min($nbPages - 1, (int) $requestedPage - 1))
    : 0;

$liste_news = array_slice($allnews, max(0, $page ?? 0), 1);


if(!empty($liste_news)) { foreach($liste_news as $id => $news) {

echo'<h2><a href=""><strong>'.elodie_cms_escape_legacy_text($news['titre']).' '.Par.' '.elodie_cms_escape_legacy_text(base64_decode($tableau[2])).' - ';

if (base64_decode($tableau[1])=='fr') { 

if (base64_decode($tableau[10])=='on') { 

echo elodie_cms_escape_legacy_text($news['jour']).' ';

if     ($news['mois']=='01') {echo ''.Janvier.'' ;}
elseif ($news['mois']=='02') {echo ''.Fevrier.'' ;}
elseif ($news['mois']=='03') {echo ''.Mars.'' ;}
elseif ($news['mois']=='04') {echo ''.Avril.'' ;}
elseif ($news['mois']=='05') {echo ''.Mai.'' ;}
elseif ($news['mois']=='06') {echo ''.Juin.'' ;}
elseif ($news['mois']=='07') {echo ''.Juillet.'' ;}
elseif ($news['mois']=='08') {echo ''.Aout.'' ;}
elseif ($news['mois']=='09') {echo ''.Septembre.'' ;}
elseif ($news['mois']=='10') {echo ''.Octobre.'' ;}
elseif ($news['mois']=='11') {echo ''.Novembre.'' ;}
elseif ($news['mois']=='12') {echo ''.Decembre.'' ;}

echo ' '.elodie_cms_escape_legacy_text($news['annee']).' ';

 }

elseif (base64_decode($tableau[10])=='off') { echo' '.elodie_cms_escape_legacy_text($news['jour']).'-'.elodie_cms_escape_legacy_text($news['mois']).'-'.elodie_cms_escape_legacy_text($news['annee']).' '; } }

else { 

if (base64_decode($tableau[10])=='on') { 

echo elodie_cms_escape_legacy_text($news['annee']).' ';

if     ($news['mois']=='01') {echo ''.Janvier.'' ;}
elseif ($news['mois']=='02') {echo ''.Fevrier.'' ;}
elseif ($news['mois']=='03') {echo ''.Mars.'' ;}
elseif ($news['mois']=='04') {echo ''.Avril.'' ;}
elseif ($news['mois']=='05') {echo ''.Mai.'' ;}
elseif ($news['mois']=='06') {echo ''.Juin.'' ;}
elseif ($news['mois']=='07') {echo ''.Juillet.'' ;}
elseif ($news['mois']=='08') {echo ''.Aout.'' ;}
elseif ($news['mois']=='09') {echo ''.Septembre.'' ;}
elseif ($news['mois']=='10') {echo ''.Octobre.'' ;}
elseif ($news['mois']=='11') {echo ''.Novembre.'' ;}
elseif ($news['mois']=='12') {echo ''.Decembre.'' ;}

echo ' '.elodie_cms_escape_legacy_text($news['jour']).' ';

 }

elseif (base64_decode($tableau[10])=='off') { echo' '.elodie_cms_escape_legacy_text($news['annee']).'-'.elodie_cms_escape_legacy_text($news['mois']).'-'.elodie_cms_escape_legacy_text($news['jour']).' '; } }

echo'</strong></a></h2><div id="article" style="padding-left:10px">'.elodie_cms_sanitize_article_html($news['contenu']).'</div>';

}
}

else { header('Location: erreur.php'); }

echo'</article><article style="min-height:0px;font-weight:bold;text-align:center;">'.Note.' :';

if ($news['note']=='0') {

echo'
<img src="/admin/images/etoile0.png" alt="0">
<img src="/admin/images/etoile0.png" alt="0">
<img src="/admin/images/etoile0.png" alt="0">
<img src="/admin/images/etoile0.png" alt="0">
<img src="/admin/images/etoile0.png" alt="0">
';
}

elseif ($news['note']=='1') {

echo'
<img src="/admin/images/etoile1.png" alt="1">
<img src="/admin/images/etoile0.png" alt="0">
<img src="/admin/images/etoile0.png" alt="0">
<img src="/admin/images/etoile0.png" alt="0">
<img src="/admin/images/etoile0.png" alt="0">
';
}

elseif ($news['note']=='2') {

echo'
<img src="/admin/images/etoile1.png" alt="1">
<img src="/admin/images/etoile1.png" alt="1">
<img src="/admin/images/etoile0.png" alt="0">
<img src="/admin/images/etoile0.png" alt="0">
<img src="/admin/images/etoile0.png" alt="0">
';
}

elseif ($news['note']=='3') {

echo'
<img src="/admin/images/etoile1.png" alt="1">
<img src="/admin/images/etoile1.png" alt="1">
<img src="/admin/images/etoile1.png" alt="1">
<img src="/admin/images/etoile0.png" alt="0">
<img src="/admin/images/etoile0.png" alt="0">
';
}

elseif ($news['note']=='4') {

echo'
<img src="/admin/images/etoile1.png" alt="1">
<img src="/admin/images/etoile1.png" alt="1">
<img src="/admin/images/etoile1.png" alt="1">
<img src="/admin/images/etoile1.png" alt="1">
<img src="/admin/images/etoile0.png" alt="0">
';
}

elseif ($news['note']=='5') {

echo'
<img src="/admin/images/etoile1.png" alt="1">
<img src="/admin/images/etoile1.png" alt="1">
<img src="/admin/images/etoile1.png" alt="1">
<img src="/admin/images/etoile1.png" alt="1">
<img src="/admin/images/etoile1.png" alt="1">
';
}

elseif ($news['note']=='Off') {

echo'
Off
';
}

else {

echo'
Off
';
}
}

function public_article_id(array $articles): ?int
{
    if ($articles === []) {
        return null;
    }
    $requestedPage = $_GET['page'] ?? null;
    $offset = is_string($requestedPage) && ctype_digit($requestedPage)
        ? max(0, (int) $requestedPage - 1)
        : 0;
    $offset = min($offset, count($articles) - 1);
    $selected = array_slice($articles, $offset, 1, true);

    return $selected === [] ? null : (int) array_key_first($selected);
}

function comments(): void
{
    echo '</article>';
    if (!elodie_cms_comments_enabled()) {
        return;
    }

    $articleId = public_article_id(elodie_cms_read_news());
    if ($articleId === null) {
        return;
    }

    echo '<article class="comments"><h2>Commentaires</h2>';
    if (($_GET['comment'] ?? '') === 'sent') {
        echo '<p role="status">Votre commentaire a été envoyé et sera visible après validation.</p>';
    }
    foreach (elodie_cms_comments_for_article($articleId) as $comment) {
        echo '<section class="comment"><h3>' . elodie_cms_escape($comment['author']) . '</h3>';
        echo '<p class="comment-date">' . elodie_cms_escape($comment['created_at']) . '</p>';
        echo '<p>' . nl2br(elodie_cms_escape($comment['body']), false) . '</p></section>';
    }

    echo '<form class="comment-form" method="post" action="index2.php?module=articles&amp;page='
        . (int) ($_GET['page'] ?? 1) . '">'
        . elodie_cms_csrf_input()
        . '<input type="hidden" name="comment_submit" value="1">'
        . '<label for="comment-author">Nom</label>'
        . '<input id="comment-author" name="author" maxlength="120" required autocomplete="name">'
        . '<label for="comment-body">Commentaire</label>'
        . '<textarea id="comment-body" name="body" maxlength="5000" required rows="6"></textarea>'
        . '<button type="submit">Envoyer le commentaire</button></form></article>';
}

/* Profil */

function tprofil()  {

include ('admin/includes/config1.php');

if ((base64_decode($tableau[11])=='') && (base64_decode($tableau[12])=='')) {echo'<title>'.base64_decode($tableau[0]).' - '.Nonrenseigne.'</title>';}

else {echo'<title>'.base64_decode($tableau[0]).' - '.base64_decode($tableau[11]).' '.base64_decode($tableau[12]).'</title>';};	

}

function profil()  {

include ('admin/includes/config1.php');

echo'<h2>'.Profil.'</h2><div id="article" style="padding-left:10px">

<h1>';
if ((base64_decode($tableau[11])=='') && (base64_decode($tableau[12])=='')) {echo Nonrenseigne;}

else {echo''.base64_decode($tableau[11]).' '.base64_decode($tableau[12]).'';};

if ((base64_decode($tableau[13])=='') && (base64_decode($tableau[14])=='')) {echo '</h1>';}

else {

echo' ( ';

function age($naiss)  {
  $dateParts = preg_split('~[/.]~', $naiss);
  if (!is_array($dateParts) || count($dateParts) !== 3
      || !elodie_cms_valid_article_date($dateParts[0], $dateParts[1], $dateParts[2])) {
      return;
  }
  [$annee, $mois, $jour] = $dateParts;
  $today['mois'] = date('n');
  $today['jour'] = date('j');
  $today['annee'] = date('Y');
  $annees = $today['annee'] - $annee;
  if ($today['mois'] <= $mois) {
    if ($mois == $today['mois']) {
      if ($jour > $today['jour'])
        $annees--;
      }
    else
      $annees--;
    }
	
if ((base64_decode($tableau[13])=='') && (base64_decode($tableau[28])=='') && (base64_decode($tableau[29])=='')) {echo '';}

else {
  echo $annees; echo' ans ';
  
  }  }
age(''.base64_decode($tableau[13]).'/'.base64_decode($tableau[28]).'/'.base64_decode($tableau[29]).'');  

if (base64_decode($tableau[14])=='Monde') {echo Monde;}

else {echo''.base64_decode($tableau[14]).'';};

echo' <img src="'.base64_decode($tableau[5]).'/admin/images/pays/'.base64_decode($tableau[14]).'.png" alt="'.base64_decode($tableau[14]).'" style="border: black 1px solid;"> )</h1>';};

echo'<table>
<tr>
<td><img src="';

if (base64_decode($tableau[15])=='') {echo ''.base64_decode($tableau[5]).'/photo.png';}

else {echo''.base64_decode($tableau[15]).'';};

echo'" alt="" style="border: solid #DDDDDD;
border-radius: 4px;
display: block;
height:200px;width:200px;
margin-right:10px;"/></td>

<td style="padding:30px;">
<h2 style="
font-family:sans-serif;
font-size: 22px;
font-weight: 700;
line-height: 24px;
margin-bottom: 20px;
">';

if (base64_decode($tableau[19])=='') {echo Defaut;}
else {echo''.base64_decode($tableau[19]).'';};

echo'</h2>';

if ((base64_decode($tableau[20])=='') && (base64_decode($tableau[21])=='')) {echo '<p>'.Loisirs.' </td>';}

else {echo'<p>'.base64_decode($tableau[20]).'</p><p><b>Loisirs  :</b> '.base64_decode($tableau[21]).'</p></td>';};


echo'<td>';

if (base64_decode($tableau[17])=='') {echo '';}
else { echo'<p><a href="https://fr-fr.facebook.com/'.base64_decode($tableau[17]).'" style="text-decoration:none;">Facebook</a><br/></p>'; };

if (base64_decode($tableau[18])=='') {echo '';}
else { echo'<p><a href="https://plus.google.com/'.base64_decode($tableau[18]).'" style="text-decoration:none;">Google+</a><br/></p>'; };

if (base64_decode($tableau[16])=='') {echo '';}
else { echo'<p><a href="https://twitter.com/'.base64_decode($tableau[16]).'" style="text-decoration:none;">Twitter</a></p>'; };

echo'</td></tr></table></div>'; 

}

/* ADMINISTRATION */

/* Index de l'administration */

function accueil() {

$test01=is_writable(__DIR__ . '/../data');
$test02=is_writable(__DIR__ . '/../images');
$test03=is_file(__DIR__ . '/../data/elodie-cms.sqlite') && is_writable(__DIR__ . '/../data/elodie-cms.sqlite');

$status = [
    [$test01, CONFIGOUI, CONFIGNON],
    [$test02, IMAGESOUI, IMAGESNON],
    [$test03, ARTICLOUI, ARTICLNON],
];
foreach ($status as [$isReady, $success, $failure]) {
    $class = $isReady ? 'valide' : 'erreur';
    echo '<div id="' . $class . '"><p>' . ($isReady ? $success : $failure) . '</p></div>';
}

echo'<div id="pays"><p>'.Pays.'</p></div>';

}

/* Configuration du blog */ 

function configuration() {

$fichier='configuration.txt';
$tableau=array();
$tableau=lire_array($fichier);

echo'
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="content-type" content="text/html; charset=utf-8" />
<meta http-equiv="x-ua-compatible" content="ie=edge" />
<title>Elodie CMS</title>
<meta name="Description" content="Administration de Elodie CMS" />
<meta name="apple-mobile-web-app-capable" content="yes" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<link rel="stylesheet" href="defaut.css" />
<link rel="stylesheet" href="defaut2.css" />
<link rel="shortcut icon" type="image/x-icon" href="'.base64_decode($tableau[5]).'/Favicon.ico" sizes="16x16" />
<link rel="icon" type="image/x-icon" href="'.base64_decode($tableau[5]).'/Favicon.ico" sizes="16x16" />
<link rel="stylesheet" href="jquery/css/ui-lightness/jquery-ui-1.10.2.custom.css" />
<script src="js/jquery.min.js"></script>
<script src="js/jquery-ui.min.js"></script>
<script src="js/jquery.coda-slider-3.0.js"></script>

<script src="js/editeur.js"></script>
<script type="text/javascript">addEvt(window,\'load\',whizzywig);</script>
    <script>
        $(function(){
            setInterval(function(){
                $(\'#ajax-refresh\').load(\'chat.php\');
            }, 0);
        });
    </script>

</head>
<body>
<body onload="whizzywig()">';

echo'<style type="text/css">
td,th{
border:none !important;
};
</style>';

if (($_GET['id'] ?? '') === '2') {

echo'<meta http-equiv="refresh" content="1; URL=index2.php?page=configuration">
<style type="text/css">
.ui-dialog,.ui-dialog-content{
min-height: 0px !important;
margin:0px !important;
};
</style>
<div class="ui-state-highlight ui-corner-all" style="text-align:center;">
<p><span class="ui-icon ui-icon-info" style="float: left; margin:auto;text-align:center;margin-right: .3em;margin-left: .3em;"></span>
'.Modificationeffectuee.'</p></div>';
 
$password = $_POST['7'] ?? null;
if (!is_string($password)) {
    http_response_code(400);
    exit('Le mot de passe est invalide.');
}
if ($password !== '' && (strlen($password) < 12 || strlen($password) > 72)) {
    http_response_code(400);
    exit('Le mot de passe doit contenir entre 12 et 72 octets.');
}
$currentPasswordHash = base64_decode($tableau[7] ?? '', true);
if ($password === '' && (!is_string($currentPasswordHash) || $currentPasswordHash === '')) {
    throw new RuntimeException('Le mot de passe administrateur actuel est introuvable.');
}

$settings = [];
for ($index = 0; $index < 32; $index++) {
    if ($index === 7) {
        $settings[$index] = $password === ''
            ? $currentPasswordHash
            : password_hash($password, PASSWORD_DEFAULT);
        continue;
    }
    $value = html_entity_decode(elodie_cms_post_string((string) $index), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    if (in_array($index, [15, 23, 25, 26, 30, 31], true) && !elodie_cms_valid_url_setting($value)) {
        http_response_code(400);
        exit('Une adresse de ressource est invalide.');
    }
    if ($index === 5 && !elodie_cms_valid_http_url($value)) {
        http_response_code(400);
        exit('L’adresse du site est invalide.');
    }
    if ($index === 3 && !in_array($value, ['on', 'off'], true)) {
        http_response_code(400);
        exit('Le réglage des commentaires est invalide.');
    }
    if ($index === 1 && !in_array($value, ['en', 'es', 'fr', 'nl'], true)) {
        http_response_code(400);
        exit('La langue sélectionnée est invalide.');
    }
    if (in_array($index, [4, 9, 10], true) && !in_array($value, ['on', 'off'], true)) {
        http_response_code(400);
        exit('Une option de configuration est invalide.');
    }
    if ($index === 8 && !in_array($value, ['on', 'on2', 'off'], true)) {
        http_response_code(400);
        exit('Une option de configuration est invalide.');
    }
    $settings[$index] = htmlentities($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
$encodedSettings = array_map('base64_encode', $settings);
elodie_cms_write_encoded_configuration($encodedSettings);
$_SESSION['_login'] = html_entity_decode($settings[6], ENT_QUOTES | ENT_HTML5, 'UTF-8');
$_SESSION['_pass'] = $settings[7];

}

else {
  
error_reporting(0);

echo'<form action="index2.php?page=configuration&id=2" method="post">'.elodie_cms_csrf_input().'
	
<div class="coda-slider"  id="main-slider">
<div>
<div class="coda-slider"  id="showcase">';
 
if (base64_decode($tableau[4])==='on') {$paginationOn = 'selected="selected"';}
elseif (base64_decode($tableau[4])==='off') {$paginationOff = 'selected="selected"';}
else {$paginationOn = 'selected="selected"';}

echo'<div>
<h2 class="title" style="display:none;">'.Accueil.'</h2>
<table style="margin:auto;padding-right:60px;">
<p><b>'.BienvenueConfig.'</b></p><br/>
<tr><td style="padding-left:10px;padding-top:10px;">'.General.'</td><td style="padding-left:20px;padding-top:10px;">'.Generala.'</td> </tr>
<tr><td style="padding-left:10px;padding-top:10px;">'.Profil.'</td><td style="padding-left:20px;padding-top:10px;">'.Profila.'</td> </tr>
<tr><td style="padding-left:10px;padding-top:10px;">'.Theme.'</td><td style="padding-left:20px;padding-top:10px;">'.Themea.'</td> </tr>
<tr><td style="padding-left:10px;padding-top:10px;">'.Menu.'</td><td style="padding-left:20px;padding-top:10px;">'.Menua.'</td> </tr>
</table>
</div>
<div>
<h2 class="title" style="display:none;">'.General.'</h2>
<table style="margin:auto;padding-right:60px;">
<tr>
<td class="titre"></br>'.Titre.'  &nbsp;</td><td></br><input type="text" name="0" value="'.base64_decode($tableau[0]).'" placeholder="'.Titreb.'" STYLE="width:170px;" /></td>
<td class="titre" style="padding-left:20px;" ></br>'.Langue.'  &nbsp;</td><td></br><SELECT value="'.base64_decode($tableau[1]).'" name="1" STYLE="width:180px;">';

$languages = array(

'en' => $selected3, 
'es' => $selected4,
'fr' => $selected5,
'nl' => $selected6 

);

foreach ($languages as $languages1 => $languages2) { 

if (base64_decode($tableau[1])==$languages1) {$languages2 = 'selected="selected"';}

echo'<option '.$languages2.'>'.$languages1.'</option>';
 
 };

echo'</SELECT></td></tr>

<tr><td class="titre"></br>'.Gerant.'  &nbsp;</td><td></br><input type="text" required name="2" value="'.base64_decode($tableau[2]).'" placeholder="'.Webmasterb.'" STYLE="width:170px;"/></td>
<td class="titre" style="padding-left:20px;"></br>Commentaires internes &nbsp;</td><td></br><select name="3" style="width:180px;">
<option value="off" '.(base64_decode($tableau[3] ?? '') === 'on' ? '' : 'selected="selected"').'>Désactivés</option>
<option value="on" '.(base64_decode($tableau[3] ?? '') === 'on' ? 'selected="selected"' : '').'>Activés</option>
</select></td></tr>
<tr>
<td class="titre"></br>'.Pagination.'  &nbsp;</td><td></br><SELECT value="'.base64_decode($tableau[4]).'" name="4" STYLE="width:180px;">
<option value="on" '.$paginationOn.'>'.Pagingi.'</option>
<option value="off" '.$paginationOff.'>'.Pagingii.'</option>
</select></td>
<td class="titre" style="padding-left:20px;"></br>'.AdresseSite.'  &nbsp;</td><td></br>

<input type="text" name="5"  required value="'.base64_decode($tableau[5]).'" placeholder="'.Urlb.'" STYLE="width:170px;"/></td></tr>

<tr>
<td class="titre"></br>'.Login.'  &nbsp;</td><td></br><input type="text" required name="6" value="'.base64_decode($tableau[6]).'" placeholder="'.Loginb.'" STYLE="width:170px;" alt=""/></td>
';

if (base64_decode($tableau[8])==='on') {$selectedon = 'selected="selected"';}

elseif (base64_decode($tableau[8])==='on2') {$selectedon2 = 'selected="selected"';}

elseif (base64_decode($tableau[8])==='off') {$selectedoff = 'selected="selected"';}

echo'<td class="titre" style="padding-left:20px;"></br>URL Rewriting  &nbsp;</td><td></br><SELECT value="'.base64_decode($tableau[8]).'" name="8" STYLE="width:180px;">
<OPTION VALUE="on" '.$selectedon.'>'.urli.'</OPTION>
<OPTION VALUE="on2" '.$selectedon2.'>'.urlii.'</OPTION>
<OPTION VALUE="off" '.$selectedoff.'>'.urliii.'</OPTION>
</SELECT></td></tr>';

if (base64_decode($tableau[9])==='on') {$selected1 = 'selected="selected"';}

elseif (base64_decode($tableau[9])==='off') {$selected2 = 'selected="selected"';}

echo'<tr><td class="titre"></br>'.LienAdmin.'  &nbsp;</td><td></br><SELECT value="'.base64_decode($tableau[9]).'" name="9" STYLE="width:180px;">
<OPTION VALUE="on" '.$selected1.'>'.urliiii.'</OPTION>
<OPTION VALUE="off" '.$selected2.'>'.urliiiii.'</OPTION>
</SELECT></td>';

if (base64_decode($tableau[10])==='on') {$selecteddate1 = 'selected="selected"';}

elseif (base64_decode($tableau[10])==='off') {$selecteddate2 = 'selected="selected"';}

echo'<td class="titre" style="padding-left:20px;"></br>'.Date.'  &nbsp;</td><td></br><SELECT value="'.base64_decode($tableau[10]).'" name="10" STYLE="width:180px;">
<OPTION VALUE="on" '.$selecteddate1.'>'.Lettre.'</OPTION>
<OPTION VALUE="off" '.$selecteddate2.'>'.Chiffre.'</OPTION>
</SELECT></td></tr>

</table>
</div>

<div>
<h2 class="title" style="display:none;">'.Profil.'</h2>
<table style="margin:auto;padding-right:60px;">
<tr>
<td class="profil"></br>'.Prenom.'  &nbsp;</td><td></br><input type="text" name="11" value="'.base64_decode($tableau[11]).'" placeholder="'.Prenoma.'" STYLE="width:170px;" /></td>
<td class="profil" style="padding-left:20px;"></br>'.Nom.'  &nbsp;</td><td></br><input type="text" name="12" value="'.base64_decode($tableau[12]).'" placeholder="'.Noma.'" STYLE="width:170px;" /></td>
</tr>
<tr>
<td class="profil"></br>'.Datedenaissance.'  &nbsp;</td><td></br>
<SELECT value="'.base64_decode($tableau[13]).'" name="13" STYLE="width:62px;">';

$year = array(

''.Annee.'' => $an0a,
(date('Y')+0) => $an0b, 
(date('Y')-1) => $an1,
(date('Y')-2) => $an2, 
(date('Y')-3) => $an3, 
(date('Y')-4) => $an4,
(date('Y')-5) => $an5, 
(date('Y')-6) => $an6,
(date('Y')-7) => $an7, 
(date('Y')-8) => $an8, 
(date('Y')-9) => $an9,
(date('Y')-10) => $an10, 
(date('Y')-11) => $an11,
(date('Y')-12) => $an12, 
(date('Y')-13) => $an13, 
(date('Y')-14) => $an14,
(date('Y')-15) => $an15, 
(date('Y')-16) => $an16,
(date('Y')-17) => $an17, 
(date('Y')-18) => $an18, 
(date('Y')-19) => $an19,
(date('Y')-20) => $an20, 
(date('Y')-21) => $an21,
(date('Y')-22) => $an22, 
(date('Y')-23) => $an23, 
(date('Y')-24) => $an24,
(date('Y')-25) => $an25, 
(date('Y')-26) => $an26,
(date('Y')-27) => $an27, 
(date('Y')-28) => $an28, 
(date('Y')-29) => $an29,
(date('Y')-30) => $an30, 
(date('Y')-31) => $an31,
(date('Y')-32) => $an32, 
(date('Y')-33) => $an33, 
(date('Y')-34) => $an34,
(date('Y')-35) => $an35, 
(date('Y')-36) => $an36,
(date('Y')-37) => $an37, 
(date('Y')-38) => $an38, 
(date('Y')-39) => $an39,
(date('Y')-40) => $an40, 
(date('Y')-41) => $an41,
(date('Y')-42) => $an42, 
(date('Y')-43) => $an43, 
(date('Y')-44) => $an44,
(date('Y')-45) => $an45, 
(date('Y')-46) => $an46,
(date('Y')-47) => $an47, 
(date('Y')-48) => $an48, 
(date('Y')-49) => $an49,
(date('Y')-50) => $an50, 
(date('Y')-51) => $an51,
(date('Y')-52) => $an52, 
(date('Y')-53) => $an53, 
(date('Y')-54) => $an54,
(date('Y')-55) => $an55, 
(date('Y')-56) => $an56,
(date('Y')-57) => $an57, 
(date('Y')-58) => $an58, 
(date('Y')-59) => $an59,
(date('Y')-60) => $an60, 
(date('Y')-61) => $an61,
(date('Y')-62) => $an62, 
(date('Y')-63) => $an63, 
(date('Y')-64) => $an64,
(date('Y')-65) => $an65, 
(date('Y')-66) => $an66,
(date('Y')-67) => $an67, 
(date('Y')-68) => $an68, 
(date('Y')-69) => $an69,
(date('Y')-70) => $an70, 
(date('Y')-71) => $an71,
(date('Y')-72) => $an72, 
(date('Y')-73) => $an73, 
(date('Y')-74) => $an74,
(date('Y')-75) => $an75, 
(date('Y')-76) => $an76,
(date('Y')-77) => $an77, 
(date('Y')-78) => $an78, 
(date('Y')-79) => $an79,
(date('Y')-80) => $an80, 
(date('Y')-81) => $an81,
(date('Y')-82) => $an82, 
(date('Y')-83) => $an83, 
(date('Y')-84) => $an84,
(date('Y')-85) => $an85, 
(date('Y')-86) => $an86,
(date('Y')-87) => $an87, 
(date('Y')-88) => $an88, 
(date('Y')-89) => $an89,
(date('Y')-90) => $an90, 
(date('Y')-91) => $an91,
(date('Y')-92) => $an92, 
(date('Y')-93) => $an93, 
(date('Y')-94) => $an94,
(date('Y')-95) => $an95, 
(date('Y')-96) => $an96,
(date('Y')-97) => $an97, 
(date('Y')-98) => $an98, 
(date('Y')-99) => $an99,
(date('Y')-100) => $an100, 
(date('Y')-101) => $an101,
(date('Y')-102) => $an102, 
(date('Y')-103) => $an103, 
(date('Y')-104) => $an104,
(date('Y')-105) => $an105, 
(date('Y')-106) => $an106,
(date('Y')-107) => $an107, 
(date('Y')-108) => $an108, 
(date('Y')-109) => $an109,
(date('Y')-110) => $an110, 
(date('Y')-111) => $an111,
(date('Y')-112) => $an112, 
(date('Y')-113) => $an113, 
(date('Y')-114) => $an114,
(date('Y')-115) => $an115, 
(date('Y')-116) => $an116,
(date('Y')-117) => $an117, 
(date('Y')-118) => $an118, 
(date('Y')-119) => $an119,
(date('Y')-120) => $an120,
(date('Y')-121) => $an121,
(date('Y')-122) => $an122  

);

foreach ($year as $year1 => $year2) { 
if (base64_decode($tableau[13])==$year1) {$year2 = 'selected="selected"';};
echo'<option '.$year2.'>'.$year1.'</option>';
 };

echo'

</SELECT>
 
<SELECT value="'.base64_decode($tableau[28]).'" name="28" STYLE="width:55px;">';

$month = array(

''.Mois.'' => $mois0,
'01' => $mois1, 
'02' => $mois2,
'03' => $mois3,
'04' => $mois4,
'05' => $mois5, 
'06' => $mois6,
'07' => $mois7,
'08' => $mois8,
'09' => $mois9, 
'10' => $mois10,
'11' => $mois11,
'12' => $mois12 

);

foreach ($month as $month1 => $month2) { 
if (base64_decode($tableau[28])==$month1) {$month2 = 'selected="selected"';}
echo'<option '.$month2.'>'.$month1.'</option>';
 };
 
echo'</SELECT>
<SELECT value="'.base64_decode($tableau[29]).'" name="29" STYLE="width:55px;">';

$day = array(

''.Jour.'' => $jour0,
'01' => $jour1, 
'02' => $jour2,
'03' => $jour3,
'04' => $jour4,
'05' => $jour5, 
'06' => $jour6,
'07' => $jour7,
'08' => $jour8,
'09' => $jour9, 
'10' => $jour10,
'11' => $jour11,
'12' => $jour12,
'13' => $jour13,
'14' => $jour14,
'15' => $jour15, 
'16' => $jour16,
'17' => $jour17,
'18' => $jour18,
'19' => $jour19, 
'20' => $jour20,
'21' => $jour21,
'22' => $jour22,
'23' => $jour23,
'24' => $jour24,
'25' => $jour25, 
'26' => $jour26,
'27' => $jour27,
'28' => $jour28,
'29' => $jour29, 
'30' => $jour30,
'31' => $jour31

);

foreach ($day as $day1 => $day2) { 
if (base64_decode($tableau[29])==$day1) {$day2 = 'selected="selected"';}
echo'<option '.$day2.'>'.$day1.'</option>';
 };

echo'</SELECT></td>
<td class="profil" style="padding-left:20px;"></br>'.Paysa.' &nbsp;</td><td></br><SELECT value="'.base64_decode($tableau[14]).'" name="14" STYLE="width:180px;">';

$world = array(

''.Monde.'' => $pays1, 
'Internet' => $pays2,
'Afghanistan' => $pays3,
'Afrique du Sud' => $pays4,
'Albanie' => $pays5,
'Alg&eacute;rie' => $pays6,
'Allemagne' => $pays7,
'Andorre' => $pays8,
'Angola' => $pays9,
'Antigua et Barbuda' => $pays10,
'Arabie Saoudite' => $pays11, 
'Argentine' => $pays12,
'Arm&eacute;nie' => $pays13,
'Australie' => $pays14,
'Autriche' => $pays15,
'Azerba&iuml;djan' => $pays16,
'Bahamas' => $pays17,
'Bahre&iuml;n' => $pays18,
'Bangladesh' => $pays19,
'Barbade' => $pays20,
'Belgique' => $pays21, 
'B&eacute;lize' => $pays22,
'B&eacute;nin' => $pays23,
'Bhoutan' => $pays24,
'Bi&eacute;lorussie' => $pays25,
'Birmanie' => $pays26,
'Bolivie' => $pays27,
'Bosnie Herz&eacute;govine' => $pays28,
'Botswana' => $pays29,
'Br&eacute;sil' => $pays30,
'Brunei' => $pays31, 
'Bulgarie' => $pays32,
'Burkina Faso' => $pays33,
'Burundi' => $pays34,
'Cambodge' => $pays35,
'Cameroun' => $pays36,
'Canada' => $pays37,
'Cap-Vert' => $pays38,
'Centrafrique' => $pays39,
'Chili' => $pays40,
'Chine' => $pays41, 
'Chypre' => $pays42,
'Colombie' => $pays43,
'Comores' => $pays44,
'Congo Kinshasa' => $pays45,
'Congo Brazzaville' => $pays46,
'Cor&eacute;e du Nord' => $pays47,
'Cor&eacute;e du Sud' => $pays48,
'Costa Rica' => $pays49,
'C&ocirc;te d\'Ivoire' => $pays50,
'Croatie' => $pays51,
'Cuba' => $pays52,
'Danemark' => $pays53,
'Djibouti' => $pays54,
'R&eacute;publique dominicaine' => $pays55,
'Dominique' => $pays56,
'&eacute;gypte' => $pays57,
'&eacute;mirats arabes unis' => $pays58,
'&eacute;quateur' => $pays59,
'&eacute;rythr&eacute;e' => $pays60,
'Espagne' => $pays61,
'Estonie' => $pays62,
'&eacute;tats Unis' => $pays63,
'&eacute;thiopie' => $pays64,
'Fidji' => $pays65,
'Finlande' => $pays66,
'France' => $pays67,
'Gabon' => $pays68,
'Gambie' => $pays69,
'G&eacute;orgie' => $pays70,
'Ghana' => $pays71,
'Gr&egrave;ce' => $pays72,
'Grenade' => $pays73,
'Guatemala' => $pays74,
'Guin&eacute;e' => $pays75,
'Guin&eacute;e Bissau' => $pays76,
'Guin&eacute;e &eacute;quatoriale' => $pays77,
'Guyana' => $pays78,
'Ha&iuml;ti' => $pays79,
'Honduras' => $pays80,
'Hongrie' => $pays81,
'Inde' => $pays82,
'Indon&eacute;sie' => $pays83,
'Irak' => $pays84,
'Iran' => $pays85,
'Irlande' => $pays86,
'Islande' => $pays87,
'Isra&euml;l' => $pays88,
'Italie' => $pays89,
'Jama&iuml;que' => $pays90,
'Japon' => $pays91,
'Jordanie' => $pays92,
'Kazakhstan' => $pays93,
'Kenya' => $pays94,
'Kirghizistan' => $pays95,
'Kiribati' => $pays96,
'Kowe&iuml;t' => $pays97,
'Laos' => $pays98,
'Lesotho' => $pays99,
'Lettonie' => $pays100,
'Liban' => $pays101,
'Liberia' => $pays102,
'Libye' => $pays103,
'Liechtenstein' => $pays104,
'Lituanie' => $pays105,
'Luxembourg' => $pays106,
'Mac&eacute;doine' => $pays107,
'Madagascar' => $pays108,
'Malaisie' => $pays109,
'Malawi' => $pays110,
'Maldives' => $pays111,
'Mali' => $pays112,
'Malte' => $pays113,
'Maroc' => $pays114,
'Marshall' => $pays115,
'Maurice' => $pays116,
'Mauritanie' => $pays117,
'Mexique' => $pays118,
'Micron&eacute;sie' => $pays119,
'Moldavie' => $pays120,
'Monaco' => $pays121,
'Mongolie' => $pays122,
'Mont&eacute;n&eacute;gro' => $pays123,
'Mozambique' => $pays124,
'Namibie' => $pays125,
'Nauru' => $pays126,
'N&eacute;pal' => $pays127,
'Nicaragua' => $pays128,
'Niger' => $pays129,
'Nigeria' => $pays130,
'Norv&egrave;ge' => $pays131,
'Nouvelle Z&eacute;lande' => $pays132,
'Oman' => $pays133,
'Ouganda' => $pays134,
'Ouzb&eacute;kistan' => $pays135,
'Pakistan' => $pays136,
'Palau' => $pays137,
'Palestine' => $pays138,
'Panama' => $pays139,
'Papouasie Nouvelle Guin&eacute;e' => $pays140,
'Paraguay' => $pays141,
'$pays Bas' => $pays142,
'P&eacute;rou' => $pays143,
'Philippines' => $pays144,
'Pologne' => $pays145,
'Portugal' => $pays146,
'Qatar' => $pays147,
'Roumanie' => $pays148,
'Royaume Uni' => $pays149,
'Russie' => $pays150,
'Rwanda' => $pays151,
'Saint Kitts et Nevis' => $pays152,
'Sainte Lucie' => $pays153,
'Saint Marin' => $pays154,
'Saint Vincent et les Grenadines' => $pays155,
'Salomon' => $pays156,
'Salvador' => $pays157,
'Samoa' => $pays158,
'Sao Tom&eacute; et Principe' => $pays159,
'S&eacute;n&eacute;gal' => $pays160,
'Serbie' => $pays161,
'Seychelles' => $pays162,
'Sierra Leone' => $pays163,
'Singapour' => $pays164,
'Slovaquie' => $pays165,
'Slov&eacute;nie' => $pays166,
'Somalie' => $pays167,
'Soudan' => $pays168,
'Soudan du Sud' => $pays169,
'Sri Lanka' => $pays170,
'Su&egrave;de' => $pays171,
'Suisse' => $pays172,
'Suriname' => $pays173,
'Swaziland' => $pays174,
'Syrie' => $pays175,
'Tadjikistan' => $pays176,
'Tanzanie' => $pays177,
'Tchad' => $pays178,
'R&eacute;publique tch&egrave;que' => $pays179,
'Tha&iuml;lande' => $pays180,
'Timor Leste' => $pays181,
'Togo' => $pays182,
'Tonga' => $pays183,
'Trinit&eacute; et Tobago' => $pays184,
'Tunisie' => $pays185,
'Turkm&eacute;nistan' => $pays186,
'Turquie' => $pays187,
'Tuvalu' => $pays188,
'Ukraine' => $pays189,
'Uruguay' => $pays190,
'Vanuatu' => $pays191,
'Vatican' => $pays192,
'Venezuela' => $pays193,
'Vietnam' => $pays194,
'Y&eacute;men' => $pays195,
'Zambie' => $pays196,
'Zimbabwe' => $pays197

);  

foreach ($world as $world1 => $world2) { 
if (base64_decode($tableau[14])==$world1) {$world2 = 'selected="selected"';};
echo'<option '.$world2.'>'.$world1.'</option>';
 } 

echo'
</select>
</td></tr><tr>
<td class="profil"></br>'.Photo.'  &nbsp;</td><td></br><input type="text" name="15" value="'.base64_decode($tableau[15]).'" placeholder="'.Photoa.'" STYLE="width:170px;" /></td>
<td class="profil" style="padding-left:20px;"></br>'.Twitter.'  &nbsp;</td><td></br><input type="text" name="16" value="'.base64_decode($tableau[16]).'" placeholder="'.Twittera.'" STYLE="width:170px;" /></td>
</tr>
<tr>
<td class="profil"></br>'.Activite.'</td><td></br><input type="text" name="19" value="'.base64_decode($tableau[19]).'" placeholder="'.Activitea.'" STYLE="width:170px;" /></td>
<td class="profil" style="padding-left:20px;"></br>'.Facebook.'  &nbsp;</td><td></br><input type="text" name="17" value="'.base64_decode($tableau[17]).'" placeholder="'.Facebooka.'" STYLE="width:170px;" /></td>
</tr>
<tr>
<td class="profil"></br>'.Biographie.'</td><td></br><input type="text" name="20" value="'.base64_decode($tableau[20]).'" placeholder="'.Biographiea.'" STYLE="width:170px;" /></td>
<td class="profil" style="padding-left:20px;"></br>'.Googleplus.'  &nbsp;</td><td></br><input type="text" name="18" value="'.base64_decode($tableau[18]).'" placeholder="'.Googleplusa.'" STYLE="width:170px;" /></td>
</tr>
<tr>
<td class="profil"></br>'.Loisirsa.'</td><td COLSPAN=3></br><input type="text" name="21" value="'.base64_decode($tableau[21]).'" placeholder="'.Loisirsaa.'" STYLE="width:450px;" /></td>
</tr>
</table>
</div>

<div>
<h2 class="title" style="display:none;">'.Theme.'</h2>
<table style="margin:auto;padding-right:60px;">
<tr>
<td COLSPAN=4><center><br/><b>'.Banniere.'</b></center></td>
</tr>

<tr>
<td class="titre"></br>'.Lienc.' &nbsp;</td><td></br><input type="text" name="26" value="'.base64_decode($tableau[26]).'" placeholder="'.LienBanniere.'" STYLE="width:170px;" /></td>
</tr><tr>
<td class="profil"></br>'.Titrec.' &nbsp;</td><td></br><input type="text" name="27" value="'.base64_decode($tableau[27]).'" placeholder="'.TitreBanniere.'" STYLE="width:170px;" /></td>
</tr>

<tr>
<td COLSPAN=4><center><br/><b>Background & Favicon</b></center></td>
</tr>

<tr>
<td class="titre"></br>Background &nbsp;</td><td></br><input type="text" name="30" value="'.base64_decode($tableau[30]).'" placeholder="'.LienBackground.'" STYLE="width:170px;" /></td>
</tr><tr>
<td class="profil"></br>Favicon &nbsp;</td><td></br><input type="text" name="31" value="'.base64_decode($tableau[31]).'" placeholder="'.LienFavicon.'" STYLE="width:170px;" /></td>
</tr>
</table>
</div>
  
<div>
<h2 class="title" style="display:none;">Menu</h2>
<table style="margin:auto;padding-right:60px;">
<tr>
<td COLSPAN=4><center><br/><b>'.Menu.'</b></center></td>
</tr>

<tr>
<td class="titre"></br>'.Titrec.' A  &nbsp;</td><td></br><input type="text" name="22" value="'.base64_decode($tableau[22]).'" placeholder="'.Titred.' A" STYLE="width:170px;" /></td>
</tr><tr>
<td class="profil"></br>'.Lienc.' A  &nbsp;</td><td></br><input type="text" name="23" value="'.base64_decode($tableau[23]).'" placeholder="'.Liend.' A" STYLE="width:170px;" /></td>
</tr>

<tr>
<td class="titre"></br>'.Titrec.' B  &nbsp;</td><td></br><input type="text" name="24" value="'.base64_decode($tableau[24]).'" placeholder="'.Titred.' B" STYLE="width:170px;" /></td>
</tr><tr>
<td class="profil"></br>'.Lienc.' B  &nbsp;</td><td></br><input type="text" name="25" value="'.base64_decode($tableau[25]).'" placeholder="'.Liend.' B" STYLE="width:170px;" /></td>
</tr>
</table>
</div>
</div>

<table style="margin:auto;padding-right:0px;">
<tr>
<td class="titre"></br>'.Code.'  &nbsp;</td><td></br><input type="password" autocomplete="new-password" minlength="12" maxlength="72" name="7" value="" placeholder="Laisser vide pour conserver le mot de passe actuel" alt="" STYLE="width:200px;" /></td>
</tr>  
</table>
		
<br/> 

<center>
<input class="submit" type="submit" value="'.Ok.'" name="submit" />
</center>
</form>
';

};

echo'
</div>
</div>
';
echo'</body>';
}

/* La liste des News dans l'administration */

function liste_news() {

$fichier='configuration.txt';
$tableau=array();
$tableau=lire_array($fichier);

echo'<table class="data" style="border-collapse: collapse !important ;"><thead><tr>
<th style="width:300px;border:1px solid #CCCCCC; text-transform: uppercase; background-color:#E2E2E2;"><center>'.Titre.'</center></th><th style="width:100px;border:1px solid #CCCCCC; text-transform: uppercase; background-color:#E2E2E2;"><center>'.Date.'</center></th><th style="width:100px;border:1px solid #CCCCCC; text-transform: uppercase; background-color:#E2E2E2;"><center>'.Auteur.'</center></th><th style="width:100px;border:1px solid #CCCCCC; text-transform: uppercase; background-color:#E2E2E2;"><center>'.Supprimer.'</center></th><th style="width:100px;border:1px solid #CCCCCC; text-transform: uppercase; background-color:#E2E2E2;"><center>'.Editer.'</center></th></tr></thead></table>';
 
$liste_news = elodie_cms_read_news(__DIR__ . '/../news.php');
if(!empty($liste_news)) {
	foreach($liste_news as $id => $news) {

echo'<table class="data" style="border-collapse: collapse !important ;">
<thead><tr >
<td style="width:300px;border:1px solid #CCCCCC;background-color:#FFF9F4;">';
echo elodie_cms_escape_legacy_text($news['titre']);
echo'</td>
<td style="width:100px;border:1px solid #CCCCCC;background-color:#FFF9F4;text-align:center;">';
if (base64_decode($tableau[1])=='fr') { echo' '.elodie_cms_escape_legacy_text($news['jour']).'-'.elodie_cms_escape_legacy_text($news['mois']).'-'.elodie_cms_escape_legacy_text($news['annee']).' '; }
else { echo' '.elodie_cms_escape_legacy_text($news['annee']).'-'.elodie_cms_escape_legacy_text($news['mois']).'-'.elodie_cms_escape_legacy_text($news['jour']).' '; }
echo'</td>
<td style="width:100px;border:1px solid #CCCCCC;background-color:#FFF9F4;"><center>';
echo base64_decode($tableau[2]);
echo'</center></td><td style="width:100px;border:1px solid #CCCCCC;background-color:#FFF9F4;"><center><form method="post" action="index2.php?page=supprimer">'.elodie_cms_csrf_input().'<input type="hidden" name="id" value="'.(int) $id.'"><button type="submit" aria-label="'.Supprimer.'"><img src="images/supprimer.png" alt="'.Supprimer.'" width="16px"></button></form></center></td><td style="width:100px;border:1px solid #CCCCCC;background-color:#FFF9F4;"><center><a href="index2.php?page=editer&id='.(int) $id.'"><img src="images/edition.png" alt="Editer" width="16px"></a></center></td></tr></thead></table>';
}
}
} 

/* Le Formulaire pour envoyer les Images */

function formulaire_images() {

echo'<form method="POST" action="index2.php?page=upload" enctype="multipart/form-data">'.elodie_cms_csrf_input().'
     <input type="hidden" name="MAX_FILE_SIZE" value="1048576">
     '.Fichier.' : <input type="file" name="avatar">
     <input type="submit" name="envoyer" value="'.Ok.'">
</form>';

}

/* Affichage des images, Le lien de l'image et lien pour la supprimer */

function images() {

$fichier='configuration.txt';
$tableau=array();
$tableau=lire_array($fichier);

echo'

<style>
html { float: left; width: 100%;     overflow: auto;
 max-height: 320px !important;; }
#gallery { float: left; width: 100%;     overflow: auto;
 max-height: 320px !important;; }
.gallery.custom-state-active { background: #eee; }
.gallery li { float: left; width: 116px; padding: 0.4em; margin: 0 0.4em 0.4em 0; text-align: center; }
.gallery li h5 { margin: 0 0 0.4em; }
.gallery li a { float: right; }
.gallery li a.ui-icon-zoomin { float: left; }
.gallery li img { width: 100%; }
#trash { float: right; width: 32%; min-height: 18em; padding: 1%; }
#trash h4 { line-height: 16px; margin: 0 0 0.4em; }
#trash h4 .ui-icon { float: left; }
#trash .gallery h5 { display: none; }
</style>

 <SCRIPT language=javascript>
    function OuvrirPopup(page,nom,option) {
       window.open(page,nom,option);
    }
  </SCRIPT>
  
<ul id="gallery" class="gallery ui-helper-reset ui-helper-clearfix">';

$dir = '../images/';
$dir2 = '/images/';
$valide_extensions = array('jpg', 'jpeg', 'gif', 'png', 'bmp');

$Ressource = opendir($dir);
while($fichier = readdir($Ressource))
{
     $berk = array('.', '..');

     $test_Fichier = $dir.$fichier;
     $test_Fichier2 = $fichier;
	 $test_Fichier3 = $dir2.$fichier;


     if(!in_array($fichier, $berk) && !is_dir($test_Fichier))
     {
 	 $ext = strtolower(pathinfo($fichier, PATHINFO_EXTENSION));

         if(in_array($ext, $valide_extensions))
         {
echo '<li class="ui-widget-content ui-corner-tr" style="list-style-type:none;margin-top:25px;"><div> <h5 class="ui-widget-header">'.elodie_cms_escape($test_Fichier2).'</h5>

<img src="'.elodie_cms_escape($test_Fichier).'" width="96" height="72">

<div style="text-align:center;"><a href="'.elodie_cms_escape(rtrim(base64_decode($tableau[5]), '/').$test_Fichier3).'" target="_blank" rel="noopener noreferrer" class="ui-icon ui-icon-zoomin" aria-label="Aperçu"></a>

<form method="post" action="index2.php?page=delete">'.elodie_cms_csrf_input().'<input type="hidden" name="id" value="'.elodie_cms_escape($test_Fichier2).'"><button type="submit" class="ui-icon ui-icon-trash" aria-label="'.Supprimer.'">';

echo'</button></form></div></div></li>'; } } }

echo'</ul>'; }

/* Script pour &eacute;viter les slash dans les articles */
function anti_slash() {

}
/* Script pour ajouter une news via l'administration */

function ajout_news() {

$fichier='configuration.txt';
$tableau=array();
$tableau=lire_array($fichier);

if(isset($_POST['titre']) && isset($_POST['contenu']) && isset($_POST['chapo']) && isset($_POST['jour']) && isset($_POST['mois']) && isset($_POST['annee'])) {
     //On d&eacute;finit les variables
$titre = elodie_cms_post_string('titre');
$contenu = elodie_cms_post_string('contenu');

     $chapo = elodie_cms_post_string('chapo');
     $jour = elodie_cms_post_string('jour');
     $mois = elodie_cms_post_string('mois');
     $annee = elodie_cms_post_string('annee');
	 $note = elodie_cms_post_string('note');
     if (!elodie_cms_valid_article_date($annee, $mois, $jour)
         || !in_array($note, ['Off', '0', '1', '2', '3', '4', '5'], true)) {
         throw new InvalidArgumentException('La date ou la note de l’article est invalide.');
     }
     $contenu = elodie_cms_sanitize_article_html($contenu);
	//On r&eacute;cup&egrave;re les donn&eacute;es d&eacutejà existantes
	$news = elodie_cms_read_news(__DIR__ . '/../news.php');
	$news[] = array('titre' => $titre, 'jour' => $jour, 'mois' => $mois, 'annee' => $annee,'contenu' => $contenu, 'chapo' => $chapo, 'note' => $note);
	elodie_cms_write_news(__DIR__ . '/../news.php', $news);
	
      echo '<style type="text/css">
.ui-dialog,.ui-dialog-content{
min-height: 0px !important;
margin:0px !important;
};
</style>
<div class="ui-state-highlight ui-corner-all" style="text-align:center;">
<p><span class="ui-icon ui-icon-info" style="float: left; margin:auto;text-align:center;margin-right: .3em;margin-left: .3em;"></span>'.NewsAdd.'</p></div>';
      echo '<br />';
      echo '<center><a href="index2.php?page=ajouter">'.Retour.'</a></center>';
}
else {
	 echo'
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="content-type" content="text/html; charset=utf-8" />
<meta http-equiv="x-ua-compatible" content="ie=edge" />
<title>Elodie CMS</title>
<meta name="Description" content="Administration de Elodie CMS" />
<meta name="apple-mobile-web-app-capable" content="yes" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<link rel="stylesheet" href="defaut.css" />
<link rel="stylesheet" href="defaut2.css" />
<link rel="shortcut icon" type="image/x-icon" href="'.base64_decode($tableau[5]).'/Favicon.ico" sizes="16x16" />
<link rel="icon" type="image/x-icon" href="'.base64_decode($tableau[5]).'/Favicon.ico" sizes="16x16" />
<link rel="stylesheet" href="jquery/css/ui-lightness/jquery-ui-1.10.2.custom.css" />
<script src="js/jquery.min.js"></script>
<script src="js/jquery-ui.min.js"></script>
<script src="js/jquery.coda-slider-3.0.js"></script>

<script src="js/editeur.js"></script>
<script type="text/javascript">addEvt(window,\'load\',whizzywig);</script>
    <script>
        $(function(){
            setInterval(function(){
                $(\'#ajax-refresh\').load(\'chat.php\');
            }, 0);
        });
    </script>

</head>
<body>
<body onload="whizzywig()">	 
	 
	 <form action="" method="post">'.elodie_cms_csrf_input().'
<label for="pseudo">'.Auteur.'</label> :<strong> '.base64_decode($tableau[2]).'</strong> -  <label for="titre">'.Titre.' : </label> <input type="text" required name="titre" id="titre" placeholder="'.Articla.'" /> -  

<label for="jour">'.Jour.'</label> : <SELECT name="jour" id="jour" STYLE="width:70px;">';

$days = array('01','02','03','04','05','06','07','08','09','10','11','12','13','14','15','16','17','18','19','20','21','22','23','24','25','26','27','28','29','30','31');  

foreach ($days as $d) { 
echo'<OPTION>'.$d.'</OPTION>';
 } 

echo'</SELECT>
-
<label for="mois">'.Mois.'</label> : <SELECT name="mois" id="mois" STYLE="width:70px;">';

$month = array('01','02','03','04','05','06','07','08','09','10','11','12');  

foreach ($month as $m) { 
echo'<OPTION>'.$m.'</OPTION>';
 } 
 
echo'</SELECT>
-
<label for="annee">'.Annee.'</label> : <SELECT name="annee" id="annee" STYLE="width:70px;">
<OPTION>'.(date('Y')+0).'</OPTION>
<OPTION>'.(date('Y')-1).'</OPTION>
<OPTION>'.(date('Y')-2).'</OPTION>
<OPTION>'.(date('Y')-3).'</OPTION>
<OPTION>'.(date('Y')-4).'</OPTION>
</SELECT>

<br /><br /><label for="chapo"> '.Chapo.' : </label><input type="text" required name="chapo" id="chapo" rows="" cols="" placeholder="'.Articlb.'" style="width: 82%;"/><br /><br />';

include ('includes/smiley.php');

echo'<textarea name="contenu" id="contenu" rows="" cols="" style="width: 100%;height: 400px;"></textarea>
<br/><label for="note">'.Note.'</label> : <SELECT name="note" id="note" STYLE="width:70px;">';

$notes = array( 'Off','1','2','3','4','5');

foreach ($notes as $notesA) { echo'<option>'.$notesA.'</option>'; };

echo'</SELECT>&nbsp; &nbsp;<b>'.Nota.'</b> .<br/><br/><center><input type="submit" value="'.Ok.'" /></center></form>';
}
}

/* Script pour editer une news via l'administration */

function editer_news() {

$fichier='configuration.txt';
$tableau=array();
$tableau=lire_array($fichier);    

if(!isset($_GET['id']) || !is_string($_GET['id']) || !ctype_digit($_GET['id'])) {
	header('Location: index.php?page=liste');
	exit();
}

$news = elodie_cms_read_news(__DIR__ . '/../news.php');
$newsAmodifier = (int) $_GET['id'];
if (!isset($news[$newsAmodifier]) || !is_array($news[$newsAmodifier])) {
    http_response_code(404);
    exit('Article introuvable.');
}
if(isset($_POST['titre']) && isset($_POST['contenu'])) {
$news[$newsAmodifier]['titre'] = elodie_cms_post_string('titre');
$news[$newsAmodifier]['jour'] = elodie_cms_post_string('jour');
$news[$newsAmodifier]['mois'] = elodie_cms_post_string('mois');
$news[$newsAmodifier]['annee'] = elodie_cms_post_string('annee');
if (!elodie_cms_valid_article_date(
    $news[$newsAmodifier]['annee'],
    $news[$newsAmodifier]['mois'],
    $news[$newsAmodifier]['jour']
)) {
    throw new InvalidArgumentException('La date de l’article est invalide.');
}
$news[$newsAmodifier]['contenu'] = elodie_cms_sanitize_article_html(elodie_cms_post_string('contenu'));

$news[$newsAmodifier]['contenu'] = elodie_cms_sanitize_article_html($news[$newsAmodifier]['contenu']);
	$news[$newsAmodifier]['chapo'] = elodie_cms_post_string('chapo');
	$news[$newsAmodifier]['note'] = elodie_cms_post_string('note');
    if (!in_array($news[$newsAmodifier]['note'], ['Off', '0', '1', '2', '3', '4', '5'], true)) {
        throw new InvalidArgumentException('La note de l’article est invalide.');
    }
	elodie_cms_write_news(__DIR__ . '/../news.php', $news);
	echo '<style type="text/css">
.ui-dialog,.ui-dialog-content{
min-height: 0px !important;
margin:0px !important;
};
</style>
<div class="ui-state-highlight ui-corner-all" style="text-align:center;">
<p><span class="ui-icon ui-icon-info" style="float: left; margin:auto;text-align:center;margin-right: .3em;margin-left: .3em;"></span>'.NewsEdit.'</p></div>';
	echo '<br />';
	echo '<center><a href="index2.php?page=liste">'.Retour.'</a></center>';
} else {

echo'
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="content-type" content="text/html; charset=utf-8" />
<meta http-equiv="x-ua-compatible" content="ie=edge" />
<title>Elodie CMS</title>
<meta name="Description" content="Administration de Elodie CMS" />
<meta name="apple-mobile-web-app-capable" content="yes" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<link rel="stylesheet" href="defaut.css" />
<link rel="stylesheet" href="defaut2.css" />
<link rel="shortcut icon" type="image/x-icon" href="'.base64_decode($tableau[5]).'/Favicon.ico" sizes="16x16" />
<link rel="icon" type="image/x-icon" href="'.base64_decode($tableau[5]).'/Favicon.ico" sizes="16x16" />
<link rel="stylesheet" href="jquery/css/ui-lightness/jquery-ui-1.10.2.custom.css" />
<script src="js/jquery.min.js"></script>
<script src="js/jquery-ui.min.js"></script>
<script src="js/jquery.coda-slider-3.0.js"></script>

<script src="js/editeur.js"></script>
<script type="text/javascript">addEvt(window,\'load\',whizzywig);</script>
    <script>
        $(function(){
            setInterval(function(){
                $(\'#ajax-refresh\').load(\'chat.php\');
            }, 0);
        });
    </script>

</head>
<body>
<body onload="whizzywig()">
	
	<form action="" method="POST">'.elodie_cms_csrf_input().'
	'.Auteur.' : <strong>'.elodie_cms_escape_legacy_text(base64_decode($tableau[2])).'</strong> - <label for="titre">'.Titre.' : </label> <input type="text" required name="titre" id="titre"  placeholder="'.Articla.'" value="'.elodie_cms_escape_legacy_text($news[$newsAmodifier]['titre']).'" /> -
<label for="jour">'.Jour.' : </label> <input type="text" name="jour" id="jour" value="'.elodie_cms_escape_legacy_text($news[$newsAmodifier]['jour']).'" STYLE="width:70px;" readonly="readonly"/ >
- <label for="mois">'.Mois.' : </label> <input type="text" name="mois" id="mois" value="'.elodie_cms_escape_legacy_text($news[$newsAmodifier]['mois']).'" STYLE="width:70px;" readonly="readonly" />
- <label for="annee">'.Annee.' : </label> <input type="text" name="annee" id="annee" value="'.elodie_cms_escape_legacy_text($news[$newsAmodifier]['annee']).'" STYLE="width:70px;" readonly="readonly" />
<br /><br /><label for="chapo">'.Chapo.' : </label><input type="text" required placeholder="'.Articlb.'" name="chapo" id="chapo" rows="" cols="" value="'.elodie_cms_escape_legacy_text($news[$newsAmodifier]['chapo']).'" style="width: 82%;"/><br /><br />';

include ('includes/smiley.php');

echo'<textarea name="contenu" id="contenu" rows="" cols="" style="width: 100%;height: 400px;">'.elodie_cms_sanitize_article_html($news[$newsAmodifier]['contenu']).'</textarea>
	
<br/><label for="note">'.Note.'</label> : <SELECT name="note" id="note" STYLE="width:70px;">';

if ($news[$newsAmodifier]['note']=='Off') {$notesoff = 'selected="selected"';}
elseif ($news[$newsAmodifier]['note']==1) {$notes1 = 'selected="selected"';}
elseif ($news[$newsAmodifier]['note']==2) {$notes2 = 'selected="selected"';}
elseif ($news[$newsAmodifier]['note']==3) {$notes3 = 'selected="selected"';}
elseif ($news[$newsAmodifier]['note']==4) {$notes4 = 'selected="selected"';}
elseif ($news[$newsAmodifier]['note']==5) {$notes5 = 'selected="selected"';}
else {$notesoff = 'selected="selected"';}

$notes = array(

'Off' => $notesoff, 
'1' => $notes1,
'2' => $notes2,
'3' => $notes3,
'4' => $notes4,
'5' => $notes5

);

foreach ($notes as $notesA => $notesB) { 
echo'<option '.$notesB.'>'.$notesA.'</option>';
 };

echo'</SELECT>&nbsp; &nbsp;<b>'.Nota.'</b> .<br/>
		<br/>
<center><input type="submit" value="'.Ok.'" /></center>
	</form>';
	
}
}

/* Cette partie sert pour la connexion au Blog */

function connexion_blog() {

elodie_cms_start_session();

echo'<style type="text/css">#titre2 {box-shadow: rgba(200, 200, 200, 0.702) 0px 4px 10px -1px;border: 1px solid #E5E5E5;background: #FFFFFF;font-weight: 400;padding: 24px 24px 24px;text-align:center;color: red;font-size: 12px;width:250px;} #titre {box-shadow: rgba(200, 200, 200, 0.702) 0px 4px 10px -1px;border: 1px solid #E5E5E5;background: #FFFFFF;font-weight: 400;padding: 24px 24px 24px;text-align:center;color: #777777;font-size: 25px;width:250px;} #retour a:hover {font-weight: bold;} #retour a {color: #777777;text-decoration: none;} #retour {box-shadow: rgba(200, 200, 200, 0.702) 0px 4px 10px -1px;border: 1px solid #E5E5E5;background: #FFFFFF;font-weight: 400;padding: 24px 24px 24px;text-align:center;color: #777777;font-size: 12px;width:250px;} #page2 { margin: auto; width: 200px;} #ElodieCMS{text-align:center;font-size: 9px;color: #666666;}#Ok input{color: #FFFFFF;font-weight: 700;background: black !important;border:1px solid #2E83D9;font-size: 14px;} #login form {box-shadow: rgba(200, 200, 200, 0.702) 0px 4px 10px -1px;border: 1px solid #E5E5E5;background: #FFFFFF;font-weight: 400;padding: 24px 24px 24px;text-align:center;color: #777777;font-size: 14px;width:95%;} #login input { box-shadow: inset 1px 1px 2px rgba(200, 200, 200, 0.196);border:1px solid #BBBBBB;background: #F5F5F5; }</style>
<div id="login"><form action="identification.php" method="post"><b>'.Connexion.'</b><br/><br/>
     '.Login.' <br/><input type="text" name="login" value="" /><br /><br />
     '.Code.' <br/><input type="password" name="mdp" value="" /><br /><br />
     Code Google Authenticator / code de secours<br/><input type="text" name="code" inputmode="numeric" autocomplete="one-time-code" maxlength="32" /><br /><br />

  '.elodie_cms_csrf_input().'

     <div id="Ok"><input type="submit" value="'.Ok.'"></div></form></div>';

}

/* Supprimer des images en cliquant sur un lien */

function supprimer_images() {

$submittedId = $_POST['id'] ?? null;
if (!is_string($submittedId) || basename($submittedId) !== $submittedId) {
    http_response_code(400);
    exit('Nom de fichier invalide.');
}

$allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'bmp'];
$extension = strtolower(pathinfo($submittedId, PATHINFO_EXTENSION));
$directory = realpath(__DIR__ . '/../images');
$fichier = realpath(__DIR__ . '/../images/' . $submittedId);
if (!in_array($extension, $allowedExtensions, true)
    || $directory === false
    || $fichier === false
    || dirname($fichier) !== $directory) {
    http_response_code(404);
    exit('Image introuvable.');
}
if (!unlink($fichier)) {
    throw new RuntimeException('Impossible de supprimer l’image.');
}
echo '<meta http-equiv="Refresh" content="2; url=index2.php?page=images" />';
echo '<style type="text/css">
.ui-dialog,.ui-dialog-content{
min-height: 0px !important;
margin:0px !important;
};
</style>
<div class="ui-state-highlight ui-corner-all" style="text-align:center;">
<p><span class="ui-icon ui-icon-info" style="float: left; margin:auto;text-align:center;margin-right: .3em;margin-left: .3em;"></span>'.ImageDelete.'</p></div>';

}

/* Supprimer des news en cliquant sur un lien */

function supprimer_news() {

//Si l'id pass&eacute; en param&egrave;tre dans l'url n'existe pas, c'est que le visiteur a &eacute;t&eacute; amenen&eacute; ici par hasard
if(!isset($_POST['id']) || !is_string($_POST['id']) || !ctype_digit($_POST['id'])) {
	//Donc on redirige vers index.php
	header('Location: index.php?page=liste');
	//Puis on stoppe l'ex&eacute;cution du script
	exit();
}
//On r&eacute;cup&egrave;re l'array des news
$news = elodie_cms_read_news(__DIR__ . '/../news.php');
//Puis l'id pass&eacute; en param&egrave;tre
$id = (int) $_POST['id'];

//Si la news existe
if(isset($news[$id])) {
	//On efface l'index correspondant à l'id de la news
	unset($news[$id]);
	
	//Puis on sauvegarde le tout
	elodie_cms_write_news(__DIR__ . '/../news.php', $news);

echo '<style type="text/css">
.ui-dialog,.ui-dialog-content{
min-height: 0px !important;
margin:0px !important;
};
</style>
<div class="ui-state-highlight ui-corner-all" style="text-align:center;">
<p><span class="ui-icon ui-icon-info" style="float: left; margin:auto;text-align:center;margin-right: .3em;margin-left: .3em;"></span>'.NewsDelOn.'</p></div>';
}
else {
echo '<style type="text/css">
.ui-dialog,.ui-dialog-content{
min-height: 0px !important;
margin:0px !important;
};
</style>
<div class="ui-state-error ui-corner-all" style="text-align:center;">
<p><span class="ui-icon ui-icon-alert" style="float: left; margin:auto;text-align:center;margin-right: .3em;margin-left: .3em;"></span>'.NewsDelOff.'</p></div>';
}
echo '<br />';
echo '<center><a href="index2.php?page=liste">'.Retour.'</a></center>';

}

/* Envoyer des images via un formulaire */

function envoyer_images() {

$upload = $_FILES['avatar'] ?? null;
if (!is_array($upload)
    || ($upload['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK
    || !isset($upload['tmp_name'])
    || !is_string($upload['tmp_name'])
    || !is_uploaded_file($upload['tmp_name'])) {
    http_response_code(400);
    exit('Envoi d’image invalide.');
}

$taille = filesize($upload['tmp_name']);
$imageInfo = getimagesize($upload['tmp_name']);
$mimeType = (new finfo(FILEINFO_MIME_TYPE))->file($upload['tmp_name']);
$extensionsByMime = [
    'image/jpeg' => '.jpg',
    'image/png' => '.png',
    'image/gif' => '.gif',
    'image/bmp' => '.bmp',
];
if ($taille === false || $taille === 0 || $taille > 1048576) {
    http_response_code(400);
    exit(elodie_cms_escape(ImageGros));
}
if ($imageInfo === false
    || !isset($extensionsByMime[$mimeType])
    || $imageInfo['mime'] !== $mimeType
    || $imageInfo[0] > 10000
    || $imageInfo[1] > 10000
    || $imageInfo[0] * $imageInfo[1] > 40000000) {
    http_response_code(400);
    exit(elodie_cms_escape(ImageUpload));
}

$dossier = '../images/';
$fichier = basename(is_string($upload['name'] ?? null) ? $upload['name'] : 'image');
$taille_maxi = 1048576;
$extensions = array('.png', '.gif', '.jpg', '.bmp');
$extension = $extensionsByMime[$mimeType];
if(!in_array($extension, $extensions)) 
{
     echo '<meta http-equiv="Refresh" content="2; url=index2.php?page=images" />';
     $erreur = '<style type="text/css">
.ui-dialog,.ui-dialog-content{
min-height: 0px !important;
margin:0px !important;
};
</style>
<div class="ui-state-error ui-corner-all" style="text-align:center;">
<p><span class="ui-icon ui-icon-alert" style="float: left; margin:auto;text-align:center;margin-right: .3em;margin-left: .3em;"></span>'.ImageUpload.'</p></div>';
}
if($taille>$taille_maxi)
{
     echo '<meta http-equiv="Refresh" content="2; url=index2.php?page=images" />';
     $erreur = '<style type="text/css">
.ui-dialog,.ui-dialog-content{
min-height: 0px !important;
margin:0px !important;
};
</style>
<div class="ui-state-error ui-corner-all" style="text-align:center;">
<p><span class="ui-icon ui-icon-alert" style="float: left; margin:auto;text-align:center;margin-right: .3em;margin-left: .3em;"></span>'.ImageGros.'</p></div>';
}
if(!isset($erreur)) 
{
     $fichier = strtr($fichier, 
          'ÀÁÂÃÄÅÇ&egrave;&eacute;Ê&euml;ÌÍÎ&iuml;ÒÓ&ocirc;ÕÖÙÚÛÜÝàáâãäåç&egrave;&eacute;ê&euml;ìíî&iuml;ðòó&ocirc;õöùúûüýÿ', 
          'AAAAAACEEEEIIIIOOOOOUUUUYaaaaaaceeeeiiiioooooouuuuyy');
     $fichier = preg_replace('/([^.a-z0-9]+)/i', '-', $fichier);
     if(move_uploaded_file($upload['tmp_name'], $dossier . bin2hex(random_bytes(16)) . $extension))
     {
echo '<meta http-equiv="Refresh" content="2; url=index2.php?page=images" />';
echo '
<style type="text/css">
.ui-dialog,.ui-dialog-content{
min-height: 0px !important;
margin:0px !important;
};
</style>
<div class="ui-state-highlight ui-corner-all" style="text-align:center;">
<p><span class="ui-icon ui-icon-info" style="float: left; margin:auto;text-align:center;margin-right: .3em;margin-left: .3em;"></span>'.ImageSuccess.'</p></div>';
     }
     else 
     {
	  echo '<meta http-equiv="Refresh" content="2; url=index2.php?page=images" />';
          echo '<style type="text/css">
.ui-dialog,.ui-dialog-content{
min-height: 0px !important;
margin:0px !important;
};
</style>
<div class="ui-state-error ui-corner-all" style="text-align:center;">
<p><span class="ui-icon ui-icon-alert" style="float: left; margin:auto;text-align:center;margin-right: .3em;margin-left: .3em;"></span>'.ImageEchec.'</p></div>';
     }
}
else
{
     echo '<meta http-equiv="Refresh" content="2; url=index2.php?page=images" />';
     echo $erreur;
}
}
?>