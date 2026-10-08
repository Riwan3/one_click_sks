import { ref } from 'vue'
import axios from 'axios'

const SEARCH_URL = '/one-roster/roster/employees/search'
const MIN_LENGTH = 2
const DEBOUNCE_MS = 250

/** Autocomplete karyawan (NRP / nama) ke endpoint JSON pencarian Roster V2. */
export function useEmployeeSearch() {
  const results = ref([])
  const message = ref({ text: '', tone: '' })
  let timer = null

  const reset = () => { results.value = []; message.value = { text: '', tone: '' } }

  const runSearch = async (term) => {
    try {
      const { data } = await axios.get(SEARCH_URL, { params: { q: term } })
      results.value = data.data
      message.value = data.data.length ? { text: '', tone: '' } : { text: 'Tidak ada karyawan yang cocok.', tone: 'muted' }
    } catch (error) {
      results.value = []
      message.value = { text: error.response?.data?.message ?? 'Pencarian gagal.', tone: 'danger' }
    }
  }

  const search = (term) => {
    clearTimeout(timer)
    reset()
    const trimmed = term.trim()
    if (trimmed.length < MIN_LENGTH) return
    timer = setTimeout(() => runSearch(trimmed), DEBOUNCE_MS)
  }

  const pick = (employee) => {
    results.value = []
    message.value = {
      text: `${employee.nrp} - ${employee.name}${employee.jabatan ? ` (${employee.jabatan})` : ''}`,
      tone: 'success',
    }
  }

  return { results, message, search, pick, reset }
}
