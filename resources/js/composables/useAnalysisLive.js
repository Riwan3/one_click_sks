import { onBeforeUnmount, onMounted, ref, watch } from 'vue'
import { router } from '@inertiajs/vue3'
import { chartIds } from '@/utils/rosterAnalysis'

const DATA_URL = '/one-roster/analisis-harian/data'

const twoDigits = (n) => String(n).padStart(2, '0')

/**
 * Penyegaran otomatis Analisis Harian: bila roster diubah (sel, target, section, publish), grafik & tabel ikut berubah tanpa reload.
 * Struktur berubah (roster/section/tanggal) -> muat ulang props halaman; selain itu data diganti langsung.
 */
export function useAnalysisLive(getAnalysis, getFilters, getIntervalSeconds) {
  const analysis = ref(getAnalysis())
  const statusText = ref('otomatis diperbarui')
  const isFailed = ref(false)
  let lastSignature = null
  let isBusy = false
  let timer = null

  const signatureOf = (data) => JSON.stringify([data.rosters.map((r) => r.id), data.groups, data.total])

  const fetchLatest = async () => {
    const { start, days, roster } = getFilters()
    const query = new URLSearchParams({ start, days, roster })
    const response = await fetch(`${DATA_URL}?${query}`, { headers: { Accept: 'application/json' }, credentials: 'same-origin', cache: 'no-store' })
    const json = await response.json()
    if (!json?.success) throw new Error(json?.message ?? 'Gagal memuat data.')
    return json.data
  }

  const applyLatest = (data) => {
    const signature = signatureOf(data)
    if (signature === lastSignature) return
    const isFirst = lastSignature === null
    lastSignature = signature
    if (isFirst) return // pembacaan pertama = data yang sudah dirender server
    const isStructureChanged = chartIds(data) !== chartIds(analysis.value) || data.dates.length !== analysis.value.dates.length
    if (isStructureChanged) return router.reload({ only: ['analysis', 'filters'], preserveScroll: true })
    analysis.value = data
  }

  const poll = async () => {
    if (isBusy || document.hidden || !analysis.value) return
    isBusy = true
    try {
      applyLatest(await fetchLatest())
      const now = new Date()
      statusText.value = `otomatis diperbarui ${twoDigits(now.getHours())}:${twoDigits(now.getMinutes())}:${twoDigits(now.getSeconds())}`
      isFailed.value = false
    } catch {
      statusText.value = 'gagal memperbarui, mencoba lagi'
      isFailed.value = true
    } finally {
      isBusy = false
    }
  }

  const handleVisible = () => {
    if (!document.hidden) poll()
  }

  watch(getAnalysis, (value) => {
    analysis.value = value
    lastSignature = null
  })

  onMounted(() => {
    poll()
    timer = setInterval(poll, getIntervalSeconds() * 1000)
    document.addEventListener('visibilitychange', handleVisible)
    window.addEventListener('focus', poll)
  })

  onBeforeUnmount(() => {
    clearInterval(timer)
    document.removeEventListener('visibilitychange', handleVisible)
    window.removeEventListener('focus', poll)
  })

  return { analysis, statusText, isFailed }
}
