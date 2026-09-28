<script setup>
import { onBeforeUnmount, onMounted, reactive, ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import { Eye, RotateCcw } from 'lucide-vue-next';
import SimplePager from './SimplePager.vue';
import AttemptDetailModal from './AttemptDetailModal.vue';
import ResetAttemptModal from './ResetAttemptModal.vue';

const props = defineProps({
  campaign: { type: Object, required: true },
  filterOptions: { type: Object, required: true },
  monitoringSummary: { type: Object, default: null },
  monitoringList: { type: Object, default: null },
  canReset: { type: Boolean, default: false },
  canReview: { type: Boolean, default: false },
});

const STATUS_LABELS = {
  belum_mulai: ['Belum Mulai', 'bg-slate-100 text-slate-600'],
  in_progress: ['Sedang Mengerjakan', 'bg-amber-100 text-amber-800'],
  submitted: ['Selesai', 'bg-emerald-100 text-emerald-800'],
  expired: ['Expired', 'bg-slate-800 text-white'],
  timed_out: ['Time Out', 'bg-rose-100 text-rose-800'],
  reset: ['Reset', 'bg-sky-100 text-sky-800'],
};

const filters = reactive({ q: '', status: '', jabatan: '', unit: '', min_score: '', max_score: '' });
const page = ref(1);
const autoRefresh = ref(true);
const loading = ref(false);
let debounceTimer = null;
let pollTimer = null;

const detailAttemptId = ref(null);
const resetAttemptId = ref(null);

function fetchMonitoring(showSpinner = false) {
  loading.value = showSpinner;
  router.reload({
    only: ['monitoringSummary', 'monitoringList'],
    data: { ...filters, page: page.value },
    preserveScroll: true,
    preserveState: true,
    onFinish: () => { loading.value = false; },
  });
}

const fmtDateTime = (s) => (s ? String(s).replace('T', ' ').slice(0, 16) : '-');

watch(filters, () => {
  clearTimeout(debounceTimer);
  debounceTimer = setTimeout(() => { page.value = 1; fetchMonitoring(true); }, 350);
}, { deep: true });

function goToPage(p) {
  page.value = p;
  fetchMonitoring(true);
}

function startPolling() {
  clearInterval(pollTimer);
  pollTimer = setInterval(() => {
    if (document.hidden || ! autoRefresh.value) return;
    fetchMonitoring(false);
  }, 20000);
}

onMounted(() => {
  fetchMonitoring(true);
  startPolling();
});
onBeforeUnmount(() => clearInterval(pollTimer));

function onAttemptChanged() {
  detailAttemptId.value = null;
  resetAttemptId.value = null;
  fetchMonitoring(false);
}
</script>

<template>
  <div class="space-y-4">
    <div class="grid grid-cols-2 gap-3 md:grid-cols-4">
      <div class="rounded-xl border border-slate-200 bg-white p-3 dark:border-slate-800 dark:bg-slate-900">
        <p class="text-xs font-bold uppercase text-slate-400">Total Peserta</p>
        <p class="text-2xl font-bold text-slate-900 dark:text-white">{{ monitoringSummary?.total_participants ?? '-' }}</p>
      </div>
      <div class="rounded-xl border border-slate-200 bg-white p-3 dark:border-slate-800 dark:bg-slate-900">
        <p class="text-xs font-bold uppercase text-slate-400">Belum Mulai</p>
        <p class="text-2xl font-bold text-slate-500">{{ monitoringSummary?.belum_mulai ?? '-' }}</p>
      </div>
      <div class="rounded-xl border border-slate-200 bg-white p-3 dark:border-slate-800 dark:bg-slate-900">
        <p class="text-xs font-bold uppercase text-slate-400">Sedang Mengerjakan</p>
        <p class="text-2xl font-bold text-amber-600">{{ monitoringSummary?.sedang_mengerjakan ?? '-' }}</p>
      </div>
      <div class="rounded-xl border border-slate-200 bg-white p-3 dark:border-slate-800 dark:bg-slate-900">
        <p class="text-xs font-bold uppercase text-slate-400">Selesai</p>
        <p class="text-2xl font-bold text-emerald-600">{{ monitoringSummary?.selesai ?? '-' }}</p>
      </div>
      <div class="rounded-xl border border-slate-200 bg-white p-3 dark:border-slate-800 dark:bg-slate-900">
        <p class="text-xs font-bold uppercase text-slate-400">Expired</p>
        <p class="text-2xl font-bold text-slate-700">{{ monitoringSummary?.expired ?? '-' }}</p>
      </div>
      <div class="rounded-xl border border-slate-200 bg-white p-3 dark:border-slate-800 dark:bg-slate-900">
        <p class="text-xs font-bold uppercase text-slate-400">Time Out</p>
        <p class="text-2xl font-bold text-rose-600">{{ monitoringSummary?.timeout ?? '-' }}</p>
      </div>
      <div class="rounded-xl border border-slate-200 bg-white p-3 dark:border-slate-800 dark:bg-slate-900">
        <p class="text-xs font-bold uppercase text-slate-400">Rata-rata Nilai</p>
        <p class="text-2xl font-bold text-amber-700">{{ monitoringSummary?.avg_score ?? '-' }}</p>
      </div>
      <div class="rounded-xl border border-slate-200 bg-white p-3 dark:border-slate-800 dark:bg-slate-900">
        <p class="text-xs font-bold uppercase text-slate-400">Completion Rate</p>
        <p class="text-2xl font-bold text-amber-700">{{ monitoringSummary?.completion_rate ?? 0 }}%</p>
      </div>
    </div>

    <div class="rounded-2xl border border-slate-200 bg-white p-3 dark:border-slate-800 dark:bg-slate-900">
      <div class="grid grid-cols-2 gap-2 md:grid-cols-7">
        <input v-model="filters.q" type="text" placeholder="Cari NRP/Nama..." class="col-span-2 rounded-lg border border-slate-200 p-1.5 text-xs dark:border-slate-700 dark:bg-slate-800">
        <select v-model="filters.status" class="rounded-lg border border-slate-200 p-1.5 text-xs dark:border-slate-700 dark:bg-slate-800">
          <option value="">Semua Status</option>
          <option v-for="(v, k) in STATUS_LABELS" :key="k" :value="k">{{ v[0] }}</option>
        </select>
        <select v-model="filters.jabatan" class="rounded-lg border border-slate-200 p-1.5 text-xs dark:border-slate-700 dark:bg-slate-800">
          <option value="">Semua Jabatan</option>
          <option v-for="j in filterOptions.jabatan" :key="j" :value="j">{{ j }}</option>
        </select>
        <select v-model="filters.unit" class="rounded-lg border border-slate-200 p-1.5 text-xs dark:border-slate-700 dark:bg-slate-800">
          <option value="">Semua Departemen</option>
          <option v-for="u in filterOptions.unit" :key="u" :value="u">{{ u }}</option>
        </select>
        <input v-model="filters.min_score" type="number" placeholder="Min" class="rounded-lg border border-slate-200 p-1.5 text-xs dark:border-slate-700 dark:bg-slate-800">
        <input v-model="filters.max_score" type="number" placeholder="Maks" class="rounded-lg border border-slate-200 p-1.5 text-xs dark:border-slate-700 dark:bg-slate-800">
      </div>
      <label class="mt-2 flex items-center gap-1.5 text-xs text-slate-500">
        <input v-model="autoRefresh" type="checkbox" class="h-3.5 w-3.5 rounded"> Auto-refresh 20 detik
      </label>
    </div>

    <div class="overflow-x-auto rounded-2xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900">
      <table class="w-full min-w-[900px] text-left text-xs">
        <thead class="bg-slate-50 font-bold uppercase text-slate-500 dark:bg-slate-800/50">
          <tr>
            <th class="p-2 pl-4">No</th><th class="p-2">NRP</th><th class="p-2">Nama</th><th class="p-2">Jabatan</th><th class="p-2">Departemen</th>
            <th class="p-2">Mulai</th><th class="p-2">Submit</th><th class="p-2">Progress</th><th class="p-2">Terjawab</th><th class="p-2">Timeout</th>
            <th class="p-2">Status</th><th class="p-2">Nilai</th><th class="p-2 pr-4 text-right">Aksi</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
          <tr v-if="loading"><td colspan="13" class="p-6 text-center text-slate-400">Memuat...</td></tr>
          <template v-else-if="monitoringList">
            <tr v-if="!monitoringList.rows.length"><td colspan="13" class="p-6 text-center text-slate-400">Tidak ada peserta yang cocok filter.</td></tr>
            <tr v-for="(r, i) in monitoringList.rows" :key="r.participant_id">
              <td class="p-2 pl-4">{{ ((page - 1) * monitoringList.per_page) + i + 1 }}</td>
              <td class="p-2">{{ r.nrp }}</td>
              <td class="p-2 font-bold text-amber-700">{{ r.nama }}</td>
              <td class="p-2 text-slate-500">{{ r.jabatan || '-' }}</td>
              <td class="p-2 text-slate-500">{{ r.unit || '-' }}</td>
              <td class="p-2">{{ fmtDateTime(r.started_at) }}</td>
              <td class="p-2">{{ fmtDateTime(r.submitted_at) }}</td>
              <td class="p-2">
                <div class="h-1.5 w-16 rounded-full bg-slate-100 dark:bg-slate-700"><div class="h-1.5 rounded-full bg-amber-500" :style="{ width: r.progress_percent + '%' }"></div></div>
                <span class="text-slate-400">{{ r.progress_percent }}%</span>
              </td>
              <td class="p-2">{{ r.answered_count }}</td>
              <td class="p-2">{{ r.timeout_count }}</td>
              <td class="p-2"><span class="rounded px-2 py-0.5 font-bold" :class="(STATUS_LABELS[r.display_status] || [r.display_status, 'bg-slate-100'])[1]">{{ (STATUS_LABELS[r.display_status] || [r.display_status])[0] }}</span></td>
              <td class="p-2 font-bold">{{ r.total_score ?? '-' }}</td>
              <td class="p-2 pr-4 text-right">
                <button v-if="r.attempt_id" type="button" class="rounded p-1.5 text-amber-600 hover:bg-amber-50" @click="detailAttemptId = r.attempt_id"><Eye class="h-4 w-4" /></button>
                <button v-if="r.attempt_id && canReset && ['in_progress','submitted','timed_out','expired'].includes(r.attempt_status)" type="button" class="rounded p-1.5 text-rose-600 hover:bg-rose-50" @click="resetAttemptId = r.attempt_id"><RotateCcw class="h-4 w-4" /></button>
              </td>
            </tr>
          </template>
        </tbody>
      </table>
      <div v-if="monitoringList" class="border-t border-slate-100 p-3 dark:border-slate-800">
        <SimplePager :page="page" :per-page="monitoringList.per_page" :total="monitoringList.total" label="peserta" @change="goToPage" />
      </div>
    </div>
  </div>

  <AttemptDetailModal :attempt-id="detailAttemptId" :can-review="canReview" :can-reset="canReset" @close="detailAttemptId = null" @reset-attempt="(id) => { detailAttemptId = null; resetAttemptId = id; }" @graded="fetchMonitoring(false)" />
  <ResetAttemptModal :attempt-id="resetAttemptId" @close="resetAttemptId = null" @reset="onAttemptChanged" />
</template>
