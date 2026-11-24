/* KopiKita Member – Beranda (Tahap C). Data dari MemberAPI (dummy + localStorage). */
(function () {
  'use strict';

  var GOLD_TARGET = 100; // poin kumulatif untuk Gold (PRD)
  var MONTHS = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];

  function $(sel) { return document.querySelector(sel); }
  function setText(id, text) { var n = document.getElementById(id); if (n) n.textContent = text; }
  function fmtNum(n) { try { return new Intl.NumberFormat('id-ID').format(n); } catch (e) { return String(n); } }
  function fmtDate(iso) {
    var p = String(iso).split('-');
    var m = parseInt(p[1], 10), d = parseInt(p[2], 10);
    return (m >= 1 && m <= 12 && d) ? d + ' ' + MONTHS[m - 1] : String(iso);
  }
  function fmtPhone(raw) {
    var d = String(raw || '').replace(/\D/g, '');
    return d.length >= 9 ? d.replace(/^(\d{4})(\d{4})(\d+)$/, '$1-$2-$3') : (d || '—');
  }
  function svgIcon(name) {
    var ns = 'http://www.w3.org/2000/svg';
    var svg = document.createElementNS(ns, 'svg');
    svg.setAttribute('class', 'icon');
    svg.setAttribute('aria-hidden', 'true');
    var use = document.createElementNS(ns, 'use');
    use.setAttribute('href', '#icon-' + name);
    svg.appendChild(use);
    return svg;
  }
  function iconFor(type) {
    if (type === 'Pembelian') return 'store';
    if (type === 'Penukaran voucher') return 'gift';
    if (type === 'Kedaluwarsa') return 'clock';
    return 'history';
  }

  var root = $('#dash');

  function setLoading(on) {
    root.classList.toggle('is-loading', on);
    root.setAttribute('aria-busy', on ? 'true' : 'false');
  }

  function showError(msg) {
    setLoading(false);
    setText('dash-error-text', msg);
    $('#dash-error').hidden = false;
    $('#dash-content').hidden = true;
  }

  /* ---------- Render ---------- */
  function renderHero(m) {
    var isGold = m.tier === 'Gold';
    var cum = Number(m.pointsCumulative) || 0;
    var active = Number(m.pointsActive) || 0;

    setText('dash-greeting', 'Halo, ' + String(m.name || 'Member').split(' ')[0]);
    setText('dash-id', m.id);
    setText('dash-points', fmtNum(active));
    setText('dash-tier', isGold ? 'Gold' : 'Regular');
    $('#dash-tier-chip').classList.toggle('dash-chip--gold', isGold);

    var bar = $('#dash-progress-bar');
    var fill = $('#dash-progress-fill');
    if (isGold) {
      setText('dash-progress-label', 'Status tier');
      setText('dash-progress-value', 'Gold aktif');
      setText('dash-progress-hint', 'Bonus poin 1,25× dan voucher biji kopi terbuka untukmu.');
      bar.setAttribute('aria-valuenow', '100');
      fill.style.width = '100%';
    } else {
      var pct = Math.min(100, Math.round((cum / GOLD_TARGET) * 100));
      var left = Math.max(0, GOLD_TARGET - cum);
      setText('dash-progress-label', 'Target Tier Gold');
      setText('dash-progress-value', fmtNum(Math.min(cum, GOLD_TARGET)) + ' / ' + GOLD_TARGET + ' Poin');
      setText('dash-progress-hint', left > 0 ? left + ' poin lagi menuju Gold' : 'Target Gold tercapai');
      bar.setAttribute('aria-valuenow', String(pct));
      requestAnimationFrame(function () { fill.style.width = pct + '%'; });
    }

    var exp = Number(m.pointsExpiringSoon) || 0;
    $('#dash-expiry').hidden = exp <= 0;
    if (exp > 0) setText('dash-expiry-text', exp + ' poin akan kedaluwarsa');

    setText('dash-dialog-phone', fmtPhone(m.phone));
    setText('dash-dialog-id', 'ID: ' + m.id);
  }

  function renderReco(vouchers, m) {
    var box = $('#dash-reco');
    var tier = m.tier === 'Gold' ? 'Gold' : 'Regular';
    var pick = null;
    for (var i = 0; i < vouchers.length; i++) {
      var v = vouchers[i];
      if (v.status === 'ACTIVE' && (!v.lockedFor || v.lockedFor === tier)) { pick = v; break; }
    }
    if (!pick) { box.hidden = true; return; }

    setText('dash-reco-heading', pick.title);
    setText('dash-reco-desc', pick.description || '');
    setText('dash-reco-cost', fmtNum(pick.cost));

    var btn = $('#dash-reco-btn');
    var active = Number(m.pointsActive) || 0;
    if (active >= pick.cost) {
      btn.textContent = 'Tukarkan Sekarang';
      btn.removeAttribute('aria-disabled');
      btn.setAttribute('href', 'vouchers.php');
      btn.onclick = null;
    } else {
      btn.textContent = 'Kurang ' + fmtNum(pick.cost - active) + ' poin';
      btn.setAttribute('aria-disabled', 'true');
      btn.removeAttribute('href');
      btn.onclick = function (e) { e.preventDefault(); };
    }
    box.hidden = false;
  }

  function renderLedger(entries, outletMap) {
    var list = $('#dash-ledger');
    var empty = $('#dash-ledger-empty');
    while (list.firstChild) list.removeChild(list.firstChild);

    entries = entries.slice().sort(function (a, b) {
      return a.date < b.date ? 1 : a.date > b.date ? -1 : (b.id || 0) - (a.id || 0);
    }).slice(0, 3);

    if (!entries.length) { list.hidden = true; empty.hidden = false; return; }
    list.hidden = false; empty.hidden = true;

    entries.forEach(function (e) {
      var li = document.createElement('li');

      var ico = document.createElement('div');
      ico.className = 'dash-ledger__ico';
      ico.appendChild(svgIcon(iconFor(e.type)));

      var main = document.createElement('div');
      main.className = 'dash-ledger__main';
      var t = document.createElement('p'); t.className = 'dash-ledger__title'; t.textContent = e.type;
      var meta = document.createElement('p'); meta.className = 'dash-ledger__meta';
      meta.textContent = (e.outlet ? (outletMap[e.outlet] || 'Outlet') : (e.type === 'Kedaluwarsa' ? 'Sistem KopiKita' : 'Portal Member')) + ' • ' + fmtDate(e.date);
      main.appendChild(t); main.appendChild(meta);

      var pts = document.createElement('span');
      var plus = e.points > 0;
      pts.className = 'dash-ledger__pts ' + (plus ? 'dash-ledger__pts--in' : 'dash-ledger__pts--out');
      pts.textContent = (plus ? '+' : '−') + Math.abs(e.points) + ' pts';
      pts.setAttribute('aria-label', (plus ? 'Menambah ' : 'Mengurangi ') + Math.abs(e.points) + ' poin');

      li.appendChild(ico); li.appendChild(main); li.appendChild(pts);
      list.appendChild(li);
    });
  }

  /* ---------- Muat data ---------- */
  async function load() {
    $('#dash-error').hidden = true;
    $('#dash-content').hidden = false;
    setLoading(true);

    if (!window.MemberAPI) { showError('Modul data tidak termuat. Muat ulang halaman.'); return; }

    var session;
    try { session = await MemberAPI.getSession(); } catch (e) { session = null; }
    if (!session) { window.location.replace('login.php'); return; }

    try {
      var results = await Promise.all([
        MemberAPI.getLedger({ pageSize: 50 }),
        MemberAPI.getVouchers(),
        MemberAPI.getOutlets()
      ]);
      var ledger = (results[0] && results[0].data) || [];
      var outletMap = {};
      (results[2] || []).forEach(function (o) { outletMap[o.id] = o.name; });

      renderHero(session);
      renderReco(results[1] || [], session);
      renderLedger(ledger, outletMap);
      setLoading(false);
    } catch (err) {
      showError('Data belum bisa dimuat. Periksa koneksi lalu coba lagi.');
    }
  }

  /* ---------- Interaksi ---------- */
  function bind() {
    $('#dash-retry').addEventListener('click', load);

    $('#dash-logout').addEventListener('click', async function () {
      try { await MemberAPI.logout(); } catch (e) { /* abaikan */ }
      window.location.replace('login.php');
    });

    var dlg = $('#dash-dialog');
    $('#dash-show-id').addEventListener('click', function () {
      if (typeof dlg.showModal === 'function') dlg.showModal(); else dlg.setAttribute('open', '');
    });
    $('#dash-dialog-close').addEventListener('click', function () {
      if (typeof dlg.close === 'function') dlg.close(); else dlg.removeAttribute('open');
    });
    dlg.addEventListener('click', function (e) { if (e.target === dlg && dlg.close) dlg.close(); });
  }

  document.addEventListener('DOMContentLoaded', function () {
    if (!root) return;
    bind();
    load();
  });
})();
