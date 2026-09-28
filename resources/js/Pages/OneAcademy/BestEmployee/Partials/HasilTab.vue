<script setup>
import { onMounted, reactive, ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { Eye, FileSpreadsheet } from 'lucide-vue-next';
import AttemptDetailModal from './AttemptDetailModal.vue';
import ResetAttemptModal from './ResetAttemptModal.vue';

const props = defineProps({
  campaign: { type: Object, required: true },
  filterOptions: { type: Object, required: true },
  rankingRows: { type: Array, default: null },
  canReview: { type: Boolean, default: false },
  canReset: { type: Boolean, default: false },
});

const filters = reactive({ q: '', jabatan: '' });
const loading = ref(false);
const detailAttemptId = ref(null);
const resetAttemptId = ref(null);
let debounceTimer = null;

const fmtDateTime = (s) => (s ? String(s).replace('T', ' ').slice(0, 16) : '-');

function fetchRanking(showSpinner = false) {
  loading.value = showSpinner;
  router.reload({
    only: ['rankingRows'],
    data: { ...filters },
    preserveScroll: true,
    preserveState: true,
    onFinish: () => { loading.value = false; },
  });
}

onMounted(() => fetchRanking(true));

let watchTimer = null;
function onFilterChange() {
  clearTimeout(watchTimer);
  watchTimer = setTimeout(() => fetchRanking(true), 350);
}

function exportCsv() {
  const params = new URLSearchParams(Object.fromEntries(Object.entries(filters).filter(([, v]) => v)));
  window.location.href = `/one-academy/best-employee/${props.campaign.id}/export?${params.toString()}`;
}
</script>

<template>
  <div class="space-y-4">
    <div class="flex flex-wrap items-center gap-2 rounded-2xl border border-slate-200 bg-white p-3 dark:border-slate-800 dark:bg-slate-900">
      <input v-model="filters.q" type="text" placeholder="Cari NRP atau Nama..." class="rounded-lg border border-slate-200 p-1.5 text-xs dark:border-slate-700 dark:bg-slate-800" @input="onFilterChange">
      <select v-model="filters.jabatan" class="rounded-lg border border-slate-200 p-1.5 text-xs dark:border-slate-700 dark:bg-slate-800" @change="fetchRanking(true)">
        <option value="">Semua Jabatan</option>
        <option v-for="j in filterOptions.jabatan" :key="j" :value="j">{{ j }}</option>
      </select>
      <button type="button" class="ml-auto flex items-center gap-1 rounded-lg border border-emerald-300 px-3 py-1.5 text-xs font-bold text-emerald-700 hover:bg-emerald-50" @click="exportCsv">
        <FileSpreadsheet class="h-4 w-4" /> Export CSV
      </button>
    </div>

    <div class="overflow-x-auto rounded-2xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900">
      <table class="w-full min-w-[900px] text-left text-xs">
        <thead class="bg-slate-50 font-bold uppercase text-slate-500 dark:bg-slate-800/50">
          <tr>
            <th class="p-2 pl-4">Rank</th><th class="p-2">NRP</th><th class="p-2">Nama</th><th class="p-2">Jabatan</th>
            <th class="p-2">Objektif</th><th class="p-2">Manual</th><th class="p-2">Total</th><th class="p-2">%</th>
            <th class="p-2">Waktu Selesai</th><th class="p-2">Benar</th><th class="p-2">Timeout</th><th class="p-2">Review</th>
            <th v-if="canReview" class="p-2 pr-4 text-right">Aksi</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
          <tr v-if="loading || rankingRows === null"><td colspan="12" class="p-6 text-center text-slate-400">Memuat...</td></tr>
          <tr v-else-if="!rankingRows.length"><td colspan="12" class="p-6 text-center text-slate-400">Belum ada peserta yang menyelesaikan assessment.</td></tr>
          <tr v-for="r in rankingRows" :key="r.attempt_id">
            <td class="p-2 pl-4 font-bold">#{{ r.rank }}</td>
            <td class="p-2">{{ r.nrp }}</td>
            <td class="p-2 font-bold text-amber-700">
              {{ r.nama }}
              <span v-if="r.status === 'timed_out'" class="ml-1 rounded bg-rose-50 px-1.5 py-0.5 text-[10px] font-bold text-rose-700">Time Out</span>
            </td>
            <td class="p-2 text-slate-500">{{ r.jabatan || '-' }}</td>
            <td class="p-2">{{ r.objective_score }}</td>
            <td class="p-2">{{ r.manual_score ?? '-' }}</td>
            <td class="p-2 font-bold">{{ r.total_score }}</td>
            <td class="p-2">{{ r.percentage }}%</td>
            <td class="p-2">{{ fmtDateTime(r.submitted_at) }}</td>
            <td class="p-2">{{ r.correct_count }}</td>
            <td class="p-2">{{ r.timeout_count }}</td>
            <td class="p-2">
              <span v-if="r.review_status === 'Belum Direview'" class="rounded bg-amber-50 px-2 py-0.5 font-bold text-amber-700">Belum Direview</span>
              <span v-else class="rounded bg-emerald-50 px-2 py-0.5 font-bold text-emerald-700">Selesai Direview</span>
            </td>
            <td v-if="canReview" class="p-2 pr-4 text-right">
              <button type="button" class="flex items-center gap-1 rounded-lg border border-amber-200 px-2 py-1 text-[11px] font-bold text-amber-700 hover:bg-amber-50" @click="detailAttemptId = r.attempt_id">
                <Eye class="h-3.5 w-3.5" /> Detail
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>

  <AttemptDetailModal
    :attempt-id="detailAttemptId"
    :can-review="canReview"
    :can-reset="canReset"
    @close="detailAttemptId = null"
    @reset-attempt="(id) => { detailAttemptId = null; resetAttemptId = id; }"
    @graded="fetchRanking(false)"
  />
  <ResetAttemptModal :attempt-id="resetAttemptId" @close="resetAttemptId = null" @reset="resetAttemptId = null; fetchRanking(false)" />
</template>
