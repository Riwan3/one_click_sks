export const BASE_URL = '/one-setting/manpower';

const MONTH_NAMES = ['JANUARI', 'FEBRUARI', 'MARET', 'APRIL', 'MEI', 'JUNI', 'JULI', 'AGUSTUS', 'SEPTEMBER', 'OKTOBER', 'NOVEMBER', 'DESEMBER'];
const DAY_NAMES = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];

export const SHIFT_LABEL = { D: 'SHIFT 1 (DAY)', N: 'SHIFT 2 (NIGHT)' };
export const SHIFT_OPTION_LABEL = { D: 'Shift 1 - Siang (D)', N: 'Shift 2 - Malam (N)' };
export const SHIFT_SHORT_LABEL = { D: 'Shift 1 (D)', N: 'Shift 2 (N)' };

/** '2026-10-05' -> '5 OKTOBER 2026'. */
export const formatDateLabel = (ymd) => {
  const [year, month, day] = ymd.split('-').map(Number);

  return `${day} ${MONTH_NAMES[month - 1]} ${year}`;
};

/** '2026-10-05' -> 'Senin, 05/10/2026'. */
export const formatDayDate = (ymd) => {
  const [year, month, day] = ymd.split('-').map(Number);
  const dayName = DAY_NAMES[new Date(year, month - 1, day).getDay()];

  return `${dayName}, ${String(day).padStart(2, '0')}/${String(month).padStart(2, '0')}/${year}`;
};

/** '2026-10-05 13:45:00' -> '05/10 13:45'. */
export const formatShortDateTime = (value) => {
  if (!value) return '-';
  const [date, time = ''] = value.split(/[ T]/);
  const [, month, day] = date.split('-');

  return `${day}/${month} ${time.slice(0, 5)}`;
};

/** '2026-10-05 13:45:00' -> '05/10/2026 13:45'. */
export const formatFullDateTime = (value) => {
  if (!value) return '';
  const [date, time = ''] = value.split(/[ T]/);
  const [year, month, day] = date.split('-');

  return `${day}/${month}/${year} ${time.slice(0, 5)}`;
};

/** Kategori dibagi per kolom layout (1 = kiri, 2 = kanan), mengikuti Master CSA. */
export const splitColumns = (categories) => ({
  1: categories.filter((c) => c.col_pos === 1),
  2: categories.filter((c) => c.col_pos !== 1),
});

/**
 * Monitoring MP: CSA yang semua kategorinya satu kolom dibagi dua (kiri diisi berurutan sampai barisnya >= separuh total),
 * seperti halaman lama. Layout Preview tidak ikut berubah.
 */
export const balanceColumns = (categories) => {
  const columns = splitColumns(categories);
  const isSingleColumn = !(columns[1].length && columns[2].length);
  if (!isSingleColumn || categories.length < 2) return columns;

  const rowsTotal = categories.reduce((sum, c) => sum + c.units.length, 0);
  const left = [];
  const right = [];
  let acc = 0;
  categories.forEach((cat) => {
    if (left.length && acc * 2 >= rowsTotal) return right.push(cat);
    left.push(cat);
    acc += cat.units.length;
  });

  return { 1: left, 2: right };
};

export const countUnits = (csa) => csa.categories.reduce((sum, cat) => sum + cat.units.length, 0);

export const countFilled = (csa) => csa.categories.reduce((sum, cat) => sum + cat.units.filter((u) => u.assignment).length, 0);
