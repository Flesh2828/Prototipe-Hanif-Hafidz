// public/js/member-data.js (v2, Tahap D)
// Lapisan data prototipe: data dummy + penyimpanan di localStorage.
// Di produksi seluruh logika ini dipindah ke API PHP + MySQL (PIN di-hash bcrypt, poin dihitung server).

/* ---------- Utilitas ---------- */
function delay(ms) {
  return new Promise(function (resolve) { setTimeout(resolve, ms); });
}

/* Nomor HP: hanya angka, awalan 62/+62 diubah jadi 0 */
function normalizePhone(raw) {
  var digits = String(raw == null ? "" : raw).replace(/\D/g, "");
  return digits.replace(/^62/, "0");
}

function pad2(n) { return (n < 10 ? "0" : "") + n; }
/* Tanggal lokal format YYYY-MM-DD (bukan UTC, agar tidak bergeser di zona WIB) */
function localISO(d) {
  return d.getFullYear() + "-" + pad2(d.getMonth() + 1) + "-" + pad2(d.getDate());
}
function todayISO() { return localISO(new Date()); }
function addDaysISO(offset) {
  var d = new Date();
  d.setDate(d.getDate() + offset);
  return localISO(d);
}

function storageGet(key, fallback) {
  try {
    var raw = localStorage.getItem(key);
    return raw ? JSON.parse(raw) : fallback;
  } catch (e) { return fallback; }
}
function storageSet(key, value) {
  try { localStorage.setItem(key, JSON.stringify(value)); } catch (e) { /* data tetap di memori */ }
}

/* ---------- Data dummy ---------- */
var DUMMY_MEMBER = {
  id: "KP-82049",
  name: "Raka Pratama",
  phone: "0812-3456-7890",
  pin: "123456",
  tier: "Regular",
  pointsActive: 87,
  pointsCumulative: 87,
  pointsExpiringSoon: 3,
  joinDate: "2026-01-01"
};

var OUTLETS = [
  { id: "O1", name: "KopiKita Kaliurang (Pusat)" },
  { id: "O2", name: "KopiKita Gejayan" },
  { id: "O3", name: "KopiKita Malioboro" }
];

/* Katalog voucher yang bisa ditukar dengan poin (PRD: berlaku 14 hari, voucher biji kopi khusus Gold) */
var VALID_DAYS = 14;
var CATALOG = [
  { id: "C-01", category: "Minuman", title: "Potongan Rp10.000", cost: 25, lockedFor: null,
    description: "Potongan langsung untuk pembelian menu minuman apa saja di seluruh gerai KopiKita." },
  { id: "C-02", category: "Minuman", title: "Gratis 1 Es Kopi Susu", cost: 30, lockedFor: null,
    description: "Nikmati 1 cup Es Kopi Susu Aren racikan barista tanpa minimum pembelian." },
  { id: "C-03", category: "Minuman", title: "Upgrade Ukuran Gratis", cost: 15, lockedFor: null,
    description: "Naikkan ukuran minuman pilihanmu satu tingkat tanpa biaya tambahan." },
  { id: "C-04", category: "Makanan Ringan", title: "Diskon 20% Pastry", cost: 20, lockedFor: null,
    description: "Diskon 20% untuk satu pastry pilihan di gerai manapun." },
  { id: "C-05", category: "Makanan Ringan", title: "Gratis 1 Croissant", cost: 40, lockedFor: null,
    description: "Tukar dengan 1 croissant butter hangat bersama minuman apa pun." },
  { id: "C-06", category: "Biji Kopi", title: "Diskon Biji Kopi 15%", cost: 60, lockedFor: "Gold",
    description: "Diskon 15% untuk pembelian biji kopi 200 gram. Khusus member Gold." }
];

/* Voucher awal milik akun dummy */
function seedDummyVouchers() {
  return [
    { id: "MV-1", code: "KK-8Q4D", catalogId: "C-01", title: "Potongan Rp10.000",
      description: "Potongan langsung untuk pembelian menu minuman apa saja di seluruh gerai KopiKita.",
      status: "ACTIVE", issuedAt: addDaysISO(-6), expiresAt: addDaysISO(8), redeemedAt: null },
    { id: "MV-2", code: "KK-F7KT", catalogId: "C-02", title: "Gratis 1 Es Kopi Susu",
      description: "Nikmati 1 cup Es Kopi Susu Aren racikan barista tanpa minimum pembelian.",
      status: "ACTIVE", issuedAt: addDaysISO(-2), expiresAt: addDaysISO(12), redeemedAt: null },
    { id: "MV-3", code: "KK-2M9P", catalogId: "C-01", title: "Potongan Rp10.000",
      description: "Potongan langsung untuk pembelian menu minuman apa saja di seluruh gerai KopiKita.",
      status: "REDEEMED", issuedAt: addDaysISO(-20), expiresAt: addDaysISO(-6), redeemedAt: addDaysISO(-15) },
    { id: "MV-4", code: "KK-7R3W", catalogId: "C-03", title: "Upgrade Ukuran Gratis",
      description: "Naikkan ukuran minuman pilihanmu satu tingkat tanpa biaya tambahan.",
      status: "EXPIRED", issuedAt: addDaysISO(-30), expiresAt: addDaysISO(-16), redeemedAt: null }
  ];
}

/* Riwayat poin akun dummy. Tanggal relatif terhadap hari ini agar selalu terlihat segar,
   dan jumlah seluruh mutasi = 87 poin (sama dengan saldo awal). */
function buildDummyLedger() {
  var rows = [
    // [hari lalu, jenis, poin, outlet, belanja (Rp, hanya pembelian)]
    [-88, "Pembelian", 24, "O1", 245000], [-80, "Pembelian", 20, "O2", 208500], [-71, "Pembelian", 18, "O1", 183000],
    [-62, "Pembelian", 25, "O3", 252000], [-55, "Pembelian", 15, "O2", 156500], [-47, "Pembelian", 22, "O1", 224000],
    [-38, "Pembelian", 12, "O2", 127000], [-30, "Penukaran voucher", -15, null, null], [-26, "Pembelian", 16, "O3", 169500],
    [-20, "Penukaran voucher", -25, null, null], [-17, "Pembelian", 14, "O1", 142000], [-12, "Kedaluwarsa", -3, null, null],
    [-9, "Pembelian", 11, "O2", 118500], [-6, "Penukaran voucher", -25, null, null], [-4, "Koreksi void", -2, "O2", null],
    [-2, "Penukaran voucher", -30, null, null], [-1, "Pembelian", 10, "O3", 104000]
  ];
  return rows.map(function (r, i) {
    var e = { id: i + 1, type: r[1], points: r[2], outlet: r[3], date: addDaysISO(r[0]) };
    if (r[4]) e.amount = r[4];
    return e;
  });
}
var HISTORY = buildDummyLedger();

/* ---------- Penyimpanan member terdaftar ---------- */
var memMembers = [];
function loadMembers() { memMembers = storageGet("kk_members", []); if (!Array.isArray(memMembers)) memMembers = []; }
function saveMembers() { storageSet("kk_members", memMembers); }
loadMembers();

/* ---------- Dompet per member: selisih poin, voucher milik, mutasi baru ---------- */
/* kk_wallet = { "<idMember>": { adj: number, seq: number, vouchers: [], ledger: [] } } */
function loadWallets() {
  var w = storageGet("kk_wallet", {});
  return (w && typeof w === "object") ? w : {};
}
function getWallet(memberId) {
  var all = loadWallets();
  var w = all[memberId];
  if (!w) {
    w = { adj: 0, seq: 1000, vouchers: memberId === DUMMY_MEMBER.id ? seedDummyVouchers() : [], ledger: [] };
    all[memberId] = w;
    storageSet("kk_wallet", all);
  }
  return w;
}
function saveWallet(memberId, wallet) {
  var all = loadWallets();
  all[memberId] = wallet;
  storageSet("kk_wallet", all);
}

/* Status voucher dihitung saat dibaca: ACTIVE yang lewat tanggal berlaku menjadi EXPIRED */
function resolveStatus(v) {
  if (v.status === "ACTIVE" && v.expiresAt < todayISO()) return "EXPIRED";
  return v.status;
}

var CODE_ALPHABET = "ABCDEFGHJKMNPQRSTUVWXYZ23456789"; // tanpa I, L, O, 0, 1 agar tidak ambigu
function makeCode(existing) {
  var code, tries = 0;
  do {
    code = "KK-";
    for (var i = 0; i < 4; i++) code += CODE_ALPHABET.charAt(Math.floor(Math.random() * CODE_ALPHABET.length));
    tries++;
  } while (existing.indexOf(code) !== -1 && tries < 50);
  return code;
}

function baseMemberById(id) {
  if (id === DUMMY_MEMBER.id) return DUMMY_MEMBER;
  for (var i = 0; i < memMembers.length; i++) if (memMembers[i].id === id) return memMembers[i];
  return null;
}

/* Gabungkan data dasar member dengan dompetnya (poin aktif dan tier) */
function composeMember(base) {
  if (!base) return null;
  var w = getWallet(base.id);
  var m = {};
  for (var k in base) if (Object.prototype.hasOwnProperty.call(base, k)) m[k] = base[k];
  m.pointsActive = Math.max(0, (Number(base.pointsActive) || 0) + (Number(w.adj) || 0));
  var cum = Number(base.pointsCumulative) || 0;
  m.tier = (base.tier === "Gold" || cum >= 100) ? "Gold" : "Regular";
  return m;
}

function currentSessionId() {
  var sess = storageGet("kk_session", null);
  return sess && sess.id ? sess.id : null;
}

/* ---------- API ---------- */
window.MemberAPI = {
  _norm: function (phone) { return normalizePhone(phone); },

  /* Akun dummy untuk demo */
  getMember: async function () {
    await delay(250);
    return composeMember(DUMMY_MEMBER);
  },

  getOutlets: async function () {
    await delay(150);
    return OUTLETS.map(function (o) { return { id: o.id, name: o.name }; });
  },

  /* Katalog voucher (status ACTIVE = tersedia untuk ditukar) */
  getVouchers: async function () {
    await delay(150);
    return CATALOG.map(function (c) {
      return { id: c.id, category: c.category, title: c.title, description: c.description,
               cost: c.cost, lockedFor: c.lockedFor, validDays: VALID_DAYS, status: "ACTIVE" };
    });
  },

  register: async function (data) {
    await delay(300);
    var normPhone = normalizePhone(data.phone);
    var existsDummy = normalizePhone(DUMMY_MEMBER.phone) === normPhone;
    var existsStored = memMembers.some(function (m) { return normalizePhone(m.phone) === normPhone; });
    if (existsDummy || existsStored) {
      return { success: false, error: "Nomor HP sudah terdaftar" };
    }
    var newMember = {
      id: "KP-" + Math.floor(Math.random() * 90000 + 10000),
      name: data.name,
      phone: normPhone,
      pin: String(data.pin), // hanya prototipe; di produksi PIN di-hash bcrypt di server
      tier: "Regular",
      pointsActive: 0,
      pointsCumulative: 0,
      pointsExpiringSoon: 0,
      joinDate: todayISO()
    };
    memMembers.push(newMember);
    saveMembers();
    return { success: true, member: composeMember(newMember) };
  },

  login: async function (rawPhone, pin) {
    await delay(250);
    var phone = normalizePhone(rawPhone);
    var pinStr = String(pin);
    var lockKey = "loginLock_" + phone;
    var now = Date.now();
    var lockInfo = storageGet(lockKey, {});

    if (lockInfo.lockUntil && now < lockInfo.lockUntil) {
      var mins = Math.max(1, Math.ceil((lockInfo.lockUntil - now) / 60000));
      return { success: false, error: "Akun terkunci. Coba lagi dalam " + mins + " menit." };
    }
    if (lockInfo.lockUntil && now >= lockInfo.lockUntil) { lockInfo = {}; }

    loadMembers();
    var found = null;
    if (normalizePhone(DUMMY_MEMBER.phone) === phone && DUMMY_MEMBER.pin === pinStr) found = DUMMY_MEMBER;
    if (!found) {
      for (var i = 0; i < memMembers.length; i++) {
        if (normalizePhone(memMembers[i].phone) === phone && memMembers[i].pin === pinStr) { found = memMembers[i]; break; }
      }
    }

    if (found) {
      storageSet("kk_session", { id: found.id, phone: found.phone }); // tanpa PIN
      try { localStorage.removeItem(lockKey); } catch (e) { /* abaikan */ }
      return { success: true, member: composeMember(found) };
    }

    lockInfo.attempts = (lockInfo.attempts || 0) + 1;
    if (lockInfo.attempts >= 5) {
      lockInfo.lockUntil = now + 15 * 60 * 1000;
      lockInfo.attempts = 0;
    }
    storageSet(lockKey, lockInfo);
    return { success: false, error: "PIN atau nomor HP salah" };
  },

  getSession: async function () {
    await delay(80);
    loadMembers();
    var id = currentSessionId();
    if (!id) return null;
    return composeMember(baseMemberById(id));
  },

  logout: async function () {
    await delay(80);
    try { localStorage.removeItem("kk_session"); } catch (e) { /* abaikan */ }
    return { success: true };
  },

  /* Riwayat poin milik member yang login (terbaru lebih dulu).
     Opsi: outletId, type, direction ("in" | "out"), page, pageSize.
     summary = total masuk/keluar untuk outlet terpilih (tidak terpengaruh filter jenis/arah/halaman). */
  getLedger: async function (opts) {
    await delay(200);
    opts = opts || {};
    var outletId = opts.outletId || null;
    var type = opts.type || "Semua";
    var direction = opts.direction || null;
    var page = opts.page || 1;
    var pageSize = opts.pageSize || 10;
    var empty = { total: 0, page: page, pageSize: pageSize, data: [], summary: { totalIn: 0, totalOut: 0 } };

    var id = currentSessionId();
    if (!baseMemberById(id)) return empty;

    var list = (id === DUMMY_MEMBER.id ? HISTORY.slice() : []).concat(getWallet(id).ledger);
    if (outletId) list = list.filter(function (h) { return h.outlet === outletId; });

    var summary = { totalIn: 0, totalOut: 0 };
    list.forEach(function (h) { if (h.points > 0) summary.totalIn += h.points; else summary.totalOut += -h.points; });

    if (type !== "Semua") list = list.filter(function (h) { return h.type === type; });
    if (direction === "in") list = list.filter(function (h) { return h.points > 0; });
    if (direction === "out") list = list.filter(function (h) { return h.points < 0; });
    list.sort(function (a, b) {
      if (a.date !== b.date) return a.date < b.date ? 1 : -1;
      return (b.id || 0) - (a.id || 0);
    });
    var start = (page - 1) * pageSize;
    return {
      total: list.length, page: page, pageSize: pageSize, summary: summary,
      data: list.slice(start, start + pageSize).map(function (e) {
        var o = { id: e.id, type: e.type, points: e.points, outlet: e.outlet, date: e.date };
        if (e.amount) o.amount = e.amount;
        return o;
      })
    };
  },

  /* Voucher milik member yang login */
  getMyVouchers: async function () {
    await delay(150);
    var id = currentSessionId();
    if (!baseMemberById(id)) return [];
    return getWallet(id).vouchers.map(function (v) {
      return { id: v.id, code: v.code, catalogId: v.catalogId, title: v.title, description: v.description,
               status: resolveStatus(v), issuedAt: v.issuedAt, expiresAt: v.expiresAt, redeemedAt: v.redeemedAt };
    });
  },

  /* Tukar poin dengan voucher dari katalog */
  claimVoucher: async function (catalogId) {
    await delay(350);
    var id = currentSessionId();
    var base = baseMemberById(id);
    if (!base) return { success: false, error: "Sesi berakhir. Silakan masuk lagi." };

    var item = null;
    for (var i = 0; i < CATALOG.length; i++) if (CATALOG[i].id === catalogId) { item = CATALOG[i]; break; }
    if (!item) return { success: false, error: "Voucher tidak ditemukan." };

    var member = composeMember(base);
    if (item.lockedFor && member.tier !== item.lockedFor) {
      return { success: false, error: "Voucher ini khusus member " + item.lockedFor + "." };
    }
    if (member.pointsActive < item.cost) {
      return { success: false, error: "Poin belum cukup. Kurang " + (item.cost - member.pointsActive) + " poin." };
    }

    var w = getWallet(id);
    var codes = w.vouchers.map(function (v) { return v.code; });
    w.seq = (w.seq || 1000) + 1;
    var voucher = {
      id: "MV-" + w.seq, code: makeCode(codes), catalogId: item.id, title: item.title,
      description: item.description, status: "ACTIVE",
      issuedAt: todayISO(), expiresAt: addDaysISO(VALID_DAYS), redeemedAt: null
    };
    w.adj = (Number(w.adj) || 0) - item.cost;
    w.vouchers.unshift(voucher);
    w.seq += 1;
    w.ledger.push({ id: w.seq, type: "Penukaran voucher", points: -item.cost, outlet: null, date: todayISO() });
    saveWallet(id, w);

    return {
      success: true,
      voucher: { id: voucher.id, code: voucher.code, title: voucher.title, expiresAt: voucher.expiresAt },
      balance: member.pointsActive - item.cost
    };
  },

  /* Ganti PIN (prototipe) */
  changePin: async function (oldPin, newPin) {
    await delay(200);
    if (!/^[0-9]{6}$/.test(String(newPin))) return { success: false, error: "PIN baru harus 6 digit angka" };
    var id = currentSessionId();
    if (!id) return { success: false, error: "Tidak ada sesi login" };
    if (id === DUMMY_MEMBER.id) {
      if (DUMMY_MEMBER.pin !== String(oldPin)) return { success: false, error: "PIN lama tidak cocok" };
      DUMMY_MEMBER.pin = String(newPin); // hanya bertahan sampai halaman dimuat ulang
      return { success: true };
    }
    loadMembers();
    for (var i = 0; i < memMembers.length; i++) {
      if (memMembers[i].id === id) {
        if (memMembers[i].pin !== String(oldPin)) return { success: false, error: "PIN lama tidak cocok" };
        memMembers[i].pin = String(newPin);
        saveMembers();
        return { success: true };
      }
    }
    return { success: false, error: "Member tidak ditemukan" };
  }
};

window.MemberAPI._exported = Object.keys(window.MemberAPI);
