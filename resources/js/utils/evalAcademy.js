export const RISK_OPTIONS = [
  { value: 'low', label: 'Rendah' },
  { value: 'medium', label: 'Sedang' },
  { value: 'high', label: 'Tinggi' },
  { value: 'critical', label: 'Kritis' },
]

export const MATERI_STATUS_OPTIONS = [
  { value: 'active', label: 'Aktif' },
  { value: 'revision_needed', label: 'Perlu Revisi' },
  { value: 'expired', label: 'Kadaluarsa' },
]

const labelOf = (options, value) => options.find((o) => o.value === value)?.label ?? value

export const riskLabel = (risk) => labelOf(RISK_OPTIONS, risk)
export const materiStatusLabel = (status) => labelOf(MATERI_STATUS_OPTIONS, status)

export const riskClass = (risk) => ({
  low: 'bg-emerald-100 text-emerald-800 border-emerald-200',
  medium: 'bg-amber-100 text-amber-800 border-amber-200',
  high: 'bg-rose-100 text-rose-800 border-rose-200',
  critical: 'bg-purple-900 text-white border-purple-900',
}[risk] ?? 'bg-slate-100 text-slate-800 border-slate-200')

export const materiStatusClass = (status) => ({
  active: 'bg-emerald-50 text-emerald-700 border-emerald-200',
  revision_needed: 'bg-amber-50 text-amber-700 border-amber-200',
  expired: 'bg-rose-50 text-rose-700 border-rose-200',
}[status] ?? 'bg-slate-50 text-slate-700 border-slate-200')

const MONTHS = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec']

/** Format 'Y-m-d' → 'd M Y' (mis. "05 Jan 2026"). */
export function formatShortDate(value) {
  if (!value) return '—'
  const [year, month, day] = String(value).slice(0, 10).split('-')
  return `${day} ${MONTHS[Number(month) - 1]} ${year}`
}

export const ALLOWED_MATERI_EXTENSIONS = ['pdf', 'jpg', 'jpeg', 'png', 'docx']
export const MAX_MATERI_FILE_BYTES = 20 * 1024 * 1024

/** Validasi file sisi klien; kembalikan pesan error atau null. */
export function materiFileError(file) {
  const ext = file.name.split('.').pop().toLowerCase()
  if (!ALLOWED_MATERI_EXTENSIONS.includes(ext)) return 'Tipe file tidak didukung. Gunakan PDF, JPG, PNG, atau DOCX.'
  if (file.size > MAX_MATERI_FILE_BYTES) return 'Ukuran file melebihi 20 MB.'
  return null
}

export const materiFileUrl = (id) => `/one-academy/evaluasi-kompetensi/materi/view/${id}`
