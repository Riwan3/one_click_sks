<script setup>
import { ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { 
  FileCheck, 
  Search, 
  Plus, 
  Trash2, 
  Award, 
  CheckCircle2, 
  XCircle, 
  AlertCircle,
  X
} from 'lucide-vue-next';

const props = defineProps({
  user: Object,
  notifCounts: Object,
  menu: Array,
  filters: Object,
  stats: Object,
  assessments: Array,
  evaluations: Array,
});

const filterAssessmentId = ref(props.filters?.assessment_id || '');
const filterStatus = ref(props.filters?.status || '');
const filterSearch = ref(props.filters?.search || '');

const applyFilter = () => {
  router.get('/one-procedure/evaluasi', {
    assessment_id: filterAssessmentId.value,
    status: filterStatus.value,
    search: filterSearch.value,
  }, { preserveState: true });
};

// Input Modal
const showModal = ref(false);
const form = ref({
  assessment_id: props.assessments[0]?.id || '',
  nrp: '',
  score: 85,
  notes: '',
});

const openInput = () => {
  form.value = {
    assessment_id: filterAssessmentId.value || props.assessments[0]?.id || '',
    nrp: '',
    score: 85,
    notes: '',
  };
  showModal.value = true;
};

const submitForm = () => {
  router.post('/one-procedure/evaluasi/store', form.value, {
    preserveScroll: true,
    onSuccess: () => showModal.value = false
  });
};

const deleteEvaluation = (id) => {
  if (confirm('Hapus data evaluasi ini?')) {
    router.delete(`/one-procedure/evaluasi/delete/${id}`, { preserveScroll: true });
  }
};
</script>

<template>
  <Head title="Evaluasi Prosedur - One-Procedure" />

  <AppLayout :user="user" :notif-counts="notifCounts" :menu="menu">
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm">
        <div>
          <div class="flex items-center gap-2 text-xs font-semibold text-purple-600 mb-1">
            <span>SOP &amp; Knowledge Management</span>
            <span>/</span>
            <span class="text-slate-500">Evaluasi Prosedur</span>
          </div>
          <h1 class="text-xl font-bold text-slate-800 tracking-tight flex items-center gap-2">
            <FileCheck class="w-6 h-6 text-purple-500" />
            Monitoring Evaluasi Prosedur &amp; Sertifikasi
          </h1>
          <p class="text-xs text-slate-500 mt-0.5">Rekapitulasi hasil ujian evaluasi pemahaman SOP, kelulusan, dan nomor sertifikat karyawan</p>
        </div>

        <button @click="openInput" class="px-4 py-2 text-xs font-semibold rounded-xl bg-purple-600 hover:bg-purple-700 text-white shadow-sm transition flex items-center gap-2">
          <Plus class="w-4 h-4" />
          Input Hasil Evaluasi
        </button>
      </div>

      <!-- Stats Bar -->
      <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm flex items-center gap-3">
          <div class="w-10 h-10 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center shrink-0">
            <FileCheck class="w-5 h-5" />
          </div>
          <div>
            <p class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Total Evaluasi</p>
            <p class="text-xl font-black text-slate-800">{{ stats?.totalEvaluations || 0 }}</p>
          </div>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm flex items-center gap-3">
          <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
            <CheckCircle2 class="w-5 h-5" />
          </div>
          <div>
            <p class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Lulus (Passed)</p>
            <p class="text-xl font-black text-emerald-600">{{ stats?.passedCount || 0 }}</p>
          </div>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm flex items-center gap-3">
          <div class="w-10 h-10 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center shrink-0">
            <XCircle class="w-5 h-5" />
          </div>
          <div>
            <p class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Tidak Lulus</p>
            <p class="text-xl font-black text-rose-600">{{ stats?.failedCount || 0 }}</p>
          </div>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm flex items-center gap-3">
          <div class="w-10 h-10 rounded-xl bg-sky-50 text-sky-600 flex items-center justify-center shrink-0">
            <Award class="w-5 h-5" />
          </div>
          <div>
            <p class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Tingkat Kelulusan</p>
            <p class="text-xl font-black text-sky-600">{{ stats?.passRate || 0 }}%</p>
          </div>
        </div>
      </div>

      <!-- Search & Filters -->
      <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
        <form @submit.prevent="applyFilter" class="flex flex-wrap items-center gap-3 text-xs w-full">
          <div>
            <label class="block text-[10px] font-bold text-slate-600 uppercase mb-1">Cari Karyawan / NRP</label>
            <div class="relative">
              <Search class="w-3.5 h-3.5 absolute left-3 top-2.5 text-slate-400" />
              <input type="text" v-model="filterSearch" placeholder="NRP / Nama Karyawan..." class="pl-8 pr-3 py-1.5 border border-slate-300 rounded-xl bg-slate-50/50 w-48" />
            </div>
          </div>

          <div>
            <label class="block text-[10px] font-bold text-slate-600 uppercase mb-1">Activity Assessment</label>
            <select v-model="filterAssessmentId" @change="applyFilter" class="px-3 py-1.5 border border-slate-300 rounded-xl bg-slate-50/50 max-w-56">
              <option value="">Semua Assessment</option>
              <option v-for="a in assessments" :key="a.id" :value="a.id">{{ a.title }}</option>
            </select>
          </div>

          <div>
            <label class="block text-[10px] font-bold text-slate-600 uppercase mb-1">Status Kelulusan</label>
            <select v-model="filterStatus" @change="applyFilter" class="px-3 py-1.5 border border-slate-300 rounded-xl bg-slate-50/50">
              <option value="">Semua Status</option>
              <option value="passed">Lulus (Passed)</option>
              <option value="failed">Tidak Lulus (Failed)</option>
              <option value="remedial_pending">Pending Remedial</option>
              <option value="coaching_assigned">Coaching Mentoring</option>
            </select>
          </div>

          <div class="self-end pb-0.5">
            <button type="submit" class="px-3.5 py-1.5 bg-slate-900 hover:bg-slate-800 text-white font-semibold rounded-xl transition">
              Filter
            </button>
          </div>
        </form>
      </div>

      <!-- Evaluations Table -->
      <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full text-left text-xs">
            <thead class="bg-slate-50 text-slate-500 uppercase tracking-wider text-[10px] font-bold border-b border-slate-100">
              <tr>
                <th class="px-4 py-3 text-center w-12">#</th>
                <th class="px-4 py-3">Karyawan / NRP</th>
                <th class="px-4 py-3">Departemen</th>
                <th class="px-4 py-3">Activity Assessment &amp; SOP</th>
                <th class="px-4 py-3 text-center">Skor / Passing</th>
                <th class="px-4 py-3">Status</th>
                <th class="px-4 py-3">No. Sertifikat</th>
                <th class="px-4 py-3">Waktu Selesai</th>
                <th class="px-4 py-3 text-center">Aksi</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-slate-700">
              <tr v-for="(e, idx) in evaluations" :key="e.id" class="hover:bg-slate-50/60 transition">
                <td class="px-4 py-3 text-center font-bold text-slate-400">{{ idx + 1 }}</td>
                <td class="px-4 py-3">
                  <div class="font-bold text-slate-800">{{ e.employee_name }}</div>
                  <div class="text-[10px] font-mono text-slate-400">NRP: {{ e.nrp }}</div>
                </td>
                <td class="px-4 py-3 font-medium text-slate-600">{{ e.department }}</td>
                <td class="px-4 py-3">
                  <div class="font-bold text-slate-800">{{ e.assessment_title }}</div>
                  <div class="text-[10px] text-purple-600 font-medium">SOP: {{ e.procedure_title }}</div>
                </td>
                <td class="px-4 py-3 text-center">
                  <span class="font-black text-sm" :class="e.score >= e.passing_grade ? 'text-emerald-600' : 'text-rose-600'">
                    {{ e.score }}
                  </span>
                  <span class="text-[10px] text-slate-400 block font-semibold">Min. {{ e.passing_grade }}</span>
                </td>
                <td class="px-4 py-3">
                  <span class="px-2.5 py-0.5 text-[10px] font-bold rounded-full uppercase" :class="{
                    'bg-emerald-100 text-emerald-800': e.status === 'passed',
                    'bg-rose-100 text-rose-800': e.status === 'failed',
                    'bg-amber-100 text-amber-800': e.status === 'remedial_pending',
                    'bg-sky-100 text-sky-800': e.status === 'coaching_assigned',
                  }">
                    {{ e.status }}
                  </span>
                </td>
                <td class="px-4 py-3 font-mono text-[11px]" :class="e.certificate_code !== '-' ? 'font-bold text-purple-700' : 'text-slate-400'">
                  {{ e.certificate_code }}
                </td>
                <td class="px-4 py-3 text-slate-500 text-[11px]">{{ e.completed_at }}</td>
                <td class="px-4 py-3 text-center">
                  <button @click="deleteEvaluation(e.id)" class="p-1.5 text-slate-400 hover:text-rose-600 rounded-lg hover:bg-slate-100 transition">
                    <Trash2 class="w-4 h-4" />
                  </button>
                </td>
              </tr>
              <tr v-if="!evaluations || evaluations.length === 0">
                <td colspan="9" class="px-4 py-8 text-center text-slate-400 text-xs">
                  Belum ada data evaluasi prosedur
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- INPUT EVALUATION MODAL -->
    <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-xs">
      <div class="bg-white w-full max-w-lg rounded-2xl shadow-xl border border-slate-100 p-5 space-y-4 text-xs">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
          <h3 class="font-bold text-slate-800 text-sm">Input Hasil Evaluasi Prosedur</h3>
          <button @click="showModal = false" class="text-slate-400 hover:text-slate-600">
            <X class="w-4 h-4" />
          </button>
        </div>

        <form @submit.prevent="submitForm" class="space-y-3">
          <div>
            <label class="block font-bold text-slate-700 mb-1">Activity Assessment *</label>
            <select v-model="form.assessment_id" required class="w-full px-3 py-2 border border-slate-300 rounded-xl bg-slate-50/50">
              <option v-for="a in assessments" :key="a.id" :value="a.id">{{ a.title }}</option>
            </select>
          </div>

          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block font-bold text-slate-700 mb-1">NRP Karyawan *</label>
              <input type="text" v-model="form.nrp" placeholder="NRP..." required class="w-full px-3 py-2 border border-slate-300 rounded-xl bg-slate-50/50" />
            </div>

            <div>
              <label class="block font-bold text-slate-700 mb-1">Skor Nilai Ujian *</label>
              <input type="number" v-model="form.score" min="0" max="100" required class="w-full px-3 py-2 border border-slate-300 rounded-xl bg-slate-50/50" />
            </div>
          </div>

          <div>
            <label class="block font-bold text-slate-700 mb-1">Catatan Evaluasi</label>
            <textarea v-model="form.notes" rows="2" placeholder="Catatan hasil ujian atau rekomendasi remedial..." class="w-full px-3 py-2 border border-slate-300 rounded-xl bg-slate-50/50"></textarea>
          </div>

          <div class="flex justify-end gap-2 pt-2 border-t border-slate-100">
            <button type="button" @click="showModal = false" class="px-4 py-2 font-semibold bg-slate-100 rounded-xl">Batal</button>
            <button type="submit" class="px-4 py-2 font-semibold text-white bg-purple-600 hover:bg-purple-700 rounded-xl">Simpan Evaluasi</button>
          </div>
        </form>
      </div>
    </div>
  </AppLayout>
</template>
