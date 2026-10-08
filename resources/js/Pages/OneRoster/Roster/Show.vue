<script setup>
import { computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import { LayoutGrid, List, CloudUpload } from 'lucide-vue-next';
import AppLayout from '@/Layouts/AppLayout.vue';
import RosterBreadcrumb from '../Partials/RosterBreadcrumb.vue';
import RosterHeaderActions from './Partials/RosterHeaderActions.vue';
import RosterInfoCard from './Partials/RosterInfoCard.vue';
import ChangelogPanel from './Partials/ChangelogPanel.vue';
import MatrixTab from './Partials/Matrix/MatrixTab.vue';
import ListTab from './Partials/List/ListTab.vue';
import UploadTab from './Partials/Upload/UploadTab.vue';

const props = defineProps({
  user: Object,
  notifCounts: Object,
  menu: Array,
  roster: { type: Object, required: true },
  totalDetail: { type: Number, default: 0 },
  tab: { type: String, required: true },
  canManage: { type: Boolean, default: false },
  canEdit: { type: Boolean, default: false },
  sectionsReady: { type: Boolean, default: true },
  me: { type: Object, required: true },
  shifts: { type: Array, default: () => [] },
  months: { type: Array, default: () => [] },
  changelog: { type: Object, default: null },
  list: { type: Object, default: null },
  matrix: { type: Object, default: null },
  ctxQuery: { type: String, default: '' },
  upload: { type: Object, default: null },
  step: { type: Number, default: 3 },
});

const TABS = [
  { key: 'matrix', label: 'Matriks', icon: LayoutGrid },
  { key: 'list', label: 'Daftar Detail', icon: List },
  { key: 'upload', label: 'Upload Roster', icon: CloudUpload },
];

const tabUrl = (key) => `/one-roster/roster/${props.roster.id}?tab=${key}`;
const tabClass = (key) => (props.tab === key ? 'border-blue-600 text-blue-600' : 'border-transparent text-slate-500 hover:text-slate-700');

const needsSections = computed(() => props.tab !== 'list' && !props.sectionsReady);
</script>

<template>
  <Head title="Detail Roster V2 - One-Roster" />

  <AppLayout :user="user" :notif-counts="notifCounts" :menu="menu">
    <div class="space-y-4">
      <RosterBreadcrumb :trail="[{ label: 'Roster', href: '/one-roster/roster' }, { label: 'Detail' }]" />

      <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
          <h1 class="text-xl font-bold tracking-tight text-slate-800">Roster V2</h1>
          <p class="mt-0.5 text-xs text-slate-500">Manajemen roster dan alokasi manpower</p>
        </div>
        <RosterHeaderActions :roster="roster" :can-edit="canEdit" :can-manage="canManage" :me="me" :months="months" />
      </div>

      <RosterInfoCard :roster="roster" :total-detail="totalDetail" />

      <nav class="flex gap-1 overflow-x-auto border-b border-slate-200">
        <Link v-for="item in TABS" :key="item.key" :href="tabUrl(item.key)" :class="['flex items-center gap-1.5 whitespace-nowrap border-b-2 px-3 py-2 text-sm font-semibold', tabClass(item.key)]">
          <component :is="item.icon" class="h-4 w-4" />{{ item.label }}
        </Link>
      </nav>

      <p v-if="needsSections" class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">Tabel section belum tersedia. Jalankan migration One-Roster di server, lalu muat ulang halaman ini.</p>
      <template v-else-if="tab === 'matrix'">
        <MatrixTab v-if="matrix" :roster="roster" :matrix="matrix" :ctx-query="ctxQuery" :can-edit="canEdit" />
        <p v-else class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">Matriks tidak dapat dimuat. Silakan coba lagi.</p>
      </template>
      <template v-else-if="tab === 'upload'">
        <UploadTab v-if="upload" :roster="roster" :upload="upload" :step="step" />
        <p v-else class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">Data upload tidak dapat dimuat. Silakan coba lagi.</p>
      </template>
      <ListTab v-else-if="list" :roster="roster" :list="list" :shifts="shifts" :can-edit="canEdit" />

      <ChangelogPanel v-if="changelog" :changelog="changelog" :can-manage="canManage" />
    </div>
  </AppLayout>
</template>
