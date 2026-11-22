<?php
// member/register.php – Registration page (dummy backend)
$pageContent = <<<HTML
<div class="card-glass">
    <h2 style="font-size: var(--size-22); margin-bottom: 12px;">Daftar Member</h2>
    <form id="register-form">
        <div style="margin-bottom: 12px;">
            <label for="name" style="display:block; font-size: var(--size-14); margin-bottom:4px;">Nama Lengkap</label>
            <input type="text" id="name" name="name" required style="width:100%; padding:8px; border:1px solid var(--color-outline-variant); border-radius: var(--radius-sm);" placeholder="Raka Pratama" />
        </div>
        <div style="margin-bottom: 12px;">
            <label for="phone" style="display:block; font-size: var(--size-14); margin-bottom:4px;">Nomor HP</label>
            <input type="tel" id="phone" name="phone" required pattern="[0-9\-]+" style="width:100%; padding:8px; border:1px solid var(--color-outline-variant); border-radius: var(--radius-sm);" placeholder="0812-3456-7890" />
        </div>
        <div style="margin-bottom: 12px;">
            <label for="pin" style="display:block; font-size: var(--size-14); margin-bottom:4px;">PIN (6 digit)</label>
            <input type="password" id="pin" name="pin" required minlength="6" maxlength="6" style="width:100%; padding:8px; border:1px solid var(--color-outline-variant); border-radius: var(--radius-sm);" placeholder="******" />
        </div>
        <div style="margin-bottom: 12px;">
            <label for="pin2" style="display:block; font-size: var(--size-14); margin-bottom:4px;">Konfirmasi PIN</label>
            <input type="password" id="pin2" name="pin2" required minlength="6" maxlength="6" style="width:100%; padding:8px; border:1px solid var(--color-outline-variant); border-radius: var(--radius-sm);" placeholder="******" />
        </div>
        <button type="submit" style="width:100%; padding:10px; background: var(--color-primary); color:#fff; border:none; border-radius: var(--radius-sm); font-size: var(--size-16);">Daftar</button>
    </form>
    <p style="margin-top:12px; font-size: var(--size-12); text-align:center;">
        Sudah punya akun? <a href="login.php" style="color: var(--color-primary); text-decoration:none;">Masuk</a>
    </p>
</div>
HTML;
include '../includes/member_layout.php';
?>