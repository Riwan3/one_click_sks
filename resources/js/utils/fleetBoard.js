export const FLEET_SETTING_URL = '/one-setting/fleet';

export const FLEET_SHIFT_LABEL = { D: 'SHIFT 1 (SIANG)', N: 'SHIFT 2 (MALAM)' };

const LOADERS_PER_ROW = 5;

/** Loader dibagi per baris (maks 5 kolom); lebih dari itu membungkus ke baris berikutnya. */
export const chunkLoaders = (loaders) => Array.from(
  { length: Math.ceil(loaders.length / LOADERS_PER_ROW) },
  (_, index) => loaders.slice(index * LOADERS_PER_ROW, (index + 1) * LOADERS_PER_ROW),
);

/** Baris meta kecil di bawah kode loader (hanya yang terisi). */
export const loaderMeta = (loader) => [
  loader.dumping,
  loader.front && `Front ${loader.front}`,
  loader.refueling && `Refuel ${loader.refueling}`,
  loader.distance > 0 && `${loader.distance} m`,
].filter(Boolean);

/** '2026-10-05' -> '5 OKT 2026'. */
export const formatSheetDate = (ymd) => {
  const [year, month, day] = ymd.split('-').map(Number);
  const names = ['JAN', 'FEB', 'MAR', 'APR', 'MEI', 'JUN', 'JUL', 'AGU', 'SEP', 'OKT', 'NOV', 'DES'];

  return `${day} ${names[month - 1]} ${year}`;
};
