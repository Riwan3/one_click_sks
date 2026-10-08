const MONTH_NAMES = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember']
const MONTH_ABBR = ['JAN', 'FEB', 'MAR', 'APR', 'MEI', 'JUN', 'JUL', 'AGU', 'SEP', 'OKT', 'NOV', 'DES']
const MONTH_SHORT = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des']
const DAY_NAMES = { 1: 'Senin', 2: 'Selasa', 3: 'Rabu', 4: 'Kamis', 5: 'Jumat', 6: 'Sabtu', 7: 'Minggu' }
const DAY_ABBR = { 1: 'SEN', 2: 'SEL', 3: 'RAB', 4: 'KAM', 5: 'JUM', 6: 'SAB', 7: 'MIN' }

/** Palet sel kode shift pada matriks roster (sama dengan V1). Kode tak dikenal = tanpa warna. */
const SHIFT_CLASS = {
  D: 'bg-white text-slate-800',
  N: 'bg-[#0070C0] text-white',
  OFF: 'bg-[#FF0000] text-white',
  CT: 'bg-[#FFC000] text-slate-800',
  ID: 'bg-[#7030A0] text-white',
  H: 'bg-[#92D050] text-slate-800',
  TR: 'bg-[#00B0F0] text-slate-800',
  MCU: 'bg-[#A9D08E] text-slate-800',
  FU: 'bg-[#006400] text-white',
  IDS: 'bg-[#FF00FF] text-slate-800',
}

export const shiftClass = (code) => SHIFT_CLASS[code] ?? ''

/** Merah di bawah target, kuning pas, hijau di atas; tanpa target = tanpa warna. */
export const targetClass = (count, target) => {
  if (target === null || target === undefined) return ''
  if (count < target) return 'bg-[#F8B4B4] font-bold text-[#7A1010]'
  if (count === target) return 'bg-[#FFE58A] font-bold text-[#6B5200]'
  return 'bg-[#A8E0B8] font-bold text-[#0F5A2A]'
}

/** '2026-10' -> 'Oktober 2026'. */
export const monthLabel = (ym) => `${MONTH_NAMES[Number(ym.slice(5, 7)) - 1]} ${ym.slice(0, 4)}`

export const monthAbbr = (monthNumber) => MONTH_ABBR[monthNumber - 1]

/** '2026-10-08' -> '8 Okt 2026'. */
export const shortDate = (ymd) => `${Number(ymd.slice(8, 10))} ${MONTH_SHORT[Number(ymd.slice(5, 7)) - 1]} ${ymd.slice(0, 4)}`

/** '2026-10-08' -> '08/10/2026'. */
export const dmy = (ymd) => `${ymd.slice(8, 10)}/${ymd.slice(5, 7)}/${ymd.slice(0, 4)}`

/** Nomor hari ISO (1 = Senin ... 7 = Minggu) dari 'YYYY-MM-DD'. */
export const isoWeekday = (ymd) => new Date(`${ymd}T00:00:00`).getDay() || 7

export const dayName = (isoDay) => DAY_NAMES[isoDay]
export const dayAbbr = (isoDay) => DAY_ABBR[isoDay]

/** Kode Off yang valid: A1..A14 dan B1..B14 (sama dengan RosterV2Rotation::codes()). */
export const KODE_OFF_OPTIONS = ['A', 'B'].flatMap((group) => Array.from({ length: 14 }, (_, i) => `${group}${i + 1}`))

export const WORK_PATTERNS = [
  ['ROTASI', 'Siang - Malam (rotasi seperti Excel)'],
  ['FULL_SIANG', 'Full Siang (tanpa malam)'],
]

const monthStartClass = (day, index) => (index > 0 && day.d === 1 ? 'border-l-[3px] border-l-slate-800' : '')

/** Kelas header kolom tanggal; hari konteks (bulan sebelum/sesudah) dipudarkan, akhir pekan kemerahan. */
export const dayHeadClass = (day, index) => {
  const isWeekend = [0, 6].includes(day.dow)
  const tone = {
    'false|false': 'bg-[#CFE8F3] text-slate-800',
    'true|false': 'bg-[#F4B6B6] text-slate-800',
    'false|true': 'bg-[#E3E8EE] text-slate-500',
    'true|true': 'bg-[#EBD3D3] text-slate-500',
  }[`${isWeekend}|${day.ctx !== ''}`]
  return [tone, monthStartClass(day, index)]
}

export const dayCellClass = (day, index) => [day.ctx !== '' ? 'opacity-70' : '', monthStartClass(day, index)]
