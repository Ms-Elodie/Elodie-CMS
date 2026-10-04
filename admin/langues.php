<?php

$language = base64_decode($tableau[1] ?? '', true);
if (!in_array($language, ['en', 'es', 'fr', 'nl'], true)) {
    $language = 'fr';
}
include __DIR__ . '/../lang/' . $language . '-lang.php';

?>
