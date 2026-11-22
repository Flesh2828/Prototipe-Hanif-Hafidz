<?php
// member/login.php – Login page (uses dummy backend)
$pageContent = <<<HTML
<div class="card-glass">
    <h2 style="font-size: var(--size-22); margin-bottom: 12px;">Masuk Member</h2>
    <form id="login-form">
        <div style="margin-bottom: 12px;">
            <label for="phone" style="display:block; font-size: var(--size-14); margin-bottom:4px;">Nomor HP</label>
            <input type="tel" id="phone" name="phone" required pattern="[0-9\-]+" style="width:100%; padding:8px; border:1px solid var(--color-outline-variant); border-radius: var(--radius-sm);" placeholder="0812-3456-7890" />
        </div>
        <div style="margin-bottom: 12px;">
            <label for="pin" style="display:block; font-size: var(--size-14); margin-bottom:4px;">PIN (6 digit)</label>
            <input type="password" id="pin" name="pin" required minlength="6" maxlength="6" style="width:100%; padding:8px; border:1px solid var(--color-outline-variant); border-radius: var(--radius-sm);" placeholder="******" />
        </div>
        <button type="submit" style="width:100%; padding:10px; background: var(--color-primary); color:#fff; border:none; border-radius: var(--radius-sm); font-size: var(--size-16);">Masuk</button>
    </form>
    <p style="margin-top:12px; font-size: var(--size-12); text-align:center;">
        Belum punya akun? <a href="register.php" style="color: var(--color-primary); text-decoration:none;">Daftar</a>
    </p>
    <div id="lock-state" style="display:none; margin-top:12px; color: var(--color-error); font-size: var(--size-12); text-align:center;">
        <span style="margin-right:4px;">🔒</span>Akun terkunci karena 5 x gagal masuk. Coba lagi dalam 15 menit.
    </div>
</div>
<script>
    // Show lock message if account is locked in localStorage (handled by MemberAPI.login)
    (function(){
        const urlParams = new URLSearchParams(window.location.search);
        const phone = document.getElementById('phone').value.trim();
        // Check stored lock flag for any phone (the API stores under loginLock_<phone>)
        // We'll read from localStorage after a failed attempt; for initial load we just hide.
        const lockDiv = document.getElementById('lock-state');
        // Function to refresh lock UI
        function refreshLock(){
            const stored = JSON.parse(localStorage.getItem('loginLock_' + (document.getElementById('phone').value || '')) || '{}');
            if (stored.lockUntil && Date.now() < stored.lockUntil) {
                lockDiv.style.display = 'block';
            } else {
                lockDiv.style.display = 'none';
            }
        }
        // Bind change event to re‑check when user types a phone number
        document.getElementById('phone').addEventListener('input', refreshLock);
        // Initial check (if query includes phone)
        if (urlParams.has('phone')) {
            document.getElementById('phone').value = urlParams.get('phone');
            refreshLock();
        }
    })();
</script>
HTML;
include '../includes/member_layout.php';
?>