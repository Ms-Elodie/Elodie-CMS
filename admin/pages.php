<?php

function elodie_cms_admin_pages(): void
{
    elodie_cms_ensure_default_about_page();
    $route = is_string($_GET['page'] ?? null) ? $_GET['page'] : 'pages';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (($_POST['page_action'] ?? '') === 'delete') {
            $id = $_POST['page_id'] ?? null;
            if (!is_string($id) || !ctype_digit($id)) {
                http_response_code(400);
                exit(elodie_cms_ui('invalid_form'));
            }
            elodie_cms_delete_page((int) $id);
            header('Location: index.php?page=pages&saved=1');
            exit();
        }

        $idValue = $_POST['page_id'] ?? '';
        $id = $idValue === '' ? null : (is_string($idValue) && ctype_digit($idValue) ? (int) $idValue : false);
        $existing = null;
        $allPages = elodie_cms_read_pages();
        if ($id !== null && $id !== false) {
            foreach ($allPages as $candidate) {
                if ((int) $candidate['id'] === $id) {
                    $existing = $candidate;
                    break;
                }
            }
        }
        $title = trim(elodie_cms_post_string('page_title'));
        $slug = $existing === null ? strtolower(trim(elodie_cms_post_string('page_slug'))) : $existing['slug'];
        $format = $_POST['format'] ?? 'visual';
        $content = elodie_cms_post_string('contenu');
        if ($id === false || ($id !== null && $existing === null)
            || $title === '' || strlen($title) > 480 || preg_match('//u', $title) !== 1
            || !preg_match('/\A[a-z0-9]+(?:-[a-z0-9]+)*\z/', $slug) || strlen($slug) > 80
            || !is_string($format) || !in_array($format, ['visual', 'markdown', 'bbcode'], true)) {
            http_response_code(400);
            exit(elodie_cms_ui('invalid_form'));
        }
        foreach ($allPages as $candidate) {
            if ($candidate['slug'] === $slug && (int) $candidate['id'] !== $id) {
                http_response_code(400);
                exit(elodie_cms_ui('invalid_form'));
            }
        }

        $content = $format === 'visual' ? elodie_cms_sanitize_article_html($content) : $content;
        $renderedContent = elodie_cms_render_article_content($content, $format);
        if (trim(strip_tags($renderedContent)) === '' && !str_contains($renderedContent, '<img')) {
            http_response_code(400);
            exit(elodie_cms_ui('empty_article'));
        }
        elodie_cms_save_page($id, $slug, $title, $content, $format);
        header('Location: index.php?page=pages&saved=1');
        exit();
    }

    if ($route === 'pages') {
        $pages = elodie_cms_read_pages();
        echo '<p>' . elodie_cms_escape(elodie_cms_ui('pages_help')) . '</p>'
            . '<p><a class="button-link" href="index.php?page=page-new">'
            . elodie_cms_escape(elodie_cms_ui('page_new')) . '</a></p>';
        if ($pages === []) {
            echo '<p>' . elodie_cms_escape(elodie_cms_ui('page_no_pages')) . '</p>';
            return;
        }

        echo '<div class="article-table-wrap"><table class="article-table"><thead><tr><th>'
            . elodie_cms_escape(elodie_cms_ui('page_title')) . '</th><th>'
            . elodie_cms_escape(elodie_cms_ui('page_url')) . '</th><th>'
            . elodie_cms_escape(elodie_cms_ui('actions')) . '</th></tr></thead><tbody>';
        foreach ($pages as $page) {
            $url = 'index2.php?module=page&slug=' . rawurlencode($page['slug']);
            echo '<tr><td><strong>' . elodie_cms_escape($page['title']) . '</strong></td><td><a href="../'
                . elodie_cms_escape($url) . '" target="_blank" rel="noopener noreferrer">'
                . elodie_cms_escape($page['slug']) . '</a></td><td><div class="article-row-actions"><a class="admin-action-link" href="index.php?page=page-edit&amp;id='
                . (int) $page['id'] . '">' . elodie_cms_escape(elodie_cms_ui('page_edit')) . '</a>';
            if ($page['slug'] !== 'about') {
                echo '<form method="post" action="index.php?page=pages">' . elodie_cms_csrf_input()
                    . '<input type="hidden" name="page_action" value="delete"><input type="hidden" name="page_id" value="'
                    . (int) $page['id'] . '"><button type="submit" class="admin-danger-button">'
                    . elodie_cms_escape(elodie_cms_ui('page_delete')) . '</button></form>';
            }
            echo '</div></td></tr>';
        }
        echo '</tbody></table></div>';
        return;
    }

    $isNew = $route === 'page-new';
    $id = $_GET['id'] ?? null;
    $page = null;
    if (!$isNew && is_string($id) && ctype_digit($id)) {
        foreach (elodie_cms_read_pages() as $candidate) {
            if ((int) $candidate['id'] === (int) $id) {
                $page = $candidate;
                break;
            }
        }
    }
    if (!$isNew && $page === null) {
        http_response_code(404);
        exit(elodie_cms_ui('page_no_pages'));
    }

    $title = $page['title'] ?? '';
    $slug = $page['slug'] ?? '';
    $format = $page['content_format'] ?? 'visual';
    echo '<form class="page-form article-form" method="post" action="index.php?page='
        . ($isNew ? 'page-new' : 'page-edit&amp;id=' . (int) $page['id']) . '">'
        . elodie_cms_csrf_input() . '<input type="hidden" name="page_id" value="'
        . ($isNew ? '' : (int) $page['id']) . '"><label for="page_title">'
        . elodie_cms_escape(elodie_cms_ui('page_title')) . '</label><input id="page_title" name="page_title" maxlength="120" required value="'
        . elodie_cms_escape($title) . '"><label for="page_slug">' . elodie_cms_escape(elodie_cms_ui('page_slug'))
        . '</label>';
    if ($isNew) {
        echo '<input id="page_slug" name="page_slug" pattern="[a-z0-9]+(-[a-z0-9]+)*" maxlength="80" required value="">';
    } else {
        echo '<input id="page_slug" value="' . elodie_cms_escape($slug) . '" readonly>';
    }
    echo '<p class="article-editor-help">' . elodie_cms_escape(elodie_cms_ui('page_url'))
        . ': <code>index2.php?module=page&amp;slug=' . elodie_cms_escape($slug) . '</code></p>';
    elodie_cms_article_editor($page['content'] ?? '', $format);
    echo '<button type="submit">' . elodie_cms_escape(Ok) . '</button></form>';
}
