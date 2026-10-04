<header class="admin-topbar">
    <a class="admin-brand" href="index.php">Elodie CMS <span><?= elodie_cms_escape(elodie_cms_version()) ?></span></a>
    <button class="admin-menu-toggle" type="button" aria-controls="admin-sidebar" aria-expanded="false"
        aria-label="<?= elodie_cms_escape(elodie_cms_ui('admin_navigation')) ?>" data-admin-menu-toggle>
        <span aria-hidden="true">☰</span><span><?= elodie_cms_escape(elodie_cms_ui('mobile_menu')) ?></span>
    </button>
    <div class="admin-topbar-tools">
        <a href="../index2.php" target="_blank" rel="noopener noreferrer"><?= elodie_cms_escape(elodie_cms_ui('view_site')) ?></a>
        <form action="deconnexion.php" method="post">
            <?= elodie_cms_csrf_input() ?>
            <button type="submit"><?= Deconnexion ?></button>
        </form>
    </div>
</header>
<script src="js/admin-navigation.js" defer></script>
