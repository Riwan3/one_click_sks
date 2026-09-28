<script setup>
import { confirmDialog } from '@/utils/confirm';
import { ref } from 'vue'
import { Head, useForm, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import { Phone, Plus, Pencil, Trash2, Tag, CheckCircle2, XCircle, MessageSquare } from 'lucide-vue-next'

const props = defineProps({
  user: Object,
  notifCounts: Object,
  menu: Array,
  list: Array,
  grouped: Object,
})

// Modal states
const isModalOpen = ref(false)
const editingItem = ref(null)

const nomorForm = useForm({
  nama: '',
  jabatan: '',
  no_telepon: '',
  kategori: 'Umum',
  urutan: 0,
  aktif: true,
})

const openAddModal = () => {
  editingItem.value = null
  nomorForm.reset()
  nomorForm.kategori = 'Umum'
  nomorForm.aktif = true
  isModalOpen.value = true
}

const openEditModal = (item) => {
  editingItem.value = item
  nomorForm.nama = item.nama
  nomorForm.jabatan = item.jabatan || ''
  nomorForm.no_telepon = item.no_telepon || ''
  nomorForm.kategori = item.kategori || 'Umum'
  nomorForm.urutan = item.urutan || 0
  nomorForm.aktif = Boolean(item.aktif)
  isModalOpen.value = true
}

const submitNomor = () => {
  if (editingItem.value) {
    nomorForm.put(`/one-core/nomor-penting/${editingItem.value.id}`, {
      preserveScroll: true,
      onSuccess: () => closeModal(),
    })
  } else {
    nomorForm.post('/one-core/nomor-penting', {
      preserveScroll: true,
      onSuccess: () => closeModal(),
    })
  }
}

const closeModal = () => {
  isModalOpen.value = false
  editingItem.value = null
  nomorForm.reset()
}

const deleteNomor = async (item) => {
  if (await confirmDialog(`Hapus nomor penting "${item.nama}"?`)) {
    router.delete(`/one-core/nomor-penting/${item.id}`, {
      preserveScroll: true,
    })
  }
}
</script>

<template>
  <Head title="Nomor Penting — One-Core" />

  <AppLayout :user="user" :notif-counts="notifCounts" :menu="menu" active-module="one-core">
    <div class="p-6 space-y-6">

      <!-- HEADER -->
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
          <h1 class="text-2xl font-bold text-slate-800 flex items-center gap-2">
            <Phone class="w-7 h-7 text-blue-600" />
            Nomor Penting
          </h1>
          <p class="text-sm text-slate-500 mt-1">
            Kelola daftar nomor telepon penting yang tampil di semua halaman dan modal darurat.
          </p>
        </div>
        <button 
          @click="openAddModal" 
          class="px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-xl shadow-sm text-sm flex items-center gap-2 transition self-start"
        >
          <Plus class="w-4 h-4" /> Tambah Nomor Penting
        </button>
      </div>

      <!-- LIST NOMOR PER KATEGORI -->
      <div v-if="!grouped || Object.keys(grouped).length === 0" class="bg-white rounded-2xl p-12 text-center text-slate-400 border border-slate-200 shadow-sm">
        <Phone class="w-12 h-12 mx-auto mb-3 opacity-30" />
        <p class="text-base font-medium text-slate-600">Belum ada nomor penting.</p>
        <p class="text-xs text-slate-400 mt-1">Klik <strong>Tambah Nomor Penting</strong> untuk mulai.</p>
      </div>

      <div v-else class="space-y-6">
        <div v-for="(rows, kategori) in grouped" :key="kategori" class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
          
          <div class="px-6 py-3.5 bg-slate-50 border-b border-slate-200 flex items-center gap-2 text-xs font-bold text-slate-700 uppercase tracking-wider">
            <Tag class="w-4 h-4 text-blue-600" />
            {{ kategori }}
          </div>

          <div class="overflow-x-auto">
            <table class="w-full text-sm text-left border-collapse">
              <thead class="bg-slate-100/70 text-slate-600 font-semibold text-xs border-b border-slate-200">
                <tr>
                  <th class="py-3 px-6">NAMA / JABATAN</th>
                  <th class="py-3 px-6">NO. TELEPON</th>
                  <th class="py-3 px-6 w-24 text-center">URUTAN</th>
                  <th class="py-3 px-6 w-28 text-center">STATUS</th>
                  <th class="py-3 px-6 w-28 text-center">AKSI</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-200">
                <tr v-for="item in rows" :key="item.id" class="hover:bg-slate-50">
                  <td class="py-3.5 px-6">
                    <div class="font-bold text-slate-800">{{ item.nama }}</div>
                    <div v-if="item.jabatan" class="text-xs text-slate-500 mt-0.5">{{ item.jabatan }}</div>
                  </td>
                  <td class="py-3.5 px-6 font-medium">
                    <div v-if="item.no_telepon" class="flex items-center gap-3">
                      <a :href="'tel:' + item.no_telepon" class="inline-flex items-center gap-1.5 text-slate-800 hover:text-blue-600 transition" title="Telepon">
                        <Phone class="w-4 h-4 text-blue-500" />
                        <span>{{ item.no_telepon }}</span>
                      </a>
                      <a :href="'https://wa.me/' + item.no_telepon.replace(/[^0-9]/g, '')" target="_blank" class="p-1 bg-emerald-50 text-emerald-600 hover:bg-emerald-100 rounded-lg transition" title="WhatsApp">
                        <MessageSquare class="w-4 h-4" />
                      </a>
                    </div>
                    <span v-else class="text-slate-400">-</span>
                  </td>
                  <td class="py-3.5 px-6 text-center">
                    <span class="px-2.5 py-1 bg-slate-100 border border-slate-200 rounded-md text-xs font-medium text-slate-600">
                      {{ item.urutan }}
                    </span>
                  </td>
                  <td class="py-3.5 px-6 text-center">
                    <span v-if="item.aktif" class="inline-flex items-center gap-1 px-2.5 py-1 bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-full text-xs font-medium">
                      <CheckCircle2 class="w-3.5 h-3.5" /> Aktif
                    </span>
                    <span v-else class="inline-flex items-center gap-1 px-2.5 py-1 bg-slate-100 text-slate-500 border border-slate-200 rounded-full text-xs font-medium">
                      <XCircle class="w-3.5 h-3.5" /> Nonaktif
                    </span>
                  </td>
                  <td class="py-3.5 px-6 text-center">
                    <div class="flex justify-center gap-1">
                      <button @click="openEditModal(item)" class="p-1.5 hover:bg-blue-100 text-blue-600 rounded-lg transition" title="Edit">
                        <Pencil class="w-4 h-4" />
                      </button>
                      <button @click="deleteNomor(item)" class="p-1.5 hover:bg-rose-100 text-rose-600 rounded-lg transition" title="Hapus">
                        <Trash2 class="w-4 h-4" />
                      </button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

        </div>
      </div>

    </div>

    <!-- MODAL FORM -->
    <div v-if="isModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 backdrop-blur-sm p-4">
      <div class="bg-white rounded-2xl shadow-xl w-full max-w-md overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-200 flex justify-between items-center bg-slate-50">
          <h3 class="font-bold text-slate-800">
            {{ editingItem ? 'Edit Nomor Penting' : 'Tambah Nomor Penting' }}
          </h3>
          <button @click="closeModal" class="text-slate-400 hover:text-slate-600">&times;</button>
        </div>

        <form @submit.prevent="submitNomor" class="p-6 space-y-4">
          <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Kontak</label>
            <input v-model="nomorForm.nama" type="text" required class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm" placeholder="Contoh: Pos Emergency, Pak Budi" />
          </div>

          <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1">Jabatan / Keterangan</label>
            <input v-model="nomorForm.jabatan" type="text" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm" placeholder="Contoh: Chief Security, Paramedis" />
          </div>

          <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1">No. Telepon / HP</label>
            <input v-model="nomorForm.no_telepon" type="text" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm" placeholder="Contoh: 081234567890" />
          </div>

          <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1">Kategori</label>
            <input v-model="nomorForm.kategori" type="text" required class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm" placeholder="Contoh: EMERGENCY SITE, SAFETY, MANAGEMENT" />
          </div>

          <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1">Urutan Tampil</label>
            <input v-model="nomorForm.urutan" type="number" min="0" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm" />
          </div>

          <div class="flex items-center gap-2 pt-2">
            <input v-model="nomorForm.aktif" type="checkbox" id="aktifCheck" class="rounded text-blue-600 focus:ring-blue-500 w-4 h-4" />
            <label for="aktifCheck" class="text-sm font-medium text-slate-700">Aktifkan Nomor Ini</label>
          </div>

          <div class="flex justify-end gap-2 pt-4 border-t border-slate-100">
            <button type="button" @click="closeModal" class="px-4 py-2 text-sm text-slate-600 hover:bg-slate-100 rounded-lg">Batal</button>
            <button type="submit" :disabled="nomorForm.processing" class="px-4 py-2 text-sm bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg shadow-sm">
              Simpan
            </button>
          </div>
        </form>
      </div>
    </div>
  </AppLayout>
</template>
