/** Label & warna status compliance, sama seperti Daily Compliance v1. */
export const COMPLIANCE_STATUS = {
  selesai: { label: 'Complete', badge: 'bg-green-100 text-green-800', dot: 'bg-emerald-500' },
  progress: { label: 'Progress', badge: 'bg-yellow-100 text-yellow-800', dot: 'bg-amber-500' },
  belum: { label: 'Belum Dimulai', badge: 'bg-red-100 text-red-800', dot: 'bg-red-500' },
}

export const complianceStatusOf = (status) => COMPLIANCE_STATUS[status] ?? COMPLIANCE_STATUS.belum

/** Label status di halaman Assignment Harian v1. */
export const ASSIGNMENT_STATUS_LABEL = { selesai: 'Selesai', progress: 'Progress', belum: 'Belum' }

/** Warna persen per baris: 100% hijau, >0 kuning, 0 merah. */
export const rowPercentColor = (pct) => {
  if (pct === 100) return '#10b981'
  return pct > 0 ? '#f59e0b' : '#ef4444'
}

/** Warna rata-rata: ≥80 hijau, ≥50 kuning, sisanya merah. */
export const averagePercentColor = (pct) => {
  if (pct >= 80) return '#10b981'
  return pct >= 50 ? '#f59e0b' : '#ef4444'
}

/** "2026-09-19" → "19 September 2026" (format tanggal v1). */
export const formatLongDate = (value) =>
  new Date(`${value}T00:00:00`).toLocaleDateString('en-GB', { day: '2-digit', month: 'long', year: 'numeric' })

/** "2026-09-19" → "19/09/2026". */
export const formatSlashDate = (value) => value.split('-').reverse().join('/')
