/* KopiKita Member – Profil & Keamanan (Tahap F). Data dari MemberAPI. */
(function () {
  'use strict';

  var MONTHS_LONG = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
  var root = document.getElementById('prf');
  var member = null;

  function $(id) { return document.getElementById(id); }
  function setText(id, t) { var n = $(id); if (n) n.textContent = t; }
  function fmtNum(n) { try { return new Intl.NumberFormat('id-ID').format(n); } catch (e) { return String(n); } }
  function fmtPhone(raw) {
    var d = String(raw || '').replace(/\D/g, '');
    return d.length >= 9 ? d.replace(/^(\d{4})(\d{4})(\d+)$/, '$1-$2-$3') : (d || '—');
  }
  function fmtMonthYear(iso) {
    var p = String(iso || '').split('-');
    var m = parseInt(p[1], 10);
    return (m >= 1 && m <= 12 && p[0]) ? MONTHS_LONG[m - 1] + ' ' + p[0] : '—';
  }
  function announce(msg) { var n = $('prf-live'); n.textContent = ''; setTimeout(function () { n.textContent = msg; }, 30); }
  function setLoading(on) { root.classList.toggle('is-loading', on); root.setAttribute('aria-busy', on ? 'true' : 'false'); }

  function showError(msg) {
    setLoading(false);
    setText('prf-error-text', msg);
    $('prf-error').hidden = false;
    $('prf-content').hidden = true;
  }

  /* ---------- Tampilkan data ---------- */
  function renderMember(m) {
    member = m;
    var name = String(m.name || 'Member');
    $('prf-avatar').textContent = name.trim().charAt(0).toUpperCase() || 'M';
    setText('prf-name-hero', name);
    setText('prf-name', name);
    setText('prf-id', m.id);
    setText('prf-joined', fmtMonthYear(m.joinDate));
    setText('prf-phone', fmtPhone(m.phone));
    setText('prf-points', fmtNum(m.pointsActive));
    var gold = m.tier === 'Gold';
    setText('prf-tier', gold ? 'Member Gold' : 'Member Regular');
    $('prf-tier-chip').classList.toggle('prf-chip--gold', gold);
  }

  /* ---------- Ubah nama ---------- */
  function bindName() {
    var view = $('prf-name-view'), form = $('prf-name-form');
    var input = $('prf-name-input'), err = $('prf-name-err'), save = $('prf-name-save');

    function showErr(msg) { err.textContent = msg; err.hidden = !msg; input.setAttribute('aria-invalid', msg ? 'true' : 'false'); }
    function open() {
      input.value = member ? member.name : '';
      showErr('');
      view.hidden = true; form.hidden = false;
      input.focus(); input.select();
    }
    function close(refocus) {
      form.hidden = true; view.hidden = false;
      if (refocus) $('prf-name-edit').focus();
    }

    $('prf-name-edit').addEventListener('click', open);
    $('prf-name-cancel').addEventListener('click', function () { close(true); });
    input.addEventListener('keydown', function (e) { if (e.key === 'Escape') { e.preventDefault(); close(true); } });

    form.addEventListener('submit', async function (e) {
      e.preventDefault();
      var value = input.value.replace(/\s+/g, ' ').trim();
      if (value.length < 2) { showErr('Nama minimal 2 karakter.'); input.focus(); return; }
      save.disabled = true; save.textContent = 'Menyimpan…';
      var res;
      try { res = await MemberAPI.updateName(value); } catch (ex) { res = { success: false, error: 'Terjadi gangguan. Coba lagi.' }; }
      save.disabled = false; save.textContent = 'Simpan';
      if (res.success) {
        renderMember(res.member);
        close(true);
        announce('Nama berhasil diperbarui');
      } else {
        showErr(res.error || 'Nama gagal disimpan.');
        input.focus();
      }
    });
  }

  /* ---------- Ganti PIN ---------- */
  function bindPin() {
    var form = $('prf-pin-form');
    var fields = { old: $('pin-old'), nw: $('pin-new'), nw2: $('pin-new2') };
    var errs = { old: $('pin-old-err'), nw: $('pin-new-err'), nw2: $('pin-new2-err') };
    var ok = $('prf-pin-ok'), fail = $('prf-pin-fail'), save = $('prf-pin-save');

    function fieldErr(key, msg) {
      errs[key].textContent = msg || '';
      errs[key].hidden = !msg;
      fields[key].setAttribute('aria-invalid', msg ? 'true' : 'false');
    }
    function clearAll() { ['old', 'nw', 'nw2'].forEach(function (k) { fieldErr(k, ''); }); fail.hidden = true; }

    // hanya angka, maksimal 6
    Object.keys(fields).forEach(function (k) {
      fields[k].addEventListener('input', function () {
        var v = fields[k].value.replace(/\D/g, '').slice(0, 6);
        if (v !== fields[k].value) fields[k].value = v;
        if (!ok.hidden) ok.hidden = true;
      });
    });

    // tampilkan / sembunyikan PIN
    Array.prototype.forEach.call(document.querySelectorAll('.prf-eye'), function (btn) {
      btn.addEventListener('click', function () {
        var input = $(btn.getAttribute('data-target'));
        var show = input.type === 'password';
        input.type = show ? 'text' : 'password';
        btn.setAttribute('aria-pressed', show ? 'true' : 'false');
        var label = btn.getAttribute('aria-label').replace(/^(Tampilkan|Sembunyikan)/, show ? 'Sembunyikan' : 'Tampilkan');
        btn.setAttribute('aria-label', label);
        btn.querySelector('use').setAttribute('href', show ? '#icon-eye-off' : '#icon-eye');
      });
    });

    form.addEventListener('submit', async function (e) {
      e.preventDefault();
      clearAll(); ok.hidden = true;

      var o = fields.old.value, n = fields.nw.value, c = fields.nw2.value, first = null;
      if (!/^\d{6}$/.test(o)) { fieldErr('old', 'Masukkan PIN saat ini (6 angka).'); first = first || fields.old; }
      if (!/^\d{6}$/.test(n)) { fieldErr('nw', 'PIN baru harus 6 angka.'); first = first || fields.nw; }
      else if (n === o) { fieldErr('nw', 'PIN baru tidak boleh sama dengan PIN saat ini.'); first = first || fields.nw; }
      if (!/^\d{6}$/.test(c)) { fieldErr('nw2', 'Ulangi PIN baru (6 angka).'); first = first || fields.nw2; }
      else if (c !== n) { fieldErr('nw2', 'Konfirmasi PIN tidak sama dengan PIN baru.'); first = first || fields.nw2; }
      if (first) { first.focus(); return; }

      save.disabled = true; save.textContent = 'Menyimpan…';
      var res;
      try { res = await MemberAPI.changePin(o, n); } catch (ex) { res = { success: false, error: 'Terjadi gangguan. Coba lagi.' }; }
      save.disabled = false; save.textContent = 'Simpan Perubahan';

      if (res.success) {
        form.reset();
        // kembalikan semua kolom ke mode tersembunyi
        Array.prototype.forEach.call(document.querySelectorAll('.prf-eye'), function (btn) {
          var input = $(btn.getAttribute('data-target'));
          input.type = 'password';
          btn.setAttribute('aria-pressed', 'false');
          btn.setAttribute('aria-label', btn.getAttribute('aria-label').replace(/^Sembunyikan/, 'Tampilkan'));
          btn.querySelector('use').setAttribute('href', '#icon-eye');
        });
        ok.hidden = false;
        announce('PIN berhasil diganti');
      } else {
        setText('prf-pin-fail-text', res.error || 'PIN gagal diganti.');
        fail.hidden = false;
        if (/saat ini/i.test(res.error || '')) { fieldErr('old', 'PIN saat ini tidak cocok.'); fields.old.focus(); }
      }
    });
  }

  /* ---------- Muat data ---------- */
  async function load() {
    $('prf-error').hidden = true;
    $('prf-content').hidden = false;
    setLoading(true);
    if (!window.MemberAPI) { showError('Modul data tidak termuat. Muat ulang halaman.'); return; }

    var session;
    try { session = await MemberAPI.getSession(); } catch (e) { session = null; }
    if (!session) { window.location.replace('login.php'); return; }
    renderMember(session);
    setLoading(false);
  }

  document.addEventListener('DOMContentLoaded', function () {
    if (!root) return;
    $('prf-retry').addEventListener('click', load);
    $('prf-logout').addEventListener('click', async function () {
      try { await MemberAPI.logout(); } catch (e) { /* abaikan */ }
      window.location.replace('login.php');
    });
    bindName();
    bindPin();
    load();
  });
})();
