<script setup>
import { computed, ref } from 'vue';
import { Head } from '@inertiajs/vue3';
import { CalendarX, FileSpreadsheet, TriangleAlert, UserPlus } from 'lucide-vue-next';
import AppLayout from '@/Layouts/AppLayout.vue';
import Pagination from '@/Components/ui/Pagination.vue';
import { yearOptions } from '@/utils/moncu';
import { useMoncuFilters } from '@/composables/useMoncuFilters';
import RosterBreadcrumb from '../Partials/RosterBreadcrumb.vue';
import MoncuFilterBar from './Partials/MoncuFilterBar.vue';
import MoncuTable from './Partials/MoncuTable.vue';
import MoncuPeriodModal from './Partials/MoncuPeriodModal.vue';
import MoncuEmployeeModal from './Partials/MoncuEmployeeModal.vue';
import MoncuSheetImportModal from './Partials/MoncuSheetImportModal.vue';

const props = defineProps({
  user: Object,
  notifCounts: Object,
  menu: Array,
  year: { type: Number, required: true },
  filters: { type: Object, required: true },
  rows: { type: Array, default: () => [] },
  pagination: { type: Object, required: true },
  groups: { type: Array, default: () => [] },
  sections: { type: Array, default: () => [] },
  canManage: { type: Boolean, default: false },
  moncuConfig: { type: Object, required: true },
  sheetRosters: { type: Array, default: () => [] },
});

const BASE_URL = '/one-roster/moncu';

const { filters, apply, reset } = useMoncuFilters(BASE_URL, { year: props.year, ...props.filters });

const currentYear = new Date().getFullYear();
const toOptions = (values) => values.map((value) => ({ value, label: value }));
const filterFields = computed(() => [
  { key: 'year', label: 'Tahun siklus', type: 'select', allLabel: null, options: toOptions(yearOptions(currentYear + 1, currentYear - 4)) },
  { key: 'q', label: 'Cari NRP / Nama', type: 'text', span: 'col-span-2 md:col-span-2' },
  { key: 'group', label: 'Grup', type: 'select', options: toOptions(props.groups), span: 'md:col-span-1' },
  { key: 'section', label: 'Section (Roster)', type: 'select', options: toOptions(props.sections), span: 'md:col-span-2' },
]);

const hasFilter = computed(() => `${props.filters.q}${props.filters.group}` !== '');
const invalidInductionCount = computed(() => props.rows.flatMap((row) => row.periods).filter((period) => period.induksi_issue).length);

const periodTarget = ref(null);
const employeeTarget = ref({ isOpen: false, row: null });
const isImportOpen = ref(false);

const openPeriod = (row, period) => { periodTarget.value = { nrp: row.nrp, name: row.name, year: props.year, period }; };
const openEmployee = (row = null) => { employeeTarget.value = { isOpen: true, row }; };
</script>

<template>
  <Head title="MONCU - One-Roster" />

  <AppLayout :user="user" :notif-counts="notifCounts" :menu="menu">
    <div class="space-y-4">
      <RosterBreadcrumb :trail="[{ label: 'MONCU' }]" />

      <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
        <div>
          <h1 class="text-xl font-bold tracking-tight text-slate-800">MONCU</h1>
          <p class="text-xs text-slate-500">Monitoring Cuti — siklus cuti karyawan</p>
        </div>
        <div v-if="canManage" class="flex flex-wrap gap-2">
          <button type="button" class="flex items-center gap-1.5 rounded-xl border border-blue-300 px-3 py-2 text-xs font-semibold text-blue-700 hover:bg-blue-50" @click="isImportOpen = true">
            <FileSpreadsheet class="h-4 w-4" /> Import Spreadsheet
          </button>
          <button type="button" class="flex items-center gap-1.5 rounded-xl bg-blue-600 px-3 py-2 text-xs font-semibold text-white hover:bg-blue-700" @click="openEmployee()">
            <UserPlus class="h-4 w-4" /> Tambah Karyawan
          </button>
        </div>
      </div>

      <MoncuFilterBar :fields="filterFields" :filters="filters" @apply="apply" @reset="reset" />

      <div class="flex flex-wrap gap-x-4 gap-y-1 text-xs text-slate-600">
        <span><span class="mr-1 inline-block h-3 w-3 rounded-sm bg-amber-100 align-middle"></span>Input manual (AWAL, TAHUNAN, IZIN RESMI)</span>
        <span><span class="mr-1 inline-block h-3 w-3 rounded-sm bg-slate-200 align-middle"></span>Otomatis / readonly (AKHIR, INDUKSI, PLAN)</span>
        <span class="text-slate-400">PLAN hanya acuan monitoring — tidak menghasilkan CT di Roster.</span>
        <span class="text-slate-400">Di Roster V2: CT = Cuti (AWAL s/d AKHIR), ID = Induksi (tanggal INDUKSI).</span>
        <span><span class="mr-1 inline-block h-3 w-3 rounded-sm bg-red-200 align-middle"></span>INDUKSI merah = jatuh di luar Selasa / Kamis / Minggu (revisi manual)</span>
      </div>

      <p v-if="invalidInductionCount" class="flex items-start gap-2 rounded-xl border border-red-200 bg-red-50 px-4 py-2 text-xs text-red-700">
        <TriangleAlert class="mt-0.5 h-4 w-4 shrink-0" />
        <span><strong>{{ invalidInductionCount }}</strong> INDUKSI pada halaman ini jatuh di luar hari Selasa / Kamis / Minggu (ditandai merah). Ubah <strong>AWAL</strong>, <strong>TAHUNAN</strong>, atau <strong>IZIN RESMI</strong> periodenya agar INDUKSI jatuh di hari yang sesuai.</span>
      </p>

      <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm">
        <div v-if="!rows.length" class="flex flex-col items-center gap-2 py-12 text-sm text-slate-400">
          <CalendarX class="h-8 w-8" />
          <p>{{ hasFilter ? 'Tidak ada karyawan yang cocok.' : 'Belum ada karyawan di MONCU.' }}</p>
          <button v-if="canManage" type="button" class="flex items-center gap-1.5 rounded-xl bg-blue-600 px-3 py-2 text-xs font-semibold text-white" @click="openEmployee()">
            <UserPlus class="h-4 w-4" /> Tambah Karyawan
          </button>
        </div>
        <MoncuTable v-else :rows="rows" :can-manage="canManage" :config="moncuConfig" @edit-period="openPeriod" @edit-employee="openEmployee" />
        <div class="p-3">
          <Pagination :current-page="pagination.page" :last-page="pagination.pages" :base-url="BASE_URL" :query="{ year, ...props.filters }" />
        </div>
      </div>
    </div>

    <template v-if="canManage">
      <MoncuPeriodModal :open="periodTarget !== null" :target="periodTarget" :config="moncuConfig" @close="periodTarget = null" />
      <MoncuEmployeeModal :open="employeeTarget.isOpen" :employee="employeeTarget.row" :groups="groups" :config="moncuConfig" @close="employeeTarget.isOpen = false" />
      <MoncuSheetImportModal :open="isImportOpen" :rosters="sheetRosters" @close="isImportOpen = false" />
    </template>
  </AppLayout>
</template>
