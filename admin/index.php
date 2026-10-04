<?php
require_once __DIR__ . '/security.php';

if (!elodie_cms_is_installed()) {
    header('Location: ../install.php');
    exit();
}


include('./verif.php');
error_reporting(0); 
include 'langues.php';
require 'fonctions.php';

echo'
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="content-type" content="text/html; charset=utf-8" />
<meta http-equiv="x-ua-compatible" content="ie=edge" />
<title>Elodie CMS '.elodie_cms_escape(elodie_cms_version()).'</title>
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
<script src="js/jquery.coda-slider-3.0.js"></script>';

?>

<script>
var _init = $.ui.dialog.prototype._init;
$.ui.dialog.prototype._init = function() {
   //Run the original initialization code
   _init.apply(this, arguments);
    
   //set some variables for use later
   var dialog_element = this;
   var dialog_id = this.uiDialogTitlebar.next().attr('id');
    
   //append our minimize icon
   this.uiDialogTitlebar.append('<a href="#" id="' + dialog_id +
   '-minbutton" class="ui-dialog-titlebar-minimize ui-corner-all">'+
   '<span class="ui-icon ui-icon-minusthick"></span></a>');
    
   //append our minimized state
   $('#dialog_window_minimized_container').append(
      '<div class="dialog_window_minimized ui-widget ui-state-default ui-corner-all" id="' +
      dialog_id + '_minimized">' + this.uiDialogTitlebar.find('.ui-dialog-title').text() +
      '<span class="ui-icon ui-icon-newwin"></div>');
    
   //create a hover event for the minimize button so that it looks good
   $('#' + dialog_id + '-minbutton').hover(function() {
      $(this).addClass('ui-state-hover');
   }, function() {
      $(this).removeClass('ui-state-hover');
   }).click(function() {
      //add a click event as well to do our "minimalization" of the window
      dialog_element.close();
      $('#' + dialog_id + '_minimized').show();
   });
    
   //create another click event that maximizes our minimized window
   $('#' + dialog_id + '_minimized').click(function() {
      $(this).hide();
      dialog_element.open();
   });
};
</script>

<?php

echo'

    <script>
      $(function() {
        $(\'#main-slider\').codaSlider({
          autoHeight: false,
          continuous:false,
          dynamicArrows: false,
          dynamicTabs: false
        });
        $(\'#showcase\').codaSlider();
        $(\'#continuous\').codaSlider({
          autoSlide: false,
          continuous: false,
          dynamicArrowsGraphical: false,
          dynamicTabsAlign: "right",
          dynamicTabsPosition: "bottom",
          panelTitleSelector: "div.title"
        });
        $(\'#dynamic-tabs\').codaSlider({
          autoSlideControls:false,
          dynamicTabsAlign: "left",
          dynamicTabsPosition: "top",
          dynamicArrows: false
        })
        $(\'#information\').codaSlider({
          dynamicArrows: false,
          dynamicTabs: false,
          slideEaseFunction: "easeOutCirc"
        });
      });
      </script>

<script src="js/editeur.js"></script>
<script type="text/javascript">addEvt(window,\'load\',whizzywig);</script>
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

<script>
$(function() {
$( "#dialog1" ).dialog({
width: 1020,
modal: false,
autoOpen: false,
resizable: false,
show: {
effect: "blind",
duration: 1000
},
hide: {
effect: "hide",
duration: 1000
}
});
$( "#opener1" ).click(function() {
$( "#dialog1" ).dialog( "open" );
});
});
</script>
<script>
$(function() {
$( "#dialog2" ).dialog({
width: 1020,
modal: false,
autoOpen: false,
resizable: false,
show: {
effect: "blind",
duration: 1000
},
hide: {
effect: "hide",
duration: 1000
}
});
$( "#opener2" ).click(function() {
$( "#dialog2" ).dialog( "open" );
});
});
</script>

<script>
$(function() {
$( "#dialog3" ).dialog({
width: 1060,
modal: false,
autoOpen: false,
resizable: false,
show: {
effect: "blind",
duration: 1000
},
hide: {
effect: "hide",
duration: 1000
}
});
$( "#opener3" ).click(function() {
$( "#dialog3" ).dialog( "open" );
});
});
</script>

<script>
$(function() {
$( "#dialog4" ).dialog({
width: 1020,
modal: false,
autoOpen: false,
resizable: false,
show: {
effect: "blind",
duration: 1000
},
hide: {
effect: "hide",
duration: 1000
}
});
$( "#opener4" ).click(function() {
$( "#dialog4" ).dialog( "open" );
});
});
</script>

<script>
$(function() {
$( "#dialog5" ).dialog({
resizable: false,
width: 1040,
modal: false,
autoOpen: false,
show: {
effect: "blind",
duration: 1000
},
hide: {
effect: "hide",
duration: 1000
}
});
$( "#opener5" ).click(function() {
$( "#dialog5" ).dialog( "open" );
});
});
</script>

<script>
$(function() {
$( "#dialog6" ).dialog({
width: 1020,
modal: false,
autoOpen: false,
resizable: false,
show: {
effect: "blind",
duration: 1000
},
hide: {
effect: "hide",
duration: 1000
}
});
$( "#opener6" ).click(function() {
$( "#dialog6" ).dialog( "open" );
});
});
</script>

</head>
<body>
<body onload="whizzywig()">';

echo'
<style type="text/css">
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
<div id="header">Elodie CMS '.elodie_cms_escape(elodie_cms_version()).'
';

include('includes/centre.php'); 

switch (is_string($_GET['page'] ?? null) ? $_GET['page'] : '')

{

default :  echo'<div id="dialog_window_minimized_container"></div>';
include('includes/bas.php');
}

?>
