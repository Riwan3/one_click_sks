<script setup>
import { ref } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { 
  BarChart2, Network, TrendingUp, Plus, Search, 
  FileSpreadsheet, UserCheck, ShieldCheck, Award
} from 'lucide-vue-next';

const props = defineProps({
  user: Object,
  menu: Array,
  tab: String,
  search: String,
  perPage: Number,
  totalRows: Number,
  gradeData: Object,
  versatilityData: Object,
  monitoringData: Array,
});

const searchQuery = ref(props.search || '');
const showAddGradeModal = ref(false);
const showAddVersatilityModal = ref(false);

const gradeForm = useForm({
  nrp: '',
  nama: '',
  posisi: '',
  eg: '',
  sub_area: '',
  atr_6_bulan: '',
  join_date: '',
  efektif_grade: '',
  efektif_position: '',
  grade_now: '',
  min_level: '',
  max_level: '',
  status_level: '',
  suite: '',
  level_posisi: '',
  sertifikasi: '',
  status_sertifikasi: '',
  remarks_grade: '',
});

const versatilityForm = useForm({
  nrp: '',
  nama: '',
  jabatan: '',
  versality: '',
});

const handleSearch = () => {
  router.get('/one-academy/monitoring-grade', {
    tab: props.tab,
    q: searchQuery.value,
  }, { preserveState: true });
};

const submitGrade = () => {
  gradeForm.post('/one-academy/monitoring-grade/store-grade', {
    onSuccess: () => {
      showAddGradeModal.value = false;
      gradeForm.reset();
    }
  });
};

const submitVersatility = () => {
  versatilityForm.post('/one-academy/monitoring-grade/store-versatility', {
    onSuccess: () => {
      showAddVersatilityModal.value = false;
      versatilityForm.reset();
    }
  });
};
</script>

<template>
  <AppLayout :user="user" :menu="menu">
    <Head title="Monitoring Grade & Jabatan - One Academy" />

    <div class="p-6 space-y-6">
      <!-- Header Banner -->
      <div class="bg-gradient-to-r from-slate-900 via-blue-900 to-indigo-900 rounded-2xl p-6 text-white shadow-xl flex justify-between items-center flex-wrap gap-4">
        <div>
          <h1 class="text-2xl font-bold flex items-center gap-3">
            <BarChart2 class="w-8 h-8 text-blue-400" />
            Monitoring Grade & Jabatan
          </h1>
          <p class="text-slate-300 text-sm mt-1">
            Pantau perkembangan grade, jabatan, dan versatility karyawan produksi secara mandiri.
          </p>
        </div>
        <div class="flex gap-3">
          <button v-if="tab === 'grade'" @click="showAddGradeModal = true" class="btn bg-blue-600 hover:bg-blue-500 text-white font-semibold px-4 py-2 rounded-xl flex items-center gap-2 shadow-lg transition">
            <Plus class="w-5 h-5" /> Tambah Data Grade
          </button>
          <button v-if="tab === 'versality'" @click="showAddVersatilityModal = true" class="btn bg-indigo-600 hover:bg-indigo-500 text-white font-semibold px-4 py-2 rounded-xl flex items-center gap-2 shadow-lg transition">
            <Plus class="w-5 h-5" /> Tambah Data Versatility
          </button>
        </div>
      </div>

      <!-- Navigation Tabs -->
      <div class="flex gap-3 border-b border-slate-200 dark:border-slate-800 pb-3">
        <a :href="`/one-academy/monitoring-grade?tab=grade`" class="px-5 py-2.5 rounded-full font-semibold text-sm transition flex items-center gap-2" :class="tab === 'grade' ? 'bg-blue-600 text-white shadow-md' : 'bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700 hover:bg-slate-50'">
          <BarChart2 class="w-4 h-4" /> Grade & Jabatan
        </a>
        <a :href="`/one-academy/monitoring-grade?tab=versality`" class="px-5 py-2.5 rounded-full font-semibold text-sm transition flex items-center gap-2" :class="tab === 'versality' ? 'bg-blue-600 text-white shadow-md' : 'bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700 hover:bg-slate-50'">
          <Network class="w-4 h-4" /> Versatility
        </a>
        <a :href="`/one-academy/monitoring-grade?tab=monitoring`" class="px-5 py-2.5 rounded-full font-semibold text-sm transition flex items-center gap-2" :class="tab === 'monitoring' ? 'bg-blue-600 text-white shadow-md' : 'bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700 hover:bg-slate-50'">
          <TrendingUp class="w-4 h-4" /> Monitoring Matrix
        </a>
      </div>

      <!-- Search Bar -->
      <div class="bg-white dark:bg-slate-900 rounded-xl p-4 shadow-sm border border-slate-200 dark:border-slate-800 flex justify-between items-center gap-4">
        <form @submit.prevent="handleSearch" class="flex gap-3 w-full max-w-md">
          <div class="relative w-full">
            <Search class="w-4 h-4 absolute left-3 top-3 text-slate-400" />
            <input v-model="searchQuery" type="text" placeholder="Cari nama, NRP, posisi..." class="w-full pl-9 pr-4 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 outline-none" />
          </div>
          <button type="submit" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-white rounded-xl text-sm font-semibold transition">
            Cari
          </button>
        </form>
        <span class="text-xs text-slate-500">Total Data: {{ totalRows }}</span>
      </div>

      <!-- Tab Content 1: Grade & Jabatan -->
      <div v-if="tab === 'grade'" class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full text-xs text-left text-slate-700 dark:text-slate-300">
            <thead class="bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 font-semibold uppercase tracking-wider">
              <tr>
                <th class="p-3 border-b">NRP</th>
                <th class="p-3 border-b">Nama</th>
                <th class="p-3 border-b">Posisi</th>
                <th class="p-3 border-b">EG</th>
                <th class="p-3 border-b">Sub Area</th>
                <th class="p-3 border-b">Grade Now</th>
                <th class="p-3 border-b">Min/Max</th>
                <th class="p-3 border-b">Suite</th>
                <th class="p-3 border-b">Sertifikasi</th>
                <th class="p-3 border-b">Status</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
              <tr v-for="item in gradeData?.data || []" :key="item.id" class="hover:bg-slate-50 dark:hover:bg-slate-800/50">
                <td class="p-3 font-mono font-bold text-blue-600">{{ item.nrp }}</td>
                <td class="p-3 font-semibold">{{ item.nama }}</td>
                <td class="p-3">{{ item.posisi }}</td>
                <td class="p-3">{{ item.eg }}</td>
                <td class="p-3">{{ item.sub_area }}</td>
                <td class="p-3 font-bold">{{ item.grade_now }}</td>
                <td class="p-3">{{ item.min_level }} / {{ item.max_level }}</td>
                <td class="p-3">
                  <span class="px-2 py-0.5 rounded text-[10px] font-bold" :class="item.suite === 'YES' ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800'">
                    {{ item.suite || 'NO' }}
                  </span>
                </td>
                <td class="p-3">{{ item.sertifikasi || '-' }}</td>
                <td class="p-3">{{ item.status_sertifikasi || '-' }}</td>
              </tr>
              <tr v-if="!gradeData?.data?.length">
                <td colspan="10" class="text-center p-8 text-slate-400">Belum ada data grade & jabatan.</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Tab Content 2: Versatility -->
      <div v-else-if="tab === 'versality'" class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full text-xs text-left text-slate-700 dark:text-slate-300">
            <thead class="bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 font-semibold uppercase tracking-wider">
              <tr>
                <th class="p-3 border-b">NRP</th>
                <th class="p-3 border-b">Nama</th>
                <th class="p-3 border-b">Jabatan</th>
                <th class="p-3 border-b">Versatility Summary</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
              <tr v-for="item in versatilityData?.data || []" :key="item.id" class="hover:bg-slate-50 dark:hover:bg-slate-800/50">
                <td class="p-3 font-mono font-bold text-indigo-600">{{ item.nrp }}</td>
                <td class="p-3 font-semibold">{{ item.nama }}</td>
                <td class="p-3">{{ item.jabatan }}</td>
                <td class="p-3 font-mono text-slate-600">{{ item.versality || '-' }}</td>
              </tr>
              <tr v-if="!versatilityData?.data?.length">
                <td colspan="4" class="text-center p-8 text-slate-400">Belum ada data versatility.</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Tab Content 3: Monitoring Matrix -->
      <div v-else-if="tab === 'monitoring'" class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full text-xs text-left text-slate-700 dark:text-slate-300">
            <thead class="bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 font-semibold uppercase tracking-wider">
              <tr>
                <th class="p-3 border-b">Type Unit / Posisi</th>
                <th class="p-3 border-b text-center">Actual Operator</th>
                <th class="p-3 border-b text-center text-emerald-600">Grade Sesuai</th>
                <th class="p-3 border-b text-center text-rose-600">Grade Belum Sesuai</th>
                <th class="p-3 border-b">Keterangan Grade</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
              <tr v-for="(item, idx) in monitoringData" :key="idx" class="hover:bg-slate-50 dark:hover:bg-slate-800/50">
                <td class="p-3 font-semibold text-slate-800 dark:text-slate-200">{{ item.type_unit }}</td>
                <td class="p-3 text-center font-bold">{{ item.actual_operator }}</td>
                <td class="p-3 text-center font-bold text-emerald-600 bg-emerald-50/50 dark:bg-emerald-950/20">{{ item.grade_sesuai }}</td>
                <td class="p-3 text-center font-bold text-rose-600 bg-rose-50/50 dark:bg-rose-950/20">{{ item.grade_belum_sesuai }}</td>
                <td class="p-3 text-slate-500 font-mono text-[11px]">{{ item.ket || '-' }}</td>
              </tr>
              <tr v-if="!monitoringData.length">
                <td colspan="5" class="text-center p-8 text-slate-400">Belum ada data agregat monitoring.</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Modal Tambah Grade -->
    <div v-if="showAddGradeModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
      <div class="bg-white dark:bg-slate-900 rounded-2xl max-w-lg w-full p-6 space-y-4 shadow-2xl border border-slate-200 dark:border-slate-800">
        <h3 class="text-lg font-bold text-slate-900 dark:text-white">Tambah Data Grade Karyawan</h3>
        <form @submit.prevent="submitGrade" class="space-y-3">
          <div class="grid grid-cols-2 gap-3">
            <input v-model="gradeForm.nrp" placeholder="NRP *" required class="input input-sm border rounded-lg p-2 text-xs" />
            <input v-model="gradeForm.nama" placeholder="Nama *" required class="input input-sm border rounded-lg p-2 text-xs" />
            <input v-model="gradeForm.posisi" placeholder="Posisi" class="input input-sm border rounded-lg p-2 text-xs" />
            <input v-model="gradeForm.grade_now" placeholder="Grade Now" class="input input-sm border rounded-lg p-2 text-xs" />
            <input v-model="gradeForm.min_level" placeholder="Min Level" class="input input-sm border rounded-lg p-2 text-xs" />
            <input v-model="gradeForm.max_level" placeholder="Max Level" class="input input-sm border rounded-lg p-2 text-xs" />
            <input v-model="gradeForm.suite" placeholder="Suite (YES/NO)" class="input input-sm border rounded-lg p-2 text-xs" />
            <input v-model="gradeForm.sertifikasi" placeholder="Sertifikasi" class="input input-sm border rounded-lg p-2 text-xs" />
          </div>
          <div class="flex justify-end gap-2 pt-3">
            <button type="button" @click="showAddGradeModal = false" class="px-4 py-2 text-xs rounded-xl bg-slate-200 dark:bg-slate-800 font-semibold">Batal</button>
            <button type="submit" class="px-4 py-2 text-xs rounded-xl bg-blue-600 text-white font-semibold shadow">Simpan</button>
          </div>
        </form>
      </div>
    </div>

    <!-- Modal Tambah Versatility -->
    <div v-if="showAddVersatilityModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
      <div class="bg-white dark:bg-slate-900 rounded-2xl max-w-lg w-full p-6 space-y-4 shadow-2xl border border-slate-200 dark:border-slate-800">
        <h3 class="text-lg font-bold text-slate-900 dark:text-white">Tambah Data Versatility Karyawan</h3>
        <form @submit.prevent="submitVersatility" class="space-y-3">
          <div class="grid grid-cols-2 gap-3">
            <input v-model="versatilityForm.nrp" placeholder="NRP *" required class="input input-sm border rounded-lg p-2 text-xs" />
            <input v-model="versatilityForm.nama" placeholder="Nama *" required class="input input-sm border rounded-lg p-2 text-xs" />
            <input v-model="versatilityForm.jabatan" placeholder="Jabatan" class="input input-sm border rounded-lg p-2 text-xs" />
            <input v-model="versatilityForm.versality" placeholder="Summary Versatility" class="input input-sm border rounded-lg p-2 text-xs col-span-2" />
          </div>
          <div class="flex justify-end gap-2 pt-3">
            <button type="button" @click="showAddVersatilityModal = false" class="px-4 py-2 text-xs rounded-xl bg-slate-200 dark:bg-slate-800 font-semibold">Batal</button>
            <button type="submit" class="px-4 py-2 text-xs rounded-xl bg-indigo-600 text-white font-semibold shadow">Simpan</button>
          </div>
        </form>
      </div>
    </div>
  </AppLayout>
</template>
