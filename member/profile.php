<?php
// Profil & Keamanan (Tahap F): data akun, ubah nama, ganti PIN, keluar.
// Prototipe front-end, data lewat MemberAPI.
$pageTitle  = 'Profil & Keamanan';
$publicPage = false;
$activePage = 'profile';
$cssFile    = __DIR__ . '/../public/css/member-profile.css';
$jsFile     = __DIR__ . '/../public/js/member-profile.js';
ob_start();
?>
<link rel="stylesheet" href="../public/css/member-profile.css?v=<?= is_file($cssFile) ? filemtime($cssFile) : 1 ?>">

<!-- Ikon tambahan khusus halaman ini (boleh dipindah ke sprite inline di member_layout.php) -->
<svg width="0" height="0" style="position:absolute" aria-hidden="true" focusable="false">
  <symbol id="icon-check" viewBox="0 0 24 24"><path d="M5 12l5 5L20 7"/></symbol>
  <symbol id="icon-edit" viewBox="0 0 24 24"><path d="M4 20h4L19 9a2.8 2.8 0 0 0-4-4L4 16v4z"/><path d="M13.5 6.5l4 4"/></symbol>
  <symbol id="icon-logout" viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><path d="M16 17l5-5-5-5M21 12H9"/></symbol>
  <symbol id="icon-store" viewBox="0 0 24 24"><path d="M4 9l1.5-5h13L20 9"/><path d="M4 9a2.5 2.5 0 0 0 5 0 2.5 2.5 0 0 0 5 0 2.5 2.5 0 0 0 5 0"/><path d="M5 12v8h14v-8"/></symbol>
  <symbol id="icon-shield" viewBox="0 0 24 24"><path d="M12 3l8 3v6c0 4.5-3.2 8-8 9-4.8-1-8-4.5-8-9V6l8-3z"/><path d="M9 12l2 2 4-4"/></symbol>
  <symbol id="icon-alert" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M12 8v5M12 16h.01"/></symbol>
</svg>

<div class="prf is-loading" id="prf" aria-busy="true">

  <header class="prf-head">
    <p class="prf-head__eyebrow">Akun Member</p>
    <h1 class="prf-head__title">Profil &amp; Keamanan</h1>
  </header>

  <section class="prf-error" id="prf-error" hidden role="alert">
    <p id="prf-error-text">Data belum bisa dimuat.</p>
    <button type="button" class="prf-btn prf-btn--primary" id="prf-retry">Coba lagi</button>
  </section>

  <div id="prf-content">

    <!-- Identitas -->
    <section class="prf-id" aria-label="Identitas member">
      <div class="prf-avatar" id="prf-avatar" aria-hidden="true" data-skel>R</div>
      <div class="prf-id__main">
        <h2 class="prf-id__name" id="prf-name-hero" data-skel>Nama Member</h2>
        <p class="prf-id__meta">ID: <span id="prf-id" data-skel>KP-00000</span></p>
        <p class="prf-id__meta">Bergabung sejak <span id="prf-joined" data-skel>Januari 2026</span></p>
        <div class="prf-id__chips">
          <span class="prf-chip" id="prf-tier-chip"><svg class="icon" aria-hidden="true"><use href="#icon-star"/></svg><span id="prf-tier">Regular</span></span>
          <span class="prf-chip"><svg class="icon" aria-hidden="true"><use href="#icon-cup"/></svg><span><strong id="prf-points">0</strong> poin aktif</span></span>
        </div>
      </div>
    </section>

    <div class="prf-grid">

      <div class="prf-col">
        <!-- Data akun -->
        <section class="prf-card" aria-labelledby="prf-acc-title">
          <div class="prf-card__head">
            <span class="prf-card__ico"><svg class="icon" aria-hidden="true"><use href="#icon-user"/></svg></span>
            <div>
              <h2 class="prf-card__title" id="prf-acc-title">Data Akun</h2>
              <p class="prf-card__sub">Informasi identitas akun member KopiKita</p>
            </div>
          </div>

          <div class="prf-field">
            <span class="prf-field__label" id="prf-name-label">Nama Lengkap</span>
            <div class="prf-field__row" id="prf-name-view">
              <span class="prf-field__value" id="prf-name" aria-labelledby="prf-name-label">—</span>
              <button type="button" class="prf-link" id="prf-name-edit" aria-label="Ubah nama lengkap">
                <svg class="icon" aria-hidden="true"><use href="#icon-edit"/></svg><span>Ubah</span>
              </button>
            </div>
            <form class="prf-edit" id="prf-name-form" hidden novalidate>
              <label class="prf-sr" for="prf-name-input">Nama lengkap</label>
              <input type="text" id="prf-name-input" maxlength="50" autocomplete="name" aria-describedby="prf-name-err">
              <p class="prf-err" id="prf-name-err" role="alert" hidden></p>
              <div class="prf-edit__actions">
                <button type="button" class="prf-btn prf-btn--secondary" id="prf-name-cancel">Batal</button>
                <button type="submit" class="prf-btn prf-btn--primary" id="prf-name-save">Simpan</button>
              </div>
            </form>
          </div>

          <div class="prf-field">
            <span class="prf-field__label" id="prf-phone-label">Nomor HP</span>
            <div class="prf-field__row">
              <span class="prf-field__value" id="prf-phone" aria-labelledby="prf-phone-label">—</span>
              <span class="prf-lock"><svg class="icon" aria-hidden="true"><use href="#icon-lock"/></svg>Terkunci</span>
            </div>
            <p class="prf-hint">Nomor HP tidak dapat diubah sendiri untuk melindungi poin dan voucher kamu.</p>
          </div>
        </section>

        <!-- Bantuan -->
        <section class="prf-help" aria-labelledby="prf-help-title">
          <svg class="icon" aria-hidden="true"><use href="#icon-store"/></svg>
          <div>
            <h2 id="prf-help-title">Butuh ganti nomor HP atau reset PIN?</h2>
            <p>Datang ke gerai KopiKita terdekat. Manajer Outlet akan membantu verifikasi tatap muka dengan menunjukkan KTP asli kamu.</p>
          </div>
        </section>
      </div>

      <div class="prf-col">
        <!-- Ganti PIN -->
        <section class="prf-card" aria-labelledby="prf-pin-title">
          <div class="prf-card__head">
            <span class="prf-card__ico"><svg class="icon" aria-hidden="true"><use href="#icon-shield"/></svg></span>
            <div>
              <h2 class="prf-card__title" id="prf-pin-title">Ganti PIN</h2>
              <p class="prf-card__sub">PIN 6 angka dipakai untuk masuk ke portal ini</p>
            </div>
          </div>

          <div class="prf-banner prf-banner--ok" id="prf-pin-ok" role="status" hidden>
            <svg class="icon" aria-hidden="true"><use href="#icon-check"/></svg><span>PIN berhasil diganti. Gunakan PIN baru saat masuk berikutnya.</span>
          </div>
          <div class="prf-banner prf-banner--err" id="prf-pin-fail" role="alert" hidden>
            <svg class="icon" aria-hidden="true"><use href="#icon-alert"/></svg><span id="prf-pin-fail-text"></span>
          </div>

          <form id="prf-pin-form" novalidate>
            <div class="prf-input">
              <label for="pin-old">PIN saat ini</label>
              <div class="prf-input__wrap">
                <input type="password" id="pin-old" inputmode="numeric" maxlength="6" autocomplete="current-password" aria-describedby="pin-old-err" placeholder="••••••">
                <button type="button" class="prf-eye" data-target="pin-old" aria-pressed="false" aria-label="Tampilkan PIN saat ini"><svg class="icon" aria-hidden="true"><use href="#icon-eye"/></svg></button>
              </div>
              <p class="prf-err" id="pin-old-err" hidden></p>
            </div>

            <div class="prf-input">
              <label for="pin-new">PIN baru (6 angka)</label>
              <div class="prf-input__wrap">
                <input type="password" id="pin-new" inputmode="numeric" maxlength="6" autocomplete="new-password" aria-describedby="pin-new-hint pin-new-err" placeholder="••••••">
                <button type="button" class="prf-eye" data-target="pin-new" aria-pressed="false" aria-label="Tampilkan PIN baru"><svg class="icon" aria-hidden="true"><use href="#icon-eye"/></svg></button>
              </div>
              <p class="prf-hint" id="pin-new-hint">Pilih angka yang mudah kamu ingat tetapi sulit ditebak.</p>
              <p class="prf-err" id="pin-new-err" hidden></p>
            </div>

            <div class="prf-input">
              <label for="pin-new2">Konfirmasi PIN baru</label>
              <div class="prf-input__wrap">
                <input type="password" id="pin-new2" inputmode="numeric" maxlength="6" autocomplete="new-password" aria-describedby="pin-new2-err" placeholder="••••••">
                <button type="button" class="prf-eye" data-target="pin-new2" aria-pressed="false" aria-label="Tampilkan konfirmasi PIN baru"><svg class="icon" aria-hidden="true"><use href="#icon-eye"/></svg></button>
              </div>
              <p class="prf-err" id="pin-new2-err" hidden></p>
            </div>

            <button type="submit" class="prf-btn prf-btn--primary prf-btn--block" id="prf-pin-save">Simpan Perubahan</button>
          </form>
        </section>

        <button type="button" class="prf-btn prf-btn--danger prf-btn--block" id="prf-logout">
          <svg class="icon" aria-hidden="true"><use href="#icon-logout"/></svg>Keluar dari Akun
        </button>
        <p class="prf-foot">KopiKita Member Portal &bull; Prototipe Tahap 1</p>
      </div>
    </div>
  </div>

  <p class="prf-sr" id="prf-live" role="status" aria-live="polite"></p>
</div>

<script src="../public/js/member-profile.js?v=<?= is_file($jsFile) ? filemtime($jsFile) : 1 ?>"></script>
<?php
$pageContent = ob_get_clean();
include __DIR__ . '/../includes/member_layout.php';
