<script setup>
import { ref } from 'vue'
import { Head, useForm, Link } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import { ArrowLeft, Loader2 } from 'lucide-vue-next'

defineProps({
  clinic: Object,
})

const form = useForm({
  employee_id: '',
  coach_id: '',
  tanggal: '',
  lokasi: '',
  waktu_pertemuan: '',
  materi_pribadi: '',
  materi_keluarga: '',
  materi_lingkungan: '',
  materi_pekerjaan: '',
  fatigue_level: 0,
  deskripsi: '',
  rekomendasi: '',
  file_bukti: null,
  ttd_anak_asuh: '',
  ttd_bapak_asuh: '',
  status: 'published',
})

const employees = ref([])
const searchTimeout = ref(null)
const showEmployeeList = ref(false)

const searchEmployees = async (query) => {
  clearTimeout(searchTimeout.value)
  if (!query || query.length < 2) {
    employees.value = []
    showEmployeeList.value = false
    return
  }

  searchTimeout.value = setTimeout(async () => {
    try {
      const response = await fetch(`/one-power/coaching-clinic/search-employee?q=${encodeURIComponent(query)}`)
      employees.value = await response.json()
      showEmployeeList.value = true
    } catch (error) {
      console.error('Search error:', error)
    }
  }, 300)
}

const selectEmployee = (employee) => {
  form.employee_id = employee.id
  showEmployeeList.value = false
  employees.value = []
}

const handleFileUpload = (event) => {
  form.file_bukti = event.target.files[0]
}

const submit = () => {
  form.put(route('coaching-clinic.update', form.id))
}
</script>

<template>
  <Head title="Edit Coaching Clinic" />
  <AppLayout>
    <div class="space-y-6">
      <div class="flex items-center gap-4">
        <Link :href="route('coaching-clinic.index')" class="rounded-lg border border-slate-300 p-2 text-slate-600 hover:bg-slate-100">
          <ArrowLeft class="h-5 w-5" />
        </Link>
        <div>
          <h1 class="text-2xl font-bold text-slate-900">Edit Coaching Clinic</h1>
          <p class="text-slate-600">Update sesi coaching untuk anak asuh</p>
        </div>
      </div>

      <div class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
        <form @submit.prevent="submit" class="space-y-6">
          <!-- Employee Selection -->
          <div>
            <label class="block text-sm font-semibold text-slate-700 mb-2">Pegawai *</label>
            <div class="relative">
              <input
                type="text"
                placeholder="Ketik NRP atau nama..."
                @input="(e) => searchEmployees(e.target.value)"
                @focus="() => employees.length > 0 && (showEmployeeList = true)"
                class="w-full rounded-lg border border-slate-300 px-4 py-2 text-sm focus:border-blue-500 focus:outline-none"
              />
              <div v-if="showEmployeeList && employees.length > 0" class="absolute top-full left-0 right-0 z-10 mt-1 rounded-lg border border-slate-200 bg-white shadow-lg max-h-40 overflow-y-auto">
                <button
                  v-for="emp in employees"
                  :key="emp.id"
                  type="button"
                  @click="selectEmployee(emp)"
                  class="w-full px-4 py-2 text-left text-sm hover:bg-slate-100 border-b border-slate-100 last:border-0"
                >
                  <div class="font-medium">{{ emp.nama }}</div>
                  <div class="text-xs text-slate-500">{{ emp.nrp }} - {{ emp.departemen }}</div>
                </button>
              </div>
            </div>
            <div v-if="form.employee_id" class="mt-2 text-sm text-green-600">✓ Pegawai dipilih</div>
            <span v-if="form.errors.employee_id" class="text-xs text-red-600">{{ form.errors.employee_id }}</span>
          </div>

          <div class="grid gap-6 md:grid-cols-2">
            <!-- Tanggal -->
            <div>
              <label class="block text-sm font-semibold text-slate-700 mb-2">Tanggal *</label>
              <input
                v-model="form.tanggal"
                type="date"
                class="w-full rounded-lg border border-slate-300 px-4 py-2 text-sm focus:border-blue-500 focus:outline-none"
              />
              <span v-if="form.errors.tanggal" class="text-xs text-red-600">{{ form.errors.tanggal }}</span>
            </div>

            <!-- Waktu -->
            <div>
              <label class="block text-sm font-semibold text-slate-700 mb-2">Waktu Pertemuan *</label>
              <input
                v-model="form.waktu_pertemuan"
                type="time"
                class="w-full rounded-lg border border-slate-300 px-4 py-2 text-sm focus:border-blue-500 focus:outline-none"
              />
              <span v-if="form.errors.waktu_pertemuan" class="text-xs text-red-600">{{ form.errors.waktu_pertemuan }}</span>
            </div>

            <!-- Lokasi -->
            <div class="md:col-span-2">
              <label class="block text-sm font-semibold text-slate-700 mb-2">Lokasi *</label>
              <input
                v-model="form.lokasi"
                type="text"
                class="w-full rounded-lg border border-slate-300 px-4 py-2 text-sm focus:border-blue-500 focus:outline-none"
              />
              <span v-if="form.errors.lokasi" class="text-xs text-red-600">{{ form.errors.lokasi }}</span>
            </div>

            <!-- Fatigue Level -->
            <div>
              <label class="block text-sm font-semibold text-slate-700 mb-2">Fatigue Level</label>
              <select
                v-model.number="form.fatigue_level"
                class="w-full rounded-lg border border-slate-300 px-4 py-2 text-sm focus:border-blue-500 focus:outline-none"
              >
                <option value="0">Normal</option>
                <option value="1">Ringan</option>
                <option value="2">Sedang</option>
                <option value="3">Berat</option>
              </select>
            </div>
          </div>

          <!-- Materi -->
          <div class="space-y-4">
            <h3 class="font-semibold text-slate-800">Materi Coaching</h3>
            <div class="grid gap-4 md:grid-cols-2">
              <div>
                <label class="block text-sm font-medium text-slate-700 mb-2">Materi Pribadi</label>
                <textarea
                  v-model="form.materi_pribadi"
                  class="w-full rounded-lg border border-slate-300 px-4 py-2 text-sm focus:border-blue-500 focus:outline-none"
                  rows="3"
                />
              </div>
              <div>
                <label class="block text-sm font-medium text-slate-700 mb-2">Materi Keluarga</label>
                <textarea
                  v-model="form.materi_keluarga"
                  class="w-full rounded-lg border border-slate-300 px-4 py-2 text-sm focus:border-blue-500 focus:outline-none"
                  rows="3"
                />
              </div>
              <div>
                <label class="block text-sm font-medium text-slate-700 mb-2">Materi Lingkungan</label>
                <textarea
                  v-model="form.materi_lingkungan"
                  class="w-full rounded-lg border border-slate-300 px-4 py-2 text-sm focus:border-blue-500 focus:outline-none"
                  rows="3"
                />
              </div>
              <div>
                <label class="block text-sm font-medium text-slate-700 mb-2">Materi Pekerjaan</label>
                <textarea
                  v-model="form.materi_pekerjaan"
                  class="w-full rounded-lg border border-slate-300 px-4 py-2 text-sm focus:border-blue-500 focus:outline-none"
                  rows="3"
                />
              </div>
            </div>
          </div>

          <!-- Deskripsi & Rekomendasi -->
          <div class="grid gap-4 md:grid-cols-2">
            <div>
              <label class="block text-sm font-semibold text-slate-700 mb-2">Deskripsi</label>
              <textarea
                v-model="form.deskripsi"
                class="w-full rounded-lg border border-slate-300 px-4 py-2 text-sm focus:border-blue-500 focus:outline-none"
                rows="4"
              />
            </div>
            <div>
              <label class="block text-sm font-semibold text-slate-700 mb-2">Rekomendasi</label>
              <textarea
                v-model="form.rekomendasi"
                class="w-full rounded-lg border border-slate-300 px-4 py-2 text-sm focus:border-blue-500 focus:outline-none"
                rows="4"
              />
            </div>
          </div>

          <!-- File Bukti -->
          <div>
            <label class="block text-sm font-semibold text-slate-700 mb-2">File Bukti (PDF/JPG/PNG)</label>
            <input
              type="file"
              @change="handleFileUpload"
              accept=".pdf,.jpg,.jpeg,.png"
              class="w-full rounded-lg border border-slate-300 px-4 py-2 text-sm focus:border-blue-500 focus:outline-none"
            />
            <span v-if="form.errors.file_bukti" class="text-xs text-red-600">{{ form.errors.file_bukti }}</span>
          </div>

          <!-- Status -->
          <div>
            <label class="block text-sm font-semibold text-slate-700 mb-2">Status</label>
            <div class="flex gap-4">
              <label class="flex items-center">
                <input v-model="form.status" type="radio" value="draft" class="mr-2" />
                <span class="text-sm text-slate-700">Draft</span>
              </label>
              <label class="flex items-center">
                <input v-model="form.status" type="radio" value="published" class="mr-2" />
                <span class="text-sm text-slate-700">Published</span>
              </label>
            </div>
          </div>

          <!-- Form Actions -->
          <div class="flex gap-3 border-t border-slate-200 pt-6">
            <Link :href="route('coaching-clinic.index')" class="rounded-lg border border-slate-300 px-6 py-2 text-sm font-medium text-slate-700 hover:bg-slate-100">
              Batal
            </Link>
            <button
              type="submit"
              :disabled="form.processing"
              class="flex items-center gap-2 rounded-lg bg-blue-600 px-6 py-2 text-sm font-semibold text-white hover:bg-blue-700 disabled:opacity-50"
            >
              <Loader2 v-if="form.processing" class="h-4 w-4 animate-spin" />
              Simpan Perubahan
            </button>
          </div>
        </form>
      </div>
    </div>
  </AppLayout>
</template>
