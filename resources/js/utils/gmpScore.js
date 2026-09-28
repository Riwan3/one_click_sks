/** Format skor GMP seperti v1: maks 2 desimal, nol di belakang dibuang, tanpa tanda % (mis. 93.53, 90.5, 100). */
export function formatGmpScore(value) {
  const rounded = Math.round((Number(value) || 0) * 100) / 100
  return String(rounded)
}

/** Warna skor: ≥80 hijau, ≥60 kuning, sisanya merah. */
export function gmpScoreColor(value) {
  if (value >= 80) return '#16a34a'
  return value >= 60 ? '#f59e0b' : '#dc2626'
}
