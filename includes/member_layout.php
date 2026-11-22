<?php
/* includes/member_layout.php */
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>KopiKita Member</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/Prototipe-Hanif-Hafidz/public/css/member.css">
    <script src="/Prototipe-Hanif-Hafidz/public/js/member-data.js" defer></script>
    <script src="/Prototipe-Hanif-Hafidz/public/js/member.js" defer></script>
</head>
<body>
    <!-- Header -->
    <header class="card-glass" style="margin-top:8px;">
        <div class="container">
            <h1 style="font-size: var(--size-22); margin:0;">KopiKita</h1>
        </div>
    </header>
    <!-- Main content placeholder -->
    <main class="container" id="main-content">
        <!-- Page specific content will be injected here -->
    <?php if (isset($pageContent)) echo $pageContent; ?></main>
    <!-- Navigation (bottom for mobile, top for desktop) -->
    <nav class="bottom-nav" id="bottom-nav">
        <a href="login.php" class="active"><span>🏠</span><br><small>Beranda</small></a>
        <a href="vouchers.php"><span>🎟️</span><br><small>Voucher</small></a>
        <a href="history.php"><span>📜</span><br><small>Riwayat</small></a>
        <a href="profile.php"><span>👤</span><br><small>Profil</small></a>
    </nav>
    <nav class="top-nav" id="top-nav">
        <ul>
            <li><a href="dashboard.php" class="active">Dashboard</a></li>
            <li><a href="vouchers.php">Voucher</a></li>
            <li><a href="history.php">Riwayat</a></li>
            <li><a href="profile.php">Profil</a></li>
        </ul>
    </nav>
    <!-- Toast container -->
    <div id="toast" style="position:fixed; top:16px; right:16px; z-index:9999;"></div>
    <!-- Modal container -->
    <div id="modal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.4); align-items:center; justify-content:center;">
        <div class="card-solid" style="max-width:90%;">
            <div id="modal-content"></div>
            <button id="modal-close" style="margin-top:8px;">Tutup</button>
        </div>
    </div>
</body>
</html>
