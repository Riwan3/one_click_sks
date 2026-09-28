<script setup>
import { computed, ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { BarChart2, CloudDownload, Network, Plus, Search, TrendingUp } from 'lucide-vue-next';

import DataTable from './Partials/DataTable.vue';
import PaginationBar from './Partials/PaginationBar.vue';
import SyncModal from './Partials/SyncModal.vue';
import AddGradeModal from './Partials/AddGradeModal.vue';
import AddVersatilityModal from './Partials/AddVersatilityModal.vue';

const props = defineProps({
  user: Object,
  notifCounts: Object,
  menu: Array,
  tab: String,
  search: String,
  page: Number,
  perPage: Number,
  totalRows: Number,
  rows: Array,
  columns: Array,
  versalityGroups: Object,
  pageFilterValues: Object,
  activeFilters: Object,
  sheetUrls: Object,
});

const searchQuery = ref(props.search || '');
const showAddGrade = ref(false);
const showAddVersatility = ref(false);
const showSync = ref(false);

const TABS = [
  { key: 'grade', label: 'Grade dan Jabatan', icon: BarChart2 },
  { key: 'versality', label: 'Versatility', icon: Network },
  { key: 'monitoring', label: 'Monitoring', icon: TrendingUp },
];

const stickyCount = computed(() => (props.tab === 'versality' ? 4 : 3));
const leftAlignKeys = computed(() => {
  if (props.tab === 'grade') return ['nama', 'posisi', 'sertifikasi', 'remarks_grade'];
  if (props.tab === 'versality') return ['nama', 'jabatan'];
  return ['type_unit', 'ket'];
});
const badgeKeys = computed(() => {
  if (props.tab === 'grade') {
    return { eg: 'bg-slate-100 text-slate-700', efektif_grade: 'bg-blue-100 text-blue-700', grade_now: 'bg-emerald-100 text-emerald-700', suite: 'bg-emerald-100 text-emerald-700' };
  }
  if (props.tab === 'versality') return { versality: 'bg-slate-100 text-slate-700' };
  return {};
});

function navigate(params) {
  router.get('/one-academy/monitoring-grade', {
    tab: props.tab,
    q: searchQuery.value || undefined,
    per: props.perPage,
    ...Object.fromEntries(Object.entries(props.activeFilters).map(([k, v]) => [`filter_${k}`, v.join(',')])),
    ...params,
  }, { preserveState: true, preserveScroll: true });
}

function goTab(key) {
  router.get('/one-academy/monitoring-grade', { tab: key }, { preserveState: false });
}

function handleSearch() {
  navigate({ page: 1 });
}

function handleFilter({ key, values }) {
  const filters = { ...Object.fromEntries(Object.entries(props.activeFilters).map(([k, v]) => [`filter_${k}`, v.join(',')])) };
  if (values.length) filters[`filter_${key}`] = values.join(',');
  else delete filters[`filter_${key}`];
  router.get('/one-academy/monitoring-grade', { tab: props.tab, q: searchQuery.value || undefined, per: props.perPage, page: 1, ...filters }, { preserveState: true, preserveScroll: true });
}

function changePage(page) {
  navigate({ page });
}

function changePerPage(per) {
  navigate({ per, page: 1 });
}

const hasActiveFilters = computed(() => Object.keys(props.activeFilters).length > 0 || !!props.search);

function resetFilters() {
  router.get('/one-academy/monitoring-grade', { tab: props.tab }, { preserveState: false });
}
</script>

<template>
  <AppLayout :user="user" :notif-counts="notifCounts" :menu="menu">
    <Head title="Monitoring Grade & Jabatan - One Academy" />

    <div class="space-y-6 p-6">
      <!-- Header Banner -->
      <div class="flex flex-wrap items-center justify-between gap-4 rounded-2xl bg-gradient-to-r from-slate-900 via-blue-900 to-indigo-900 p-6 text-white shadow-xl">
        <div>
          <h1 class="flex items-center gap-3 text-2xl font-bold">
            <BarChart2 class="h-8 w-8 text-blue-400" />
            Monitoring Grade & Jabatan
          </h1>
          <p class="mt-1 text-sm text-slate-300">
            Pantau perkembangan grade, jabatan, dan versatility karyawan produksi secara mandiri.
          </p>
        </div>
        <div class="flex flex-wrap gap-2">
          <button v-if="tab !== 'monitoring'" type="button" class="flex items-center gap-2 rounded-xl border border-white/30 px-4 py-2 text-sm font-semibold text-white shadow-lg transition hover:bg-white/10" @click="showSync = true">
            <CloudDownload class="h-4 w-4" /> Sync dari Google Sheets
          </button>
          <button v-if="tab === 'grade'" type="button" class="flex items-center gap-2 rounded-xl bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-lg transition hover:bg-blue-500" @click="showAddGrade = true">
            <Plus class="h-5 w-5" /> Tambah Data Grade
          </button>
          <button v-if="tab === 'versality'" type="button" class="flex items-center gap-2 rounded-xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-lg transition hover:bg-indigo-500" @click="showAddVersatility = true">
            <Plus class="h-5 w-5" /> Tambah Data Versatility
          </button>
        </div>
      </div>

      <!-- Navigation Tabs -->
      <div class="flex flex-wrap gap-3 border-b border-slate-200 pb-3 dark:border-slate-800">
        <button
          v-for="t in TABS"
          :key="t.key"
          type="button"
          class="flex items-center gap-2 rounded-full px-5 py-2.5 text-sm font-semibold transition"
          :class="tab === t.key ? 'bg-blue-600 text-white shadow-md' : 'border border-slate-200 bg-white text-slate-600 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300'"
          @click="goTab(t.key)"
        >
          <component :is="t.icon" class="h-4 w-4" /> {{ t.label }}
        </button>
      </div>

      <!-- Search Bar -->
      <div class="flex flex-wrap items-center justify-between gap-4 rounded-xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <form class="flex w-full max-w-md gap-2" @submit.prevent="handleSearch">
          <div class="relative w-full">
            <Search class="absolute left-3 top-2.5 h-4 w-4 text-slate-400" />
            <input v-model="searchQuery" type="text" placeholder="Cari nama, NRP, posisi..." class="w-full rounded-xl border border-slate-300 bg-slate-50 py-2 pl-9 pr-4 text-sm outline-none focus:ring-2 focus:ring-blue-500 dark:border-slate-700 dark:bg-slate-800">
          </div>
          <button type="submit" class="rounded-xl bg-slate-800 px-4 py-2 text-sm font-semibold text-white transition hover:bg-slate-700">Cari</button>
          <button v-if="hasActiveFilters" type="button" class="rounded-xl border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-600 dark:border-slate-700" @click="resetFilters">Reset</button>
        </form>
        <span class="text-xs text-slate-500">Total Data: {{ totalRows }}</span>
      </div>

      <!-- Data Table -->
      <div class="rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <DataTable
          :columns="columns"
          :groups="versalityGroups"
          :rows="rows"
          :row-offset="(page - 1) * perPage"
          :page-filter-values="pageFilterValues"
          :active-filters="activeFilters"
          :sticky-count="stickyCount"
          :left-align-keys="leftAlignKeys"
          :badge-keys="badgeKeys"
          @filter="handleFilter"
        />
        <PaginationBar :page="page" :per-page="perPage" :total="totalRows" @change-page="changePage" @change-per-page="changePerPage" />
      </div>
    </div>

    <AddGradeModal v-if="tab === 'grade'" :show="showAddGrade" :columns="columns" @close="showAddGrade = false" />
    <AddVersatilityModal v-if="tab === 'versality'" :show="showAddVersatility" :columns="columns" :groups="versalityGroups" @close="showAddVersatility = false" />
    <SyncModal :show="showSync" :target="tab === 'versality' ? 'versality' : 'grade'" :current-url="tab === 'versality' ? sheetUrls.versality : sheetUrls.grade" @close="showSync = false" />
  </AppLayout>
</template>
