export const FINDING_BASE_URL = '/one-gmp/temuan'

export const FINDING_ASPECTS = ['TEKNIS', 'KESELAMATAN', 'LINGKUNGAN', 'OPTIMALISASI']

export const findingStatusClass = (status) => ({
  draft: 'bg-slate-100 text-slate-600',
  open: 'bg-indigo-100 text-indigo-700',
  on_progress: 'bg-amber-100 text-amber-700',
  waiting_verification: 'bg-blue-100 text-blue-700',
  rework: 'bg-orange-100 text-orange-700',
  closed: 'bg-green-100 text-green-700',
  overdue: 'bg-red-100 text-red-700',
}[status] ?? 'bg-slate-100 text-slate-600')

export const priorityClass = (priority) => ({
  Critical: 'bg-red-600 text-white',
  High: 'bg-orange-100 text-orange-700',
  Medium: 'bg-yellow-100 text-yellow-800',
  Low: 'bg-slate-100 text-slate-600',
}[priority] ?? 'bg-slate-100 text-slate-500')

/** "2026-09-25" → "25 Sep 2026". */
export const formatFindingDate = (value) => {
  if (!value) return '-'
  const d = new Date(value)
  return Number.isNaN(d.getTime()) ? '-' : d.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' })
}

/** Tanggal hari ini + n hari (format input date). */
export const addDays = (days) => {
  const d = new Date()
  d.setDate(d.getDate() + days)
  return d.toISOString().slice(0, 10)
}
