const DAY_NAMES = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu']
const DAY_SHORT = ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab']
const MONTH_NAMES = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember']
const MONTH_SHORT = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des']

/** 'YYYY-MM-DD' -> Date lokal (tanpa pergeseran zona waktu). */
const parseYmd = (ymd) => {
  const [y, m, d] = ymd.split('-').map(Number)
  return new Date(y, m - 1, d)
}

export const formatYmd = (date) => {
  const pad = (n) => String(n).padStart(2, '0')
  return `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())}`
}

export const shiftYmd = (ymd, days) => {
  const date = parseYmd(ymd)
  date.setDate(date.getDate() + days)
  return formatYmd(date)
}

/** Contoh: "Kamis, 8 Oktober 2026". */
export const formatLongDate = (ymd) => {
  const date = parseYmd(ymd)
  return `${DAY_NAMES[date.getDay()]}, ${date.getDate()} ${MONTH_NAMES[date.getMonth()]} ${date.getFullYear()}`
}

/** Label sumbu grafik: [hari singkat, "8 Okt"]. */
export const chartDateLabel = (ymd) => {
  const date = parseYmd(ymd)
  return [DAY_SHORT[date.getDay()], `${date.getDate()} ${MONTH_SHORT[date.getMonth()]}`]
}

export const dayShort = (ymd) => DAY_SHORT[parseYmd(ymd).getDay()]
export const dayOfMonth = (ymd) => parseYmd(ymd).getDate()
export const isWeekend = (ymd) => [0, 6].includes(parseYmd(ymd).getDay())

export { DAY_SHORT }

/** Kelas warna sel kode shift pada matriks monitoring (sama dengan palet V1). */
export const SHIFT_CELL_CLASS = {
  D: 'bg-white text-black font-bold',
  N: 'bg-[#0070c0] text-white font-bold',
  OFF: 'bg-[#ff0000] text-white font-bold',
  CT: 'bg-[#ffc000] text-black font-bold',
  ID: 'bg-[#7030a0] text-white font-bold',
  IDS: 'bg-[#ff00ff] text-black font-bold',
  MCU: 'bg-[#00b050] text-white font-bold',
  FU: 'bg-[#006400] text-white font-bold',
  H: 'bg-[#ff0000] text-white font-bold',
  TR: 'bg-[#00b0f0] text-black font-bold',
}
