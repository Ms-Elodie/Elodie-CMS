<?php

$language = base64_decode($tableau[1] ?? '', true);
if (!in_array($language, ['de', 'en', 'es', 'fr', 'it', 'nl', 'pt'], true)) {
    $language = 'fr';
}
include_once __DIR__ . '/../lang/' . $language . '-lang.php';
$GLOBALS['elodieCmsLanguage'] = $language;
require_once __DIR__ . '/../lang/interface.php';

?>
