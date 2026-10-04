<?php
require_once __DIR__ . '/security.php';
require_once __DIR__ . '/markup.php';

/* Public comments */

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
    if (!elodie_cms_comments_enabled()) {
        return;
    }

    $articleId = public_article_id(elodie_cms_read_news());
    if ($articleId === null) {
        return;
    }

    echo '<article class="comments"><h2>' . elodie_cms_escape(elodie_cms_ui('comments')) . '</h2>';
    if (($_GET['comment'] ?? '') === 'sent') {
        echo '<p role="status">' . elodie_cms_escape(elodie_cms_ui('comments_sent')) . '</p>';
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
        . '<label for="comment-author">' . elodie_cms_escape(elodie_cms_ui('name')) . '</label>'
        . '<input id="comment-author" name="author" maxlength="120" required autocomplete="name">'
        . '<label for="comment-body">' . elodie_cms_escape(elodie_cms_ui('comment')) . '</label>'
        . '<textarea id="comment-body" name="body" maxlength="5000" required rows="6"></textarea>'
        . '<button type="submit">' . elodie_cms_escape(elodie_cms_ui('send_comment')) . '</button></form></article>';
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

if (($_GET['id'] ?? '') === '2') {

$password = $_POST['7'] ?? null;
if (!is_string($password)) {
    http_response_code(400);
    exit(elodie_cms_ui('invalid_password'));
}
if ($password !== '' && (strlen($password) < 12 || strlen($password) > 72)) {
    http_response_code(400);
    exit(elodie_cms_ui('password_length'));
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
        exit(elodie_cms_ui('invalid_resource_url'));
    }
    if ($index === 5 && !elodie_cms_valid_http_url($value)) {
        http_response_code(400);
        exit(elodie_cms_ui('invalid_site_url'));
    }
    if ($index === 3 && !in_array($value, ['on', 'off'], true)) {
        http_response_code(400);
        exit(elodie_cms_ui('invalid_option'));
    }
    if ($index === 1 && !in_array($value, ['de', 'en', 'es', 'fr', 'it', 'nl', 'pt'], true)) {
        http_response_code(400);
        exit(elodie_cms_ui('invalid_language'));
    }
    if (in_array($index, [4, 9, 10], true) && !in_array($value, ['on', 'off'], true)) {
        http_response_code(400);
        exit(elodie_cms_ui('invalid_option'));
    }
    if ($index === 8 && !in_array($value, ['on', 'on2', 'off'], true)) {
        http_response_code(400);
        exit(elodie_cms_ui('invalid_option'));
    }
    $settings[$index] = htmlentities($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
$encodedSettings = array_map('base64_encode', $settings);
elodie_cms_write_encoded_configuration($encodedSettings);
$_SESSION['_login'] = html_entity_decode($settings[6], ENT_QUOTES | ENT_HTML5, 'UTF-8');
$_SESSION['_pass'] = $settings[7];
header('Location: index.php?page=configuration&saved=1');
exit();

}

else {
  
error_reporting(0);

echo'<form action="index.php?page=configuration&id=2" method="post">'.elodie_cms_csrf_input().'
	
<div class="settings-sections">
<div>
<div class="settings-overview">';
 
if (base64_decode($tableau[4])==='on') {$paginationOn = 'selected="selected"';}
elseif (base64_decode($tableau[4])==='off') {$paginationOff = 'selected="selected"';}
else {$paginationOn = 'selected="selected"';}

echo'<div>
<h2 class="settings-title">'.Accueil.'</h2>
<p><b>'.BienvenueConfig.'</b></p>
<table style="margin:auto;padding-right:60px;">
<tr><td style="padding-left:10px;padding-top:10px;">'.General.'</td><td style="padding-left:20px;padding-top:10px;">'.Generala.'</td> </tr>
<tr><td style="padding-left:10px;padding-top:10px;">'.Profil.'</td><td style="padding-left:20px;padding-top:10px;">'.Profila.'</td> </tr>
<tr><td style="padding-left:10px;padding-top:10px;">'.Theme.'</td><td style="padding-left:20px;padding-top:10px;">'.Themea.'</td> </tr>
<tr><td style="padding-left:10px;padding-top:10px;">'.Menu.'</td><td style="padding-left:20px;padding-top:10px;">'.Menua.'</td> </tr>
</table>
</div>
<div>
<h2 class="settings-title">'.General.'</h2>
<table style="margin:auto;padding-right:60px;">
<tr>
<td class="titre"></br>'.Titre.'  &nbsp;</td><td></br><input type="text" name="0" value="'.base64_decode($tableau[0]).'" placeholder="'.Titreb.'" STYLE="width:170px;" /></td>
<td class="titre" style="padding-left:20px;" ></br>'.Langue.'  &nbsp;</td><td></br><SELECT value="'.base64_decode($tableau[1]).'" name="1" STYLE="width:180px;">';

$languages = [
    'de' => 'Deutsch',
    'en' => 'English',
    'es' => 'Español',
    'fr' => 'Français',
    'it' => 'Italiano',
    'nl' => 'Nederlands',
    'pt' => 'Português',
];

foreach ($languages as $languageCode => $languageName) {
    $selected = base64_decode($tableau[1]) === $languageCode ? ' selected="selected"' : '';
    echo '<option value="' . elodie_cms_escape($languageCode) . '"' . $selected . '>'
        . elodie_cms_escape($languageName) . '</option>';
}

echo'</SELECT></td></tr>

<tr><td class="titre"></br>'.Gerant.'  &nbsp;</td><td></br><input type="text" required name="2" value="'.base64_decode($tableau[2]).'" placeholder="'.Webmasterb.'" STYLE="width:170px;"/></td>
<td class="titre" style="padding-left:20px;"></br>'.elodie_cms_escape(elodie_cms_ui('internal_comments')).' &nbsp;</td><td></br><select name="3" style="width:180px;">
<option value="off" '.(base64_decode($tableau[3] ?? '') === 'on' ? '' : 'selected="selected"').'>'.elodie_cms_escape(elodie_cms_ui('disabled')).'</option>
<option value="on" '.(base64_decode($tableau[3] ?? '') === 'on' ? 'selected="selected"' : '').'>'.elodie_cms_escape(elodie_cms_ui('enabled')).'</option>
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

echo'<td class="titre" style="padding-left:20px;"></br>'.elodie_cms_escape(elodie_cms_ui('url_rewriting')).'  &nbsp;</td><td></br><SELECT value="'.base64_decode($tableau[8]).'" name="8" STYLE="width:180px;">
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
<h2 class="settings-title">'.Profil.'</h2>
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
<h2 class="settings-title">'.Theme.'</h2>
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
<td COLSPAN=4><center><br/><b>'.elodie_cms_escape(elodie_cms_ui('background_favicon')).'</b></center></td>
</tr>

<tr>
<td class="titre"></br>'.elodie_cms_escape(elodie_cms_ui('background')).' &nbsp;</td><td></br><input type="text" name="30" value="'.base64_decode($tableau[30]).'" placeholder="'.LienBackground.'" STYLE="width:170px;" /></td>
</tr><tr>
<td class="profil"></br>'.elodie_cms_escape(elodie_cms_ui('favicon')).' &nbsp;</td><td></br><input type="text" name="31" value="'.base64_decode($tableau[31]).'" placeholder="'.LienFavicon.'" STYLE="width:170px;" /></td>
</tr>
</table>
</div>
  
<div>
<h2 class="settings-title">'.Menu.'</h2>
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
</div>
</div>

<table style="margin:auto;padding-right:0px;">
<tr>
<td class="titre"></br>'.Code.'  &nbsp;</td><td></br><input type="password" autocomplete="new-password" minlength="12" maxlength="72" name="7" value="" placeholder="'.elodie_cms_escape(elodie_cms_ui('keep_password')).'" alt="" STYLE="width:200px;" /></td>
</tr>  
</table>
		
<br/> 

<center>
<input class="submit" type="submit" value="'.Ok.'" name="submit" />
</center>
</form>
';

};

}

/* La liste des News dans l'administration */

function liste_news() {

    $settings = lire_array('configuration.txt');
    $articles = elodie_cms_read_news(__DIR__ . '/../news.php');
    if ($articles === []) {
        echo '<p>' . elodie_cms_escape(elodie_cms_ui('no_articles')) . ' <a href="index.php?page=ajouter">'
            . elodie_cms_escape(elodie_cms_ui('write_article')) . '</a>.</p>';
        return;
    }

    echo '<div class="article-table-wrap"><table class="article-table">
        <thead><tr>
            <th scope="col">' . Titre . '</th>
            <th scope="col">' . Date . '</th>
            <th scope="col">' . Auteur . '</th>
            <th scope="col">' . elodie_cms_escape(elodie_cms_ui('actions')) . '</th>
        </tr></thead><tbody>';
    foreach ($articles as $id => $article) {
        $date = base64_decode($settings[1] ?? '', true) === 'fr'
            ? $article['jour'] . '-' . $article['mois'] . '-' . $article['annee']
            : $article['annee'] . '-' . $article['mois'] . '-' . $article['jour'];
        echo '<tr>
            <td data-label="' . elodie_cms_escape(Titre) . '"><strong>' . elodie_cms_escape_legacy_text($article['titre']) . '</strong></td>
            <td data-label="' . elodie_cms_escape(Date) . '">' . elodie_cms_escape_legacy_text($date) . '</td>
            <td data-label="' . elodie_cms_escape(Auteur) . '">' . elodie_cms_escape_legacy_text(base64_decode($settings[2] ?? '', true) ?: '') . '</td>
            <td data-label="' . elodie_cms_escape(elodie_cms_ui('actions')) . '"><div class="article-row-actions">
                <a class="admin-action-link" href="index.php?page=editer&amp;id=' . (int) $id . '">' . elodie_cms_escape(Editer) . '</a>
                <form method="post" action="index.php?page=supprimer">
                    ' . elodie_cms_csrf_input() . '
                    <input type="hidden" name="id" value="' . (int) $id . '">
                    <button type="submit" class="admin-danger-button">' . elodie_cms_escape(Supprimer) . '</button>
                </form>
            </div></td>
        </tr>';
    }
    echo '</tbody></table></div>';
} 

/* Le Formulaire pour envoyer les Images */

function formulaire_images() {

echo'<form method="POST" action="index.php?page=upload" enctype="multipart/form-data">'.elodie_cms_csrf_input().'
     <input type="hidden" name="MAX_FILE_SIZE" value="1048576">
     '.Fichier.' : <input type="file" name="avatar">
     <input type="submit" name="envoyer" value="'.Ok.'">
</form>';

}

/* Affichage des images, Le lien de l'image et lien pour la supprimer */

function images() {

    $imageDirectory = __DIR__ . '/../images';
    $files = scandir($imageDirectory);
    if ($files === false) {
        throw new RuntimeException('Impossible de lire le dossier des images.');
    }

    $allowedExtensions = ['jpg', 'jpeg', 'gif', 'png', 'bmp'];
    echo '<div class="media-grid">';
    $count = 0;
    foreach ($files as $filename) {
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        if (!in_array($extension, $allowedExtensions, true)
            || !is_file($imageDirectory . DIRECTORY_SEPARATOR . $filename)) {
            continue;
        }

        $safeFilename = elodie_cms_escape($filename);
        $imageUrl = '../images/' . rawurlencode($filename);
        echo '<article class="media-card">
            <a href="' . elodie_cms_escape($imageUrl) . '" target="_blank" rel="noopener noreferrer">
                <img src="' . elodie_cms_escape($imageUrl) . '" alt="' . $safeFilename . '" loading="lazy">
            </a>
            <p class="media-filename">' . $safeFilename . '</p>
            <div class="media-actions">
                <a href="' . elodie_cms_escape($imageUrl) . '" target="_blank" rel="noopener noreferrer">'
                    . elodie_cms_escape(elodie_cms_ui('preview')) . '</a>
                <form method="post" action="index.php?page=delete">
                    ' . elodie_cms_csrf_input() . '
                    <input type="hidden" name="id" value="' . $safeFilename . '">
                    <button type="submit" aria-label="' . elodie_cms_escape(Supprimer) . '">' . elodie_cms_escape(Supprimer) . '</button>
                </form>
            </div>
        </article>';
        $count++;
    }
    if ($count === 0) {
        echo '<p class="media-empty">' . elodie_cms_escape(elodie_cms_ui('no_images')) . '</p>';
    }
    echo '</div>';
}

function elodie_cms_article_editor_images(): array
{
    $directory = __DIR__ . '/../images';
    $files = scandir($directory);
    if ($files === false) {
        throw new RuntimeException('Impossible de lire le dossier des images.');
    }
    $settings = elodie_cms_read_encoded_configuration();
    $siteUrl = base64_decode($settings[5] ?? '', true);
    if (!is_string($siteUrl) || !elodie_cms_valid_http_url($siteUrl)) {
        throw new RuntimeException('L’adresse du site est invalide.');
    }

    $images = [];
    foreach ($files as $filename) {
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $path = $directory . DIRECTORY_SEPARATOR . $filename;
        if (!in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'bmp'], true)
            || is_link($path)
            || !is_file($path)
            || getimagesize($path) === false) {
            continue;
        }

        $images[] = [
            'filename' => $filename,
            'url' => rtrim($siteUrl, '/') . '/images/' . rawurlencode($filename),
            'alt' => pathinfo($filename, PATHINFO_FILENAME),
        ];
    }

    return $images;
}

function elodie_cms_article_editor(string $content, string $format = 'visual'): void
{
    if ($format === 'visual') {
        $content = elodie_cms_sanitize_article_html($content);
    }
    $images = elodie_cms_article_editor_images();
    $isVisual = $format === 'visual';
    echo '<div class="article-editor" data-article-editor'
        . ' data-empty-article="' . elodie_cms_escape(elodie_cms_ui('empty_article')) . '">
        <label for="article-format">' . elodie_cms_escape(elodie_cms_ui('format_choice')) . '</label>
        <select id="article-format" name="format" data-content-format>
            <option value="visual"' . ($format === 'visual' ? ' selected' : '') . '>' . elodie_cms_escape(elodie_cms_ui('format_visual')) . '</option>
            <option value="markdown"' . ($format === 'markdown' ? ' selected' : '') . '>' . elodie_cms_escape(elodie_cms_ui('format_markdown')) . '</option>
            <option value="bbcode"' . ($format === 'bbcode' ? ' selected' : '') . '>' . elodie_cms_escape(elodie_cms_ui('format_bbcode')) . '</option>
        </select>
        <p class="article-editor-help" data-format-help>' . elodie_cms_escape(elodie_cms_ui('format_help')) . '</p>
        <p class="article-editor-help" data-markup-help hidden>' . elodie_cms_escape(elodie_cms_ui('markup_help')) . '</p>
        <div class="article-editor-toolbar" role="toolbar" aria-label="' . elodie_cms_escape(elodie_cms_ui('editor_toolbar')) . '"' . ($isVisual ? '' : ' hidden') . '>
            <button type="button" data-editor-command="formatBlock" data-editor-value="H2">' . elodie_cms_escape(Titre) . '</button>
            <button type="button" data-editor-command="formatBlock" data-editor-value="H3">' . elodie_cms_escape(elodie_cms_ui('subtitle')) . '</button>
            <button type="button" data-editor-command="formatBlock" data-editor-value="P">' . elodie_cms_escape(elodie_cms_ui('paragraph')) . '</button>
            <button type="button" data-editor-command="bold" aria-label="' . elodie_cms_escape(elodie_cms_ui('bold')) . '"><strong>B</strong></button>
            <button type="button" data-editor-command="italic" aria-label="' . elodie_cms_escape(elodie_cms_ui('italic')) . '"><em>I</em></button>
            <button type="button" data-editor-command="underline" aria-label="' . elodie_cms_escape(elodie_cms_ui('underline')) . '"><u>U</u></button>
            <button type="button" data-editor-command="justifyLeft" aria-label="' . elodie_cms_escape(elodie_cms_ui('align_left')) . '">' . elodie_cms_escape(elodie_cms_ui('align_left')) . '</button>
            <button type="button" data-editor-command="justifyCenter" aria-label="' . elodie_cms_escape(elodie_cms_ui('align_center')) . '">' . elodie_cms_escape(elodie_cms_ui('align_center')) . '</button>
            <button type="button" data-editor-command="justifyRight" aria-label="' . elodie_cms_escape(elodie_cms_ui('align_right')) . '">' . elodie_cms_escape(elodie_cms_ui('align_right')) . '</button>
            <button type="button" data-editor-command="justifyFull" aria-label="' . elodie_cms_escape(elodie_cms_ui('align_justify')) . '">' . elodie_cms_escape(elodie_cms_ui('align_justify')) . '</button>
            <button type="button" data-editor-command="insertUnorderedList">' . elodie_cms_escape(elodie_cms_ui('list')) . '</button>
        </div>
        <div class="article-image-insert"' . ($isVisual ? '' : ' hidden') . '>
            <label for="article-image-selection">' . elodie_cms_escape(elodie_cms_ui('choose_image')) . '</label>
            <select id="article-image-selection" data-editor-image>
                <option value="">' . elodie_cms_escape(elodie_cms_ui('choose_image')) . '</option>';
    foreach ($images as $image) {
        echo '<option value="' . elodie_cms_escape($image['url']) . '" data-alt="'
            . elodie_cms_escape($image['alt']) . '">' . elodie_cms_escape($image['filename']) . '</option>';
    }
    echo '</select>
            <button type="button" data-insert-image' . ($images === [] ? ' disabled' : '') . '>'
                . elodie_cms_escape(elodie_cms_ui('insert_image')) . '</button>
        </div>
        <p class="article-editor-help"' . ($isVisual ? '' : ' hidden') . '>' . elodie_cms_escape(elodie_cms_ui('editor_help')) . '</p>
        <p class="article-editor-error" data-editor-error role="alert" hidden></p>
        <div class="article-editor-canvas" data-editor-canvas role="textbox" aria-label="' . elodie_cms_escape(elodie_cms_ui('article_content')) . '" aria-multiline="true" contenteditable="true" hidden></div>
        <textarea name="contenu" id="contenu" class="article-editor-source" rows="16" required>'
        . elodie_cms_escape($content) . '</textarea>
    </div>';
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
     $format = $_POST['format'] ?? 'visual';
     if (!is_string($format) || !in_array($format, ['visual', 'markdown', 'bbcode'], true)) {
         throw new InvalidArgumentException('Le format de l’article est invalide.');
     }
     if (!elodie_cms_valid_article_date($annee, $mois, $jour)
         || !in_array($note, ['Off', '0', '1', '2', '3', '4', '5'], true)) {
         throw new InvalidArgumentException('La date ou la note de l’article est invalide.');
     }
     if ($titre === '') {
         throw new InvalidArgumentException('Le titre de l’article est obligatoire.');
     }
     $contenu = $format === 'visual' ? elodie_cms_sanitize_article_html($contenu) : $contenu;
     $renderedContent = elodie_cms_render_article_content($contenu, $format);
     if (trim(strip_tags($renderedContent)) === '' && !str_contains($renderedContent, '<img')) {
         throw new InvalidArgumentException('Le contenu de l’article ne peut pas être vide.');
     }
	//On r&eacute;cup&egrave;re les donn&eacute;es d&eacutejà existantes
	$news = elodie_cms_read_news(__DIR__ . '/../news.php');
	$news[] = array('titre' => $titre, 'jour' => $jour, 'mois' => $mois, 'annee' => $annee,'contenu' => $contenu, 'chapo' => $chapo, 'note' => $note, 'format' => $format);
	elodie_cms_write_news(__DIR__ . '/../news.php', $news);
	
      echo '<p class="admin-notice" role="status">'.NewsAdd.'</p>';
      echo '<br />';
      echo '<a href="index.php?page=ajouter">'.Retour.'</a>';
}
else {
	 echo'<form class="article-form" action="index.php?page=ajouter" method="post">'.elodie_cms_csrf_input().'
<p><strong>'.Auteur.' :</strong> '.elodie_cms_escape_legacy_text(base64_decode($tableau[2] ?? '', true) ?: '').'</p>
<label for="titre">'.Titre.' : </label> <input type="text" required name="titre" id="titre" placeholder="'.Articla.'" />

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

<br /><br /><label for="chapo"> '.Chapo.' : </label><input type="text" name="chapo" id="chapo" placeholder="'.Articlb.'" style="width: 82%;"/><br />
<p class="article-editor-help">'.elodie_cms_escape(elodie_cms_ui('summary_help')).'</p>';

elodie_cms_article_editor('', 'visual');

echo'
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
$currentFormat = $news[$newsAmodifier]['format'] ?? 'visual';
if (!is_string($currentFormat) || !in_array($currentFormat, ['visual', 'markdown', 'bbcode'], true)) {
    $currentFormat = 'visual';
}
if(isset($_POST['titre']) && isset($_POST['contenu'])) {
$format = $_POST['format'] ?? 'visual';
if (!is_string($format) || !in_array($format, ['visual', 'markdown', 'bbcode'], true)) {
    throw new InvalidArgumentException('Le format de l’article est invalide.');
}
$news[$newsAmodifier]['format'] = $format;
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
$rawContent = elodie_cms_post_string('contenu');
$news[$newsAmodifier]['contenu'] = $format === 'visual'
    ? elodie_cms_sanitize_article_html($rawContent)
    : $rawContent;
	$news[$newsAmodifier]['chapo'] = elodie_cms_post_string('chapo');
	$news[$newsAmodifier]['note'] = elodie_cms_post_string('note');
    if (!in_array($news[$newsAmodifier]['note'], ['Off', '0', '1', '2', '3', '4', '5'], true)) {
        throw new InvalidArgumentException('La note de l’article est invalide.');
    }
    $renderedContent = elodie_cms_render_article_content($news[$newsAmodifier]['contenu'], $format);
    if ($news[$newsAmodifier]['titre'] === ''
        || (trim(strip_tags($renderedContent)) === '' && !str_contains($renderedContent, '<img'))) {
        throw new InvalidArgumentException('Le titre et le contenu sont obligatoires.');
    }
	elodie_cms_write_news(__DIR__ . '/../news.php', $news);
	echo '<p class="admin-notice" role="status">'.NewsEdit.'</p>';
	echo '<br />';
	echo '<a href="index.php?page=liste">'.Retour.'</a>';
} else {

echo'<form class="article-form" action="index.php?page=editer&amp;id='.(int) $newsAmodifier.'" method="post">'.elodie_cms_csrf_input().'
	'.Auteur.' : <strong>'.elodie_cms_escape_legacy_text(base64_decode($tableau[2])).'</strong> - <label for="titre">'.Titre.' : </label> <input type="text" required name="titre" id="titre"  placeholder="'.Articla.'" value="'.elodie_cms_escape_legacy_text($news[$newsAmodifier]['titre']).'" /> -
<label for="jour">'.Jour.' : </label> <input type="text" name="jour" id="jour" value="'.elodie_cms_escape_legacy_text($news[$newsAmodifier]['jour']).'" STYLE="width:70px;" readonly="readonly"/ >
- <label for="mois">'.Mois.' : </label> <input type="text" name="mois" id="mois" value="'.elodie_cms_escape_legacy_text($news[$newsAmodifier]['mois']).'" STYLE="width:70px;" readonly="readonly" />
- <label for="annee">'.Annee.' : </label> <input type="text" name="annee" id="annee" value="'.elodie_cms_escape_legacy_text($news[$newsAmodifier]['annee']).'" STYLE="width:70px;" readonly="readonly" />
<br /><br /><label for="chapo">'.Chapo.' : </label><input type="text" placeholder="'.Articlb.'" name="chapo" id="chapo" value="'.elodie_cms_escape_legacy_text($news[$newsAmodifier]['chapo']).'" style="width: 82%;"/><br />
<p class="article-editor-help">'.elodie_cms_escape(elodie_cms_ui('summary_help')).'</p>';

elodie_cms_article_editor($news[$newsAmodifier]['contenu'], $currentFormat);

echo'
	
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

echo'<form class="auth-form" action="identification.php" method="post">
    <label for="login">'.Login.'</label>
    <input id="login" type="text" name="login" autocomplete="username" required>
    <label for="password">'.Code.'</label>
    <input id="password" type="password" name="mdp" autocomplete="current-password" required>
    <label for="totp">'.elodie_cms_escape(elodie_cms_ui('authenticator_or_recovery')).'</label>
    <input id="totp" type="text" name="code" inputmode="numeric" autocomplete="one-time-code" maxlength="32" required>
    '.elodie_cms_csrf_input().'
    <button type="submit">'.Ok.'</button>
</form>';

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
echo '<meta http-equiv="Refresh" content="2; url=index.php?page=images" />';
echo '<p class="admin-notice" role="status">'.ImageDelete.'</p>';

}

/* Supprimer des news en cliquant sur un lien */

function supprimer_news() {

//Si l'id pass&eacute; en param&egrave;tre dans l'url n'existe pas, c'est que le visiteur a &eacute;t&eacute; amenen&eacute; ici par hasard
if(!isset($_POST['id']) || !is_string($_POST['id']) || !ctype_digit($_POST['id'])) {
	//Donc on redirige vers index.php
	http_response_code(400);
	exit('Identifiant d’article invalide.');
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

echo '<p class="admin-notice" role="status">'.NewsDelOn.'</p>';
}
else {
echo '<p class="admin-notice admin-notice-error" role="alert">'.NewsDelOff.'</p>';
}
echo '<br />';
echo '<a href="index.php?page=liste">'.Retour.'</a>';

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
     echo '<meta http-equiv="Refresh" content="2; url=index.php?page=images" />';
     $erreur = '<p class="admin-notice admin-notice-error" role="alert">'.ImageUpload.'</p>';
}
if($taille>$taille_maxi)
{
     echo '<meta http-equiv="Refresh" content="2; url=index.php?page=images" />';
     $erreur = '<p class="admin-notice admin-notice-error" role="alert">'.ImageGros.'</p>';
}
if(!isset($erreur)) 
{
     $fichier = strtr($fichier, 
          'ÀÁÂÃÄÅÇ&egrave;&eacute;Ê&euml;ÌÍÎ&iuml;ÒÓ&ocirc;ÕÖÙÚÛÜÝàáâãäåç&egrave;&eacute;ê&euml;ìíî&iuml;ðòó&ocirc;õöùúûüýÿ', 
          'AAAAAACEEEEIIIIOOOOOUUUUYaaaaaaceeeeiiiioooooouuuuyy');
     $fichier = preg_replace('/([^.a-z0-9]+)/i', '-', $fichier);
     if(move_uploaded_file($upload['tmp_name'], $dossier . bin2hex(random_bytes(16)) . $extension))
     {
echo '<meta http-equiv="Refresh" content="2; url=index.php?page=images" />';
echo '<p class="admin-notice" role="status">'.ImageSuccess.'</p>';
     }
     else 
     {
	  echo '<meta http-equiv="Refresh" content="2; url=index.php?page=images" />';
          echo '<p class="admin-notice admin-notice-error" role="alert">'.ImageEchec.'</p>';
     }
}
else
{
     echo '<meta http-equiv="Refresh" content="2; url=index.php?page=images" />';
     echo $erreur;
}
}
?>