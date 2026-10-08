export const FLEET_SETTING_URL = '/one-setting/fleet';

export const FLEET_SHIFT_NUMBER = { D: 1, N: 2 };
export const FLEET_SHIFT_LABEL = { D: 'SHIFT 1 (SIANG)', N: 'SHIFT 2 (MALAM)' };

const DEFAULT_SECTION = { bg: '#455a64', fg: '#fff' };
const SECTION_COLORS = [
  ['EWT', { bg: '#00b050', fg: '#000' }],
  ['IPPKH', { bg: '#00b050', fg: '#000' }],
  ['BARAT', { bg: '#e65100', fg: '#fff' }],
  ['TIMUR', { bg: '#7030a0', fg: '#fff' }],
  ['TENGAH', { bg: '#00695c', fg: '#fff' }],
  ['SOIL', { bg: '#f9a825', fg: '#000' }],
  ['COAL', { bg: '#2e7d32', fg: '#fff' }],
];

const ALL_AREA_COLORS = [
  ['MONTE', '#00bcd4'],
  ['GIBSON', '#e74c3c'],
  ['OPERATOR', '#9c27b0'],
];

/** Warna judul grup (kata kunci pada nama grup, seperti Fleet V1). */
export const sectionColor = (groupName) => {
  const name = groupName.toUpperCase();

  return SECTION_COLORS.find(([keyword]) => name.includes(keyword))?.[1] ?? DEFAULT_SECTION;
};

export const allAreaColor = (loaderCode) => {
  const code = loaderCode.toUpperCase();

  return ALL_AREA_COLORS.find(([keyword]) => code.includes(keyword))?.[1] ?? '#9e9e9e';
};

export const materialLabel = (groupName) => (groupName.toUpperCase().includes('COAL') ? 'COAL' : 'OVERBURDEN');

/** Bulatan huruf status unit (M/G/J/R/N). */
export const UNIT_STATUS_CLASS = {
  G: 'bg-[#e74c3c] text-white',
  J: 'bg-[#c0392b] text-black',
  M: 'bg-[#2980b9] text-white',
  R: 'bg-[#922b21] text-white',
  N: 'bg-[#85c1e9] text-[#1a3a6e]',
};

export const maxUnits = (loaders) => Math.max(1, ...loaders.map((loader) => loader.units.length));

/** '2026-10-05' -> '5 OKT 2026'. */
export const formatSheetDate = (ymd) => {
  const [year, month, day] = ymd.split('-').map(Number);
  const names = ['JAN', 'FEB', 'MAR', 'APR', 'MEI', 'JUN', 'JUL', 'AGU', 'SEP', 'OKT', 'NOV', 'DES'];

  return `${day} ${names[month - 1]} ${year}`;
};
