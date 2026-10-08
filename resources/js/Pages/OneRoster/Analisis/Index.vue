<script setup>
import { computed, ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import { CalendarRange, ExternalLink, Info, LineChart, Network, XOctagon } from 'lucide-vue-next';
import AppLayout from '@/Layouts/AppLayout.vue';
import { useAnalysisLive } from '@/composables/useAnalysisLive';
import { buildAnalysisBlocks } from '@/utils/rosterAnalysis';
import RosterBreadcrumb from '../Partials/RosterBreadcrumb.vue';
import AnalysisFilter from './Partials/AnalysisFilter.vue';
import AnalysisChartCard from './Partials/AnalysisChartCard.vue';
import TargetModal from './Partials/TargetModal.vue';

const props = defineProps({
  user: Object,
  notifCounts: Object,
  menu: Array,
  status: { type: String, required: true }, // ok | sections_missing | error
  analysis: { type: Object, default: null },
  filters: { type: Object, required: true },
  refreshSeconds: { type: Number, required: true },
  canManage: { type: Boolean, default: false },
  targetsReady: { type: Boolean, default: false },
});

const targetChart = ref(null);

const { analysis: liveAnalysis, statusText, isFailed } = useAnalysisLive(() => props.analysis, () => props.filters, () => props.refreshSeconds);

const blocks = computed(() => buildAnalysisBlocks(liveAnalysis.value));
const isReady = computed(() => props.status === 'ok' && liveAnalysis.value !== null);
const canSetTarget = (chart) => props.canManage && props.targetsReady && chart.sid !== undefined;
const gridClass = (block) => (block.isFull || block.charts.length === 1 ? 'grid-cols-1' : 'grid-cols-1 xl:grid-cols-2');
</script>

<template>
  <Head title="Analisis Harian - One-Roster" />

  <AppLayout :user="user" :notif-counts="notifCounts" :menu="menu">
    <div class="space-y-4">
      <div>
        <RosterBreadcrumb :trail="[{ label: 'Analisis Harian' }]" />
        <h1 class="text-xl font-bold tracking-tight text-slate-800">Analisis Harian</h1>
        <p class="mt-0.5 text-xs text-slate-500">Jumlah orang <strong>Siang (D)</strong> dan <strong>Malam (N)</strong> per hari tiap section — roster Published, hanya baca</p>
      </div>

      <div v-if="status === 'sections_missing'" class="rounded-xl border border-amber-200 bg-amber-50 p-3 text-sm text-amber-800">
        Tabel section belum dibuat. Jalankan migrasi One-Roster di server.
      </div>
      <div v-else-if="!isReady" class="flex items-center gap-2 rounded-xl border border-red-200 bg-red-50 p-3 text-sm text-red-700">
        <XOctagon class="h-4 w-4" /> Data analisis tidak dapat dimuat saat ini. Silakan coba lagi.
      </div>

      <template v-else>
        <AnalysisFilter :filters="filters" :rosters="liveAnalysis.rosters" :status-text="statusText" :is-failed="isFailed" />

        <div v-if="canManage && !targetsReady" class="flex items-center gap-2 rounded-xl border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-800">
          <Info class="h-4 w-4 shrink-0" /> Pengaturan target manual belum aktif (tabel target analisis belum dibuat). Sementara ini target mengikuti roster.
        </div>

        <div v-if="!liveAnalysis.rosters.length" class="rounded-2xl border border-slate-200/80 bg-white p-10 text-center text-slate-500 shadow-sm">
          <LineChart class="mx-auto mb-2 h-8 w-8" />
          <p class="text-sm">Belum ada roster Published pada rentang tanggal ini.</p>
        </div>
        <div v-else-if="!blocks.length" class="rounded-2xl border border-slate-200/80 bg-white p-10 text-center text-slate-500 shadow-sm">
          <Network class="mx-auto mb-2 h-8 w-8" />
          <p class="text-sm">Roster Published belum punya section.</p>
        </div>

        <template v-else>
          <section v-for="(block, blockIndex) in blocks" :key="blockIndex" class="space-y-2">
            <div v-if="block.title" class="mt-4 flex items-center gap-2">
              <h2 class="flex items-center gap-1.5 text-base font-semibold text-slate-800"><CalendarRange class="h-4 w-4 text-slate-400" /> {{ block.title }}</h2>
              <span class="text-xs text-slate-500">{{ block.sections }} section</span>
              <Link :href="`/one-roster/roster/${block.rosterId}?tab=matrix`" class="ml-auto flex items-center gap-1 text-xs text-blue-600 hover:underline">
                Buka roster <ExternalLink class="h-3 w-3" />
              </Link>
            </div>
            <div :class="['grid gap-3', gridClass(block)]">
              <AnalysisChartCard
                v-for="chart in block.charts"
                :key="chart.id"
                :chart="chart"
                :dates="liveAnalysis.dates"
                :can-set-target="canSetTarget(chart)"
                @set-target="targetChart = $event"
              />
            </div>
          </section>
        </template>
      </template>
    </div>

    <TargetModal :chart="targetChart" @close="targetChart = null" />
  </AppLayout>
</template>
