<?php
// admin/include/header.php
$fullName = $_SESSION['full_name'] ?? $_SESSION['username'] ?? 'User';
$firstLetter = strtoupper(substr($fullName, 0, 1));
$basePath = '/rave';
?>
<header class="rn-top-navbar">
    <div class="rn-search-box">
        <img src="<?= $basePath ?>/assets/admin/adminheader/search.png" alt="Search" class="search-icon">
        <input type="text" placeholder="Start searching here...">
    </div>
    
    <div class="rn-top-actions">
        <!-- Settings Action PNG -->
        <a href="settings.php" class="header-icon-btn" title="Settings">
            <img src="<?= $basePath ?>/assets/admin/adminnav/settings.png" alt="Settings" class="header-action-icon">
        </a>

        <!-- Grid Menu PNG -->
        <a href="index.php" class="header-icon-btn" title="Grid Menu">
            <img src="<?= $basePath ?>/assets/admin/adminnav/grid.png" alt="Grid" class="header-action-icon">
        </a>
        
        <!-- Calendar PNG + Date -->
        <div class="header-date-pill">
        
            <span><?= date('D, d M') ?></span>
        </div>
        
        <!-- Profile Avatar Pill -->
        <div class="rn-header-avatar" title="<?= htmlspecialchars($fullName) ?>">
            <?= $firstLetter ?>
        </div>
    </div>
</header>