<?php
// Beranda Member (Tahap C) – prototipe front-end, data dummy lewat MemberAPI.
$pageTitle  = 'Beranda';
$publicPage = false;
$activePage = 'dashboard';
$cssFile    = __DIR__ . '/../public/css/member-dashboard.css';
$jsFile     = __DIR__ . '/../public/js/member-dashboard.js';
ob_start();
?>
<link rel="stylesheet" href="../public/css/member-dashboard.css?v=<?= is_file($cssFile) ? filemtime($cssFile) : 1 ?>">

<!-- Ikon tambahan khusus halaman ini (boleh dipindah ke sprite inline di member_layout.php) -->
<svg width="0" height="0" style="position:absolute" aria-hidden="true" focusable="false">
  <symbol id="icon-clock" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></symbol>
  <symbol id="icon-gift" viewBox="0 0 24 24"><rect x="3" y="8" width="18" height="4" rx="1"/><path d="M12 8v13M5 12v8a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-8"/><path d="M7.5 8a2.5 2.5 0 0 1 0-5C10 3 12 8 12 8s2-5 4.5-5a2.5 2.5 0 0 1 0 5"/></symbol>
  <symbol id="icon-store" viewBox="0 0 24 24"><path d="M4 9l1.5-5h13L20 9"/><path d="M4 9a2.5 2.5 0 0 0 5 0 2.5 2.5 0 0 0 5 0 2.5 2.5 0 0 0 5 0"/><path d="M5 12v8h14v-8"/></symbol>
  <symbol id="icon-id" viewBox="0 0 24 24"><rect x="3" y="5" width="18" height="14" rx="2"/><circle cx="9" cy="11" r="2"/><path d="M6 16c.5-1.5 1.8-2 3-2s2.5.5 3 2M14 10h4M14 14h3"/></symbol>
  <symbol id="icon-logout" viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><path d="M16 17l5-5-5-5M21 12H9"/></symbol>
  <symbol id="icon-trend" viewBox="0 0 24 24"><path d="M3 17l6-6 4 4 8-8"/><path d="M15 7h6v6"/></symbol>
</svg>

<div class="dash is-loading" id="dash" aria-busy="true">

  <header class="dash-head">
    <div>
      <p class="dash-head__eyebrow">Secangkir semangat hari ini</p>
      <h1 class="dash-head__title"><span id="dash-greeting" data-skel>Halo, Member</span></h1>
    </div>
    <button type="button" class="dash-logout" id="dash-logout">
      <svg class="icon" aria-hidden="true"><use href="#icon-logout"/></svg><span>Keluar</span>
    </button>
  </header>

  <!-- Galat memuat data -->
  <section class="dash-error" id="dash-error" hidden role="alert">
    <p id="dash-error-text">Data belum bisa dimuat.</p>
    <button type="button" class="dash-btn dash-btn--primary" id="dash-retry">Coba lagi</button>
  </section>

  <div class="dash-grid" id="dash-content">

    <!-- Kolom kiri -->
    <div class="dash-col">

      <section class="dash-hero" aria-labelledby="dash-points-label">
        <div class="dash-hero__top">
          <span class="dash-chip" id="dash-tier-chip">
            <svg class="icon" aria-hidden="true"><use href="#icon-star"/></svg>
            <span id="dash-tier">Regular</span>
          </span>
          <span class="dash-hero__id">ID: <span id="dash-id" data-skel>KP-00000</span></span>
        </div>

        <p class="dash-hero__label" id="dash-points-label">Poin aktif</p>
        <p class="dash-hero__points">
          <span class="dash-hero__num" id="dash-points" data-skel>00</span>
          <span class="dash-hero__unit">pts</span>
        </p>

        <div class="dash-progress">
          <div class="dash-progress__row">
            <span id="dash-progress-label">Target Tier Gold</span>
            <span id="dash-progress-value" data-skel>0 / 100 Poin</span>
          </div>
          <div class="dash-progress__bar" role="progressbar" id="dash-progress-bar"
               aria-label="Progres menuju tier Gold" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0">
            <div class="dash-progress__fill" id="dash-progress-fill"></div>
          </div>
          <p class="dash-progress__hint" id="dash-progress-hint" data-skel>Sedikit lagi menuju Gold</p>
        </div>

        <div class="dash-expiry" id="dash-expiry" hidden>
          <svg class="icon" aria-hidden="true"><use href="#icon-clock"/></svg>
          <div>
            <strong id="dash-expiry-text">0 poin akan kedaluwarsa</strong>
            <small>dalam 30 hari ke depan</small>
          </div>
        </div>

        <div class="dash-hero__actions">
          <a class="dash-btn dash-btn--light" href="vouchers.php">
            <svg class="icon" aria-hidden="true"><use href="#icon-gift"/></svg>Tukar Poin
          </a>
          <button type="button" class="dash-btn dash-btn--ghost" id="dash-show-id">
            <svg class="icon" aria-hidden="true"><use href="#icon-id"/></svg>Tunjukkan ID
          </button>
        </div>
      </section>

      <nav class="dash-actions" aria-label="Pintasan">
        <a class="dash-action" href="vouchers.php">
          <svg class="icon" aria-hidden="true"><use href="#icon-gift"/></svg>Tukar Voucher
        </a>
        <a class="dash-action" href="vouchers.php?tab=saya">
          <svg class="icon" aria-hidden="true"><use href="#icon-ticket"/></svg>Voucher Saya
        </a>
        <a class="dash-action" href="history.php">
          <svg class="icon" aria-hidden="true"><use href="#icon-history"/></svg>Riwayat Poin
        </a>
      </nav>

      <section class="dash-card dash-reco" id="dash-reco" aria-labelledby="dash-reco-heading" hidden>
        <span class="dash-reco__badge">Rekomendasi Spesial</span>
        <h2 class="dash-reco__title" id="dash-reco-heading">—</h2>
        <p class="dash-reco__desc" id="dash-reco-desc"></p>
        <div class="dash-reco__foot">
          <span class="dash-reco__cost">Biaya poin <strong id="dash-reco-cost">0</strong> pts</span>
          <a class="dash-btn dash-btn--primary" id="dash-reco-btn" href="vouchers.php">Tukarkan Sekarang</a>
        </div>
      </section>

    </div>

    <!-- Kolom kanan -->
    <div class="dash-col">
      <section class="dash-card" aria-labelledby="dash-ledger-heading">
        <div class="dash-card__head">
          <div>
            <h2 class="dash-card__title" id="dash-ledger-heading">Mutasi terakhir</h2>
            <p class="dash-card__sub">Aktivitas perolehan &amp; penukaran poin</p>
          </div>
          <a class="dash-link" href="history.php">Lihat Semua</a>
        </div>
        <ul class="dash-ledger" id="dash-ledger" aria-live="polite">
          <li><div class="dash-ledger__ico" data-skel></div><div class="dash-ledger__main"><p class="dash-ledger__title" data-skel>Memuat mutasi</p><p class="dash-ledger__meta" data-skel>Tanggal</p></div></li>
          <li><div class="dash-ledger__ico" data-skel></div><div class="dash-ledger__main"><p class="dash-ledger__title" data-skel>Memuat mutasi</p><p class="dash-ledger__meta" data-skel>Tanggal</p></div></li>
        </ul>
        <div class="dash-empty" id="dash-ledger-empty" hidden>
          <strong>Belum ada mutasi poin</strong>
          Poin akan muncul di sini setelah kamu bertransaksi di gerai KopiKita.
        </div>
      </section>
    </div>

  </div>

  <!-- Dialog: tunjukkan ID ke kasir -->
  <dialog class="dash-dialog" id="dash-dialog" aria-labelledby="dash-dialog-title">
    <h2 id="dash-dialog-title">ID Member</h2>
    <p>Sebutkan nomor HP ini ke kasir agar poin tercatat.</p>
    <div class="dash-dialog__box">
      <span class="dash-dialog__label">Nomor HP</span>
      <span class="dash-dialog__value" id="dash-dialog-phone">—</span>
      <span class="dash-dialog__id" id="dash-dialog-id">—</span>
    </div>
    <button type="button" class="dash-btn dash-btn--primary" id="dash-dialog-close">Tutup</button>
  </dialog>

</div>

<script src="../public/js/member-dashboard.js?v=<?= is_file($jsFile) ? filemtime($jsFile) : 1 ?>"></script>
<?php
$pageContent = ob_get_clean();
include __DIR__ . '/../includes/member_layout.php';
