<script setup>
import { computed, ref } from 'vue';
import { Head } from '@inertiajs/vue3';
import { CalendarX, FileSpreadsheet, Plus } from 'lucide-vue-next';
import AppLayout from '@/Layouts/AppLayout.vue';
import RosterBreadcrumb from '../Partials/RosterBreadcrumb.vue';
import RosterSummaryCards from './Partials/RosterSummaryCards.vue';
import RosterFilterBar from './Partials/RosterFilterBar.vue';
import RosterListTable from './Partials/RosterListTable.vue';
import ArchivedRosterCards from './Partials/ArchivedRosterCards.vue';
import RosterFormModal from './Partials/Modals/RosterFormModal.vue';
import DuplicateRosterModal from './Partials/Modals/DuplicateRosterModal.vue';
import ArchiveRosterModal from './Partials/Modals/ArchiveRosterModal.vue';
import SheetImportModal from './Partials/Modals/SheetImportModal.vue';

const props = defineProps({
  user: Object,
  notifCounts: Object,
  menu: Array,
  summary: { type: Object, required: true },
  groups: { type: Array, default: () => [] },
  filters: { type: Object, required: true },
  rosters: { type: Array, default: () => [] },
  archivedRosters: { type: Array, default: () => [] },
  sheetRosters: { type: Array, default: () => [] },
  canManage: { type: Boolean, default: false },
  me: { type: Object, required: true },
  yearNow: { type: Number, required: true },
});

const isCreateOpen = ref(false);
const isSheetOpen = ref(false);
const editingRoster = ref(null);
const duplicatingRoster = ref(null);
const archivingRoster = ref(null);

const isEditOpen = computed(() => editingRoster.value !== null);
const hasFilter = computed(() => Object.values(props.filters).some((value) => value !== ''));
const emptyMessage = computed(() => {
  if (props.archivedRosters.length) return 'Tidak ada roster aktif (draft / published). Roster yang diarsipkan ada di bawah.';
  return hasFilter.value ? 'Tidak ada roster yang cocok dengan filter.' : 'Belum ada data roster.';
});
</script>

<template>
  <Head title="Roster V2 - One-Roster" />

  <AppLayout :user="user" :notif-counts="notifCounts" :menu="menu">
    <div class="space-y-4">
      <RosterBreadcrumb :trail="[{ label: 'Roster' }]" />

      <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
          <h1 class="text-xl font-bold tracking-tight text-slate-800">Roster V2</h1>
          <p class="mt-0.5 text-xs text-slate-500">Manajemen roster dan alokasi manpower</p>
        </div>
        <div v-if="canManage" class="flex flex-wrap gap-2">
          <button type="button" class="flex items-center gap-1.5 rounded-xl border border-blue-300 bg-white px-3 py-2 text-xs font-semibold text-blue-600 hover:bg-blue-50" @click="isSheetOpen = true">
            <FileSpreadsheet class="h-4 w-4" />Import Spreadsheet
          </button>
          <button type="button" class="flex items-center gap-1.5 rounded-xl bg-blue-600 px-3 py-2 text-xs font-semibold text-white hover:bg-blue-700" @click="isCreateOpen = true">
            <Plus class="h-4 w-4" />Buat Roster
          </button>
        </div>
      </div>

      <RosterSummaryCards :summary="summary" />
      <RosterFilterBar :filters="filters" :groups="groups" />

      <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div v-if="!rosters.length" class="px-4 py-12 text-center text-slate-500">
          <CalendarX class="mx-auto h-10 w-10 text-slate-300" />
          <p class="mb-3 mt-2 text-sm">{{ emptyMessage }}</p>
          <button v-if="canManage" type="button" class="inline-flex items-center gap-1.5 rounded-xl bg-blue-600 px-3 py-2 text-xs font-semibold text-white hover:bg-blue-700" @click="isCreateOpen = true">
            <Plus class="h-4 w-4" />Buat Roster
          </button>
        </div>
        <RosterListTable v-else :rosters="rosters" :can-manage="canManage" @duplicate="duplicatingRoster = $event" @edit="editingRoster = $event" @archive="archivingRoster = $event" />
      </div>

      <ArchivedRosterCards v-if="archivedRosters.length" :rosters="archivedRosters" :can-manage="canManage" @duplicate="duplicatingRoster = $event" />
    </div>

    <template v-if="canManage">
      <RosterFormModal :open="isCreateOpen" :me="me" :groups="groups" :year-now="yearNow" @close="isCreateOpen = false" />
      <RosterFormModal :open="isEditOpen" :roster="editingRoster" :me="me" :groups="groups" :year-now="yearNow" @close="editingRoster = null" />
      <DuplicateRosterModal :roster="duplicatingRoster" :groups="groups" :year-now="yearNow" @close="duplicatingRoster = null" />
      <ArchiveRosterModal :roster="archivingRoster" @close="archivingRoster = null" />
      <SheetImportModal :open="isSheetOpen" :rosters="sheetRosters" @close="isSheetOpen = false" />
    </template>
  </AppLayout>
</template>
