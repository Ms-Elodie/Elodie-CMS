<?php

require_once __DIR__ . '/security.php';
uag_start_session();

// on inclu la page de config
require_once __DIR__ . '/config.php';

if(!isset($_SESSION['_login']) || !isset($_SESSION['_pass']  ))
{
     // si on ne détecte aucune sessions, c'est que cette personne n'est pas connecté
     // on affiche le formulaire de connexion
     include("connexion.php");
     exit();
}
else
{
     // les sessions existe ... reste à savoir si les informations sont correct ou non
     if (!is_string($_SESSION['_login'])
         || !is_string($_SESSION['_pass'])
         || !hash_equals($_admin_login, $_SESSION['_login'])
         || !hash_equals($_admin_pass, $_SESSION['_pass']))
     {
         include("connexion.php");
         exit();
     }
}
?>
