<script setup>
import { ref, reactive, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { ClipboardList, OctagonAlert, Hourglass, CheckCircle2, AlarmClock, Search, Plus, Calendar, MapPin, Flag } from 'lucide-vue-next';
import { INCIDENT_BASE_URL, INCIDENT_STATUS_OPTIONS, incidentStatusClass } from '@/utils/odrIncident';
import { formatSlashDate } from '@/utils/odrCompliance';
import AddIncidentModal from './Partials/Incident/AddIncidentModal.vue';
import IncidentDetailModal from './Partials/Incident/IncidentDetailModal.vue';

const props = defineProps({
  user: Object,
  notifCounts: Object,
  menu: Array,
  filters: { type: Object, required: true },
  kpi: { type: Object, required: true },
  incidents: { type: Array, default: () => [] },
  areaOptions: { type: Array, default: () => [] },
});

const form = reactive({ ...props.filters });
const isAddOpen = ref(false);
const selectedId = ref(null);

/** Diambil ulang dari props agar modal ikut ter-update setelah aksi (props di-refresh Inertia). */
const selectedIncident = computed(() => props.incidents.find((i) => i.id === selectedId.value) ?? null);

const kpiCards = computed(() => [
  { label: 'Total Kasus', value: props.kpi.total, icon: ClipboardList, bg: 'bg-blue-50', color: 'text-blue-500' },
  { label: 'Open', value: props.kpi.open, icon: OctagonAlert, bg: 'bg-rose-50', color: 'text-red-500' },
  { label: 'Progress', value: props.kpi.progress, icon: Hourglass, bg: 'bg-yellow-50', color: 'text-amber-500' },
  { label: 'Selesai', value: props.kpi.selesai, icon: CheckCircle2, bg: 'bg-green-50', color: 'text-emerald-500' },
  { label: 'Overdue', value: props.kpi.overdue, icon: AlarmClock, bg: 'bg-rose-50', color: 'text-red-600' },
]);

const applyFilters = () => {
  const filled = Object.fromEntries(Object.entries(form).filter(([, v]) => v !== ''));
  router.get(INCIDENT_BASE_URL, filled, { preserveScroll: true, preserveState: true, replace: true });
};
</script>

<template>
  <Head title="Perbaikan Pasca-Insiden - One Click" />

  <AppLayout :user="user" :notif-counts="notifCounts" :menu="menu">
    <div class="mx-auto max-w-[1400px] space-y-4">
      <div>
        <nav class="mb-1 text-xs text-slate-500">
          <Link href="/one-reporting/dashboard" class="text-blue-500">Dashboard</Link> / Operation Compliance Report / <span class="text-slate-400">Perbaikan Pasca-Insiden</span>
        </nav>
        <h1 class="text-xl font-bold text-slate-800">Perbaikan Pasca-Insiden</h1>
        <p class="text-[13px] text-slate-500">Kelola tindak lanjut, langkah pencegahan &amp; mitigasi, serta eviden setiap insiden</p>
      </div>

      <div class="grid grid-cols-2 gap-3 md:grid-cols-3 xl:grid-cols-5">
        <div v-for="card in kpiCards" :key="card.label" class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
          <div class="flex items-center gap-3">
            <div :class="['flex h-12 w-12 shrink-0 items-center justify-center rounded-xl', card.bg]"><component :is="card.icon" :class="['h-6 w-6', card.color]" /></div>
            <div>
              <div :class="['text-3xl font-extrabold leading-none', card.color]">{{ card.value }}</div>
              <div class="mt-1 text-xs font-semibold text-slate-500">{{ card.label }}</div>
            </div>
          </div>
        </div>
      </div>

      <div class="flex flex-wrap items-center justify-between gap-2">
        <form class="flex flex-wrap gap-2" @submit.prevent="applyFilters">
          <input v-model="form.q" type="text" placeholder="Cari judul insiden..." class="w-56 rounded-lg border border-slate-200 px-3 py-1.5 text-sm" />
          <select v-model="form.status" class="w-36 rounded-lg border border-slate-200 px-3 py-1.5 text-sm" @change="applyFilters">
            <option value="">Semua Status</option>
            <option v-for="s in INCIDENT_STATUS_OPTIONS" :key="s.value" :value="s.value">{{ s.label }}</option>
          </select>
          <select v-model="form.area" class="w-44 rounded-lg border border-slate-200 px-3 py-1.5 text-sm" @change="applyFilters">
            <option value="">Semua Area</option>
            <option v-for="a in areaOptions" :key="a" :value="a">{{ a }}</option>
          </select>
          <button type="submit" title="Cari" class="rounded-lg border border-slate-400 px-2.5 text-slate-600 hover:bg-slate-50"><Search class="h-4 w-4" /></button>
        </form>
        <button type="button" class="inline-flex items-center gap-1 rounded-lg bg-red-600 px-3 py-1.5 text-sm font-semibold text-white hover:bg-red-700" @click="isAddOpen = true">
          <Plus class="h-4 w-4" />Tambah Data Insiden
        </button>
      </div>

      <div v-if="incidents.length === 0" class="rounded-2xl bg-white py-10 text-center text-slate-500 shadow-md">Belum ada data insiden</div>
      <div v-else class="grid grid-cols-1 gap-3 md:grid-cols-2 xl:grid-cols-3">
        <button
          v-for="incident in incidents"
          :key="incident.id"
          type="button"
          class="h-full rounded-2xl border border-slate-200 bg-white p-4 text-left shadow-sm transition hover:-translate-y-0.5 hover:shadow-md"
          @click="selectedId = incident.id"
        >
          <div class="mb-2 flex items-start justify-between">
            <span :class="['rounded-full px-2.5 py-0.5 text-xs font-bold', incidentStatusClass(incident.status)]">{{ incident.status_label }}</span>
            <span v-if="incident.is_overdue" class="rounded bg-red-600 px-2 py-0.5 text-[11px] font-bold text-white">Overdue</span>
          </div>
          <div class="mb-2 font-bold text-slate-800">{{ incident.judul_insiden }}</div>
          <div class="space-y-0.5 text-xs text-slate-500">
            <div class="flex items-center gap-1.5"><Calendar class="h-3.5 w-3.5" />{{ formatSlashDate(incident.tanggal_insiden) }}</div>
            <div class="flex items-center gap-1.5"><MapPin class="h-3.5 w-3.5" />{{ incident.area || 'Area belum diisi' }}</div>
            <div class="flex items-center gap-1.5"><Flag class="h-3.5 w-3.5" />Target: {{ incident.target_selesai ? formatSlashDate(incident.target_selesai) : 'Belum diisi' }}</div>
          </div>
        </button>
      </div>
    </div>

    <AddIncidentModal v-if="isAddOpen" :area-options="areaOptions" @closed="isAddOpen = false" />
    <IncidentDetailModal v-if="selectedIncident" :key="selectedIncident.id" :incident="selectedIncident" @closed="selectedId = null" />
  </AppLayout>
</template>
