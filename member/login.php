<?php
$pageTitle = 'Masuk Member';
$publicPage = true; // hide global header/nav
ob_start();
?>
<div class="auth-shell">
    <div class="auth-hero">
        <div class="logo">
            <svg class="icon" aria-hidden="true"><use href="/Prototipe-Hanif-Hafidz/public/img/sprite.svg#icon-cup"/></svg>
        </div>
        <div class="title">KopiKita</div>
        <div class="subtitle">Portal Member Resmi</div>
        <div class="tagline">Teman Ngopi &amp; Keuntungan Spesial Member</div>
        <div class="blob"></div>
        <div class="blob"></div>
    </div>
    <div class="auth-card">
        <h2 class="form-title" style="font-size: var(--size-22); margin-bottom:8px;">Selamat datang kembali</h2>
        <p class="form-sub" style="font-size: var(--size-14); color:var(--color-on-surface-variant); margin-bottom:16px;">Masuk untuk lihat poin dan voucher kamu</p>
        <form id="login-form">
            <div style="margin-bottom:12px;">
                <label for="phone" style="display:block; font-size: var(--size-12); margin-bottom:4px;">Nomor HP</label>
                <input type="tel" id="phone" name="phone" required placeholder="08xx xxxx xxxx" style="width:100%; padding:8px; border:1px solid var(--color-outline-variant); border-radius: var(--radius-sm);" />
            </div>
            <div style="margin-bottom:12px; position:relative;">
                <label for="pin" style="display:block; font-size: var(--size-12); margin-bottom:4px;">PIN 6 Digit</label>
                <input type="password" id="pin" name="pin" required minlength="6" maxlength="6" placeholder="******" style="width:100%; padding:8px 36px 8px 8px; border:1px solid var(--color-outline-variant); border-radius: var(--radius-sm);" />
                <button type="button" id="toggle-pin" aria-label="Tampilkan PIN" aria-pressed="false" style="position:absolute; right:8px; top:34px; background:none; border:none; cursor:pointer; color:var(--color-on-surface-variant);">
                    <svg class="icon" aria-hidden="true"><use href="/Prototipe-Hanif-Hafidz/public/img/sprite.svg#icon-eye"/></svg>
                </button>
            </div>
            <div style="text-align:right; margin-bottom:12px;">
                <a href="#" style="font-size: var(--size-12); color:var(--color-primary); text-decoration:none;">Lupa PIN?</a>
            </div>
            <button type="submit" style="width:100%; padding:10px; background:var(--color-primary); color:#fff; border:none; border-radius:12px; font-size:var(--size-16);">Masuk</button>
        </form>
        <p style="margin-top:12px; font-size: var(--size-12); color:var(--color-on-surface-variant);">Info Keamanan: Reset PIN dilakukan langsung di gerai melalui Manajer Outlet.</p>
        <p style="margin-top:8px; font-size: var(--size-12);">Belum punya akun? <a href="register.php" style="color:var(--color-primary); text-decoration:none;">Daftar</a></p>
    </div>
    <div style="text-align:center; margin-top:16px;">
        <span class="chip"><svg class="icon" aria-hidden="true"><use href="/Prototipe-Hanif-Hafidz/public/img/sprite.svg#icon-star"/></svg>Poin Tiap Pembelian</span>
        <span class="chip"><svg class="icon" aria-hidden="true"><use href="/Prototipe-Hanif-Hafidz/public/img/sprite.svg#icon-ticket"/></svg>Voucher Hemat</span>
    </div>
</div>
<script>
    // Toggle PIN visibility – swaps eye/eye-off icons from sprite
    document.getElementById('toggle-pin').addEventListener('click', function(){
        const pin = document.getElementById('pin');
        const use = this.querySelector('use');
        const showing = pin.type === 'text';
        pin.type = showing ? 'password' : 'text';
        this.setAttribute('aria-pressed', showing ? 'false' : 'true');
        this.setAttribute('aria-label', showing ? 'Tampilkan PIN' : 'Sembunyikan PIN');
        const newHref = showing ? '/Prototipe-Hanif-Hafidz/public/img/sprite.svg#icon-eye' : '/Prototipe-Hanif-Hafidz/public/img/sprite.svg#icon-eye-off';
        use.setAttribute('href', newHref);
    });
</script>
<?php
$pageContent = ob_get_clean();
include __DIR__ . '/../includes/member_layout.php';
?>