<script setup>
import { computed } from 'vue';
import { Head } from '@inertiajs/vue3';
import { AlertTriangle, CalendarX, XOctagon } from 'lucide-vue-next';
import AppLayout from '@/Layouts/AppLayout.vue';
import RosterBreadcrumb from '../Partials/RosterBreadcrumb.vue';
import DashboardFilter from './Partials/DashboardFilter.vue';
import SummaryCards from './Partials/SummaryCards.vue';
import SummaryTable from './Partials/SummaryTable.vue';

const props = defineProps({
  user: Object,
  notifCounts: Object,
  menu: Array,
  status: { type: String, required: true }, // ok | sections_missing | error
  dash: { type: Object, default: null },
  filters: { type: Object, required: true },
  today: { type: String, required: true },
});

const hasGroups = computed(() => Object.keys(props.dash?.groups ?? {}).length > 0);

const groupRows = computed(() => Object.entries(props.dash?.groups ?? {}).map(([name, group]) => ({ ...group, key: name, grup: name })));

const sectionRows = computed(() => (props.dash?.sections ?? []).map((section) => ({ ...section, key: `${section.grup}|${section.section}` })));

const emptyMessage = computed(() => {
  const suffix = props.filters.jabatan ? ` untuk jabatan ${props.filters.jabatan}` : '';
  return `Tidak ada roster Published yang mencakup tanggal ini${suffix}.`;
});
</script>

<template>
  <Head title="Dashboard Monitoring - One-Roster" />

  <AppLayout :user="user" :notif-counts="notifCounts" :menu="menu">
    <div class="space-y-4">
      <div>
        <RosterBreadcrumb :trail="[{ label: 'Dashboard Monitoring' }]" />
        <h1 class="text-xl font-bold tracking-tight text-slate-800">Dashboard Monitoring One-Roster</h1>
        <p class="mt-0.5 text-xs text-slate-500">Ringkasan roster yang sudah <strong>Published</strong> pada tanggal yang dipilih — hanya baca</p>
      </div>

      <div v-if="status === 'sections_missing'" class="rounded-xl border border-amber-200 bg-amber-50 p-3 text-sm text-amber-800">
        Tabel section belum dibuat. Jalankan migrasi One-Roster di server.
      </div>
      <div v-else-if="status === 'error'" class="flex items-center gap-2 rounded-xl border border-red-200 bg-red-50 p-3 text-sm text-red-700">
        <XOctagon class="h-4 w-4" /> Data dashboard tidak dapat dimuat saat ini. Silakan coba lagi.
      </div>

      <template v-else>
        <DashboardFilter :filters="filters" :jabatan-list="dash.jabatan_list" :today="today" />

        <div v-if="!hasGroups" class="rounded-2xl border border-slate-200/80 bg-white p-10 text-center text-slate-500 shadow-sm">
          <CalendarX class="mx-auto mb-2 h-8 w-8" />
          <p class="text-sm">{{ emptyMessage }}</p>
        </div>

        <template v-else>
          <SummaryCards :total="dash.total" />

          <div v-if="dash.total.kosong > 0" class="flex items-center gap-2 rounded-xl border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-800">
            <AlertTriangle class="h-4 w-4 shrink-0" />
            <span><strong>{{ dash.total.kosong }}</strong> orang belum punya isi roster pada tanggal ini (tidak termasuk Siang/Malam/Cuti/OFF).</span>
          </div>

          <SummaryTable title="Per Roster" :rows="groupRows" :total="dash.total" />
          <SummaryTable title="Per Section" hint="(mis. PC 2000, LS KJB)" has-section-column :rows="sectionRows" />

          <p class="text-xs text-slate-500">Lainnya = H, TR, ID, MCU, FU, IDS. Orang yang ada di lebih dari satu roster Published dihitung sekali (roster terbaru).</p>
        </template>
      </template>
    </div>
  </AppLayout>
</template>
