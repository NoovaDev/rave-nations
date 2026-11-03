<?php
// admin/include/navbar.php
$currentScript = basename($_SERVER['PHP_SELF']);
$basePath = '/rave';
?>
<aside class="rn-admin-sidebar">
    <!-- Top Brand / Logo Pill -->
    <div class="rn-sidebar-section">
        <a href="index.php" class="rn-nav-item brand-pill" title="Rave Nations">
            <span style="font-size:14px; font-weight:900; color:#fff;">RN</span>
        </a>
    </div>

    <!-- Main Navigation Group 1 -->
    <div class="rn-sidebar-section pill-group">
        <a href="index.php" class="rn-nav-item <?= ($currentScript === 'index.php') ? 'active' : '' ?>" title="Dashboard">
            <img src="<?= $basePath ?>/assets/admin/adminnav/grid.png" alt="Dashboard" class="nav-icon">
        </a>
        <a href="events.php" class="rn-nav-item <?= ($currentScript === 'events.php') ? 'active' : '' ?>" title="Events">
            <img src="<?= $basePath ?>/assets/admin/adminnav/events.png" alt="Events" class="nav-icon">
        </a>
        <a href="analytics.php" class="rn-nav-item <?= ($currentScript === 'analytics.php') ? 'active' : '' ?>" title="Analytics">
            <img src="<?= $basePath ?>/assets/admin/adminnav/analytics.png" alt="Analytics" class="nav-icon">
        </a>
        <a href="staff.php" class="rn-nav-item <?= ($currentScript === 'staff.php') ? 'active' : '' ?>" title="Staff Directory">
            <img src="<?= $basePath ?>/assets/admin/adminnav/staff.png" alt="Staff" class="nav-icon">
        </a>
    </div>

    <!-- Management Group 2 -->
    <div class="rn-sidebar-section pill-group">
        <a href="settings.php" class="rn-nav-item <?= ($currentScript === 'settings.php') ? 'active' : '' ?>" title="Settings">
            <img src="<?= $basePath ?>/assets/admin/adminnav/settings.png" alt="Settings" class="nav-icon">
        </a>
    </div>


    <!-- Footer Actions & Profile -->
    <div class="rn-sidebar-section pill-group" style="margin-top: auto;">
        <a href="#logout" onclick="document.getElementById('logout-form').submit(); return false;" class="rn-nav-item logout-icon" title="Sign Out">
            <img src="<?= $basePath ?>/assets/admin/adminnav/logout.png" alt="Logout" class="nav-icon">
        </a>
        <form id="logout-form" action="../logout.php" method="POST" style="display: none;"></form>
        
        <div class="rn-sidebar-avatar" title="<?= htmlspecialchars($_SESSION['full_name'] ?? 'User') ?>">
            <?= strtoupper(substr($_SESSION['full_name'] ?? 'RN', 0, 2)) ?>
        </div>
    </div>
    
</aside>