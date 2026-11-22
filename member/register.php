<?php
$pageTitle = 'Daftar Member';
$publicPage = true; // hide global header/nav
ob_start();
?>
<div class="auth-shell">
    <div class="auth-hero">
        <div class="logo">
            <svg class="icon" aria-hidden="true"><use href="#icon-cup"/></svg>
        </div>
        <div class="title">KopiKita</div>
        <div class="subtitle">Portal Member Resmi</div>
        <div class="tagline">Teman Ngopi &amp; Keuntungan Spesial Member</div>
        <div class="blob"></div>
        <div class="blob"></div>
    </div>
    <div class="auth-card">
        <h2 class="form-title" style="font-size: var(--size-22); margin-bottom:8px;">Buat Akun Baru</h2>
        <p class="form-sub" style="font-size: var(--size-14); color:var(--color-on-surface-variant); margin-bottom:16px;">Isi data di bawah untuk menjadi member KopiKita</p>
        <form id="register-form">
            <div style="margin-bottom:12px;">
                <label for="name" style="display:block; font-size: var(--size-12); margin-bottom:4px;">Nama Lengkap</label>
                <input type="text" id="name" name="name" required placeholder="Raka Pratama" style="width:100%; padding:8px; border:1px solid var(--color-outline-variant); border-radius: var(--radius-sm);" />
            </div>
            <div style="margin-bottom:12px;">
                <label for="phone" style="display:block; font-size: var(--size-12); margin-bottom:4px;">Nomor HP</label>
                <input type="tel" id="phone" name="phone" required placeholder="08xx xxxx xxxx" style="width:100%; padding:8px; border:1px solid var(--color-outline-variant); border-radius: var(--radius-sm);" />
            </div>
            <div style="margin-bottom:12px;">
                <label for="pin" style="display:block; font-size: var(--size-12); margin-bottom:4px;">PIN 6 Digit</label>
                <input type="password" id="pin" name="pin" required minlength="6" maxlength="6" placeholder="******" style="width:100%; padding:8px; border:1px solid var(--color-outline-variant); border-radius: var(--radius-sm);" />
            </div>
            <div style="margin-bottom:12px;">
                <label for="pin2" style="display:block; font-size: var(--size-12); margin-bottom:4px;">Konfirmasi PIN</label>
                <input type="password" id="pin2" name="pin2" required minlength="6" maxlength="6" placeholder="******" style="width:100%; padding:8px; border:1px solid var(--color-outline-variant); border-radius: var(--radius-sm);" />
            </div>
            <button type="submit" style="width:100%; padding:10px; background:var(--color-primary); color:#fff; border:none; border-radius:12px; font-size:var(--size-16);">Daftar</button>
        </form>
        <p style="margin-top:12px; font-size: var(--size-12); color:var(--color-on-surface-variant);">Sudah punya akun? <a href="login.php" style="color:var(--color-primary); text-decoration:none;">Masuk</a></p>
    </div>
    <div style="text-align:center; margin-top:16px;">
        <span class="chip"><svg class="icon" aria-hidden="true"><use href="#icon-star"/></svg>Poin Tiap Pembelian</span>
        <span class="chip"><svg class="icon" aria-hidden="true"><use href="#icon-ticket"/></svg>Voucher Hemat</span>
    </div>
</div>
<?php
$pageContent = ob_get_clean();
include __DIR__ . '/../includes/member_layout.php';
?>