export const MONITORING_STATUS_OPTIONS = [
  { value: 'PASS', label: 'Lulus' },
  { value: 'FAILED', label: 'Tidak Lulus' },
  { value: 'IN_PROGRESS', label: 'Sedang Dikerjakan' },
  { value: 'PENDING', label: 'Pending' },
]

export const monitoringStatusClass = (status) => ({
  PASS: 'bg-emerald-100 text-emerald-700',
  FAILED: 'bg-rose-100 text-rose-700',
  IN_PROGRESS: 'bg-amber-100 text-amber-800',
}[status] ?? 'bg-slate-100 text-slate-500')

const COMPETENCY = {
  competent: { label: 'Competent', badge: 'bg-emerald-100 text-emerald-700', stroke: '#15803d' },
  improve: { label: 'Need Improvement', badge: 'bg-amber-100 text-amber-800', stroke: '#854d0e' },
  notCompetent: { label: 'Not Competent', badge: 'bg-rose-100 text-rose-700', stroke: '#b91c1c' },
  none: { label: '—', badge: 'bg-slate-50 text-slate-400', stroke: '#94a3b8' },
}

/** Kompetensi relatif terhadap passing grade: lulus / kurang ≤10 poin / di bawahnya. */
export function competencyOf(score, passingGrade = 75) {
  if (score === null || score === undefined) return COMPETENCY.none
  if (score >= passingGrade) return COMPETENCY.competent
  if (score >= passingGrade - 10) return COMPETENCY.improve
  return COMPETENCY.notCompetent
}

export const formatScore = (score) => (score === null || score === undefined ? '—' : Number(score).toFixed(1))
