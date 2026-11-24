<?php
// Voucher & Penukaran (Tahap D): Katalog + Voucher Saya. Prototipe front-end, data lewat MemberAPI.
$pageTitle  = 'Voucher';
$publicPage = false;
$activePage = 'vouchers';
$cssFile    = __DIR__ . '/../public/css/member-vouchers.css';
$jsFile     = __DIR__ . '/../public/js/member-vouchers.js';
ob_start();
?>
<link rel="stylesheet" href="../public/css/member-vouchers.css?v=<?= is_file($cssFile) ? filemtime($cssFile) : 1 ?>">

<!-- Ikon tambahan khusus halaman ini (boleh dipindah ke sprite inline di member_layout.php) -->
<svg width="0" height="0" style="position:absolute" aria-hidden="true" focusable="false">
  <symbol id="icon-gift" viewBox="0 0 24 24"><rect x="3" y="8" width="18" height="4" rx="1"/><path d="M12 8v13M5 12v8a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-8"/><path d="M7.5 8a2.5 2.5 0 0 1 0-5C10 3 12 8 12 8s2-5 4.5-5a2.5 2.5 0 0 1 0 5"/></symbol>
  <symbol id="icon-bean" viewBox="0 0 24 24"><path d="M12 3c4.5 0 8 3.6 8 9s-3.5 9-8 9-8-3.6-8-9 3.5-9 8-9z"/><path d="M12 3c-2 3-2 5 0 9s2 6 0 9"/></symbol>
  <symbol id="icon-cookie" viewBox="0 0 24 24"><path d="M12 3a9 9 0 1 0 9 9 4 4 0 0 1-4-4 4 4 0 0 1-5-5z"/><path d="M8.5 12h.01M12 16h.01M15.5 13h.01"/></symbol>
  <symbol id="icon-copy" viewBox="0 0 24 24"><rect x="9" y="9" width="12" height="12" rx="2"/><path d="M5 15H4a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1h10a1 1 0 0 1 1 1v1"/></symbol>
  <symbol id="icon-check" viewBox="0 0 24 24"><path d="M5 12l5 5L20 7"/></symbol>
  <symbol id="icon-clock" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></symbol>
</svg>

<div class="vch is-loading" id="vch" aria-busy="true">

  <header class="vch-head">
    <div>
      <p class="vch-head__eyebrow">Voucher &amp; Penukaran</p>
      <h1 class="vch-head__title">Tukar poinmu dengan keuntungan kopi spesial</h1>
    </div>
    <div class="vch-points" aria-live="polite">
      <svg class="icon" aria-hidden="true"><use href="#icon-star"/></svg>
      <span><strong id="vch-points" data-skel>00</strong> poin</span>
    </div>
  </header>

  <div class="vch-tabs" role="tablist" aria-label="Voucher">
    <button type="button" role="tab" class="vch-tab" id="tab-katalog" aria-controls="panel-katalog" aria-selected="true">
      <svg class="icon" aria-hidden="true"><use href="#icon-gift"/></svg><span>Katalog</span>
    </button>
    <button type="button" role="tab" class="vch-tab" id="tab-saya" aria-controls="panel-saya" aria-selected="false" tabindex="-1">
      <svg class="icon" aria-hidden="true"><use href="#icon-ticket"/></svg><span>Voucher Saya <span id="vch-mine-count"></span></span>
    </button>
  </div>

  <section class="vch-error" id="vch-error" hidden role="alert">
    <p id="vch-error-text">Data belum bisa dimuat.</p>
    <button type="button" class="vch-btn vch-btn--primary" id="vch-retry">Coba lagi</button>
  </section>

  <!-- Panel: Katalog -->
  <section role="tabpanel" id="panel-katalog" aria-labelledby="tab-katalog" tabindex="0">
    <div class="vch-chips" role="group" aria-label="Filter kategori" id="vch-cat-chips"></div>
    <div class="vch-grid" id="vch-catalog">
      <article class="vch-card"><div class="vch-skel" data-skel></div></article>
      <article class="vch-card"><div class="vch-skel" data-skel></div></article>
    </div>
    <div class="vch-empty" id="vch-catalog-empty" hidden>
      <strong>Belum ada voucher di kategori ini</strong>
      Coba pilih kategori lain.
    </div>
  </section>

  <!-- Panel: Voucher Saya -->
  <section role="tabpanel" id="panel-saya" aria-labelledby="tab-saya" tabindex="0" hidden>
    <div class="vch-chips" role="group" aria-label="Filter status" id="vch-status-chips"></div>
    <div class="vch-list" id="vch-mine"></div>
    <div class="vch-empty" id="vch-mine-empty" hidden>
      <strong id="vch-mine-empty-title">Belum ada voucher</strong>
      <span id="vch-mine-empty-text"></span>
    </div>
  </section>

  <!-- Dialog konfirmasi / hasil penukaran -->
  <dialog class="vch-dialog" id="vch-dialog" aria-labelledby="vch-dialog-title">
    <div id="vch-dialog-body"></div>
  </dialog>

  <!-- Pengumuman untuk pembaca layar -->
  <p class="vch-sr" id="vch-live" role="status" aria-live="polite"></p>
</div>

<script src="../public/js/member-vouchers.js?v=<?= is_file($jsFile) ? filemtime($jsFile) : 1 ?>"></script>
<?php
$pageContent = ob_get_clean();
include __DIR__ . '/../includes/member_layout.php';
