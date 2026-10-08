const DAY_NAMES = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu']

/** Kode shift -> keterangan & kelas warna lembar roster pribadi (sama dengan V1). */
const SHIFT_INFO = {
  d: { note: 'Day Shift', tone: 'text-green-700' },
  n: { note: 'Night Shift', tone: 'text-blue-600' },
  off: { note: 'Off', tone: 'text-red-600' },
  ct: { note: 'Cuti', tone: 'text-purple-700' },
  h: { note: 'Dirumahkan', tone: 'text-orange-500' },
  id: { note: 'Induksi', tone: 'text-orange-500' },
  ids: { note: 'Induksi Permit', tone: 'text-orange-500' },
  tr: { note: 'Training', tone: '' },
  fu: { note: 'Followup', tone: 'text-orange-500' },
  mcu: { note: 'MCU', tone: 'text-orange-500' },
}

/** Satu baris per hari dalam bulan; cells = { [hari]: kode }. */
export const buildShareDays = (year, month, monthName, cells) => {
  const daysInMonth = new Date(year, month, 0).getDate()

  return Array.from({ length: daysInMonth }, (_, index) => {
    const day = index + 1
    const dow = new Date(year, month - 1, day).getDay()
    const kode = cells[day] ?? ''
    const info = SHIFT_INFO[kode.toLowerCase()] ?? { note: '', tone: '' }

    return {
      day,
      dayName: DAY_NAMES[dow],
      dateLabel: `${String(day).padStart(2, '0')} ${monthName} ${year}`,
      isSunday: dow === 0,
      kode,
      note: info.note,
      tone: info.tone,
    }
  })
}
