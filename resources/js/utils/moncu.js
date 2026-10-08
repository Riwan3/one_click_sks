const MONTHS_EN = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
const MONTHS_ID = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agt', 'Sep', 'Okt', 'Nov', 'Des'];
const MONTHS_ID_LONG = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

export const PERIOD_MONTH_LABELS = ['JAN', 'FEB', 'MAR', 'APR', 'MEI', 'JUN', 'JUL', 'AGT', 'SEP', 'OKT', 'NOV', 'DES'];
export const WEEKDAYS_ID = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
export const MONTH_OPTIONS = MONTHS_ID_LONG.map((label, i) => ({ value: i + 1, label }));

const splitYmd = (ymd) => String(ymd).split('-');

/** 'YYYY-MM-DD' -> 'dd-Mon-YYYY' (format tabel V1). Kosong -> ''. */
export const formatDate = (ymd) => {
  if (!ymd) return '';
  const [y, m, d] = splitYmd(ymd);
  return `${d}-${MONTHS_EN[Number(m) - 1]}-${y}`;
};

/** Sama, dengan singkatan bulan Indonesia (format modal detail V1). */
export const formatDateId = (ymd) => {
  if (!ymd) return '';
  const [y, m, d] = splitYmd(ymd);
  return `${d}-${MONTHS_ID[Number(m) - 1]}-${y}`;
};

/** 'YYYY-MM-DD' -> '8 Oktober 2026'. */
export const formatLongDate = (ymd) => {
  const [y, m, d] = splitYmd(ymd);
  return `${Number(d)} ${MONTHS_ID_LONG[Number(m) - 1]} ${y}`;
};

export const yearOptions = (from, to) => Array.from({ length: from - to + 1 }, (_, i) => from - i);

export const STATUS_BADGE_CLASS = {
  CUTI: 'bg-blue-100 text-blue-800',
  INDUKSI: 'bg-amber-100 text-amber-800',
  'AKAN CUTI': 'bg-sky-100 text-sky-800',
  'BELUM ADA AWAL': 'bg-slate-200 text-slate-700',
  SELESAI: 'bg-emerald-100 text-emerald-800',
};

const toYmd = (date) => date.toISOString().slice(0, 10);

/** Semua tanggal 'YYYY-MM-DD' dari start s/d end (inklusif). */
export const dateRange = (start, end) => {
  const dates = [];
  for (let d = new Date(`${start}T00:00:00Z`); toYmd(d) <= end; d.setUTCDate(d.getUTCDate() + 1)) {
    dates.push(toYmd(d));
  }
  return dates;
};

/** Geser bulan: return { year, month } untuk bulan ke-(offset) dari year/month. */
export const shiftMonth = (year, month, offset) => {
  const d = new Date(Date.UTC(year, month - 1 + offset, 1));
  return { year: d.getUTCFullYear(), month: d.getUTCMonth() + 1 };
};
