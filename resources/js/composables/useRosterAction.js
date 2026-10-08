import { ref } from 'vue'
import { router } from '@inertiajs/vue3'
import Swal from 'sweetalert2'
import { confirmDialog } from '@/utils/confirm'

const showError = (errors) => Swal.fire({
  icon: 'error',
  text: errors.form ?? Object.values(errors)[0] ?? 'Aksi gagal.',
  confirmButtonText: 'Tutup',
})

/**
 * Aksi tombol (tanpa form): konfirmasi opsional -> kirim lewat Inertia -> flash sukses dari server,
 * pesan error domain (errors.form) ditampilkan sebagai dialog.
 */
export function useRosterAction() {
  const isBusy = ref(false)

  const perform = async ({ method = 'post', url, data = {}, confirm = null, confirmOptions = {}, onSuccess }) => {
    if (confirm && !(await confirmDialog(confirm, confirmOptions))) return
    const options = {
      preserveScroll: true,
      onStart: () => { isBusy.value = true },
      onFinish: () => { isBusy.value = false },
      onSuccess,
      onError: showError,
    }
    if (method === 'delete') return router.delete(url, { ...options, data })
    return router[method](url, data, options)
  }

  return { isBusy, perform }
}
