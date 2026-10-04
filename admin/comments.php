<?php

require_once __DIR__ . '/security.php';
require __DIR__ . '/verif.php';
include __DIR__ . '/langues.php';
require_once __DIR__ . '/fonctions.php';
$page = 'comments';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    elodie_cms_require_valid_csrf_token();
    $commentId = $_POST['id'] ?? null;
    $operation = $_POST['operation'] ?? null;
    if (!is_string($commentId) || !ctype_digit($commentId) || (int) $commentId < 1
        || !is_string($operation)) {
        http_response_code(400);
        exit(elodie_cms_ui('invalid_moderation_request'));
    }

    if ($operation === 'approve') {
        elodie_cms_set_comment_status((int) $commentId, 'approved');
    } elseif ($operation === 'delete') {
        elodie_cms_delete_comment((int) $commentId);
    } else {
        http_response_code(400);
        exit(elodie_cms_ui('invalid_moderation_action'));
    }

    header('Location: comments.php');
    exit();
}

$pendingComments = elodie_cms_list_comments('pending');
$approvedComments = elodie_cms_list_comments('approved');
?>
<!DOCTYPE html>
<html lang="<?= elodie_cms_escape($GLOBALS['elodieCmsLanguage']) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= elodie_cms_escape(elodie_cms_ui('moderation_title')) ?> - Elodie CMS</title>
    <link rel="stylesheet" href="mobile.css">
    <style>
        .comment-card { margin: 1rem 0; padding: 1rem; border: 1px solid #aaa; background: #fff; }
        .comment-card form { display: inline-block; width: auto; margin: .5rem .5rem 0 0; }
        .comment-card button { min-height: 2.5rem; padding: .5rem .8rem; }
        .comment-meta { color: #555; }
    </style>
</head>
<body class="admin">
    <?php include __DIR__ . '/includes/topbar.php'; ?>
    <div class="admin-layout">
        <?php include __DIR__ . '/includes/menu.php'; ?>
        <main class="admin-main" id="contenu2">
        <div class="admin-page-heading"><p class="admin-eyebrow"><?= elodie_cms_escape(elodie_cms_ui('administration')) ?></p><h1><?= elodie_cms_escape(elodie_cms_ui('comments')) ?></h1></div>
        <section class="admin-content">
    <p><?= elodie_cms_escape(elodie_cms_ui('pending_help')) ?></p>

    <h2><?= elodie_cms_escape(elodie_cms_ui('pending')) ?> (<?= count($pendingComments) ?>)</h2>
    <?php if ($pendingComments === []): ?>
        <p><?= elodie_cms_escape(elodie_cms_ui('no_pending')) ?></p>
    <?php endif; ?>
    <?php foreach ($pendingComments as $comment): ?>
        <article class="comment-card">
            <h3><?= elodie_cms_escape($comment['author']) ?></h3>
            <p class="comment-meta"><?= elodie_cms_escape(elodie_cms_ui('article')) ?> <?= (int) $comment['article_id'] ?> · <?= elodie_cms_escape($comment['created_at']) ?></p>
            <p><?= nl2br(elodie_cms_escape($comment['body']), false) ?></p>
            <form method="post" action="comments.php">
                <?= elodie_cms_csrf_input() ?>
                <input type="hidden" name="id" value="<?= (int) $comment['id'] ?>">
                <input type="hidden" name="operation" value="approve">
                <button type="submit"><?= elodie_cms_escape(elodie_cms_ui('approve')) ?></button>
            </form>
            <form method="post" action="comments.php">
                <?= elodie_cms_csrf_input() ?>
                <input type="hidden" name="id" value="<?= (int) $comment['id'] ?>">
                <input type="hidden" name="operation" value="delete">
                <button type="submit"><?= elodie_cms_escape(Supprimer) ?></button>
            </form>
        </article>
    <?php endforeach; ?>

    <h2><?= elodie_cms_escape(elodie_cms_ui('published')) ?> (<?= count($approvedComments) ?>)</h2>
    <?php if ($approvedComments === []): ?>
        <p><?= elodie_cms_escape(elodie_cms_ui('no_published')) ?></p>
    <?php endif; ?>
    <?php foreach ($approvedComments as $comment): ?>
        <article class="comment-card">
            <h3><?= elodie_cms_escape($comment['author']) ?></h3>
            <p class="comment-meta"><?= elodie_cms_escape(elodie_cms_ui('article')) ?> <?= (int) $comment['article_id'] ?> · <?= elodie_cms_escape($comment['created_at']) ?></p>
            <p><?= nl2br(elodie_cms_escape($comment['body']), false) ?></p>
            <form method="post" action="comments.php">
                <?= elodie_cms_csrf_input() ?>
                <input type="hidden" name="id" value="<?= (int) $comment['id'] ?>">
                <input type="hidden" name="operation" value="delete">
                <button type="submit"><?= elodie_cms_escape(Supprimer) ?></button>
            </form>
        </article>
    <?php endforeach; ?>
        </section>
        </main>
    </div>
</body>
</html>
