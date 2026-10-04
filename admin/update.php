<?php

require_once __DIR__ . '/security.php';
require __DIR__ . '/verif.php';

$checked = ($_GET['check'] ?? '') === '1';
$message = '';
$releaseUrl = '';
$latestVersion = '';
$hasUpdate = false;

if ($checked) {
    $context = stream_context_create([
        'http' => [
            'method' => 'GET',
            'timeout' => 5,
            'ignore_errors' => true,
            'header' => "Accept: application/vnd.github+json\r\n"
                . 'User-Agent: ElodieCMS/' . elodie_cms_version() . "\r\n"
                . "X-GitHub-Api-Version: 2022-11-28\r\n",
        ],
        'ssl' => [
            'verify_peer' => true,
            'verify_peer_name' => true,
        ],
    ]);
    $response = @file_get_contents(
        'https://api.github.com/repos/Ms-Elodie/Elodie-CMS/releases/latest',
        false,
        $context
    );
    $statusLine = $http_response_header[0] ?? '';
    preg_match('/\s([0-9]{3})\s/', $statusLine, $statusMatches);
    $statusCode = isset($statusMatches[1]) ? (int) $statusMatches[1] : 0;

    if ($statusCode === 404) {
        $message = 'Aucune version officielle n’est encore publiée sur GitHub.';
    } elseif ($response === false || $statusCode !== 200) {
        $message = 'Vérification impossible. Réessayez plus tard ou consultez les versions sur GitHub.';
    } else {
        $release = json_decode($response, true);
        $tag = is_array($release) && is_string($release['tag_name'] ?? null)
            ? $release['tag_name']
            : '';
        $candidateUrl = is_array($release) && is_string($release['html_url'] ?? null)
            ? $release['html_url']
            : '';
        $urlParts = parse_url($candidateUrl);
        if (!preg_match('/^v?([0-9]+(?:\.[0-9]+){1,2})$/', $tag, $versionMatches)
            || !is_array($urlParts)
            || ($urlParts['scheme'] ?? '') !== 'https'
            || ($urlParts['host'] ?? '') !== 'github.com'
            || !str_starts_with(
                $urlParts['path'] ?? '',
                '/Ms-Elodie/Elodie-CMS/releases/tag/'
            )) {
            $message = 'La version reçue de GitHub n’a pas un format reconnu.';
        } else {
            $latestVersion = $versionMatches[1];
            $releaseUrl = $candidateUrl;
            $hasUpdate = version_compare($latestVersion, elodie_cms_version(), '>');
            $message = $hasUpdate
                ? 'Une nouvelle version est disponible.'
                : 'Elodie CMS est déjà à jour.';
        }
    }
}

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Mise à jour - Elodie CMS</title>
    <link rel="stylesheet" href="defaut.css">
    <link rel="stylesheet" href="defaut2.css">
    <style>
        body { max-width: 48rem; margin: 1rem auto; padding: 0 1rem; }
        .update-panel { margin: 1rem 0; padding: 1rem; border: 1px solid #aaa; background: #fff; }
        .update-panel button { min-height: 2.75rem; padding: .6rem 1rem; }
    </style>
</head>
<body>
    <h1>Elodie CMS <?= elodie_cms_escape(elodie_cms_version()) ?></h1>
    <p><a href="index.php">Retour au tableau de bord</a></p>
    <section class="update-panel">
        <h2>Vérifier les mises à jour</h2>
        <p>Cette vérification est lancée uniquement à votre demande et contacte l’API GitHub. Le CMS ne télécharge ni n’installe de fichiers automatiquement.</p>
        <form method="get" action="update.php">
            <button type="submit" name="check" value="1">Vérifier sur GitHub</button>
        </form>
        <?php if ($checked): ?>
            <p role="status"><?= elodie_cms_escape($message) ?></p>
            <?php if ($latestVersion !== ''): ?>
                <p>Version installée : <?= elodie_cms_escape(elodie_cms_version()) ?> · Version publiée : <?= elodie_cms_escape($latestVersion) ?></p>
            <?php endif; ?>
            <?php if ($hasUpdate): ?>
                <p><a href="<?= elodie_cms_escape($releaseUrl) ?>" target="_blank" rel="noopener noreferrer">Consulter la mise à jour <?= elodie_cms_escape($latestVersion) ?> sur GitHub</a></p>
            <?php endif; ?>
        <?php endif; ?>
        <p><a href="https://github.com/Ms-Elodie/Elodie-CMS/releases" target="_blank" rel="noopener noreferrer">Consulter toutes les versions publiées</a></p>
    </section>
</body>
</html>
