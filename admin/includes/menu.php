<?php
$navigationItems = [
    ['page' => '', 'label' => elodie_cms_ui('dashboard'), 'icon' => '⌂'],
    ['page' => 'liste', 'label' => Articles, 'icon' => '▤'],
    ['page' => 'ajouter', 'label' => elodie_cms_ui('write_article'), 'icon' => '+'],
    ['page' => 'pages', 'label' => elodie_cms_ui('pages_title'), 'icon' => '▧'],
    ['page' => 'images', 'label' => elodie_cms_ui('media'), 'icon' => '▧'],
    ['page' => 'configuration', 'label' => Configuration, 'icon' => '⚙'],
    ['page' => 'theme', 'label' => elodie_cms_ui('theme_title'), 'icon' => '◐'],
];
?>
<aside class="admin-sidebar" aria-label="<?= elodie_cms_escape(elodie_cms_ui('admin_navigation')) ?>">
    <nav class="admin-nav">
        <?php foreach ($navigationItems as $item): ?>
            <?php $href = 'index.php' . ($item['page'] === '' ? '' : '?page=' . rawurlencode($item['page'])); ?>
            <a class="admin-nav-link" href="<?= elodie_cms_escape($href) ?>"<?= ($activePage ?? $page) === $item['page'] ? ' aria-current="page"' : '' ?>>
                <span class="admin-nav-icon" aria-hidden="true"><?= $item['icon'] ?></span>
                <span><?= elodie_cms_escape((string) $item['label']) ?></span>
            </a>
        <?php endforeach; ?>
        <span class="admin-nav-divider" aria-hidden="true"></span>
        <a class="admin-nav-link" href="comments.php"<?= $page === 'comments' ? ' aria-current="page"' : '' ?>>
            <span class="admin-nav-icon" aria-hidden="true">◌</span><span><?= elodie_cms_escape(elodie_cms_ui('comments')) ?></span>
        </a>
        <a class="admin-nav-link" href="update.php"<?= $page === 'update' ? ' aria-current="page"' : '' ?>>
            <span class="admin-nav-icon" aria-hidden="true">↻</span><span><?= elodie_cms_escape(elodie_cms_ui('updates')) ?></span>
        </a>
        <a class="admin-nav-link" href="mfa.php?mode=recovery"<?= $page === 'recovery' ? ' aria-current="page"' : '' ?>>
            <span class="admin-nav-icon" aria-hidden="true">⌑</span><span><?= elodie_cms_escape(elodie_cms_ui('recovery_codes')) ?></span>
        </a>
    </nav>
</aside>
