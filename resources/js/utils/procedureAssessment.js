export const ASSESSMENT_BASE_URL = '/one-procedure/evaluasi/assessment'

export const assessmentStatusLabel = (status) => ({ draft: 'Draft', published: 'Published', archived: 'Arsip' }[status] ?? status)

export const assessmentStatusClass = (status) => ({
  draft: 'bg-gray-100 text-gray-700',
  published: 'bg-green-100 text-green-700',
  archived: 'bg-red-100 text-red-700',
}[status] ?? 'bg-gray-100 text-gray-700')

export const RETRY_OPTIONS = [
  { value: 1, label: '1x' },
  { value: 2, label: '2x' },
  { value: 3, label: '3x' },
  { value: 5, label: '5x' },
  { value: 99, label: 'Unlimited' },
]

export const VALIDITY_OPTIONS = [
  { value: 0, label: '♾️ Selamanya' },
  { value: 3, label: '3 Bulan' },
  { value: 6, label: '6 Bulan' },
  { value: 12, label: '1 Tahun' },
  { value: 24, label: '2 Tahun' },
  { value: 36, label: '3 Tahun' },
]

export const validityLabel = (months) => VALIDITY_OPTIONS.find((o) => o.value === months)?.label ?? `${months} Bulan`

export const QUESTION_TYPES = [
  { value: 'multiple_choice', label: 'Pilihan Ganda', badge: 'bg-blue-100 text-blue-700', questionLabel: 'Pertanyaan' },
  { value: 'true_false', label: 'Benar / Salah', badge: 'bg-green-100 text-green-700', questionLabel: 'Pernyataan' },
  { value: 'case_based', label: 'Studi Kasus', badge: 'bg-yellow-100 text-yellow-800', questionLabel: 'Skenario / Kasus' },
]

export const questionTypeOf = (type) => QUESTION_TYPES.find((t) => t.value === type) ?? QUESTION_TYPES[0]

export const ANSWER_LETTERS = ['A', 'B', 'C', 'D']
