export const CERTIFICATE_STATUS_OPTIONS = [
  { value: 'active', label: 'Active' },
  { value: 'expiring_soon', label: 'Expiring Soon' },
  { value: 'expired', label: 'Expired' },
  { value: 'revoked', label: 'Revoked' },
]

export const certificateStatusClass = (status) => ({
  active: 'bg-green-100 text-green-700',
  expiring_soon: 'bg-yellow-100 text-yellow-800',
  expired: 'bg-red-100 text-red-700',
}[status] ?? 'bg-gray-100 text-gray-500')

/** Warna skor sertifikat seperti v1: ≥80 hijau, di bawahnya kuning tua. */
export const certificateScoreClass = (score) => (Number(score) >= 80 ? 'text-green-700' : 'text-yellow-800')

export const qrImageUrl = (data, size = 100) =>
  `https://api.qrserver.com/v1/create-qr-code/?data=${encodeURIComponent(data)}&size=${size}x${size}&margin=4`
