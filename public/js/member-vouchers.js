/* KopiKita Member – Voucher & Penukaran (Tahap D). Data dari MemberAPI. */
(function () {
  'use strict';

  var MONTHS = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
  var CATEGORIES = ['Semua', 'Minuman', 'Makanan Ringan', 'Biji Kopi'];
  var STATUSES = [
    { key: 'ACTIVE', label: 'Aktif' },
    { key: 'REDEEMED', label: 'Terpakai' },
    { key: 'EXPIRED', label: 'Kedaluwarsa' }
  ];
  var CAT_ICON = { 'Minuman': 'cup', 'Makanan Ringan': 'cookie', 'Biji Kopi': 'bean' };

  var root = document.getElementById('vch');
  var state = { member: null, catalog: [], mine: [], cat: 'Semua', status: 'ACTIVE', tab: 'katalog' };

  function $(id) { return document.getElementById(id); }
  function fmtNum(n) { try { return new Intl.NumberFormat('id-ID').format(n); } catch (e) { return String(n); } }
  function parseISO(iso) { var p = String(iso).split('-'); return new Date(+p[0], +p[1] - 1, +p[2]); }
  function fmtDate(iso) { var d = parseISO(iso); return d.getDate() + ' ' + MONTHS[d.getMonth()] + ' ' + d.getFullYear(); }
  function daysLeft(iso) {
    var t = new Date(); t.setHours(0, 0, 0, 0);
    return Math.round((parseISO(iso) - t) / 86400000);
  }
  function announce(msg) { var n = $('vch-live'); n.textContent = ''; setTimeout(function () { n.textContent = msg; }, 30); }

  /* Pembuat elemen kecil. Semua teks lewat textContent (aman dari injeksi). */
  function h(tag, cls, text) {
    var n = document.createElement(tag);
    if (cls) n.className = cls;
    if (text != null) n.textContent = text;
    return n;
  }
  function icon(name) {
    var ns = 'http://www.w3.org/2000/svg';
    var svg = document.createElementNS(ns, 'svg');
    svg.setAttribute('class', 'icon'); svg.setAttribute('aria-hidden', 'true');
    var use = document.createElementNS(ns, 'use'); use.setAttribute('href', '#icon-' + name);
    svg.appendChild(use);
    return svg;
  }
  function clear(n) { while (n.firstChild) n.removeChild(n.firstChild); }

  function setLoading(on) { root.classList.toggle('is-loading', on); root.setAttribute('aria-busy', on ? 'true' : 'false'); }

  function showError(msg) {
    setLoading(false);
    $('vch-error-text').textContent = msg;
    $('vch-error').hidden = false;
    $('panel-katalog').hidden = true;
    $('panel-saya').hidden = true;
  }

  /* ---------- Salin kode ---------- */
  function copyText(text) {
    if (navigator.clipboard && window.isSecureContext) {
      return navigator.clipboard.writeText(text);
    }
    return new Promise(function (resolve, reject) {
      try {
        var ta = document.createElement('textarea');
        ta.value = text; ta.setAttribute('readonly', ''); ta.style.position = 'fixed'; ta.style.opacity = '0';
        document.body.appendChild(ta); ta.select();
        var ok = document.execCommand('copy');
        document.body.removeChild(ta);
        ok ? resolve() : reject(new Error('copy gagal'));
      } catch (e) { reject(e); }
    });
  }
  function copyButton(code) {
    var btn = h('button', 'vch-btn vch-btn--secondary');
    btn.type = 'button';
    btn.setAttribute('aria-label', 'Salin kode ' + code);
    btn.appendChild(icon('copy'));
    var label = h('span', null, 'Salin');
    btn.appendChild(label);
    btn.addEventListener('click', function () {
      copyText(code).then(function () {
        label.textContent = 'Tersalin'; announce('Kode ' + code + ' tersalin');
      }, function () {
        label.textContent = 'Gagal'; announce('Kode gagal disalin. Salin manual: ' + code);
      }).then(function () { setTimeout(function () { label.textContent = 'Salin'; }, 1600); });
    });
    return btn;
  }

  /* ---------- Tab & filter ---------- */
  function setTab(tab, push) {
    state.tab = tab;
    var isKatalog = tab === 'katalog';
    $('tab-katalog').setAttribute('aria-selected', isKatalog ? 'true' : 'false');
    $('tab-katalog').tabIndex = isKatalog ? 0 : -1;
    $('tab-saya').setAttribute('aria-selected', isKatalog ? 'false' : 'true');
    $('tab-saya').tabIndex = isKatalog ? -1 : 0;
    $('panel-katalog').hidden = !isKatalog;
    $('panel-saya').hidden = isKatalog;
    if (push) {
      try { history.replaceState(null, '', isKatalog ? location.pathname : location.pathname + '?tab=saya'); } catch (e) { /* abaikan */ }
    }
  }

  function renderChips(container, items, current, onPick) {
    clear(container);
    items.forEach(function (it) {
      var b = h('button', 'vch-chip', it.label);
      b.type = 'button';
      b.setAttribute('aria-pressed', it.key === current ? 'true' : 'false');
      b.addEventListener('click', function () { onPick(it.key); });
      container.appendChild(b);
    });
  }

  /* ---------- Katalog ---------- */
  function renderCatalog() {
    renderChips($('vch-cat-chips'), CATEGORIES.map(function (c) { return { key: c, label: c }; }), state.cat, function (k) {
      state.cat = k; renderCatalog();
    });

    var grid = $('vch-catalog');
    clear(grid);
    var items = state.catalog.filter(function (c) { return state.cat === 'Semua' || c.category === state.cat; });
    $('vch-catalog-empty').hidden = items.length > 0;
    grid.hidden = items.length === 0;

    var points = state.member.pointsActive;
    items.forEach(function (c) {
      var locked = c.lockedFor && state.member.tier !== c.lockedFor;
      var short = c.cost - points;

      var card = h('article', 'vch-card');
      var top = h('div', 'vch-card__top');
      var cat = h('span', 'vch-card__cat');
      var ico = h('span', 'vch-card__ico'); ico.appendChild(icon(CAT_ICON[c.category] || 'gift'));
      cat.appendChild(ico); cat.appendChild(h('span', null, c.category));
      top.appendChild(cat); top.appendChild(h('span', 'vch-cost', fmtNum(c.cost) + ' poin'));
      card.appendChild(top);

      card.appendChild(h('h3', 'vch-card__title', c.title));
      card.appendChild(h('p', 'vch-card__desc', c.description));
      if (c.lockedFor) {
        var g = h('span', 'vch-gold'); g.appendChild(icon('lock')); g.appendChild(h('span', null, 'Khusus member ' + c.lockedFor));
        card.appendChild(g);
      }

      var foot = h('div', 'vch-card__foot');
      var meta = h('span', 'vch-meta'); meta.appendChild(icon('clock')); meta.appendChild(h('span', null, 'Berlaku ' + c.validDays + ' hari'));
      foot.appendChild(meta);

      var btn = h('button', 'vch-btn vch-btn--primary');
      btn.type = 'button';
      if (locked) {
        btn.textContent = 'Khusus ' + c.lockedFor; btn.setAttribute('aria-disabled', 'true');
      } else if (short > 0) {
        btn.textContent = 'Kurang ' + fmtNum(short) + ' poin'; btn.setAttribute('aria-disabled', 'true');
      } else {
        btn.textContent = 'Tukar Poin';
        btn.addEventListener('click', function () { openConfirm(c, btn); });
      }
      if (btn.getAttribute('aria-disabled') === 'true') {
        btn.addEventListener('click', function (e) { e.preventDefault(); });
      }
      foot.appendChild(btn);
      card.appendChild(foot);
      grid.appendChild(card);
    });
  }

  /* ---------- Voucher saya ---------- */
  function countBy(status) { return state.mine.filter(function (v) { return v.status === status; }).length; }

  function renderMine() {
    var active = countBy('ACTIVE');
    $('vch-mine-count').textContent = active ? '(' + active + ')' : '';

    renderChips($('vch-status-chips'),
      STATUSES.map(function (s) { return { key: s.key, label: s.label + ' (' + countBy(s.key) + ')' }; }),
      state.status, function (k) { state.status = k; renderMine(); });

    var list = $('vch-mine');
    clear(list);
    var items = state.mine.filter(function (v) { return v.status === state.status; });
    $('vch-mine-empty').hidden = items.length > 0;
    list.hidden = items.length === 0;

    if (!items.length) {
      var texts = {
        ACTIVE: ['Belum ada voucher aktif', 'Tukar poinmu di tab Katalog untuk mendapat voucher.'],
        REDEEMED: ['Belum ada voucher terpakai', 'Voucher yang sudah dipakai di kasir akan muncul di sini.'],
        EXPIRED: ['Tidak ada voucher kedaluwarsa', 'Voucher yang lewat masa berlaku akan muncul di sini.']
      }[state.status];
      $('vch-mine-empty-title').textContent = texts[0];
      $('vch-mine-empty-text').textContent = texts[1];
      return;
    }

    items.forEach(function (v) {
      var inactive = v.status !== 'ACTIVE';
      var card = h('article', 'vch-mine' + (inactive ? ' vch-mine--inactive' : ''));

      var head = h('div', 'vch-mine__head');
      head.appendChild(h('h3', 'vch-mine__title', v.title));
      var st = h('span', 'vch-status vch-status--' + v.status.toLowerCase());
      var stIcon = v.status === 'ACTIVE' ? 'check' : v.status === 'REDEEMED' ? 'ticket' : 'clock';
      st.appendChild(icon(stIcon));
      st.appendChild(h('span', null, v.status === 'ACTIVE' ? 'Aktif' : v.status === 'REDEEMED' ? 'Terpakai' : 'Kedaluwarsa'));
      head.appendChild(st);
      card.appendChild(head);

      var code = h('div', 'vch-code');
      code.appendChild(h('span', 'vch-code__text', v.code));
      if (!inactive) code.appendChild(copyButton(v.code));
      card.appendChild(code);

      var meta = h('p', 'vch-mine__meta');
      if (v.status === 'ACTIVE') {
        var left = daysLeft(v.expiresAt);
        meta.appendChild(document.createTextNode('Berlaku s/d ' + fmtDate(v.expiresAt) + ' • '));
        meta.appendChild(h('strong', null, left <= 0 ? 'Berakhir hari ini' : 'Sisa ' + left + ' hari'));
      } else if (v.status === 'REDEEMED') {
        meta.textContent = 'Dipakai pada ' + fmtDate(v.redeemedAt || v.expiresAt);
      } else {
        meta.textContent = 'Berakhir pada ' + fmtDate(v.expiresAt);
      }
      card.appendChild(meta);
      list.appendChild(card);
    });
  }

  function renderPoints() { $('vch-points').textContent = fmtNum(state.member.pointsActive); }

  /* ---------- Dialog penukaran ---------- */
  var dlg = $('vch-dialog');
  var dlgBody = $('vch-dialog-body');
  var lastTrigger = null;

  function openDialog() { if (typeof dlg.showModal === 'function') dlg.showModal(); else dlg.setAttribute('open', ''); }
  function closeDialog() {
    if (typeof dlg.close === 'function') dlg.close(); else dlg.removeAttribute('open');
    if (lastTrigger && document.body.contains(lastTrigger)) lastTrigger.focus();
  }
  function row(label, value) {
    var r = h('div', 'vch-dialog__row'); r.appendChild(h('span', null, label)); r.appendChild(h('strong', null, value)); return r;
  }
  function titleNode(text) { var t = h('h2', null, text); t.id = 'vch-dialog-title'; return t; }
  function closeButton(label, cls) {
    var b = h('button', 'vch-btn ' + (cls || 'vch-btn--secondary'), label); b.type = 'button';
    b.addEventListener('click', closeDialog); return b;
  }

  function openConfirm(item, trigger) {
    lastTrigger = trigger;
    clear(dlgBody);
    dlg.classList.remove('vch-dialog--center');
    dlgBody.appendChild(titleNode('Tukar voucher ini?'));
    dlgBody.appendChild(h('p', null, item.title));

    var box = h('div', 'vch-dialog__box');
    box.appendChild(row('Biaya', fmtNum(item.cost) + ' poin'));
    box.appendChild(row('Poin saat ini', fmtNum(state.member.pointsActive) + ' poin'));
    box.appendChild(row('Sisa poin', fmtNum(state.member.pointsActive - item.cost) + ' poin'));
    box.appendChild(row('Masa berlaku', item.validDays + ' hari sejak ditukar'));
    dlgBody.appendChild(box);

    var err = h('p', 'vch-dialog__err'); err.hidden = true; err.setAttribute('role', 'alert');
    dlgBody.appendChild(err);

    var actions = h('div', 'vch-dialog__actions');
    var cancel = closeButton('Batal');
    var ok = h('button', 'vch-btn vch-btn--primary', 'Ya, Tukar'); ok.type = 'button';
    ok.addEventListener('click', async function () {
      ok.disabled = true; cancel.disabled = true; ok.textContent = 'Memproses…';
      var res;
      try { res = await MemberAPI.claimVoucher(item.id); } catch (e) { res = { success: false, error: 'Terjadi gangguan. Coba lagi.' }; }
      if (res.success) {
        await refreshData();
        showSuccess(res.voucher);
      } else {
        err.textContent = res.error || 'Penukaran gagal.'; err.hidden = false;
        ok.hidden = true; cancel.disabled = false; cancel.textContent = 'Tutup';
        await refreshData();
      }
    });
    actions.appendChild(cancel); actions.appendChild(ok);
    dlgBody.appendChild(actions);
    openDialog();
    ok.focus();
  }

  function showSuccess(v) {
    clear(dlgBody);
    dlg.classList.add('vch-dialog--center');
    var okIcon = h('div', 'vch-dialog__ok'); okIcon.appendChild(icon('check'));
    dlgBody.appendChild(okIcon);
    dlgBody.appendChild(titleNode('Voucher berhasil ditukar'));
    dlgBody.appendChild(h('p', null, v.title + ' • berlaku s/d ' + fmtDate(v.expiresAt)));

    var code = h('div', 'vch-code');
    code.appendChild(h('span', 'vch-code__text', v.code));
    code.appendChild(copyButton(v.code));
    dlgBody.appendChild(code);
    dlgBody.appendChild(h('p', null, 'Tunjukkan kode ini ke kasir saat membayar.'));

    var actions = h('div', 'vch-dialog__actions');
    var tutup = closeButton('Tutup');
    var lihat = h('button', 'vch-btn vch-btn--primary', 'Lihat Voucher Saya'); lihat.type = 'button';
    lihat.addEventListener('click', function () {
      state.status = 'ACTIVE'; renderMine(); setTab('saya', true); closeDialog();
    });
    actions.appendChild(tutup); actions.appendChild(lihat);
    dlgBody.appendChild(actions);
    announce('Voucher berhasil ditukar. Kode ' + v.code);
    lihat.focus();
  }

  dlg.addEventListener('click', function (e) { if (e.target === dlg) closeDialog(); });

  /* ---------- Muat data ---------- */
  async function refreshData() {
    var results = await Promise.all([MemberAPI.getSession(), MemberAPI.getVouchers(), MemberAPI.getMyVouchers()]);
    if (!results[0]) { window.location.replace('login.php'); return; }
    state.member = results[0];
    state.catalog = results[1] || [];
    state.mine = results[2] || [];
    renderPoints(); renderCatalog(); renderMine();
  }

  async function load() {
    $('vch-error').hidden = true;
    setLoading(true);
    if (!window.MemberAPI) { showError('Modul data tidak termuat. Muat ulang halaman.'); return; }
    var session;
    try { session = await MemberAPI.getSession(); } catch (e) { session = null; }
    if (!session) { window.location.replace('login.php'); return; }
    try {
      await refreshData();
      setTab(state.tab, false);
      setLoading(false);
    } catch (e) {
      showError('Data belum bisa dimuat. Periksa koneksi lalu coba lagi.');
    }
  }

  function bind() {
    $('vch-retry').addEventListener('click', load);
    $('tab-katalog').addEventListener('click', function () { setTab('katalog', true); });
    $('tab-saya').addEventListener('click', function () { setTab('saya', true); });
    // Navigasi tab dengan panah kiri/kanan (pola WAI-ARIA)
    document.querySelector('.vch-tabs').addEventListener('keydown', function (e) {
      if (e.key !== 'ArrowLeft' && e.key !== 'ArrowRight') return;
      var next = state.tab === 'katalog' ? 'saya' : 'katalog';
      setTab(next, true); $('tab-' + next).focus();
    });
  }

  document.addEventListener('DOMContentLoaded', function () {
    if (!root) return;
    try { if (new URLSearchParams(location.search).get('tab') === 'saya') state.tab = 'saya'; } catch (e) { /* abaikan */ }
    bind();
    load();
  });
})();
