<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { PenLine, Search, Inbox, Clock, ShieldCheck, RefreshCw, ArrowRight, BarChart2, Award, Hourglass } from 'lucide-vue-next';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
  user: Object,
  notifCounts: Object,
  menu: Array,
  filters: { type: Object, default: () => ({}) },
  stats: { type: Object, required: true },
  assignments: { type: Array, default: () => [] },
});

const search = ref(props.filters.search || '');

const STATUS_CHIPS = [
  { value: '', label: 'Semua' },
  { value: 'pending', label: '🔴 Pending' },
  { value: 'in_progress', label: '🔵 Sedang' },
  { value: 'passed', label: '🟢 Lulus' },
  { value: 'failed', label: '🟡 Gagal' },
];

const RISK_LABEL = { low: 'Rendah', medium: 'Sedang', high: 'Tinggi', critical: 'Kritis' };
const RISK_CLASS = {
  low: 'bg-green-100 text-green-700',
  medium: 'bg-yellow-100 text-yellow-800',
  high: 'bg-red-100 text-red-700',
  critical: 'bg-purple-100 text-purple-700',
};

const STATUS_BADGE = {
  pending: { label: 'PENDING', class: 'bg-red-100 text-red-600', icon: Hourglass },
  in_progress: { label: 'IN PROGRESS', class: 'bg-blue-100 text-blue-700', icon: RefreshCw },
  passed: { label: 'PASSED', class: 'bg-green-100 text-green-700', icon: ShieldCheck },
  failed: { label: 'FAILED', class: 'bg-amber-100 text-amber-800', icon: RefreshCw },
};

const applyFilter = (status) => {
  router.get('/one-procedure/assessment', { search: search.value || undefined, status: status || undefined }, { preserveScroll: true, preserveState: true });
};

const submitSearch = () => applyFilter(props.filters.status || '');
</script>

<template>
  <Head title="Assessment Saya - One Click" />

  <AppLayout :user="user" :notif-counts="notifCounts" :menu="menu">
    <div class="mx-auto max-w-4xl space-y-4">
      <div>
        <h1 class="flex items-center gap-2 text-xl font-bold text-slate-800"><PenLine class="h-5 w-5 text-red-600" />Assessment Saya</h1>
        <p class="text-[13px] text-slate-500">Evaluasi prosedur yang ditugaskan kepada Anda</p>
      </div>

      <div class="grid grid-cols-2 gap-2 md:grid-cols-4">
        <div class="rounded-2xl bg-white p-3.5 text-center shadow-sm" style="border-top: 3px solid #ef4444">
          <div class="text-2xl font-extrabold text-red-500">{{ stats.pending }}</div>
          <div class="text-[10px] font-semibold uppercase text-slate-400">Pending</div>
        </div>
        <div class="rounded-2xl bg-white p-3.5 text-center shadow-sm" style="border-top: 3px solid #15803d">
          <div class="text-2xl font-extrabold text-green-700">{{ stats.passed }}</div>
          <div class="text-[10px] font-semibold uppercase text-slate-400">Lulus</div>
        </div>
        <div class="rounded-2xl bg-white p-3.5 text-center shadow-sm" style="border-top: 3px solid #b45309">
          <div class="text-2xl font-extrabold text-amber-700">{{ stats.failed }}</div>
          <div class="text-[10px] font-semibold uppercase text-slate-400">Gagal</div>
        </div>
        <div class="rounded-2xl bg-white p-3.5 text-center shadow-sm" style="border-top: 3px solid #c9a84c">
          <div class="text-2xl font-extrabold text-amber-500">{{ stats.certs }}</div>
          <div class="text-[10px] font-semibold uppercase text-slate-400">Sertifikat</div>
        </div>
      </div>

      <form class="flex items-center gap-2 rounded-xl bg-white px-3.5 py-2.5 shadow-sm" @submit.prevent="submitSearch">
        <Search class="h-4 w-4 shrink-0 text-slate-400" />
        <input v-model="search" type="text" placeholder="Cari prosedur..." class="w-full border-none text-sm outline-none" />
      </form>

      <div class="flex flex-wrap gap-1.5">
        <button
          v-for="chip in STATUS_CHIPS"
          :key="chip.value"
          type="button"
          class="rounded-full border-[1.5px] px-3.5 py-1 text-xs font-semibold transition"
          :class="(filters.status || '') === chip.value ? 'border-slate-800 bg-slate-800 text-white' : 'border-slate-200 bg-white text-slate-500 hover:border-slate-300'"
          @click="applyFilter(chip.value)"
        >
          {{ chip.label }}
        </button>
      </div>

      <div v-if="assignments.length === 0" class="rounded-2xl bg-white py-14 text-center shadow-sm">
        <Inbox class="mx-auto mb-3 h-10 w-10 text-slate-300" />
        <p class="mb-1 font-semibold text-slate-500">Belum Ada Assessment</p>
        <p class="text-sm text-slate-400">Assessment yang ditugaskan akan muncul di sini.</p>
      </div>

      <div v-for="a in assignments" :key="a.assessment_id" class="overflow-hidden rounded-2xl bg-white shadow-sm">
        <div class="p-4">
          <div class="mb-2 flex items-start justify-between gap-2">
            <div class="min-w-0 flex-1">
              <div class="truncate text-[15px] font-bold text-slate-800">{{ a.procedure_title }}</div>
              <div class="mt-0.5 text-xs text-slate-500">{{ a.assessment_title }}</div>
            </div>
            <span class="inline-flex shrink-0 items-center gap-1 whitespace-nowrap rounded-full px-2.5 py-1 text-[10px] font-bold" :class="STATUS_BADGE[a.status].class">
              <component :is="STATUS_BADGE[a.status].icon" class="h-3 w-3" />{{ STATUS_BADGE[a.status].label }}
            </span>
          </div>

          <div class="flex flex-wrap items-center gap-2 text-[11px] text-slate-400">
            <span class="rounded-full px-2 py-0.5 font-bold" :class="RISK_CLASS[a.risk_level] ?? 'bg-slate-100 text-slate-600'">{{ RISK_LABEL[a.risk_level] ?? '—' }}</span>
            <span class="inline-flex items-center gap-1"><Clock class="h-3 w-3" />{{ a.timer_minutes }} mnt</span>
            <span class="inline-flex items-center gap-1"><ShieldCheck class="h-3 w-3" />Pass {{ a.passing_grade }}%</span>
            <span v-if="a.total_attempts > 0" class="inline-flex items-center gap-1">
              <RefreshCw class="h-3 w-3" />Percobaan {{ a.total_attempts }}/{{ a.retry_limit >= 99 ? '∞' : a.effective_retry_limit }}
            </span>
            <span v-if="a.score !== null" class="inline-flex items-center gap-1 font-semibold text-slate-600">
              <BarChart2 class="h-3 w-3 text-amber-500" />Nilai: {{ Number(a.score).toFixed(1) }}
            </span>
          </div>
        </div>

        <div class="flex items-center gap-2 border-t border-slate-100 bg-slate-50 px-4 py-2.5">
          <Link
            v-if="a.can_start || a.can_retake"
            :href="`/one-procedure/assessment/${a.assessment_id}/read`"
            class="inline-flex flex-1 items-center justify-center gap-1.5 rounded-full bg-red-600 py-2 text-[13px] font-bold text-white hover:bg-red-700"
          >
            <ArrowRight class="h-3.5 w-3.5" />{{ a.status === 'failed' ? 'Ulangi Assessment' : 'Mulai Assessment' }}
          </Link>
          <Link
            v-else-if="a.can_continue"
            :href="`/one-procedure/assessment/evaluation/${a.evaluation_id}`"
            class="inline-flex flex-1 items-center justify-center gap-1.5 rounded-full bg-blue-600 py-2 text-[13px] font-bold text-white hover:bg-blue-700"
          >
            <ArrowRight class="h-3.5 w-3.5" />Lanjutkan
          </Link>

          <Link
            v-if="a.evaluation_id && a.status !== 'in_progress'"
            :href="`/one-procedure/assessment/evaluation/${a.evaluation_id}/result`"
            class="inline-flex items-center justify-center gap-1 rounded-full border border-slate-300 px-3 py-2 text-xs font-semibold text-slate-600 hover:bg-white"
          >
            <BarChart2 class="h-3.5 w-3.5" />
          </Link>

          <a
            v-if="a.certificate_code"
            :href="`/one-procedure/evaluasi/certificates/${a.certificate_code}/pdf`"
            target="_blank"
            class="inline-flex items-center justify-center gap-1 rounded-full border border-amber-300 px-3 py-2 text-xs font-semibold text-amber-600 hover:bg-amber-50"
            title="Sertifikat"
          >
            <Award class="h-3.5 w-3.5" />
          </a>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
