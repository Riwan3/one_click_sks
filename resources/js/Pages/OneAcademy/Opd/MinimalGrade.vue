<script setup>
import { ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import { FileSpreadsheet, Pencil, Plus, Search, Trash2 } from 'lucide-vue-next';
import AppLayout from '@/Layouts/AppLayout.vue';

import MinimalGradeFormModal from './Partials/MinimalGradeFormModal.vue';
import MinimalGradeImportModal from './Partials/MinimalGradeImportModal.vue';

const props = defineProps({
  user: Object,
  notifCounts: Object,
  menu: Array,
  search: String,
  rows: Array,
});

const BASE_URL = '/one-academy/opd/master-grade/minimal-grade';

const keyword = ref(props.search || '');
const editingRow = ref(null);
const isFormOpen = ref(false);
const isImportOpen = ref(false);

const handleSearch = () => router.get(BASE_URL, { q: keyword.value || undefined }, { preserveState: true });

const openForm = (row = null) => {
  editingRow.value = row;
  isFormOpen.value = true;
};

const handleDelete = (row) => {
  if (!confirm(`Hapus data unit ${row.unit}?`)) return;
  router.delete(`${BASE_URL}/${row.id}`, { preserveScroll: true });
};
</script>

<template>
  <AppLayout :user="user" :notif-counts="notifCounts" :menu="menu">
    <Head title="Minimal Grade - Master Grade - One Academy" />

    <div class="space-y-4 p-6">
      <div class="flex flex-wrap items-center justify-between gap-2">
        <div>
          <h1 class="text-xl font-bold text-slate-900">Minimal Grade</h1>
          <p class="text-xs text-slate-500">OPD &middot; Master Grade</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
          <form class="relative" @submit.prevent="handleSearch">
            <Search class="absolute left-3 top-2 h-4 w-4 text-slate-400" />
            <input v-model="keyword" type="text" placeholder="Cari unit / cluster..." class="w-52 rounded-xl border border-slate-300 bg-slate-50 py-1.5 pl-9 pr-3 text-xs outline-none focus:ring-2 focus:ring-blue-500">
          </form>
          <button type="button" class="flex items-center gap-1.5 rounded-xl bg-emerald-600 px-4 py-2 text-xs font-bold text-white hover:bg-emerald-500" @click="isImportOpen = true">
            <FileSpreadsheet class="h-4 w-4" /> Import Spreadsheet
          </button>
          <button type="button" class="flex items-center gap-1.5 rounded-xl bg-blue-600 px-4 py-2 text-xs font-bold text-white hover:bg-blue-500" @click="openForm()">
            <Plus class="h-4 w-4" /> Tambah
          </button>
        </div>
      </div>

      <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
        <div class="overflow-x-auto">
          <table class="w-full border-collapse text-sm">
            <thead>
              <tr class="bg-lime-200 text-slate-900">
                <th class="w-14 border border-slate-300 px-3 py-1.5">NO</th>
                <th class="border border-slate-300 px-3 py-1.5">UNIT</th>
                <th class="border border-slate-300 px-3 py-1.5">GRADE MIN-MAX</th>
                <th class="border border-slate-300 px-3 py-1.5">GRADE MIN</th>
                <th class="border border-slate-300 px-3 py-1.5">CLUSTER UNIT</th>
                <th class="w-28 border border-slate-300 px-3 py-1.5">AKSI</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(row, index) in rows" :key="row.id">
                <td class="border border-slate-300 px-3 py-1.5 text-center">{{ index + 1 }}</td>
                <td class="border border-slate-300 px-3 py-1.5 font-semibold">{{ row.unit }}</td>
                <td class="border border-slate-300 px-3 py-1.5 text-center">{{ row.grade_min_max }}</td>
                <td class="border border-slate-300 px-3 py-1.5 text-center">{{ row.grade_min }}</td>
                <td class="border border-slate-300 px-3 py-1.5">{{ row.cluster_unit }}</td>
                <td class="border border-slate-300 px-3 py-1.5 text-center">
                  <button type="button" class="rounded p-1.5 text-blue-600 hover:bg-blue-50" title="Ubah" @click="openForm(row)"><Pencil class="h-4 w-4" /></button>
                  <button type="button" class="rounded p-1.5 text-red-600 hover:bg-red-50" title="Hapus" @click="handleDelete(row)"><Trash2 class="h-4 w-4" /></button>
                </td>
              </tr>
              <tr v-if="!rows.length">
                <td colspan="6" class="border border-slate-300 py-8 text-center text-slate-400">
                  {{ search ? 'Tidak ada data yang cocok dengan pencarian.' : 'Belum ada data. Klik "Tambah" atau "Import Spreadsheet" untuk mengisi.' }}
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        <p class="mt-2 text-xs text-slate-500">{{ rows.length }} data</p>
      </div>
    </div>

    <MinimalGradeFormModal :open="isFormOpen" :row="editingRow" @close="isFormOpen = false" />
    <MinimalGradeImportModal :open="isImportOpen" @close="isImportOpen = false" />
  </AppLayout>
</template>
