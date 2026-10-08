import { reactive } from 'vue'
import { router } from '@inertiajs/vue3'
import Swal from 'sweetalert2'
import { postJson } from '@/composables/useMoncuApi'

const showError = (error) => Swal.fire({ icon: 'error', text: error.message, confirmButtonText: 'Tutup' })

/**
 * Edit sel & unit langsung di matriks (tanpa reload halaman, seperti V1): hasil simpan ditaruh sebagai
 * override lokal di atas props matriks; riwayat perubahan di-refresh lewat partial reload.
 */
export function useRosterMatrixEdits(rosterId) {
  const cellOverrides = reactive({})
  const unitOverrides = reactive({})
  const base = `/one-roster/roster/${rosterId}`

  const cellKey = (nrp, date) => `${nrp}|${date}`

  const cellCode = (member, date) => {
    const key = cellKey(member.nrp, date)
    return key in cellOverrides ? cellOverrides[key] : (member.cells[date] ?? '')
  }

  const unitOf = (member) => (member.id in unitOverrides ? unitOverrides[member.id] : member.unit)

  const refreshChangelog = () => router.reload({ only: ['changelog'], preserveScroll: true })

  const saveCell = async (nrp, date, shiftCode) => {
    try {
      const data = await postJson(`${base}/cells`, { nrp, work_date: date, shift_code: shiftCode })
      cellOverrides[cellKey(nrp, date)] = data.code
      refreshChangelog()
    } catch (error) {
      showError(error)
    }
  }

  const saveUnit = async (member, unit) => {
    try {
      const data = await postJson(`${base}/members/${member.id}/unit`, { unit })
      unitOverrides[member.id] = data.unit
      refreshChangelog()
    } catch (error) {
      showError(error)
    }
  }

  const reset = () => {
    Object.keys(cellOverrides).forEach((key) => delete cellOverrides[key])
    Object.keys(unitOverrides).forEach((key) => delete unitOverrides[key])
  }

  return { cellCode, unitOf, saveCell, saveUnit, reset }
}
