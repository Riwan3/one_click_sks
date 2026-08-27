export function formatDate(date) {
  if (!date) return '-'
  const d = new Date(date)
  return d.toLocaleDateString('id-ID', {
    year: 'numeric',
    month: '2-digit',
    day: '2-digit',
  })
}

export function formatCurrency(value) {
  if (!value) return 'Rp0'
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
  }).format(value)
}

export function formatNumber(value, decimals = 0) {
  if (!value) return '0'
  return new Intl.NumberFormat('id-ID', {
    minimumFractionDigits: decimals,
    maximumFractionDigits: decimals,
  }).format(value)
}

export function formatPercent(value, decimals = 2) {
  if (value === null || value === undefined) return '0%'
  return `${formatNumber(value, decimals)}%`
}
