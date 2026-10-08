export const FLEET_URL = '/one-setting/fleet';

export const STATUS_OPTIONS = [
  { code: 'M', label: 'M' },
  { code: 'G', label: 'G' },
  { code: 'J', label: 'J' },
];

export const STATUS_CLASS = {
  M: 'bg-blue-600 text-white',
  G: 'bg-rose-600 text-white',
  J: 'bg-amber-500 text-white',
};

export const UNIT_TYPE_LABEL = { loader: 'Loader', hauler: 'Hauler' };

const pad = (n) => String(n).padStart(2, '0');

const toYmd = (date) => `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())}`;

/** Shift sebelumnya: D -> shift N kemarin, N -> shift D di hari yang sama. */
export const previousShift = (ymd, shift) => {
  if (shift === 'N') return { date: ymd, shift: 'D' };
  const [year, month, day] = ymd.split('-').map(Number);

  return { date: toYmd(new Date(year, month - 1, day - 1)), shift: 'N' };
};

/** Hari yang sama kemarin (shift sama). */
export const yesterday = (ymd, shift) => ({ date: previousShift(ymd, 'D').date, shift });
