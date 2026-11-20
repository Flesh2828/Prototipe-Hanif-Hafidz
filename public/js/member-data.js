// public/js/member-data.js – dummy data & async API

/* Utility: simple delay */
function delay(ms) {
  return new Promise(resolve => setTimeout(resolve, ms));
}

/* Design tokens (mirrored from member.css) – not required here but kept for reference */
const DESIGN = {
  primary: "#623f18",
  success: "#047857",
  error: "#ba1a1a",
};

/* Dummy member */
const DUMMY_MEMBER = {
  id: "KP-82049",
  name: "Raka Pratama",
  phone: "0812-3456-7890",
  pin: "123456",
  tier: "Regular",
  pointsActive: 87,
  pointsCumulative: 87, // this year
  pointsExpiringSoon: 3,
  pointsExpiringDate: "2026-12-20",
};

/* Outlets */
const OUTLETS = [
  { id: "O1", name: "KopiKita Kaliurang (Pusat)" },
  { id: "O2", name: "KopiKita Gejayan" },
  { id: "O3", name: "KopiKita Malioboro" },
];

/* Vouchers */
const VOUCHERS = [
  {
    id: "VV-001",
    code: "KK-8921A",
    title: "Potongan Rp10.000",
    cost: 25,
    status: "ACTIVE",
    description: "Gunakan untuk potongan pembelian minimal Rp50.000",
    lockedFor: null, // regular can redeem
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

/* History entries – 12 sample mutations */
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

/* Exported async API */
window.MemberAPI = {
  /** Get member data (simulated) */
  async getMember() {
    await delay(250);
    // return a shallow copy to avoid accidental mutation
    return { ...DUMMY_MEMBER };
  },

  /** Get list of outlets */
  async getOutlets() {
    await delay(200);
    return OUTLETS.map(o => ({ ...o }));
  },

  /** Get voucher catalog (all) */
  async getVouchers() {
    await delay(250);
    return VOUCHERS.map(v => ({ ...v }));
  },

  /** Claim a voucher by id – updates points if possible */
  async claimVoucher(voucherId) {
    await delay(300);
    const voucher = VOUCHERS.find(v => v.id === voucherId);
    if (!voucher) {
      return { success: false, error: "Voucher tidak ditemukan" };
    }
    if (voucher.status !== "ACTIVE") {
      return { success: false, error: "Voucher tidak dapat ditukarkan" };
    }
    if (voucher.lockedFor && voucher.lockedFor !== DUMMY_MEMBER.tier) {
      return { success: false, error: `Voucher hanya untuk tier ${voucher.lockedFor}` };
    }
    if (DUMMY_MEMBER.pointsActive < voucher.cost) {
      return { success: false, error: "Poin tidak cukup" };
    }
    // Deduct points & mark redeemed
    DUMMY_MEMBER.pointsActive -= voucher.cost;
    voucher.status = "REDEEMED";
    // Add to history as Penukaran voucher
    HISTORY.unshift({
      id: HISTORY.length + 1,
      type: "Penukaran voucher",
      points: -voucher.cost,
      outlet: "O1",
      date: new Date().toISOString().slice(0, 10),
    });
    return { success: true, member: { ...DUMMY_MEMBER } };
  },

  /** Get full ledger (history) */
  async getLedger({ outletId = null, type = "Semua", page = 1, pageSize = 10 } = {}) {
    await delay(300);
    let list = HISTORY;
    if (outletId) {
      list = list.filter(h => h.outlet === outletId);
    }
    if (type !== "Semua") {
      list = list.filter(h => h.type === type);
    }
    const total = list.length;
    const start = (page - 1) * pageSize;
    const pageData = list.slice(start, start + pageSize).map(e => ({ ...e }));
    return { total, page, pageSize, data: pageData };
  },

  /** Simulated login – checks phone & PIN, handles lock for test account */
  async login(phone, pin) {
    await delay(250);
    const LOCKED_ACCOUNT = "0899-0000-0000";
    const lockKey = `loginLock_${phone}`;
    const now = Date.now();
    const stored = JSON.parse(localStorage.getItem(lockKey) || "{}");
    if (stored.lockUntil && now < stored.lockUntil) {
      return { success: false, error: "Akun terkunci, coba lagi nanti" };
    }
    // Test locked account – always fail and increase counter
    if (phone === LOCKED_ACCOUNT) {
      stored.attempts = (stored.attempts || 0) + 1;
      if (stored.attempts >= 5) {
        stored.lockUntil = now + 15 * 60 * 1000; // 15 menit
        stored.attempts = 0;
      }
      localStorage.setItem(lockKey, JSON.stringify(stored));
      return { success: false, error: "PIN salah" };
    }
    // Normal dummy member
    if (phone === DUMMY_MEMBER.phone && pin === DUMMY_MEMBER.pin) {
      // reset any lock info
      localStorage.removeItem(lockKey);
      return { success: true, member: { ...DUMMY_MEMBER } };
    }
    // Wrong credential
    stored.attempts = (stored.attempts || 0) + 1;
    if (stored.attempts >= 5) {
      stored.lockUntil = now + 15 * 60 * 1000;
      stored.attempts = 0;
    }
    localStorage.setItem(lockKey, JSON.stringify(stored));
    return { success: false, error: "PIN atau nomor HP salah" };
  },

  /** Simulated registration – returns created member (no persistence) */
  async register({ name, phone, pin }) {
    await delay(300);
    // In dummy world we just echo back
    const newMember = {
      id: "KP-" + Math.floor(Math.random() * 90000 + 10000),
      name,
      phone,
      pin,
      tier: "Regular",
      pointsActive: 0,
      pointsCumulative: 0,
      pointsExpiringSoon: 0,
    };
    return { success: true, member: newMember };
  },
};

/* End of member-data.js */
