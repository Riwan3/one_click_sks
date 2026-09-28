export const INCIDENT_BASE_URL = '/one-reporting/perbaikan-pasca-insiden'

export const INCIDENT_STATUS_OPTIONS = [
  { value: 'open', label: 'Open' },
  { value: 'progress', label: 'Progress' },
  { value: 'selesai', label: 'Selesai' },
]

export const incidentStatusClass = (status) => ({
  open: 'bg-red-100 text-red-800',
  progress: 'bg-yellow-100 text-yellow-800',
  selesai: 'bg-green-100 text-green-800',
}[status] ?? 'bg-slate-100 text-slate-600')
