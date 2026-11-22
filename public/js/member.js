// public/js/member.js – UI logic for Member pages (login, register, dashboard, etc.)

(function () {
  const $ = document.querySelector.bind(document);
  const $$ = document.querySelectorAll.bind(document);

  // Utility: simple toast
  function showToast(message, type = "info", duration = 3000) {
    const toastContainer = $('#toast');
    if (!toastContainer) return;
    const toast = document.createElement('div');
    const bg = {
      info: 'var(--color-primary)',
      success: 'var(--color-success)',
      error: 'var(--color-error)'
    }[type] || 'var(--color-primary)';
    toast.style.background = bg;
    toast.style.color = '#fff';
    toast.style.padding = '8px 12px';
    toast.style.marginTop = '8px';
    toast.style.borderRadius = 'var(--radius-sm)';
    toast.textContent = message;
    toastContainer.appendChild(toast);
    setTimeout(() => toast.remove(), duration);
  }

  // Utility: show loading skeleton overlay (optional)
  function setLoading(isLoading) {
    if (isLoading) {
      document.body.style.pointerEvents = 'none';
      const overlay = document.createElement('div');
      overlay.id = 'loading-overlay';
      overlay.style.position = 'fixed';
      overlay.style.inset = '0';
      overlay.style.background = 'rgba(255,255,255,0.6)';
      overlay.style.zIndex = '10000';
      overlay.innerHTML = '<div class="skeleton" style="width:100%;height:100vh;"></div>';
      document.body.appendChild(overlay);
    } else {
      const overlay = $('#loading-overlay');
      if (overlay) overlay.remove();
      document.body.style.pointerEvents = '';
    }
  }

  // Page handlers -----------------------------------------------------------

  function isValidPhone(raw) {
    const norm = MemberAPI._norm(raw);
    return norm.startsWith('08') && norm.length >= 10 && norm.length <= 14;
  }

  function initLoginPage() {
    const form = $('#login-form');
    if (!form) return;
    form.addEventListener('submit', async e => {
      e.preventDefault();
      const phone = $('#phone').value.trim();
      const pin = $('#pin').value.trim();
      if (!isValidPhone(phone)) {
        showToast('Nomor HP harus diawali 08 dan 10–14 digit', 'error');
        return;
      }
      setLoading(true);
      const res = await MemberAPI.login(phone, pin);
      setLoading(false);
      if (res.success) {
        showToast('Berhasil masuk', 'success');
        // Simulate redirect to dashboard after short delay
        setTimeout(() => { window.location.href = 'dashboard.php'; }, 800);
      } else {
        showToast(res.error || 'Gagal masuk', 'error');
      }
    });
  }

  function initRegisterPage() {
    const form = $('#register-form');
    if (!form) return;
    form.addEventListener('submit', async e => {
      e.preventDefault();
      const name = $('#name').value.trim();
      const phone = $('#phone').value.trim();
      const pin = $('#pin').value.trim();
      const pin2 = $('#pin2').value.trim();
      if (pin !== pin2) {
        showToast('PIN tidak cocok', 'error');
        return;
      }
      if (!/^\d{6}$/.test(pin)) {
        showToast('PIN harus 6 digit', 'error');
        return;
      }
      if (!isValidPhone(phone)) {
        showToast('Nomor HP harus diawali 08 dan 10–14 digit', 'error');
        return;
      }
      setLoading(true);
      const res = await MemberAPI.register({ name, phone, pin });
      setLoading(false);
      if (res.success) {
        showToast('Akun berhasil dibuat', 'success');
        setTimeout(() => { window.location.href = 'login.php'; }, 800);
      } else {
        showToast(res.error || 'Registrasi gagal', 'error');
      }
    });
  }

  // Detect which page we are on based on pathname
  const path = window.location.pathname.split('/').pop();
  switch (path) {
    case 'login.php':
      initLoginPage();
      break;
    case 'register.php':
      initRegisterPage();
      break;
    // future cases: dashboard, vouchers, history, profile
  }
})();
