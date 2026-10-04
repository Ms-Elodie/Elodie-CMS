<?php

require_once __DIR__ . '/security.php';
if (!uag_is_installed()) {
    header('Location: ../install.php');
    exit();
}
;

error_reporting(0); 
include '../lang/en-lang.php';
require 'fonctions.php';

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

    <script>
        $(function(){
            setInterval(function(){
                $(\'#ajax-refresh\').load(\'chat.php\');
            }, 0);
        });
    </script>
	
<script>
$(function() {
$( "#dialog-modal" ).dialog({
width: 1020,
modal: false
});
});
</script>

</head>
<body>';

echo'
<style type="text/css">
#menu{display:none;}
.ui-dialog-titlebar-close{display:none;}
';
if (base64_decode($tableau[30])=='') {echo'
html {background-image:url(\''.base64_decode($tableau[5]).'/fond.jpg\');}
page{background-color:transparent !important;}
body{background-color:transparent !important;}';}
else {echo'
html {background-image:url(\''.base64_decode($tableau[30]).'\');}
page{background-color:transparent !important;}
body{background-color:transparent !important;}';}

echo'</style>

<div id="page">
<div id="header2">
<div id="ajax-refresh">';
include('chat.php');

echo'</div>';

echo'</div>
<div id="header">Elodie CMS
';

echo'</div><div id="menu">';
echo'</div><div id="contenu2">';

/* Les différentes Pages de l'administration */

echo'<div id="dialog-modal" title="';

echo Connexion;

echo'">';

connexion_blog();

echo'</div>';

include('includes/bas.php');

?>