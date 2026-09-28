<script setup>
import { ref, computed } from 'vue'
import { Head, useForm, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import Pagination from '@/Components/ui/Pagination.vue'
import { 
  BookOpen, 
  Plus, 
  Pencil, 
  Trash2, 
  Search, 
  X, 
  AlertCircle, 
  CheckCircle2, 
  HelpCircle,
  FolderMinus,
  Layers,
  Filter
} from 'lucide-vue-next'

const props = defineProps({
  user: { type: Object, required: true },
  notifCounts: { type: Object, default: () => ({ signature: 0, notification: 0, message: 0 }) },
  menu: { type: Array, required: true },
  spdks: {
    type: Object,
    required: true,
    default: () => ({ data: [], currentPage: 1, lastPage: 1, total: 0, perPage: 20 }),
  },
  categories: { type: Array, default: () => [] },
  filters: {
    type: Object,
    default: () => ({ search: '', kategori: '' }),
  },
  canManage: { type: Boolean, default: true },
})

// Search & Filter state
const searchInput = ref(props.filters.search || '')
const selectedKategori = ref(props.filters.kategori || '')

const applyFilters = () => {
  router.get(
    '/one-core/spdk',
    {
      search: searchInput.value || undefined,
      kategori: selectedKategori.value || undefined,
    },
    { preserveState: true, preserveScroll: true, replace: true }
  )
}

const resetFilters = () => {
  searchInput.value = ''
  selectedKategori.value = ''
  applyFilters()
}

// Modal Form state
const isModalOpen = ref(false)
const editingItem = ref(null)

const form = useForm({
  no_pelanggaran: '',
  kategori: '',
  jenis_pelanggaran: '',
  sanksi: '',
  lubang: 0,
  keterangan_sanksi: '',
})

const openAddModal = () => {
  editingItem.value = null
  form.reset()
  form.clearErrors()
  form.lubang = 0
  if (props.categories.length > 0 && !form.kategori) {
    form.kategori = props.categories[0]
  }
  isModalOpen.value = true
}

const openEditModal = (item) => {
  editingItem.value = item
  form.clearErrors()
  form.no_pelanggaran = item.no_pelanggaran || ''
  form.kategori = item.kategori || ''
  form.jenis_pelanggaran = item.jenis_pelanggaran || ''
  form.sanksi = item.sanksi || ''
  form.lubang = Number(item.lubang ?? 0)
  form.keterangan_sanksi = item.keterangan_sanksi || ''
  isModalOpen.value = true
}

const closeModal = () => {
  isModalOpen.value = false
  editingItem.value = null
  form.reset()
  form.clearErrors()
}

const submitForm = () => {
  if (editingItem.value) {
    form.put(`/one-core/spdk/${editingItem.value.id}`, {
      preserveScroll: true,
      onSuccess: () => closeModal(),
    })
  } else {
    form.post('/one-core/spdk', {
      preserveScroll: true,
      onSuccess: () => closeModal(),
    })
  }
}

// Delete Confirmation
const deleteTarget = ref(null)
const isDeleteModalOpen = ref(false)

const confirmDelete = (item) => {
  deleteTarget.value = item
  isDeleteModalOpen.value = true
}

const closeDeleteModal = () => {
  deleteTarget.value = null
  isDeleteModalOpen.value = false
}

const executeDelete = () => {
  if (!deleteTarget.value) return
  router.delete(`/one-core/spdk/${deleteTarget.value.id}`, {
    preserveScroll: true,
    onSuccess: () => closeDeleteModal(),
  })
}

// Common Sanksi Quick Buttons
const quickSanksiList = [
  'Surat Teguran',
  'Pemberian Surat Peringatan',
  '1 Lubang',
  '2 Lubang',
  '3 Lubang',
  'Pemutusan Hubungan Kerja (PHK)',
]

const setQuickSanksi = (s) => {
  form.sanksi = s
  // Otomatis deteksi lubang jika ada kata 'lubang'
  const match = s.match(/(\d+)\s*lubang/i)
  if (match) {
    form.lubang = parseInt(match[1], 10)
  }
}

// Calculate row index across pagination
const getRowNumber = (index) => {
  return ((props.spdks.currentPage || 1) - 1) * (props.spdks.perPage || 20) + index + 1
}

// Calculate pagination showing info
const showingStart = computed(() => {
  if (!props.spdks.total) return 0
  return ((props.spdks.currentPage - 1) * props.spdks.perPage) + 1
})

const showingEnd = computed(() => {
  if (!props.spdks.total) return 0
  return Math.min(props.spdks.currentPage * props.spdks.perPage, props.spdks.total)
})
</script>

<template>
  <Head title="Master SPDK — One-Core" />

  <AppLayout :user="user" :notif-counts="notifCounts" :menu="menu" active-module="one-core">
    <div class="p-6 space-y-6">

      <!-- HEADER -->
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
        <div>
          <h1 class="text-2xl font-bold text-slate-800 flex items-center gap-2.5">
            <span class="p-2 bg-blue-50 text-blue-600 rounded-xl">
              <BookOpen class="w-6 h-6" />
            </span>
            Master SPDK (Standar Pedoman Disiplin Karyawan)
          </h1>
          <p class="text-sm text-slate-500 mt-1">
            Kelola daftar pasal pelanggaran, kategori, sanksi, dan lubang sebagai acuan modul pembinaan dan berita acara.
          </p>
        </div>
        <div v-if="canManage" class="flex items-center gap-3">
          <button 
            @click="openAddModal" 
            class="px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-xl shadow-sm text-sm flex items-center gap-2 transition"
          >
            <Plus class="w-4 h-4" /> Tambah Pelanggaran SPDK
          </button>
        </div>
      </div>

      <!-- FILTER & SEARCH BAR -->
      <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm space-y-3">
        <form @submit.prevent="applyFilters" class="flex flex-col md:flex-row items-stretch md:items-center gap-3">
          <!-- Search Input -->
          <div class="relative flex-1">
            <Search class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2" />
            <input 
              v-model="searchInput"
              type="text" 
              placeholder="Cari nomor pasal, kategori, jenis pelanggaran, atau sanksi..."
              class="w-full pl-10 pr-9 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
            />
            <button 
              v-if="searchInput" 
              type="button"
              @click="searchInput = ''; applyFilters()"
              class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600"
            >
              <X class="w-4 h-4" />
            </button>
          </div>

          <!-- Kategori Filter -->
          <div class="w-full md:w-64">
            <select 
              v-model="selectedKategori"
              @change="applyFilters"
              class="w-full py-2.5 px-3 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
            >
              <option value="">Semua Kategori ({{ categories.length }})</option>
              <option v-for="cat in categories" :key="cat" :value="cat">
                {{ cat }}
              </option>
            </select>
          </div>

          <!-- Action Buttons -->
          <div class="flex items-center gap-2">
            <button 
              type="submit" 
              class="px-4 py-2.5 bg-slate-800 hover:bg-slate-900 text-white font-medium rounded-xl text-sm transition flex items-center justify-center gap-1.5"
            >
              <Filter class="w-4 h-4" /> Filter
            </button>
            <button 
              v-if="searchInput || selectedKategori"
              type="button" 
              @click="resetFilters" 
              class="px-3 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-600 font-medium rounded-xl text-sm transition"
              title="Reset Filter"
            >
              Reset
            </button>
          </div>
        </form>

        <!-- Summary Bar -->
        <div class="flex flex-wrap items-center justify-between text-xs text-slate-500 pt-2 border-t border-slate-100">
          <div class="flex items-center gap-2">
            <span>Total: <strong class="text-slate-700 font-semibold">{{ spdks.total }}</strong> pelanggaran terdaftar</span>
            <span v-if="selectedKategori" class="bg-blue-50 text-blue-700 px-2 py-0.5 rounded-full border border-blue-200">
              Kategori: {{ selectedKategori }}
            </span>
            <span v-if="searchInput" class="bg-amber-50 text-amber-700 px-2 py-0.5 rounded-full border border-amber-200">
              Keyword: "{{ searchInput }}"
            </span>
          </div>
          <div>
            Menampilkan {{ showingStart }}-{{ showingEnd }} data
          </div>
        </div>
      </div>

      <!-- TABLE DATA -->
      <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full text-left text-sm">
            <thead class="bg-slate-50 border-b border-slate-200 text-xs uppercase font-semibold text-slate-600">
              <tr>
                <th scope="col" class="px-4 py-3.5 w-12 text-center">No</th>
                <th scope="col" class="px-4 py-3.5 w-36">No. Pasal</th>
                <th scope="col" class="px-4 py-3.5 w-48">Kategori</th>
                <th scope="col" class="px-4 py-3.5">Jenis Pelanggaran</th>
                <th scope="col" class="px-4 py-3.5 w-44">Sanksi & Lubang</th>
                <th scope="col" class="px-4 py-3.5 w-48">Keterangan</th>
                <th v-if="canManage" scope="col" class="px-4 py-3.5 w-24 text-center">Aksi</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
              <tr 
                v-for="(item, index) in spdks.data" 
                :key="item.id" 
                class="hover:bg-slate-50/70 transition-colors"
              >
                <!-- Nomor Urut -->
                <td class="px-4 py-3 text-center text-xs font-mono text-slate-400">
                  {{ getRowNumber(index) }}
                </td>

                <!-- No Pasal -->
                <td class="px-4 py-3 whitespace-nowrap">
                  <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-semibold font-mono bg-blue-50 text-blue-700 border border-blue-200">
                    {{ item.no_pelanggaran }}
                  </span>
                </td>

                <!-- Kategori -->
                <td class="px-4 py-3">
                  <span class="inline-block px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-700 max-w-[180px] truncate" :title="item.kategori">
                    {{ item.kategori || '-' }}
                  </span>
                </td>

                <!-- Jenis Pelanggaran -->
                <td class="px-4 py-3 text-slate-800 leading-relaxed font-normal">
                  {{ item.jenis_pelanggaran }}
                </td>

                <!-- Sanksi & Lubang -->
                <td class="px-4 py-3">
                  <div class="space-y-1">
                    <div class="font-medium text-slate-800 text-xs">
                      {{ item.sanksi }}
                    </div>
                    <div v-if="item.lubang > 0" class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-rose-50 text-rose-700 border border-rose-200">
                      <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                      {{ item.lubang }} Lubang
                    </div>
                  </div>
                </td>

                <!-- Keterangan Sanksi -->
                <td class="px-4 py-3 text-xs text-slate-500">
                  <span v-if="item.keterangan_sanksi" class="line-clamp-2" :title="item.keterangan_sanksi">
                    {{ item.keterangan_sanksi }}
                  </span>
                  <span v-else class="text-slate-300">-</span>
                </td>

                <!-- Aksi -->
                <td v-if="canManage" class="px-4 py-3 text-center whitespace-nowrap">
                  <div class="inline-flex items-center gap-1">
                    <button 
                      @click="openEditModal(item)"
                      class="p-1.5 text-slate-500 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition"
                      title="Edit Pelanggaran"
                    >
                      <Pencil class="w-4 h-4" />
                    </button>
                    <button 
                      @click="confirmDelete(item)"
                      class="p-1.5 text-slate-500 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition"
                      title="Hapus Pelanggaran"
                    >
                      <Trash2 class="w-4 h-4" />
                    </button>
                  </div>
                </td>
              </tr>

              <!-- Empty State -->
              <tr v-if="spdks.data.length === 0">
                <td :colspan="canManage ? 7 : 6" class="px-6 py-12 text-center">
                  <div class="flex flex-col items-center justify-center space-y-3">
                    <div class="p-3 bg-slate-100 rounded-full text-slate-400">
                      <FolderMinus class="w-8 h-8" />
                    </div>
                    <div class="text-slate-600 font-medium">Tidak ada data SPDK ditemukan</div>
                    <p class="text-xs text-slate-400 max-w-sm">
                      Silakan sesuaikan kata kunci pencarian atau kategori filter di atas.
                    </p>
                    <button 
                      v-if="searchInput || selectedKategori" 
                      @click="resetFilters" 
                      class="text-xs font-semibold text-blue-600 hover:underline pt-1"
                    >
                      Bersihkan Filter
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- PAGINATION -->
        <div class="p-4 border-t border-slate-200 bg-slate-50/50 flex flex-col sm:flex-row items-center justify-between gap-3">
          <div class="text-xs text-slate-500">
            Halaman <strong class="text-slate-700">{{ spdks.currentPage }}</strong> dari <strong class="text-slate-700">{{ spdks.lastPage }}</strong>
          </div>
          <Pagination
            :current-page="spdks.currentPage"
            :last-page="spdks.lastPage"
            base-url="/one-core/spdk"
            :query="{ search: filters.search || undefined, kategori: filters.kategori || undefined }"
          />
        </div>
      </div>

    </div>

    <!-- MODAL ADD / EDIT SPDK -->
    <div 
      v-if="isModalOpen" 
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm overflow-y-auto"
      @click.self="closeModal"
    >
      <div class="bg-white w-full max-w-xl rounded-2xl shadow-xl border border-slate-200 overflow-hidden my-8">
        <!-- Modal Header -->
        <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 bg-slate-50/50">
          <div class="flex items-center gap-2">
            <span class="p-1.5 bg-blue-100 text-blue-700 rounded-lg">
              <BookOpen class="w-5 h-5" />
            </span>
            <h3 class="font-bold text-slate-800 text-base">
              {{ editingItem ? 'Edit Data Pelanggaran SPDK' : 'Tambah Pelanggaran SPDK Baru' }}
            </h3>
          </div>
          <button 
            @click="closeModal" 
            class="text-slate-400 hover:text-slate-600 p-1 rounded-lg hover:bg-slate-100 transition"
          >
            <X class="w-5 h-5" />
          </button>
        </div>

        <!-- Modal Body / Form -->
        <form @submit.prevent="submitForm" class="p-6 space-y-4">
          <!-- Row: No Pelanggaran & Lubang -->
          <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <!-- No Pelanggaran -->
            <div class="sm:col-span-2">
              <label class="block text-xs font-semibold text-slate-700 mb-1">
                No. Pasal / Kode Pelanggaran <span class="text-rose-500">*</span>
              </label>
              <input 
                v-model="form.no_pelanggaran"
                type="text" 
                placeholder="Contoh: 1.1, 2.5, Pasal 14"
                class="w-full px-3.5 py-2 text-sm bg-white border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                :class="{ 'border-rose-400 focus:ring-rose-400': form.errors.no_pelanggaran }"
              />
              <p v-if="form.errors.no_pelanggaran" class="text-xs text-rose-500 mt-1">
                {{ form.errors.no_pelanggaran }}
              </p>
            </div>

            <!-- Jumlah Lubang -->
            <div>
              <label class="block text-xs font-semibold text-slate-700 mb-1">
                Lubang (Simper)
              </label>
              <div class="flex items-center">
                <input 
                  v-model.number="form.lubang"
                  type="number" 
                  min="0"
                  max="10"
                  placeholder="0"
                  class="w-full px-3.5 py-2 text-sm text-center font-bold bg-white border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                  :class="{ 'border-rose-400 focus:ring-rose-400': form.errors.lubang }"
                />
              </div>
              <p v-if="form.errors.lubang" class="text-xs text-rose-500 mt-1">
                {{ form.errors.lubang }}
              </p>
            </div>
          </div>

          <!-- Kategori -->
          <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1">
              Kategori Pelanggaran <span class="text-rose-500">*</span>
            </label>
            <div class="relative">
              <input 
                v-model="form.kategori"
                list="category-suggestions"
                type="text" 
                placeholder="Pilih atau ketik nama kategori baru..."
                class="w-full px-3.5 py-2 text-sm bg-white border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                :class="{ 'border-rose-400 focus:ring-rose-400': form.errors.kategori }"
              />
              <datalist id="category-suggestions">
                <option v-for="cat in categories" :key="cat" :value="cat" />
              </datalist>
            </div>
            <p v-if="form.errors.kategori" class="text-xs text-rose-500 mt-1">
              {{ form.errors.kategori }}
            </p>
            <p class="text-[11px] text-slate-400 mt-1">
              Bisa pilih dari kategori yang sudah ada atau ketik nama kategori baru secara langsung.
            </p>
          </div>

          <!-- Jenis Pelanggaran -->
          <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1">
              Uraian / Jenis Pelanggaran <span class="text-rose-500">*</span>
            </label>
            <textarea 
              v-model="form.jenis_pelanggaran"
              rows="3"
              placeholder="Deskripsi detail perbuatan atau tindakan pelanggaran..."
              class="w-full px-3.5 py-2 text-sm bg-white border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
              :class="{ 'border-rose-400 focus:ring-rose-400': form.errors.jenis_pelanggaran }"
            ></textarea>
            <p v-if="form.errors.jenis_pelanggaran" class="text-xs text-rose-500 mt-1">
              {{ form.errors.jenis_pelanggaran }}
            </p>
          </div>

          <!-- Sanksi -->
          <div>
            <div class="flex items-center justify-between mb-1">
              <label class="block text-xs font-semibold text-slate-700">
                Sanksi Yang Diberikan <span class="text-rose-500">*</span>
              </label>
            </div>
            <input 
              v-model="form.sanksi"
              type="text" 
              placeholder="Contoh: Pemberian Surat Peringatan, Surat Teguran, 2 Lubang"
              class="w-full px-3.5 py-2 text-sm bg-white border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
              :class="{ 'border-rose-400 focus:ring-rose-400': form.errors.sanksi }"
            />
            <!-- Quick Chips -->
            <div class="flex flex-wrap items-center gap-1.5 mt-2">
              <span class="text-[11px] text-slate-400 mr-1">Pilihan Cepat:</span>
              <button 
                v-for="s in quickSanksiList" 
                :key="s"
                type="button" 
                @click="setQuickSanksi(s)"
                class="px-2 py-0.5 text-[11px] rounded-md bg-slate-100 hover:bg-blue-50 hover:text-blue-700 text-slate-600 transition border border-slate-200"
              >
                {{ s }}
              </button>
            </div>
            <p v-if="form.errors.sanksi" class="text-xs text-rose-500 mt-1">
              {{ form.errors.sanksi }}
            </p>
          </div>

          <!-- Keterangan Sanksi -->
          <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1">
              Keterangan Sanksi / Catatan Administratif (Opsional)
            </label>
            <input 
              v-model="form.keterangan_sanksi"
              type="text" 
              placeholder="Contoh: SP Bertingkat, Dapat diberikan SP III langsung bila berbahaya"
              class="w-full px-3.5 py-2 text-sm bg-white border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            />
          </div>

          <!-- Modal Actions -->
          <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
            <button 
              type="button" 
              @click="closeModal" 
              class="px-4 py-2 text-sm font-medium text-slate-600 hover:text-slate-800 hover:bg-slate-100 rounded-xl transition"
            >
              Batal
            </button>
            <button 
              type="submit" 
              :disabled="form.processing"
              class="px-5 py-2 text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 disabled:opacity-50 rounded-xl shadow-sm transition flex items-center gap-2"
            >
              <span v-if="form.processing" class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
              {{ editingItem ? 'Simpan Perubahan' : 'Simpan SPDK Baru' }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- MODAL CONFIRM DELETE -->
    <div 
      v-if="isDeleteModalOpen" 
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm"
      @click.self="closeDeleteModal"
    >
      <div class="bg-white w-full max-w-md rounded-2xl shadow-xl border border-slate-200 overflow-hidden p-6 space-y-4">
        <div class="flex items-start gap-3">
          <div class="p-2.5 bg-rose-50 text-rose-600 rounded-xl shrink-0">
            <AlertCircle class="w-6 h-6" />
          </div>
          <div>
            <h3 class="text-base font-bold text-slate-800">Hapus Pelanggaran SPDK?</h3>
            <p class="text-xs text-slate-500 mt-1 leading-relaxed">
              Anda akan menghapus data pasal 
              <strong class="text-slate-700">"{{ deleteTarget?.no_pelanggaran }}"</strong>:
              <span class="italic text-slate-600"> {{ deleteTarget?.jenis_pelanggaran }}</span>.
              Tindakan ini tidak dapat dibatalkan.
            </p>
          </div>
        </div>

        <div class="flex items-center justify-end gap-3 pt-2">
          <button 
            type="button" 
            @click="closeDeleteModal" 
            class="px-4 py-2 text-sm font-medium text-slate-600 hover:text-slate-800 hover:bg-slate-100 rounded-xl transition"
          >
            Batal
          </button>
          <button 
            type="button" 
            @click="executeDelete" 
            class="px-4 py-2 text-sm font-semibold text-white bg-rose-600 hover:bg-rose-700 rounded-xl shadow-sm transition"
          >
            Ya, Hapus
          </button>
        </div>
      </div>
    </div>

  </AppLayout>
</template>
