<?php

require_once __DIR__ . '/security.php';
require __DIR__ . '/verif.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    elodie_cms_require_valid_csrf_token();
    $commentId = $_POST['id'] ?? null;
    $operation = $_POST['operation'] ?? null;
    if (!is_string($commentId) || !ctype_digit($commentId) || (int) $commentId < 1
        || !is_string($operation)) {
        http_response_code(400);
        exit('La demande de modération est invalide.');
    }

    if ($operation === 'approve') {
        elodie_cms_set_comment_status((int) $commentId, 'approved');
    } elseif ($operation === 'delete') {
        elodie_cms_delete_comment((int) $commentId);
    } else {
        http_response_code(400);
        exit('L’action de modération est invalide.');
    }

    header('Location: comments.php');
    exit();
}

$pendingComments = elodie_cms_list_comments('pending');
$approvedComments = elodie_cms_list_comments('approved');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Modération des commentaires - Elodie CMS</title>
    <link rel="stylesheet" href="defaut.css">
    <link rel="stylesheet" href="defaut2.css">
    <link rel="stylesheet" href="mobile.css">
    <style>
        body { max-width: 70rem; margin: 1rem auto; padding: 0 1rem; }
        .comment-card { margin: 1rem 0; padding: 1rem; border: 1px solid #aaa; background: #fff; }
        .comment-card form { display: inline-block; width: auto; margin: .5rem .5rem 0 0; }
        .comment-card button { min-height: 2.5rem; padding: .5rem .8rem; }
        .comment-meta { color: #555; }
    </style>
</head>
<body>
    <h1>Modération des commentaires</h1>
    <p><a href="index.php">Retour au tableau de bord</a></p>
    <p>Les nouveaux commentaires restent privés jusqu’à leur approbation.</p>

    <h2>En attente (<?= count($pendingComments) ?>)</h2>
    <?php if ($pendingComments === []): ?>
        <p>Aucun commentaire en attente.</p>
    <?php endif; ?>
    <?php foreach ($pendingComments as $comment): ?>
        <article class="comment-card">
            <h3><?= elodie_cms_escape($comment['author']) ?></h3>
            <p class="comment-meta">Article <?= (int) $comment['article_id'] ?> · <?= elodie_cms_escape($comment['created_at']) ?></p>
            <p><?= nl2br(elodie_cms_escape($comment['body']), false) ?></p>
            <form method="post" action="comments.php">
                <?= elodie_cms_csrf_input() ?>
                <input type="hidden" name="id" value="<?= (int) $comment['id'] ?>">
                <input type="hidden" name="operation" value="approve">
                <button type="submit">Approuver</button>
            </form>
            <form method="post" action="comments.php">
                <?= elodie_cms_csrf_input() ?>
                <input type="hidden" name="id" value="<?= (int) $comment['id'] ?>">
                <input type="hidden" name="operation" value="delete">
                <button type="submit">Supprimer</button>
            </form>
        </article>
    <?php endforeach; ?>

    <h2>Publiés (<?= count($approvedComments) ?>)</h2>
    <?php if ($approvedComments === []): ?>
        <p>Aucun commentaire publié.</p>
    <?php endif; ?>
    <?php foreach ($approvedComments as $comment): ?>
        <article class="comment-card">
            <h3><?= elodie_cms_escape($comment['author']) ?></h3>
            <p class="comment-meta">Article <?= (int) $comment['article_id'] ?> · <?= elodie_cms_escape($comment['created_at']) ?></p>
            <p><?= nl2br(elodie_cms_escape($comment['body']), false) ?></p>
            <form method="post" action="comments.php">
                <?= elodie_cms_csrf_input() ?>
                <input type="hidden" name="id" value="<?= (int) $comment['id'] ?>">
                <input type="hidden" name="operation" value="delete">
                <button type="submit">Supprimer</button>
            </form>
        </article>
    <?php endforeach; ?>
</body>
</html>
