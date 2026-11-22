// public/js/member-data.js – prototype data layer with persistence

/* Utility: simple delay */
function delay(ms) {
  return new Promise(resolve => setTimeout(resolve, ms));
}

/* Phone normalisation – keep only digits, convert Indonesia prefix to local 0 */
function normalizePhone(raw) {
  const digits = raw.replace(/\D/g, "");
  // replace leading 62 (or 62 after +) with 0
  return digits.replace(/^62/, "0");
}

/* -------------------- Dummy data (hard‑coded) -------------------- */
const DUMMY_MEMBER = {
  id: "KP-82049",
  name: "Raka Pratama",
  phone: "0812-3456-7890",
  pin: "123456",
  tier: "Regular",
  pointsActive: 87,
  pointsCumulative: 87,
  pointsExpiringSoon: 3,
  joinDate: "2026-01-01",
};

const OUTLETS = [
  { id: "O1", name: "KopiKita Kaliurang (Pusat)" },
  { id: "O2", name: "KopiKita Gejayan" },
  { id: "O3", name: "KopiKita Malioboro" },
];

const VOUCHERS = [
  {
    id: "VV-001",
    code: "KK-8921A",
    title: "Potongan Rp10.000",
    cost: 25,
    status: "ACTIVE",
    description: "Gunakan untuk potongan pembelian minimal Rp50.000",
    lockedFor: null,
  },
  {
    id: "VV-002",
    code: "KK-8932B",
    title: "Gratis 1 Es Kopi Susu",
    cost: 30,
    status: "REDEEMED",
    description: "Sudah ditukarkan pada 2026-11-15",
    lockedFor: null,
  },
  {
    id: "VV-003",
    code: "KK-8943C",
    title: "Diskon Biji Kopi 15%",
    cost: 60,
    status: "EXPIRED",
    description: "Masa berlaku sudah lewat",
    lockedFor: "Gold",
  },
];

const HISTORY = [
  { id: 1, type: "Pembelian", points: 10, outlet: "O1", date: "2026-11-10" },
  { id: 2, type: "Pembelian", points: 20, outlet: "O2", date: "2026-11-12" },
  { id: 3, type: "Penukaran voucher", points: -25, outlet: "O1", date: "2026-11-13" },
  { id: 4, type: "Koreksi void", points: -5, outlet: "O3", date: "2026-11-14" },
  { id: 5, type: "Kedaluwarsa", points: -3, outlet: "O2", date: "2026-11-15" },
  { id: 6, type: "Pembelian", points: 15, outlet: "O1", date: "2026-11-16" },
  { id: 7, type: "Pembelian", points: 12, outlet: "O3", date: "2026-11-17" },
  { id: 8, type: "Penukaran voucher", points: -30, outlet: "O2", date: "2026-11-18" },
  { id: 9, type: "Pembelian", points: 8, outlet: "O1", date: "2026-11-19" },
  { id: 10, type: "Pembelian", points: 7, outlet: "O3", date: "2026-11-20" },
  { id: 11, type: "Penukaran voucher", points: -25, outlet: "O1", date: "2026-11-21" },
  { id: 12, type: "Koreksi void", points: -2, outlet: "O2", date: "2026-11-22" },
];

/* -------------------- Persistent storage helpers -------------------- */
let memMembers = [];
function loadMembers() {
  try {
    const raw = localStorage.getItem('kk_members');
    memMembers = raw ? JSON.parse(raw) : [];
  } catch (e) {
    // fallback to in‑memory list if storage fails
    memMembers = [];
  }
}
function saveMembers() {
  try {
    localStorage.setItem('kk_members', JSON.stringify(memMembers));
  } catch (e) {
    // ignore – data stays in memory only
  }
}

// Initialise on load
loadMembers();

/* -------------------- Core API -------------------- */
window.MemberAPI = {
  /** Helper – normalise phone number */
  _norm(phone) { return normalizePhone(phone); },

  /** Get dummy member (for demo) */
  async getMember() {
    await delay(250);
    return { ...DUMMY_MEMBER };
  },

  async getOutlets() {
    await delay(200);
    return OUTLETS.map(o => ({ ...o }));
  },

  async getVouchers() {
    await delay(250);
    return VOUCHERS.map(v => ({ ...v }));
  },

  /** Register a new member */
  async register({ name, phone, pin }) {
    await delay(300);
    const normPhone = normalizePhone(phone);
    // check against dummy and stored members
    const existsDummy = normalizePhone(DUMMY_MEMBER.phone) === normPhone;
    const existsStored = memMembers.some(m => normalizePhone(m.phone) === normPhone);
    if (existsDummy || existsStored) {
      return { success: false, error: "Nomor HP sudah terdaftar" };
    }
    const newMember = {
      id: "KP-" + Math.floor(Math.random() * 90000 + 10000),
      name,
      phone: normPhone,
      pin: String(pin),
      tier: "Silver",
      pointsActive: 0,
      pointsCumulative: 0,
      pointsExpiringSoon: 0,
      joinDate: new Date().toISOString().slice(0, 10),
    };
    memMembers.push(newMember);
    saveMembers();
    return { success: true, member: { ...newMember } };
  },

  /** Login – checks dummy + stored members */
  async login(rawPhone, pin) {
    await delay(250);
    const phone = normalizePhone(rawPhone);
    const lockKey = `loginLock_${phone}`;
    const now = Date.now();
    let lockInfo = {};
    try { lockInfo = JSON.parse(localStorage.getItem(lockKey) || "{}"); } catch (e) { lockInfo = {}; }
    if (lockInfo.lockUntil && now < lockInfo.lockUntil) {
      return { success: false, error: "Akun terkunci, coba lagi nanti" };
    }

    // find member (dummy or stored)
    const dummyMatch = normalizePhone(DUMMY_MEMBER.phone) === phone && DUMMY_MEMBER.pin === pin;
    const stored = memMembers.find(m => normalizePhone(m.phone) === phone && m.pin === pin);

    if (dummyMatch || stored) {
      // successful login – store session (only id & phone)
      const sess = dummyMatch ? { id: DUMMY_MEMBER.id, phone: DUMMY_MEMBER.phone } : { id: stored.id, phone: stored.phone };
      try { localStorage.setItem('kk_session', JSON.stringify(sess)); } catch (e) { /* ignore */ }
      // reset lock counter
      try { localStorage.removeItem(lockKey); } catch (e) { /* ignore */ }
      return { success: true, member: dummyMatch ? { ...DUMMY_MEMBER } : { ...stored } };
    }

    // failed attempt – update lock info
    lockInfo.attempts = (lockInfo.attempts || 0) + 1;
    if (lockInfo.attempts >= 5) {
      lockInfo.lockUntil = now + 15 * 60 * 1000; // 15 menit
      lockInfo.attempts = 0;
    }
    try { localStorage.setItem(lockKey, JSON.stringify(lockInfo)); } catch (e) { /* ignore */ }
    return { success: false, error: "PIN atau nomor HP salah" };
  },

  /** Retrieve current logged‑in member */
  async getSession() {
    await delay(100);
    let sess = null;
    try { sess = JSON.parse(localStorage.getItem('kk_session') || "null"); } catch (e) { sess = null; }
    if (!sess) return null;
    // locate member data
    if (sess.id === DUMMY_MEMBER.id) return { ...DUMMY_MEMBER };
    const stored = memMembers.find(m => m.id === sess.id);
    return stored ? { ...stored } : null;
  },

  /** Logout – clear session */
  async logout() {
    await delay(100);
    try { localStorage.removeItem('kk_session'); } catch (e) { /* ignore */ }
    return { success: true };
  },

  /** Get ledger (history) – unchanged */
  async getLedger({ outletId = null, type = "Semua", page = 1, pageSize = 10 } = {}) {
    await delay(300);
    let list = HISTORY;
    if (outletId) list = list.filter(h => h.outlet === outletId);
    if (type !== "Semua") list = list.filter(h => h.type === type);
    const total = list.length;
    const start = (page - 1) * pageSize;
    const pageData = list.slice(start, start + pageSize).map(e => ({ ...e }));
    return { total, page, pageSize, data: pageData };
  },

  /** Get member's vouchers – simple proxy */
  async getMyVouchers() {
    // in a real app this would be filtered; here we return all for demo
    await delay(200);
    return VOUCHERS.map(v => ({ ...v }));
  },

  /** Change PIN – prototype only */
  // hanya untuk prototipe; di produksi PIN di‑hash bcrypt di server
  async changePin(oldPin, newPin) {
    await delay(200);
    if (!/^[0-9]{6}$/.test(newPin)) {
      return { success: false, error: "PIN baru harus 6 digit angka" };
    }
    const session = await this.getSession();
    if (!session) return { success: false, error: "Tidak ada sesi login" };
    // locate member (dummy or stored)
    if (session.id === DUMMY_MEMBER.id) {
      if (DUMMY_MEMBER.pin !== oldPin) return { success: false, error: "PIN lama tidak cocok" };
      DUMMY_MEMBER.pin = newPin;
    } else {
      const idx = memMembers.findIndex(m => m.id === session.id);
      if (idx === -1) return { success: false, error: "Member tidak ditemukan" };
      if (memMembers[idx].pin !== oldPin) return { success: false, error: "PIN lama tidak cocok" };
      memMembers[idx].pin = newPin;
      saveMembers();
    }
    return { success: true };
  },
};

// Export list of function names for verification (not part of API surface)
window.MemberAPI._exported = Object.keys(window.MemberAPI);
