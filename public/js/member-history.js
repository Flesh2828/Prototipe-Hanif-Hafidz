/* KopiKita Member – Riwayat Poin (Tahap E). Data dari MemberAPI.getLedger(). */
(function () {
  'use strict';

  var MONTHS_SHORT = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
  var MONTHS_LONG = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
  var PAGE_SIZE = 8;
  var DIRECTIONS = [
    { key: '', label: 'Semua' },
    { key: 'in', label: 'Poin Masuk' },
    { key: 'out', label: 'Poin Keluar' }
  ];
  var TYPE_LABEL = {
    'Pembelian': 'Pembelian di gerai',
    'Penukaran voucher': 'Tukar voucher',
    'Koreksi void': 'Koreksi void kasir',
    'Kedaluwarsa': 'Poin kedaluwarsa'
  };
  var TYPE_ICON = { 'Pembelian': 'store', 'Penukaran voucher': 'gift', 'Koreksi void': 'history', 'Kedaluwarsa': 'clock' };

  var root = document.getElementById('hst');
  var state = { member: null, outlets: {}, direction: '', outlet: '', page: 1, total: 0, token: 0 };

  function $(id) { return document.getElementById(id); }
  function fmtNum(n) { try { return new Intl.NumberFormat('id-ID').format(n); } catch (e) { return String(n); } }
  function parseISO(iso) { var p = String(iso).split('-'); return new Date(+p[0], +p[1] - 1, +p[2]); }
  function fmtDate(iso) { var d = parseISO(iso); return d.getDate() + ' ' + MONTHS_SHORT[d.getMonth()] + ' ' + d.getFullYear(); }
  function announce(msg) { var n = $('hst-live'); n.textContent = ''; setTimeout(function () { n.textContent = msg; }, 30); }
  function h(tag, cls, text) { var n = document.createElement(tag); if (cls) n.className = cls; if (text != null) n.textContent = text; return n; }
  function clear(n) { while (n.firstChild) n.removeChild(n.firstChild); }
  function icon(name) {
    var ns = 'http://www.w3.org/2000/svg';
    var svg = document.createElementNS(ns, 'svg');
    svg.setAttribute('class', 'icon'); svg.setAttribute('aria-hidden', 'true');
    var use = document.createElementNS(ns, 'use'); use.setAttribute('href', '#icon-' + name);
    svg.appendChild(use); return svg;
  }
  function setLoading(on) { root.classList.toggle('is-loading', on); root.setAttribute('aria-busy', on ? 'true' : 'false'); }

  function showError(msg) {
    setLoading(false);
    $('hst-error-text').textContent = msg;
    $('hst-error').hidden = false;
    $('hst-content').hidden = true;
  }

  function outletLabel(e) {
    if (e.outlet) return state.outlets[e.outlet] || 'Outlet';
    return e.type === 'Kedaluwarsa' ? 'Sistem KopiKita' : 'Portal Member';
  }

  /* ---------- Render ---------- */
  function renderSummary(summary) {
    $('hst-total').textContent = fmtNum(state.member.pointsActive);
    $('hst-in').textContent = '+' + fmtNum(summary.totalIn) + ' pts';
    $('hst-out').textContent = '−' + fmtNum(summary.totalOut) + ' pts';

    var exp = Number(state.member.pointsExpiringSoon) || 0;
    $('hst-expiry').hidden = exp <= 0;
    $('hst-rules-open2').hidden = exp > 0;
    if (exp > 0) $('hst-expiry-text').textContent = exp + ' poin akan kedaluwarsa dalam 30 hari ke depan';
  }

  function renderChips() {
    var box = $('hst-dir-chips');
    clear(box);
    DIRECTIONS.forEach(function (d) {
      var b = h('button', 'hst-chip', d.label);
      b.type = 'button';
      b.setAttribute('aria-pressed', state.direction === d.key ? 'true' : 'false');
      b.addEventListener('click', function () { state.direction = d.key; state.page = 1; fetchLedger(); });
      box.appendChild(b);
    });
  }

  function renderList(data) {
    var list = $('hst-list');
    clear(list);
    var empty = $('hst-empty');
    empty.hidden = data.length > 0;
    list.hidden = data.length === 0;

    if (!data.length) {
      var filtered = !!(state.direction || state.outlet);
      $('hst-empty-title').textContent = filtered ? 'Tidak ada mutasi untuk filter ini' : 'Belum ada mutasi poin';
      $('hst-empty-text').textContent = filtered
        ? 'Coba ubah filter arah poin atau lokasi outlet.'
        : 'Poin akan muncul di sini setelah kamu bertransaksi di gerai KopiKita.';
      $('hst-reset').hidden = !filtered;
      return;
    }

    // kelompokkan per bulan (data sudah urut terbaru lebih dulu)
    var groups = [], map = {};
    data.forEach(function (e) {
      var key = e.date.slice(0, 7);
      if (!map[key]) { map[key] = { key: key, items: [] }; groups.push(map[key]); }
      map[key].items.push(e);
    });

    groups.forEach(function (g) {
      var d = parseISO(g.key + '-01');
      var wrap = h('section', 'hst-group');
      var head = h('div', 'hst-group__head');
      var title = h('h3', 'hst-group__title', MONTHS_LONG[d.getMonth()] + ' ' + d.getFullYear());
      head.appendChild(title);
      head.appendChild(h('span', 'hst-group__count', g.items.length + ' transaksi'));
      wrap.appendChild(head);

      var ul = h('ul', 'hst-rows');
      g.items.forEach(function (e) {
        var li = h('li', 'hst-row');
        var ico = h('div', 'hst-row__ico'); ico.appendChild(icon(TYPE_ICON[e.type] || 'history'));
        var main = h('div', 'hst-row__main');
        main.appendChild(h('p', 'hst-row__title', TYPE_LABEL[e.type] || e.type));
        var meta = outletLabel(e) + ' • ' + fmtDate(e.date) + (e.amount ? ' • Belanja Rp' + fmtNum(e.amount) : '');
        main.appendChild(h('p', 'hst-row__meta', meta));

        var plus = e.points > 0;
        var pts = h('span', 'hst-row__pts ' + (plus ? 'hst-row__pts--in' : 'hst-row__pts--out'), (plus ? '+' : '−') + Math.abs(e.points) + ' pts');
        pts.setAttribute('aria-label', (plus ? 'Menambah ' : 'Mengurangi ') + Math.abs(e.points) + ' poin');

        li.appendChild(ico); li.appendChild(main); li.appendChild(pts);
        ul.appendChild(li);
      });
      wrap.appendChild(ul);
      list.appendChild(wrap);
    });
  }

  function renderPager() {
    var pages = Math.max(1, Math.ceil(state.total / PAGE_SIZE));
    $('hst-pager').hidden = pages <= 1;
    $('hst-pager-info').textContent = 'Halaman ' + state.page + ' dari ' + pages;
    $('hst-prev').disabled = state.page <= 1;
    $('hst-next').disabled = state.page >= pages;
  }

  /* ---------- Muat data ---------- */
  async function fetchLedger() {
    var token = ++state.token; // abaikan respons usang bila filter berganti cepat
    renderChips();
    try {
      var res = await MemberAPI.getLedger({
        outletId: state.outlet || null, direction: state.direction || null, page: state.page, pageSize: PAGE_SIZE
      });
      if (token !== state.token) return;
      state.total = res.total;
      renderSummary(res.summary || { totalIn: 0, totalOut: 0 });
      renderList(res.data || []);
      renderPager();
      setLoading(false);
      announce('Menampilkan ' + (res.data || []).length + ' dari ' + res.total + ' mutasi poin');
    } catch (err) {
      if (token === state.token) showError('Riwayat poin belum bisa dimuat. Periksa koneksi lalu coba lagi.');
    }
  }

  async function load() {
    $('hst-error').hidden = true;
    $('hst-content').hidden = false;
    setLoading(true);
    if (!window.MemberAPI) { showError('Modul data tidak termuat. Muat ulang halaman.'); return; }

    var session;
    try { session = await MemberAPI.getSession(); } catch (e) { session = null; }
    if (!session) { window.location.replace('login.php'); return; }
    state.member = session;

    try {
      var outlets = await MemberAPI.getOutlets();
      var sel = $('hst-outlet');
      while (sel.options.length > 1) sel.remove(1);
      outlets.forEach(function (o) {
        state.outlets[o.id] = o.name;
        var opt = document.createElement('option'); opt.value = o.id; opt.textContent = o.name; sel.appendChild(opt);
      });
      sel.value = state.outlet;
    } catch (e) { /* daftar outlet gagal: filter outlet tetap "semua" */ }

    await fetchLedger();
  }

  /* ---------- Interaksi ---------- */
  function bind() {
    $('hst-retry').addEventListener('click', load);
    $('hst-outlet').addEventListener('change', function (e) { state.outlet = e.target.value; state.page = 1; fetchLedger(); });
    $('hst-reset').addEventListener('click', function () {
      state.direction = ''; state.outlet = ''; state.page = 1; $('hst-outlet').value = ''; fetchLedger();
    });
    function goto(page) {
      state.page = page; fetchLedger();
      var top = document.querySelector('.hst-toolbar');
      if (top && top.scrollIntoView) top.scrollIntoView({ block: 'start' });
    }
    $('hst-prev').addEventListener('click', function () { if (state.page > 1) goto(state.page - 1); });
    $('hst-next').addEventListener('click', function () { goto(state.page + 1); });

    var dlg = $('hst-dialog');
    function openRules() { if (typeof dlg.showModal === 'function') dlg.showModal(); else dlg.setAttribute('open', ''); }
    function closeRules() { if (typeof dlg.close === 'function') dlg.close(); else dlg.removeAttribute('open'); }
    $('hst-rules-open').addEventListener('click', openRules);
    $('hst-rules-open2').addEventListener('click', openRules);
    $('hst-dialog-close').addEventListener('click', closeRules);
    dlg.addEventListener('click', function (e) { if (e.target === dlg) closeRules(); });
  }

  document.addEventListener('DOMContentLoaded', function () {
    if (!root) return;
    bind();
    load();
  });
})();
