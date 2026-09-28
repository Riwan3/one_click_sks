<script setup>
import { confirmDialog } from '@/utils/confirm';
import { ref, computed } from 'vue'
import { Head, useForm, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import { 
  Truck, Plus, Pencil, Trash2, Settings, Shield, ChevronRight, CheckCircle, AlertTriangle 
} from 'lucide-vue-next'

const props = defineProps({
  user: Object,
  notifCounts: Object,
  menu: Array,
  units: Array,
  settings: Object,
  mppData: Object,
})

// Global settings form
const settingsForm = useForm({
  target_1: props.settings?.mpp_target_1 ?? 2.7,
  target_2: props.settings?.mpp_target_2 ?? 2.9,
})

const submitSettings = () => {
  settingsForm.post('/one-core/units/settings', {
    preserveScroll: true,
  })
}

// Modal state for Add/Edit Unit
const isModalOpen = ref(false)
const editingUnit = ref(null)

const unitForm = useForm({
  category_type: 'nonStaff',
  name: '',
  jumlah_unit: 0,
  patterns: '',
  index_pengawas: 0,
})

const openAddModal = (type = 'nonStaff') => {
  editingUnit.value = null
  unitForm.reset()
  unitForm.category_type = type
  isModalOpen.value = true
}

const openEditModal = (unit) => {
  editingUnit.value = unit
  unitForm.category_type = unit.category_type
  unitForm.name = unit.name
  unitForm.jumlah_unit = unit.jumlah_unit || 0
  unitForm.patterns = unit.patterns || ''
  unitForm.index_pengawas = unit.index_pengawas || 0
  isModalOpen.value = true
}

const submitUnit = () => {
  if (editingUnit.value) {
    unitForm.put(`/one-core/units/${editingUnit.value.id}`, {
      preserveScroll: true,
      onSuccess: () => closeModal(),
    })
  } else {
    unitForm.post('/one-core/units', {
      preserveScroll: true,
      onSuccess: () => closeModal(),
    })
  }
}

const closeModal = () => {
  isModalOpen.value = false
  editingUnit.value = null
  unitForm.reset()
}

const deleteUnit = async (unit) => {
  if (await confirmDialog(`Hapus unit/kategori "${unit.name}"?`)) {
    router.delete(`/one-core/units/${unit.id}`, {
      preserveScroll: true,
    })
  }
}

// Computed lists
const staffUnits = computed(() => props.units?.filter(u => u.category_type === 'staff') || [])
const nonStaffUnits = computed(() => props.units?.filter(u => u.category_type === 'nonStaff') || [])

// Calculations
const t1 = computed(() => Number(props.settings?.mpp_target_1) || 2.7)
const t2 = computed(() => Number(props.settings?.mpp_target_2) || 2.9)

const totalStaffAktual = computed(() => {
  return staffUnits.value.reduce((acc, u) => acc + (props.mppData?.staff?.[u.name] || 0), 0)
})

const nonStaffTotals = computed(() => {
  let unitsCount = 0
  let aktualCount = 0
  let planMp29Count = 0
  let deviasiCount = 0

  nonStaffUnits.value.forEach(u => {
    const jml = Number(u.jumlah_unit) || 0
    const plan29 = Math.round(jml * t2.value)
    const akt = props.mppData?.nonStaff?.[u.name] || 0
    const dev = akt - plan29

    unitsCount += jml
    aktualCount += akt
    planMp29Count += plan29
    deviasiCount += dev
  })

  return { unitsCount, aktualCount, planMp29Count, deviasiCount }
})
</script>

<template>
  <Head title="Unit Produksi — One-Core" />

  <AppLayout :user="user" :notif-counts="notifCounts" :menu="menu" active-module="one-core">
    <div class="p-6 space-y-6">

      <!-- HEADER -->
      <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
          <h1 class="text-2xl font-bold text-slate-800 flex items-center gap-2">
            <Truck class="w-7 h-7 text-emerald-600" />
            Pengaturan Data Master Unit & MPP
          </h1>
          <p class="text-sm text-slate-500 mt-1">
            Kelola kategori unit produksi (Staff & Non-Staff) serta pengaturan target % MPP Global.
          </p>
        </div>
        <div class="flex gap-2">
          <button 
            @click="openAddModal('staff')" 
            class="px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-xl shadow-sm text-sm flex items-center gap-2 transition"
          >
            <Plus class="w-4 h-4" /> Tambah Staff
          </button>
          <button 
            @click="openAddModal('nonStaff')" 
            class="px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-medium rounded-xl shadow-sm text-sm flex items-center gap-2 transition"
          >
            <Plus class="w-4 h-4" /> Tambah Unit
          </button>
        </div>
      </div>

      <!-- SETTINGS GLOBAL FORM -->
      <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm">
        <div class="flex items-center gap-2 text-blue-700 font-semibold text-sm uppercase tracking-wider mb-2">
          <Settings class="w-4 h-4" />
          Pengaturan Target % MPP (Global)
        </div>
        <p class="text-xs text-slate-500 mb-4">
          Nilai persentase ini digunakan untuk kalkulasi otomatis target Manpower di tabel di bawah.
        </p>

        <form @submit.prevent="submitSettings" class="grid grid-cols-1 sm:grid-cols-3 gap-4 items-end max-w-xl">
          <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1">Target 1 (%)</label>
            <div class="relative">
              <input 
                v-model="settingsForm.target_1" 
                type="number" 
                step="0.1" 
                class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                placeholder="2.7"
              />
              <span class="absolute right-3 top-2.5 text-xs text-slate-400 font-bold">%</span>
            </div>
          </div>
          <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1">Target 2 (%)</label>
            <div class="relative">
              <input 
                v-model="settingsForm.target_2" 
                type="number" 
                step="0.1" 
                class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                placeholder="2.9"
              />
              <span class="absolute right-3 top-2.5 text-xs text-slate-400 font-bold">%</span>
            </div>
          </div>
          <div>
            <button 
              type="submit" 
              :disabled="settingsForm.processing"
              class="w-full px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg text-sm transition shadow-sm"
            >
              Simpan Target
            </button>
          </div>
        </form>
      </div>

      <!-- TABEL 1: ANALISIS MPP STAFF (PENGAWAS) -->
      <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="px-6 py-4 bg-slate-50 border-b border-slate-200 flex justify-between items-center">
          <h2 class="font-bold text-slate-800 text-sm tracking-wider uppercase flex items-center gap-2">
            <Shield class="w-4 h-4 text-blue-600" />
            ANALISIS MPP STAFF (PENGAWAS)
          </h2>
          <span class="text-xs font-medium text-slate-500">Total Staff: {{ staffUnits.length }} Kategori</span>
        </div>

        <div class="overflow-x-auto">
          <table class="w-full text-sm text-center border-collapse">
            <thead class="bg-blue-50 text-blue-900 font-bold text-xs uppercase border-b border-slate-200">
              <tr>
                <th class="py-3 px-4 w-12 border-r border-slate-200">NO</th>
                <th class="py-3 px-4 text-left border-r border-slate-200">KATEGORI JABATAN</th>
                <th class="py-3 px-4 text-left border-r border-slate-200">PATTERNS (KATA KUNCI)</th>
                <th class="py-3 px-4 w-32 border-r border-slate-200">INDEX PENGAWAS</th>
                <th class="py-3 px-4 w-28 border-r border-slate-200 bg-blue-100">MP AKTUAL</th>
                <th class="py-3 px-4 w-24 border-r border-slate-200">ACH (%)</th>
                <th class="py-3 px-4 w-24">AKSI</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-200">
              <tr v-if="staffUnits.length === 0">
                <td colspan="7" class="py-8 text-center text-slate-400 italic">Belum ada data MPP Staff.</td>
              </tr>
              <tr v-for="(u, idx) in staffUnits" :key="u.id" class="hover:bg-slate-50">
                <td class="py-3 px-4 border-r border-slate-200 text-slate-500">{{ idx + 1 }}</td>
                <td class="py-3 px-4 border-r border-slate-200 text-left font-semibold text-slate-800">{{ u.name }}</td>
                <td class="py-3 px-4 border-r border-slate-200 text-left text-xs text-slate-500">{{ u.patterns || u.name }}</td>
                <td class="py-3 px-4 border-r border-slate-200 text-slate-700 font-medium">{{ u.index_pengawas }}</td>
                <td class="py-3 px-4 border-r border-slate-200 font-bold text-blue-700 bg-blue-50/50">
                  {{ mppData?.staff?.[u.name] || 0 }}
                </td>
                <td class="py-3 px-4 border-r border-slate-200 font-semibold" :class="((mppData?.staff?.[u.name] || 0) / (u.index_pengawas || 1)) >= 1 ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800'">
                  {{ u.index_pengawas > 0 ? Math.round(((mppData?.staff?.[u.name] || 0) / u.index_pengawas) * 100) : 0 }}%
                </td>
                <td class="py-3 px-4 text-center">
                  <div class="flex justify-center gap-1">
                    <button @click="openEditModal(u)" class="p-1.5 hover:bg-blue-100 text-blue-600 rounded-lg transition" title="Edit">
                      <Pencil class="w-4 h-4" />
                    </button>
                    <button @click="deleteUnit(u)" class="p-1.5 hover:bg-rose-100 text-rose-600 rounded-lg transition" title="Hapus">
                      <Trash2 class="w-4 h-4" />
                    </button>
                  </div>
                </td>
              </tr>
              <tr class="bg-slate-100 font-bold text-slate-800">
                <td colspan="4" class="py-3 px-4 text-right border-r border-slate-200">TOTAL STAFF PRODUKSI</td>
                <td class="py-3 px-4 border-r border-slate-200 text-blue-800">{{ totalStaffAktual }}</td>
                <td colspan="2" class="py-3 px-4"></td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- TABEL 2: ANALISIS MPP NON-STAFF (OPERATOR/UNIT) -->
      <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="px-6 py-4 bg-slate-50 border-b border-slate-200 flex justify-between items-center">
          <h2 class="font-bold text-slate-800 text-sm tracking-wider uppercase flex items-center gap-2">
            <Truck class="w-4 h-4 text-emerald-600" />
            ANALISIS MPP NON-STAFF (OPERATOR/UNIT)
          </h2>
          <span class="px-3 py-1 bg-emerald-100 text-emerald-800 rounded-full text-xs font-semibold">
            Target Aktif: {{ t2 }}%
          </span>
        </div>

        <div class="overflow-x-auto">
          <table class="w-full text-sm text-center border-collapse">
            <thead class="bg-blue-50 text-blue-900 font-bold text-xs uppercase border-b border-slate-200">
              <tr>
                <th class="py-3 px-3 w-12 border-r border-slate-200">NO</th>
                <th class="py-3 px-3 text-left border-r border-slate-200">TYPE UNIT / KATEGORI</th>
                <th class="py-3 px-3 text-left border-r border-slate-200">PATTERNS (KATA KUNCI)</th>
                <th class="py-3 px-3 w-24 border-r border-slate-200">JML UNIT</th>
                <th class="py-3 px-3 w-28 border-r border-slate-200">MP {{ t1 }} %</th>
                <th class="py-3 px-3 w-28 border-r border-slate-200 bg-blue-100">MP AKTUAL</th>
                <th class="py-3 px-3 w-28 border-r border-slate-200">MP {{ t2 }} %</th>
                <th class="py-3 px-3 w-24 border-r border-slate-200">DEVIASI</th>
                <th class="py-3 px-3 w-24 border-r border-slate-200">ACH (%)</th>
                <th class="py-3 px-3 w-24">AKSI</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-200">
              <tr v-if="nonStaffUnits.length === 0">
                <td colspan="10" class="py-8 text-center text-slate-400 italic">Belum ada data Unit Non-Staff.</td>
              </tr>
              <tr v-for="(u, idx) in nonStaffUnits" :key="u.id" class="hover:bg-slate-50">
                <td class="py-3 px-3 border-r border-slate-200 text-slate-500">{{ idx + 1 }}</td>
                <td class="py-3 px-3 border-r border-slate-200 text-left font-semibold text-slate-800">{{ u.name }}</td>
                <td class="py-3 px-3 border-r border-slate-200 text-left text-xs text-slate-500">{{ u.patterns || u.name }}</td>
                <td class="py-3 px-3 border-r border-slate-200 text-slate-700 font-medium">{{ u.jumlah_unit }}</td>
                <td class="py-3 px-3 border-r border-slate-200 text-slate-600">{{ Math.round(u.jumlah_unit * t1) }}</td>
                <td class="py-3 px-3 border-r border-slate-200 font-bold text-slate-900 bg-blue-50/50">
                  {{ mppData?.nonStaff?.[u.name] || 0 }}
                </td>
                <td class="py-3 px-3 border-r border-slate-200 text-slate-700 font-semibold">{{ Math.round(u.jumlah_unit * t2) }}</td>
                <td 
                  class="py-3 px-3 border-r border-slate-200 font-bold"
                  :class="((mppData?.nonStaff?.[u.name] || 0) - Math.round(u.jumlah_unit * t2)) < 0 ? 'bg-rose-500 text-white' : (((mppData?.nonStaff?.[u.name] || 0) - Math.round(u.jumlah_unit * t2)) > 0 ? 'bg-sky-500 text-white' : 'bg-emerald-500 text-white')"
                >
                  {{ (mppData?.nonStaff?.[u.name] || 0) - Math.round(u.jumlah_unit * t2) }}
                </td>
                <td 
                  class="py-3 px-3 border-r border-slate-200 font-semibold"
                  :class="((mppData?.nonStaff?.[u.name] || 0) / (Math.round(u.jumlah_unit * t2) || 1)) >= 1 ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800'"
                >
                  {{ Math.round((mppData?.nonStaff?.[u.name] || 0) / (Math.round(u.jumlah_unit * t2) || 1) * 100) }}%
                </td>
                <td class="py-3 px-3 text-center">
                  <div class="flex justify-center gap-1">
                    <button @click="openEditModal(u)" class="p-1.5 hover:bg-blue-100 text-blue-600 rounded-lg transition" title="Edit">
                      <Pencil class="w-4 h-4" />
                    </button>
                    <button @click="deleteUnit(u)" class="p-1.5 hover:bg-rose-100 text-rose-600 rounded-lg transition" title="Hapus">
                      <Trash2 class="w-4 h-4" />
                    </button>
                  </div>
                </td>
              </tr>
              <tr class="bg-slate-100 font-bold text-slate-800">
                <td colspan="3" class="py-3 px-3 text-right border-r border-slate-200">TOTAL NON-STAFF</td>
                <td class="py-3 px-3 border-r border-slate-200">{{ nonStaffTotals.unitsCount }}</td>
                <td class="py-3 px-3 border-r border-slate-200"></td>
                <td class="py-3 px-3 border-r border-slate-200 text-blue-800">{{ nonStaffTotals.aktualCount }}</td>
                <td class="py-3 px-3 border-r border-slate-200">{{ nonStaffTotals.planMp29Count }}</td>
                <td class="py-3 px-3 border-r border-slate-200">{{ nonStaffTotals.deviasiCount }}</td>
                <td colspan="2" class="py-3 px-3"></td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

    </div>

    <!-- MODAL FORM UNIT -->
    <div v-if="isModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 backdrop-blur-sm p-4">
      <div class="bg-white rounded-2xl shadow-xl w-full max-w-md overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-200 flex justify-between items-center bg-slate-50">
          <h3 class="font-bold text-slate-800">
            {{ editingUnit ? 'Edit Unit / Kategori' : 'Tambah Unit / Kategori Baru' }}
          </h3>
          <button @click="closeModal" class="text-slate-400 hover:text-slate-600">&times;</button>
        </div>

        <form @submit.prevent="submitUnit" class="p-6 space-y-4">
          <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1">Kategori Tipe</label>
            <select v-model="unitForm.category_type" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm">
              <option value="staff">Staff (Pengawas)</option>
              <option value="nonStaff">Non-Staff (Operator / Unit)</option>
            </select>
          </div>

          <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Type / Kategori</label>
            <input v-model="unitForm.name" type="text" required class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm" placeholder="Contoh: PC 2000, DOZER, FOREMAN" />
          </div>

          <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1">Patterns (Kata Kunci Filter Employee)</label>
            <input v-model="unitForm.patterns" type="text" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm" placeholder="Contoh: PC2000, EXCA 200" />
            <p class="text-[11px] text-slate-400 mt-1">Gunakan kata kunci unik untuk mencocokkan jabatan karyawan.</p>
          </div>

          <div v-if="unitForm.category_type === 'nonStaff'">
            <label class="block text-xs font-semibold text-slate-700 mb-1">Jumlah Unit</label>
            <input v-model="unitForm.jumlah_unit" type="number" min="0" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm" />
          </div>

          <div v-else>
            <label class="block text-xs font-semibold text-slate-700 mb-1">Index Pengawas</label>
            <input v-model="unitForm.index_pengawas" type="number" step="0.1" min="0" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm" />
          </div>

          <div class="flex justify-end gap-2 pt-2 border-t border-slate-100">
            <button type="button" @click="closeModal" class="px-4 py-2 text-sm text-slate-600 hover:bg-slate-100 rounded-lg">Batal</button>
            <button type="submit" :disabled="unitForm.processing" class="px-4 py-2 text-sm bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg shadow-sm">
              Simpan
            </button>
          </div>
        </form>
      </div>
    </div>
  </AppLayout>
</template>
