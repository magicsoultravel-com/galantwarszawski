<!DOCTYPE html>
<html lang="<?= htmlspecialchars($current_lang ?? $default_lang) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <?php
    // Drag-and-drop safe base URL for assets
    $baseUrl = str_replace($_SERVER['DOCUMENT_ROOT'], '', realpath(__DIR__ . '/..'));
    ?>

    <title><?= strip_tags($page_title ?? spawn_content('site-title', ['show_title' => false])) ?></title>

    <!-- Stylesheet with dynamic base URL -->
<link rel="stylesheet" href="<?= $baseUrl ?>/assets/styles-scrolly.php">
<link rel="stylesheet" href="<?= $baseUrl ?>/assets/styles-modal.php"> 
<link rel="stylesheet" href="<?= $baseUrl ?>/assets/styles.php">
 <link rel="icon" href="assets/favicon.png" type="image/png">
</head>
<body>
<header>

<?php include 'menu-modal.php'; ?>
<div id="top-banner">
    <div class="logo-container">
        
        <a href="#" class="logo-link" title="Return to Top">
            <img src="/logo.png" 
                 alt="Site Logo" 
                 class="logo"
                 onerror="this.onerror=null; this.src='https://placehold.co/150x40/4f46e5/ffffff?text=LOGO';">
        </a>
&nbsp;&nbsp;<?php include 'hamburger-menu.php'; ?>
</div>    
<div class="edge-text">
<nav>
<div style="text-align: right;">

<a href="?lang=pl">🇵🇱</a>&nbsp;
<a href="?lang=en">🇬🇧</a>
</div>
</nav>
</div>
</div>
</div>

</header>
<main>
