<?php

require_once __DIR__ . '/admin/security.php';
require_once __DIR__ . '/admin/fonctions.php';

if (!elodie_cms_is_installed()) {
    header('Location: install.php');
    exit();
}

$settings = elodie_cms_read_encoded_configuration();
$allArticles = elodie_cms_read_news();
$articleIds = array_keys($allArticles);
$siteLanguage = base64_decode($settings[1] ?? '', true);
if (!in_array($siteLanguage, ['de', 'en', 'es', 'fr', 'it', 'nl', 'pt'], true)) {
    $siteLanguage = 'fr';
}
$GLOBALS['elodieCmsLanguage'] = $siteLanguage;
require_once __DIR__ . '/lang/interface.php';

$siteName = base64_decode($settings[0] ?? '', true);
$siteName = $siteName === false ? 'Elodie CMS' : elodie_cms_escape_legacy_text($siteName);
$logoUrl = base64_decode($settings[26] ?? '', true);
$logoAlt = base64_decode($settings[27] ?? '', true);
$logoUrl = is_string($logoUrl) && elodie_cms_valid_url_setting($logoUrl) ? $logoUrl : '';
$logoAlt = $logoAlt === false || $logoAlt === '' ? $siteName : elodie_cms_escape_legacy_text($logoAlt);
$backgroundUrl = base64_decode($settings[30] ?? '', true);
$backgroundUrl = is_string($backgroundUrl) && elodie_cms_valid_url_setting($backgroundUrl)
    ? $backgroundUrl
    : '';
$faviconUrl = base64_decode($settings[31] ?? '', true);
$faviconUrl = is_string($faviconUrl) && elodie_cms_valid_url_setting($faviconUrl)
    ? $faviconUrl
    : '';
$authorFirstName = base64_decode($settings[11] ?? '', true);
$authorLastName = base64_decode($settings[12] ?? '', true);
$authorName = trim(
    ($authorFirstName === false ? '' : html_entity_decode($authorFirstName, ENT_QUOTES | ENT_HTML5, 'UTF-8'))
    . ' '
    . ($authorLastName === false ? '' : html_entity_decode($authorLastName, ENT_QUOTES | ENT_HTML5, 'UTF-8'))
);
$authorActivity = base64_decode($settings[19] ?? '', true);
$authorBio = base64_decode($settings[20] ?? '', true);
$authorInterests = base64_decode($settings[21] ?? '', true);
$themeSettings = elodie_cms_read_theme_settings();
elodie_cms_ensure_default_about_page();
$customPages = elodie_cms_read_pages();
$menuItems = elodie_cms_read_menu_items();
$description = trim($themeSettings['description']);
$themeColors = [];
foreach ([
    'brand_color' => '#264a68',
    'accent_color' => '#bf6c45',
    'page_color' => '#f4f6f8',
    'text_color' => '#202b38',
] as $key => $defaultColor) {
    $color = $themeSettings[$key] ?? '';
    $themeColors[$key] = is_string($color) && preg_match('/\A#[0-9a-fA-F]{6}\z/', $color)
        ? $color
        : $defaultColor;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['comment_submit'] ?? '') === '1') {
    if (!elodie_cms_comments_enabled()) {
        http_response_code(404);
        exit(elodie_cms_ui('comment_disabled'));
    }
    elodie_cms_require_valid_csrf_token();
    $articleId = public_article_id($allArticles);
    if ($articleId === null || !array_key_exists($articleId, $allArticles)) {
        http_response_code(404);
        exit(elodie_cms_ui('article_missing'));
    }

    $author = elodie_cms_post_string('author');
    $body = trim(elodie_cms_post_string('body'));
    if ($author === '' || strlen($author) > 120 || preg_match('/[\x00-\x1F\x7F]/', $author)
        || $body === '' || strlen($body) > 5000
        || preg_match('//u', $author) !== 1 || preg_match('//u', $body) !== 1
        || preg_match('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', $body)) {
        http_response_code(400);
        exit(elodie_cms_ui('invalid_comment'));
    }

    $remoteAddress = is_string($_SERVER['REMOTE_ADDR'] ?? null) ? $_SERVER['REMOTE_ADDR'] : '';
    if (!elodie_cms_add_comment($articleId, $author, $body, $remoteAddress)) {
        http_response_code(429);
        exit(elodie_cms_ui('comment_rate_limit'));
    }
    header('Location: index2.php?module=articles&page=' . max(1, (int) ($_GET['page'] ?? 1)) . '&comment=sent');
    exit();
}

$module = is_string($_GET['module'] ?? null) ? $_GET['module'] : 'home';
$requestedPage = $_GET['page'] ?? null;
$page = is_string($requestedPage) && ctype_digit($requestedPage)
    ? max(1, (int) $requestedPage)
    : 1;
$article = null;
$articlePage = null;
$standalonePage = null;

if ($module === 'articles' && $allArticles !== []) {
    $articleId = public_article_id($allArticles);
    if ($articleId !== null && isset($allArticles[$articleId])) {
        $article = $allArticles[$articleId];
        $articlePage = array_search($articleId, $articleIds, true);
        $articlePage = $articlePage === false ? null : $articlePage + 1;
    }
}
if ($module === 'about' || $module === 'page') {
    $slug = $module === 'about' ? 'about' : ($_GET['slug'] ?? '');
    if (is_string($slug) && preg_match('/\A[a-z0-9]+(?:-[a-z0-9]+)*\z/', $slug)) {
        $standalonePage = elodie_cms_find_page($slug);
    }
}

$postsPerPage = 6;
$newestFirst = $allArticles;
krsort($newestFirst);
$totalPages = max(1, (int) ceil(count($newestFirst) / $postsPerPage));
$archivePage = min($page, $totalPages);
$archiveItems = array_slice($newestFirst, ($archivePage - 1) * $postsPerPage, $postsPerPage, true);

$pageTitle = $siteName;
if ($article !== null) {
    $pageTitle = elodie_cms_escape_legacy_text($article['titre']) . ' - ' . $siteName;
} elseif ($standalonePage !== null) {
    $pageTitle = $standalonePage['title'] . ' - ' . $siteName;
}

function elodie_cms_blog_date(array $article, string $language): string
{
    $year = $article['annee'] ?? '';
    $month = $article['mois'] ?? '';
    $day = $article['jour'] ?? '';
    if (!is_string($year) || !is_string($month) || !is_string($day)
        || !elodie_cms_valid_article_date($year, $month, $day)) {
        return '';
    }

    $months = [
        'fr' => ['', 'janvier', 'février', 'mars', 'avril', 'mai', 'juin', 'juillet', 'août', 'septembre', 'octobre', 'novembre', 'décembre'],
        'en' => ['', 'January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'],
        'es' => ['', 'enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'],
        'nl' => ['', 'januari', 'februari', 'maart', 'april', 'mei', 'juni', 'juli', 'augustus', 'september', 'oktober', 'november', 'december'],
        'de' => ['', 'Januar', 'Februar', 'März', 'April', 'Mai', 'Juni', 'Juli', 'August', 'September', 'Oktober', 'November', 'Dezember'],
        'it' => ['', 'gennaio', 'febbraio', 'marzo', 'aprile', 'maggio', 'giugno', 'luglio', 'agosto', 'settembre', 'ottobre', 'novembre', 'dicembre'],
        'pt' => ['', 'janeiro', 'fevereiro', 'março', 'abril', 'maio', 'junho', 'julho', 'agosto', 'setembro', 'outubro', 'novembro', 'dezembro'],
    ];
    $monthNumber = (int) $month;

    return (int) $day . ' ' . $months[$language][$monthNumber] . ' ' . $year;
}

function elodie_cms_article_excerpt(array $article): string
{
    $summary = is_string($article['chapo'] ?? null)
        ? trim(strip_tags(html_entity_decode($article['chapo'], ENT_QUOTES | ENT_HTML5, 'UTF-8')))
        : '';
    if ($summary === '') {
        $content = is_string($article['contenu'] ?? null)
            ? elodie_cms_render_article_content($article['contenu'], $article['format'] ?? 'visual')
            : '';
        $summary = html_entity_decode(strip_tags($content), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }
    $summary = trim((string) preg_replace('/\s+/u', ' ', $summary));
    if ($summary === '' || preg_match('/^.{0,217}$/us', $summary) === 1) {
        return $summary;
    }
    if (preg_match('/^(.{1,217}?[.!?])(?:\s|$)/us', $summary, $sentence) === 1) {
        return trim($sentence[1]);
    }
    if (preg_match('/^(.{1,214})(?:\s|$)/us', $summary, $prefix) !== 1) {
        preg_match('/^.{1,214}/us', $summary, $prefix);
    }

    return trim($prefix[0] ?? '') . '...';
}

?>
<!DOCTYPE html>
<html lang="<?= elodie_cms_escape($siteLanguage) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="generator" content="Elodie CMS <?= elodie_cms_escape(elodie_cms_version()) ?>">
    <meta name="description" content="<?= elodie_cms_escape($description !== '' ? $description : $siteName) ?>">
    <title><?= elodie_cms_escape($pageTitle) ?></title>
    <link rel="alternate" type="application/rss+xml" title="<?= elodie_cms_escape(elodie_cms_ui('rss')) ?>" href="rss.php">
    <?php if ($faviconUrl !== ''): ?>
        <link rel="icon" href="<?= elodie_cms_escape($faviconUrl) ?>">
    <?php endif; ?>
    <link rel="stylesheet" href="style.css">
</head>
<body class="blog-site" style="--brand: <?= elodie_cms_escape($themeColors['brand_color']) ?>; --brand-dark: <?= elodie_cms_escape($themeColors['brand_color']) ?>; --accent: <?= elodie_cms_escape($themeColors['accent_color']) ?>; --page: <?= elodie_cms_escape($themeColors['page_color']) ?>; --ink: <?= elodie_cms_escape($themeColors['text_color']) ?>;<?= $backgroundUrl !== '' ? ' --site-wallpaper: url(&quot;' . elodie_cms_escape(str_replace('"', '%22', $backgroundUrl)) . '&quot;);' : '' ?>">
    <div class="site-shell">
        <header class="site-header">
            <a class="site-brand" href="index2.php">
                <?php if ($logoUrl !== ''): ?>
                    <img class="site-brand-logo" src="<?= elodie_cms_escape($logoUrl) ?>" alt="<?= elodie_cms_escape($logoAlt) ?>">
                <?php else: ?>
                    <span class="site-brand-name"><?= elodie_cms_escape($siteName) ?></span>
                <?php endif; ?>
                <?php if ($description !== ''): ?>
                    <span class="site-brand-tagline"><?= elodie_cms_escape($description) ?></span>
                <?php else: ?>
                    <span class="site-brand-tagline"><?= elodie_cms_escape(elodie_cms_ui('blog_tagline')) ?></span>
                <?php endif; ?>
            </a>
            <nav class="site-nav" aria-label="<?= elodie_cms_escape(elodie_cms_ui('main_navigation')) ?>">
                <a href="index2.php"<?= $module === 'home' ? ' aria-current="page"' : '' ?>><?= elodie_cms_escape(elodie_cms_ui('home')) ?></a>
                <?php foreach ($menuItems as $menuItem): ?>
                    <?php
                    $menuHref = null;
                    if ($menuItem['type'] === 'link') {
                        $menuHref = $menuItem['target'];
                    } elseif ($menuItem['type'] === 'article') {
                        $position = array_search((int) $menuItem['target'], $articleIds, true);
                        if ($position !== false) {
                            $menuHref = 'index2.php?module=articles&page=' . ($position + 1);
                        }
                    } elseif ($menuItem['type'] === 'page' && elodie_cms_find_page($menuItem['target']) !== null) {
                        $menuHref = $menuItem['target'] === 'about'
                            ? 'index2.php?module=about'
                            : 'index2.php?module=page&slug=' . rawurlencode($menuItem['target']);
                    }
                    ?>
                    <?php if ($menuHref !== null): ?>
                        <a href="<?= elodie_cms_escape($menuHref) ?>"<?= ($menuItem['type'] === 'page'
                            && $standalonePage !== null && $standalonePage['slug'] === $menuItem['target'])
                            ? ' aria-current="page"' : '' ?>><?= elodie_cms_escape($menuItem['label']) ?></a>
                    <?php endif; ?>
                <?php endforeach; ?>
                <?php if (base64_decode($settings[9] ?? '', true) !== 'off'): ?>
                    <a href="admin/"><?= elodie_cms_escape(elodie_cms_ui('administration')) ?></a>
                <?php endif; ?>
            </nav>
        </header>

        <main class="site-main">
            <?php if ($module === 'articles' && $article !== null && $articlePage !== null): ?>
                <article class="post post-single">
                    <p class="post-eyebrow"><?= elodie_cms_escape(elodie_cms_ui('article')) ?></p>
                    <h1><?= elodie_cms_escape_legacy_text($article['titre']) ?></h1>
                    <p class="post-meta">
                        <?php if (elodie_cms_blog_date($article, $siteLanguage) !== ''): ?>
                            <time><?= elodie_cms_escape(elodie_cms_blog_date($article, $siteLanguage)) ?></time>
                        <?php endif; ?>
                        <?php if ($authorName !== ''): ?>
                            <span><?= elodie_cms_escape(sprintf(elodie_cms_ui('post_author'), $authorName)) ?></span>
                        <?php endif; ?>
                    </p>
                    <div class="post-content"><?= elodie_cms_render_article_content($article['contenu'], $article['format'] ?? 'visual') ?></div>
                    <p class="post-back"><a href="index2.php">← <?= elodie_cms_escape(elodie_cms_ui('back_articles')) ?></a></p>
                </article>
                <?php comments(); ?>
            <?php elseif ($standalonePage !== null): ?>
                <article class="post post-single">
                    <p class="post-eyebrow"><?= elodie_cms_escape(elodie_cms_ui('pages_title')) ?></p>
                    <h1><?= elodie_cms_escape($standalonePage['title']) ?></h1>
                    <div class="post-content"><?= elodie_cms_render_article_content(
                        $standalonePage['content'],
                        $standalonePage['content_format']
                    ) ?></div>
                    <p class="post-back"><a href="index2.php"><?= elodie_cms_escape(elodie_cms_ui('back_home')) ?></a></p>
                </article>
            <?php elseif ($module === 'page' || $module === 'about'): ?>
                <section class="empty-state">
                    <h1><?= elodie_cms_escape(elodie_cms_ui('not_found')) ?></h1>
                    <p><?= elodie_cms_escape(elodie_cms_ui('not_found_help')) ?></p>
                </section>
            <?php elseif ($module === 'erreurs'): ?>
                <section class="empty-state">
                    <h1><?= elodie_cms_escape(elodie_cms_ui('not_found')) ?></h1>
                    <p><?= elodie_cms_escape(elodie_cms_ui('not_found_help')) ?></p>
                    <a class="button-link" href="index2.php"><?= elodie_cms_escape(elodie_cms_ui('back_home')) ?></a>
                </section>
            <?php else: ?>
                <section class="archive-heading">
                    <p class="post-eyebrow"><?= elodie_cms_escape(elodie_cms_ui('the_blog')) ?></p>
                    <h1><?= elodie_cms_escape(elodie_cms_ui('latest_posts')) ?></h1>
                    <p><?= elodie_cms_escape(sprintf(elodie_cms_ui('latest_posts_help'), $siteName)) ?></p>
                </section>
                <?php if ($archiveItems === []): ?>
                    <section class="empty-state">
                        <h2><?= elodie_cms_escape(elodie_cms_ui('welcome_blog')) ?></h2>
                        <p><?= elodie_cms_escape(elodie_cms_ui('posts_appear')) ?></p>
                    </section>
                <?php else: ?>
                    <div class="post-list">
                        <?php foreach ($archiveItems as $id => $post): ?>
                            <?php
                            $postPosition = array_search($id, $articleIds, true);
                            $postPosition = $postPosition === false ? 1 : $postPosition + 1;
                            $excerpt = elodie_cms_article_excerpt($post);
                            ?>
                            <article class="post-card">
                                <p class="post-eyebrow"><?= elodie_cms_escape(elodie_cms_ui('publication')) ?></p>
                                <h2><a href="index2.php?module=articles&amp;page=<?= (int) $postPosition ?>"><?= elodie_cms_escape_legacy_text($post['titre']) ?></a></h2>
                                <p class="post-meta">
                                    <?php if (elodie_cms_blog_date($post, $siteLanguage) !== ''): ?>
                                        <time><?= elodie_cms_escape(elodie_cms_blog_date($post, $siteLanguage)) ?></time>
                                    <?php endif; ?>
                                    <?php if ($authorName !== ''): ?>
                                        <span><?= elodie_cms_escape(sprintf(elodie_cms_ui('post_author'), $authorName)) ?></span>
                                    <?php endif; ?>
                                </p>
                                <p class="post-excerpt"><?= elodie_cms_escape($excerpt) ?></p>
                                <a class="read-more" href="index2.php?module=articles&amp;page=<?= (int) $postPosition ?>"><?= elodie_cms_escape(elodie_cms_ui('read_article')) ?> <span aria-hidden="true">→</span></a>
                            </article>
                        <?php endforeach; ?>
                    </div>
                    <?php if ($totalPages > 1): ?>
                        <nav class="pagination" aria-label="<?= elodie_cms_escape(elodie_cms_ui('pagination_label')) ?>">
                            <?php if ($archivePage > 1): ?>
                                <a href="index2.php?page=<?= $archivePage - 1 ?>">← <?= elodie_cms_escape(elodie_cms_ui('newer')) ?></a>
                            <?php endif; ?>
                            <span><?= elodie_cms_escape(sprintf(elodie_cms_ui('page_of'), $archivePage, $totalPages)) ?></span>
                            <?php if ($archivePage < $totalPages): ?>
                                <a href="index2.php?page=<?= $archivePage + 1 ?>"><?= elodie_cms_escape(elodie_cms_ui('older')) ?> →</a>
                            <?php endif; ?>
                        </nav>
                    <?php endif; ?>
                <?php endif; ?>
            <?php endif; ?>
        </main>

        <footer class="site-footer">
            <span><?= elodie_cms_escape($siteName) ?> · Elodie CMS <?= elodie_cms_escape(elodie_cms_version()) ?></span>
            <span><a href="rss.php"><?= elodie_cms_escape(elodie_cms_ui('follow_rss')) ?></a> · <a href="LICENSE"><?= elodie_cms_escape(elodie_cms_ui('license')) ?></a></span>
        </footer>
    </div>
</body>
</html>
