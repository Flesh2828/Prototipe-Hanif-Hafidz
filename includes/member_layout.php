<?php
$publicPage = $publicPage ?? false; // flag to hide global UI for login/register pages
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
    <svg width="0" height="0" style="position:absolute" aria-hidden="true" focusable="false">
  <symbol id="icon-cup" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M10 2v2M14 2v2M6 2v2"/><path d="M16 8a1 1 0 0 1 1 1v8a4 4 0 0 1-4 4H7a4 4 0 0 1-4-4V9a1 1 0 0 1 1-1h14a4 4 0 1 1 0 8h-1"/></symbol>
  <symbol id="icon-eye" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/></symbol>
  <symbol id="icon-eye-off" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 10 8 10 8a13.16 13.16 0 0 1-1.67 2.68"/><path d="M6.61 6.61A13.53 13.53 0 0 0 2 12s3 8 10 8a9.74 9.74 0 0 0 5.39-1.61"/><path d="M14.12 14.12A3 3 0 1 1 9.88 9.88"/><line x1="2" y1="2" x2="22" y2="22"/></symbol>
  <symbol id="icon-star" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></symbol>
  <symbol id="icon-ticket" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M2 9a3 3 0 0 1 0 6v2a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-2a3 3 0 0 1 0-6V7a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2z"/><path d="M13 5v2M13 11v2M13 17v2"/></symbol>
  <symbol id="icon-lock" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></symbol>
  <symbol id="icon-home" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M3 10.5 12 3l9 7.5V20a1 1 0 0 1-1 1h-5v-6H9v6H4a1 1 0 0 1-1-1z"/></symbol>
  <symbol id="icon-history" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12a9 9 0 1 0 3-6.7L3 8"/><path d="M3 3v5h5"/><path d="M12 7v5l4 2"/></symbol>
  <symbol id="icon-user" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="4"/><path d="M4 21c0-4 4-6 8-6s8 2 8 6"/></symbol>
</svg>
<?php if (!$publicPage): ?>
    <!-- Header (visible on internal pages) -->
    <header class="card-glass" style="margin-top:8px;">
        <div class="container">
            <h1 style="font-size: var(--size-22); margin:0;">KopiKita</h1>
        </div>
    </header>
<?php endif; ?>

    <!-- Main content injected by page scripts -->
    <main class="container" id="main-content">
        <?php if (isset($pageContent)) echo $pageContent; ?>
    </main>

<?php if (!$publicPage): ?>
    <!-- Bottom navigation (mobile) -->
    <nav class="bottom-nav" id="bottom-nav">
        <a href="dashboard.php" class="active"><span>🏠</span><br><small>Beranda</small></a>
        <a href="vouchers.php"><span>🎟️</span><br><small>Voucher</small></a>
        <a href="history.php"><span>📜</span><br><small>Riwayat</small></a>
        <a href="profile.php"><span>👤</span><br><small>Profil</small></a>
    </nav>
    <!-- Top navigation (desktop) -->
    <nav class="top-nav" id="top-nav">
        <ul>
            <li><a href="dashboard.php" class="active">Dashboard</a></li>
            <li><a href="vouchers.php">Voucher</a></li>
            <li><a href="history.php">Riwayat</a></li>
            <li><a href="profile.php">Profil</a></li>
        </ul>
    </nav>
<?php endif; ?>

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
