<script setup>
import { computed, ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import { ArrowLeft, Archive, CalendarCheck2, Eye, FileUp, Pencil, SendHorizontal } from 'lucide-vue-next';
import { useRosterAction } from '@/composables/useRosterAction';
import RosterFormModal from './Modals/RosterFormModal.vue';
import VisibilityModal from './Modals/VisibilityModal.vue';
import ArchiveRosterModal from './Modals/ArchiveRosterModal.vue';
import ImportExcelModal from './Modals/ImportExcelModal.vue';

const props = defineProps({
  roster: { type: Object, required: true },
  canEdit: { type: Boolean, default: false },
  canManage: { type: Boolean, default: false },
  me: { type: Object, required: true },
  months: { type: Array, default: () => [] },
});

// Tombol Import Excel disembunyikan seperti V1 (data roster diisi lewat Import Spreadsheet di halaman daftar); ubah ke true untuk memunculkannya lagi.
const SHOW_EXCEL_IMPORT = false;
const BUTTON = 'flex items-center gap-1.5 rounded-xl border px-3 py-2 text-xs font-semibold';
const SYNC_CONFIRM = 'Terapkan cuti aktual dari MONCU ke roster ini? Tanggal cuti (AWAL s/d AKHIR) menjadi CT dan tanggal INDUKSI menjadi ID untuk karyawan MONCU yang ada di roster ini. PLAN tidak dipakai.';
const PUBLISH_CONFIRM = 'Publish roster ini? Roster dianggap final, tetapi isinya tetap bisa disesuaikan bila ada perubahan.';

const { isBusy, perform } = useRosterAction();

const isEditOpen = ref(false);
const isVisibilityOpen = ref(false);
const isArchiveOpen = ref(false);
const isImportOpen = ref(false);

const base = computed(() => `/one-roster/roster/${props.roster.id}`);
const isDraft = computed(() => props.roster.status === 'draft');
const canArchive = computed(() => props.canManage && props.roster.status !== 'archived');

const handleSync = () => perform({ url: `${base.value}/moncu-sync`, confirm: SYNC_CONFIRM, confirmOptions: { confirmButtonText: 'Terapkan' } });
const handlePublish = () => perform({ url: `${base.value}/status`, data: { status: 'published' }, confirm: PUBLISH_CONFIRM, confirmOptions: { confirmButtonText: 'Publish', confirmButtonColor: '#16a34a' } });
</script>

<template>
  <div class="flex flex-wrap gap-2">
    <Link href="/one-roster/roster" :class="[BUTTON, 'border-slate-300 bg-white text-slate-600 hover:bg-slate-50']"><ArrowLeft class="h-4 w-4" />Kembali</Link>
    <template v-if="canEdit">
      <button v-if="SHOW_EXCEL_IMPORT" type="button" :class="[BUTTON, 'border-blue-300 bg-white text-blue-600 hover:bg-blue-50']" @click="isImportOpen = true"><FileUp class="h-4 w-4" />Import Excel</button>
      <button type="button" :disabled="isBusy" :class="[BUTTON, 'border-blue-300 bg-white text-blue-600 hover:bg-blue-50 disabled:opacity-60']" @click="handleSync"><CalendarCheck2 class="h-4 w-4" />Terapkan Cuti (MONCU)</button>
      <button type="button" :class="[BUTTON, 'border-slate-300 bg-white text-slate-600 hover:bg-slate-50']" @click="isEditOpen = true"><Pencil class="h-4 w-4" />Edit</button>
      <button type="button" title="Atur bulan yang boleh dilihat operator / helper di Share Roster" :class="[BUTTON, 'border-slate-300 bg-white text-slate-600 hover:bg-slate-50']" @click="isVisibilityOpen = true"><Eye class="h-4 w-4" />Visibilitas</button>
      <button v-if="isDraft" type="button" :disabled="isBusy" :class="[BUTTON, 'border-emerald-600 bg-emerald-600 text-white hover:bg-emerald-700 disabled:opacity-60']" @click="handlePublish"><SendHorizontal class="h-4 w-4" />Publish</button>
    </template>
    <button v-if="canArchive" type="button" :class="[BUTTON, 'border-red-300 bg-white text-red-600 hover:bg-red-50']" @click="isArchiveOpen = true"><Archive class="h-4 w-4" />Archive</button>

    <template v-if="canEdit">
      <RosterFormModal :open="isEditOpen" :roster="roster" :me="me" @close="isEditOpen = false" />
      <VisibilityModal :open="isVisibilityOpen" :roster="roster" :months="months" @close="isVisibilityOpen = false" />
      <ImportExcelModal v-if="SHOW_EXCEL_IMPORT" :open="isImportOpen" :roster-id="roster.id" @close="isImportOpen = false" />
    </template>
    <ArchiveRosterModal :roster="isArchiveOpen ? roster : null" @close="isArchiveOpen = false" />
  </div>
</template>
