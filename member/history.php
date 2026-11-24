<?php
// Riwayat Poin (Tahap E): ringkasan, filter arah/outlet, daftar per bulan, paginasi.
// Prototipe front-end, data lewat MemberAPI.getLedger().
$pageTitle  = 'Riwayat Poin';
$publicPage = false;
$activePage = 'history';
$cssFile    = __DIR__ . '/../public/css/member-history.css';
$jsFile     = __DIR__ . '/../public/js/member-history.js';
ob_start();
?>
<link rel="stylesheet" href="../public/css/member-history.css?v=<?= is_file($cssFile) ? filemtime($cssFile) : 1 ?>">

<!-- Ikon tambahan khusus halaman ini (boleh dipindah ke sprite inline di member_layout.php) -->
<svg width="0" height="0" style="position:absolute" aria-hidden="true" focusable="false">
  <symbol id="icon-gift" viewBox="0 0 24 24"><rect x="3" y="8" width="18" height="4" rx="1"/><path d="M12 8v13M5 12v8a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-8"/><path d="M7.5 8a2.5 2.5 0 0 1 0-5C10 3 12 8 12 8s2-5 4.5-5a2.5 2.5 0 0 1 0 5"/></symbol>
  <symbol id="icon-store" viewBox="0 0 24 24"><path d="M4 9l1.5-5h13L20 9"/><path d="M4 9a2.5 2.5 0 0 0 5 0 2.5 2.5 0 0 0 5 0 2.5 2.5 0 0 0 5 0"/><path d="M5 12v8h14v-8"/></symbol>
  <symbol id="icon-clock" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></symbol>
  <symbol id="icon-arrow-in" viewBox="0 0 24 24"><path d="M12 5v14M6 13l6 6 6-6"/></symbol>
  <symbol id="icon-arrow-out" viewBox="0 0 24 24"><path d="M12 19V5M6 11l6-6 6 6"/></symbol>
  <symbol id="icon-info" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M12 16v-4M12 8h.01"/></symbol>
  <symbol id="icon-chev-l" viewBox="0 0 24 24"><path d="M15 6l-6 6 6 6"/></symbol>
  <symbol id="icon-chev-r" viewBox="0 0 24 24"><path d="M9 6l6 6-6 6"/></symbol>
</svg>

<div class="hst is-loading" id="hst" aria-busy="true">

  <header class="hst-head">
    <p class="hst-head__eyebrow">Loyalty Ledger</p>
    <h1 class="hst-head__title">Riwayat Poin</h1>
    <p class="hst-head__sub">Lacak perolehan &amp; penukaran poin KopiKita</p>
  </header>

  <section class="hst-error" id="hst-error" hidden role="alert">
    <p id="hst-error-text">Data belum bisa dimuat.</p>
    <button type="button" class="hst-btn hst-btn--primary" id="hst-retry">Coba lagi</button>
  </section>

  <div id="hst-content">

    <!-- Ringkasan -->
    <section class="hst-summary" aria-labelledby="hst-total-label">
      <p class="hst-summary__label" id="hst-total-label">Total Poin Aktif</p>
      <p class="hst-summary__total"><span id="hst-total" data-skel>00</span><span class="hst-summary__unit">pts</span></p>
      <div class="hst-summary__stats">
        <div class="hst-stat">
          <svg class="icon" aria-hidden="true"><use href="#icon-arrow-in"/></svg>
          <div><span class="hst-stat__label">Poin Masuk</span><strong id="hst-in" data-skel>+0 pts</strong></div>
        </div>
        <div class="hst-stat">
          <svg class="icon" aria-hidden="true"><use href="#icon-arrow-out"/></svg>
          <div><span class="hst-stat__label">Poin Keluar</span><strong id="hst-out" data-skel>−0 pts</strong></div>
        </div>
      </div>
      <div class="hst-expiry" id="hst-expiry" hidden>
        <svg class="icon" aria-hidden="true"><use href="#icon-info"/></svg>
        <span id="hst-expiry-text">0 poin akan kedaluwarsa</span>
        <button type="button" class="hst-rules-link" id="hst-rules-open">Aturan Poin</button>
      </div>
      <button type="button" class="hst-rules-link hst-rules-link--alone" id="hst-rules-open2" hidden>Aturan Poin</button>
    </section>

    <!-- Filter -->
    <div class="hst-toolbar">
      <div class="hst-chips" role="group" aria-label="Filter arah poin" id="hst-dir-chips"></div>
      <div class="hst-select">
        <label for="hst-outlet">Lokasi outlet</label>
        <select id="hst-outlet"><option value="">Semua Outlet KopiKita</option></select>
      </div>
    </div>

    <!-- Daftar -->
    <section aria-labelledby="hst-list-title">
      <h2 class="hst-sr" id="hst-list-title">Daftar mutasi poin</h2>
      <div id="hst-list" aria-live="polite">
        <div class="hst-group">
          <ul class="hst-rows">
            <li class="hst-row"><div class="hst-row__ico" data-skel></div><div class="hst-row__main"><p class="hst-row__title" data-skel>Memuat mutasi poin</p><p class="hst-row__meta" data-skel>Outlet • tanggal</p></div></li>
            <li class="hst-row"><div class="hst-row__ico" data-skel></div><div class="hst-row__main"><p class="hst-row__title" data-skel>Memuat mutasi poin</p><p class="hst-row__meta" data-skel>Outlet • tanggal</p></div></li>
            <li class="hst-row"><div class="hst-row__ico" data-skel></div><div class="hst-row__main"><p class="hst-row__title" data-skel>Memuat mutasi poin</p><p class="hst-row__meta" data-skel>Outlet • tanggal</p></div></li>
          </ul>
        </div>
      </div>
      <div class="hst-empty" id="hst-empty" hidden>
        <strong id="hst-empty-title">Belum ada mutasi poin</strong>
        <span id="hst-empty-text">Poin akan muncul di sini setelah kamu bertransaksi di gerai KopiKita.</span>
        <button type="button" class="hst-btn hst-btn--secondary" id="hst-reset" hidden>Reset filter</button>
      </div>
      <nav class="hst-pager" id="hst-pager" aria-label="Halaman riwayat" hidden>
        <button type="button" class="hst-btn hst-btn--secondary" id="hst-prev"><svg class="icon" aria-hidden="true"><use href="#icon-chev-l"/></svg><span>Sebelumnya</span></button>
        <span class="hst-pager__info" id="hst-pager-info">Halaman 1 dari 1</span>
        <button type="button" class="hst-btn hst-btn--secondary" id="hst-next"><span>Berikutnya</span><svg class="icon" aria-hidden="true"><use href="#icon-chev-r"/></svg></button>
      </nav>
    </section>

    <aside class="hst-note">
      <svg class="icon" aria-hidden="true"><use href="#icon-star"/></svg>
      <p>Tiap transaksi kelipatan <strong>Rp10.000</strong> menghasilkan <strong>1 poin</strong>. Tukarkan poinmu dengan voucher di <a href="vouchers.php">halaman Voucher</a>.</p>
    </aside>
  </div>

  <!-- Dialog: aturan poin (sesuai PRD) -->
  <dialog class="hst-dialog" id="hst-dialog" aria-labelledby="hst-dialog-title">
    <h2 id="hst-dialog-title">Aturan Poin</h2>
    <ul>
      <li>Setiap belanja kelipatan <strong>Rp10.000</strong> mendapat <strong>1 poin</strong> (dibulatkan ke bawah).</li>
      <li>Poin berlaku <strong>12 bulan</strong> sejak diperoleh. Poin yang lewat masa berlaku dihapus otomatis.</li>
      <li>Member <strong>Gold</strong> (100 poin kumulatif dalam setahun) mendapat bonus poin <strong>1,25×</strong> dan akses voucher biji kopi.</li>
      <li>Jika transaksi dibatalkan (void) oleh kasir, poin dikoreksi otomatis dan tercatat sebagai &ldquo;Koreksi void&rdquo;.</li>
      <li>Perolehan poin maksimal <strong>3 kali per hari</strong> per member.</li>
      <li>Voucher hasil penukaran berlaku <strong>14 hari</strong> sejak ditukar.</li>
    </ul>
    <button type="button" class="hst-btn hst-btn--primary" id="hst-dialog-close">Mengerti</button>
  </dialog>

  <p class="hst-sr" id="hst-live" role="status" aria-live="polite"></p>
</div>

<script src="../public/js/member-history.js?v=<?= is_file($jsFile) ? filemtime($jsFile) : 1 ?>"></script>
<?php
$pageContent = ob_get_clean();
include __DIR__ . '/../includes/member_layout.php';
