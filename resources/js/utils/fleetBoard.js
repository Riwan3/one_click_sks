export const FLEET_SETTING_URL = '/one-setting/fleet';

export const FLEET_SHIFT_LABEL = { D: 'SHIFT 1 (SIANG)', N: 'SHIFT 2 (MALAM)' };

export const SLOTS_PER_ROW = 8;
export const MIN_HAULER_ROWS = 5;

/** Sel status parkiran: M = Monte, G = Gibson, J = Jumbo Vessel. */
export const STATUS_STYLE = {
  M: { background: '#1f5f7a', color: '#fff' },
  G: { background: '#e6a8e8', color: '#fff' },
  J: { background: '#ff0000', color: '#fff' },
};

const CLUSTER_KEYWORD_STYLES = [
  ['IPPKH', { background: '#cccc00', color: '#000' }],
  ['BARAT', { background: '#d070d0', color: '#000' }],
  ['PERU', { background: '#d070d0', color: '#000' }],
  ['OUTPIT', { background: '#00b0f0', color: '#000' }],
  ['COAL', { background: '#66e620', color: '#000' }],
  ['EWT', { background: '#ff9933', color: '#000' }],
  ['SOIL', { background: '#8c6d46', color: '#fff' }],
];
const CLUSTER_PALETTE = ['#1a3a6e', '#7a3e9d', '#0f766e', '#b45309', '#be123c'];

const DUMPING_KEYWORD_COLORS = [
  [['HIGH RISK', 'TIMUR'], '#ffff00'],
  [['TENGAH'], '#e6a0dc'],
  [['MONTE'], '#92d050'],
];
const DUMPING_DEFAULT_COLOR = '#ffd966';

const REFUEL_COLOR = '#00b050';
const REFUEL_LATE_COLOR = '#e97132';
const REFUEL_LATE_START = '08:00';

/** Warna header cluster menurut kata kunci pada nama (case-insensitive); lainnya palet bergilir stabil. */
export const clusterStyle = (name) => {
  const upper = name.toUpperCase();
  const matched = CLUSTER_KEYWORD_STYLES.find(([keyword]) => upper.includes(keyword));
  if (matched) return matched[1];
  const index = [...upper].reduce((sum, char) => sum + char.charCodeAt(0), 0) % CLUSTER_PALETTE.length;

  return { background: CLUSTER_PALETTE[index], color: '#fff' };
};

export const dumpingColor = (text) => {
  const upper = (text ?? '').toUpperCase();

  return DUMPING_KEYWORD_COLORS.find(([keywords]) => keywords.some((keyword) => upper.includes(keyword)))?.[1] ?? DUMPING_DEFAULT_COLOR;
};

const pad = (value) => String(value).padStart(2, '0');

/** '07:00' -> { label: '07:00-08:00 PM/AM', background } (jam selesai +1 jam); teks bebas dipakai apa adanya. */
export const refuelInfo = (text) => {
  const raw = (text ?? '').trim();
  if (!raw) return { label: '', background: REFUEL_COLOR };
  const match = raw.match(/^(\d{1,2}):(\d{2})$/);
  if (!match) return { label: raw, background: raw.startsWith(REFUEL_LATE_START) ? REFUEL_LATE_COLOR : REFUEL_COLOR };

  const start = `${pad(match[1])}:${match[2]}`;

  return {
    label: `${start}-${pad((Number(match[1]) + 1) % 24)}:${match[2]} PM/AM`,
    background: start === REFUEL_LATE_START ? REFUEL_LATE_COLOR : REFUEL_COLOR,
  };
};

/**
 * Kemas cluster ke baris 8 slot (1 slot = 1 kolom loader). Cluster memakai slot sebanyak loadernya (min 1),
 * pindah baris bila sisa slot tidak cukup, cluster > 8 loader dipecah per 8 (header diulang), sisa slot jadi sel kosong.
 * Hasil: [{ cells: [{ name, slots, blank }], slots: [loader|null x 8] }].
 */
export const packClusters = (clusters) => {
  const pieces = clusters.flatMap((cluster) => {
    const parts = Math.max(1, Math.ceil(cluster.loaders.length / SLOTS_PER_ROW));

    return Array.from({ length: parts }, (_, part) => {
      const loaders = cluster.loaders.slice(part * SLOTS_PER_ROW, (part + 1) * SLOTS_PER_ROW);

      return { name: cluster.name, loaders, slots: Math.max(1, loaders.length), blank: false };
    });
  });

  const rows = [];
  let current = [];
  const flush = () => {
    if (!current.length) return;
    const used = current.reduce((sum, piece) => sum + piece.slots, 0);
    const cells = used < SLOTS_PER_ROW ? [...current, { name: '', loaders: [], slots: SLOTS_PER_ROW - used, blank: true }] : current;
    rows.push({
      cells,
      slots: cells.flatMap((cell) => Array.from({ length: cell.slots }, (_, index) => cell.loaders[index] ?? null)),
    });
    current = [];
  };

  pieces.forEach((piece) => {
    const used = current.reduce((sum, item) => sum + item.slots, 0);
    if (used + piece.slots > SLOTS_PER_ROW) flush();
    current.push(piece);
  });
  flush();

  return rows;
};

/** Jumlah baris hauler pada satu baris-blok: maks hauler loader, minimal 5. */
export const haulerRowCount = (slots) => Math.max(MIN_HAULER_ROWS, ...slots.map((loader) => loader?.units.length ?? 0));

/** '2026-10-05' -> '5 OKT 2026'. */
export const formatSheetDate = (ymd) => {
  const [year, month, day] = ymd.split('-').map(Number);
  const names = ['JAN', 'FEB', 'MAR', 'APR', 'MEI', 'JUN', 'JUL', 'AGU', 'SEP', 'OKT', 'NOV', 'DES'];

  return `${day} ${names[month - 1]} ${year}`;
};
