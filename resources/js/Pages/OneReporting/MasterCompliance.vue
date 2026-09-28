<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { Map as MapIcon, Truck, ListChecks, Plus, Pencil, Trash2, FileText } from 'lucide-vue-next';
import { confirmDialog } from '@/utils/confirm';
import MasterFormModal from './Partials/Master/MasterFormModal.vue';

const props = defineProps({
  user: Object,
  notifCounts: Object,
  menu: Array,
  tab: { type: String, default: 'area' },
  rows: { type: Array, default: () => [] },
  areaOptions: { type: Array, default: () => [] },
  kategoriOptions: { type: Array, default: () => [] },
  documentOptions: { type: Array, default: () => [] },
});

const TABS = {
  area: { label: 'Master Area', icon: MapIcon, noun: 'area', add: 'Tambah Area', empty: 'Belum ada data area' },
  unit: { label: 'Master Unit/Lokasi', icon: Truck, noun: 'unit', add: 'Tambah Unit', empty: 'Belum ada data unit' },
  aktivitas: { label: 'Master Aktivitas', icon: ListChecks, noun: 'aktivitas', add: 'Tambah Aktivitas', empty: 'Belum ada data aktivitas' },
};

const COLUMNS = {
  area: [{ key: 'nama', label: 'Nama Area', bold: true }, { key: 'keterangan', label: 'Keterangan', muted: true }],
  unit: [
    { key: 'kode', label: 'Kode Unit', badge: 'bg-blue-50 text-blue-700' },
    { key: 'nama', label: 'Nama Unit/Lokasi', bold: true },
    { key: 'area', label: 'Area', muted: true },
    { key: 'keterangan', label: 'Keterangan', muted: true },
  ],
  aktivitas: [
    { key: 'nama', label: 'Nama Aktivitas', bold: true },
    { key: 'kategori', label: 'Kategori', badge: 'bg-violet-50 text-violet-700' },
    { key: 'dokumen_urls', label: 'Keterangan', documents: true },
  ],
};

const formModal = ref(null);

const current = computed(() => TABS[props.tab]);
const columns = computed(() => COLUMNS[props.tab]);

const handleDelete = async (row) => {
  if (!(await confirmDialog(`Hapus ${current.value.noun} ini?`))) return;
  router.delete(`/one-reporting/master/${props.tab}/${row.id}`, { preserveScroll: true });
};
</script>

<template>
  <Head title="Master Compliance - One Click" />

  <AppLayout :user="user" :notif-counts="notifCounts" :menu="menu">
    <div class="mx-auto max-w-[1100px] space-y-4">
      <div>
        <nav class="mb-1 text-xs text-slate-500">
          <Link href="/one-reporting/dashboard" class="text-blue-500">Dashboard</Link> / Operation Compliance Report / <span class="text-slate-400">Master Compliance</span>
        </nav>
        <h1 class="text-xl font-bold text-slate-800">Master Compliance</h1>
        <p class="text-[13px] text-slate-500">Kelola data master area, unit/lokasi, dan aktivitas</p>
      </div>

      <nav class="flex flex-wrap gap-1.5">
        <Link
          v-for="(item, key) in TABS"
          :key="key"
          :href="`/one-reporting/master?tab=${key}`"
          :class="['inline-flex items-center gap-1 rounded-full border-[1.5px] px-4 py-1.5 text-sm font-semibold transition', tab === key ? 'border-blue-500 bg-blue-500 text-white' : 'border-slate-200 text-slate-600 hover:bg-slate-100']"
        >
          <component :is="item.icon" class="h-4 w-4" />{{ item.label }}
        </Link>
      </nav>

      <div class="flex items-center justify-between">
        <span class="text-[13px] text-slate-500">{{ rows.length }} {{ current.noun }} terdaftar</span>
        <button type="button" class="inline-flex items-center gap-1 rounded-lg bg-red-600 px-3 py-1.5 text-sm font-semibold text-white hover:bg-red-700" @click="formModal = { row: null }">
          <Plus class="h-4 w-4" />{{ current.add }}
        </button>
      </div>

      <div class="overflow-hidden rounded-2xl bg-white shadow-md">
        <div class="overflow-x-auto">
          <table class="w-full min-w-[640px] text-left text-[13px] text-slate-700">
            <thead class="whitespace-nowrap border-b-2 border-slate-100 text-xs font-bold uppercase tracking-wide text-slate-500">
              <tr>
                <th class="py-2 pl-4 pr-2">#</th>
                <th v-for="col in columns" :key="col.key" class="px-2 py-2">{{ col.label }}</th>
                <th class="px-2 py-2 text-center">Status</th>
                <th class="py-2 pl-2 pr-4 text-center">Aksi</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
              <tr v-if="rows.length === 0">
                <td :colspan="columns.length + 3" class="py-10 text-center text-slate-500">{{ current.empty }}</td>
              </tr>
              <tr v-for="(row, idx) in rows" :key="row.id" class="hover:bg-slate-50">
                <td class="py-2.5 pl-4 pr-2 text-slate-400">{{ idx + 1 }}</td>
                <td v-for="col in columns" :key="col.key" :class="['px-2 py-2.5', col.bold && 'font-semibold', col.muted && 'text-slate-500']">
                  <template v-if="col.documents">
                    <span v-if="row.dokumen_urls.length === 0" class="text-slate-500">—</span>
                    <a
                      v-for="(url, docIdx) in row.dokumen_urls"
                      :key="url"
                      :href="url"
                      target="_blank"
                      class="mb-1 mr-1 inline-flex items-center gap-1 rounded-md border border-slate-300 px-2 py-0.5 text-[11px] text-slate-600 hover:bg-slate-50"
                    >
                      <FileText class="h-3 w-3" />Lihat Dokumen {{ row.dokumen_urls.length > 1 ? docIdx + 1 : '' }}
                    </a>
                  </template>
                  <span v-else-if="col.badge && row[col.key]" :class="['rounded px-2 py-0.5 text-xs font-semibold', col.badge]">{{ row[col.key] }}</span>
                  <span v-else>{{ row[col.key] || '—' }}</span>
                </td>
                <td class="px-2 py-2.5 text-center">
                  <span :class="['rounded-full px-2.5 py-1 text-xs', row.aktif ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800']">{{ row.aktif ? 'Aktif' : 'Nonaktif' }}</span>
                </td>
                <td class="whitespace-nowrap py-2.5 pl-2 pr-4 text-center">
                  <button type="button" title="Edit" class="mr-1 rounded-md border border-blue-500 px-2 py-1 text-blue-600 hover:bg-blue-50" @click="formModal = { row }"><Pencil class="h-3 w-3" /></button>
                  <button type="button" title="Hapus" class="rounded-md border border-red-500 px-2 py-1 text-red-600 hover:bg-red-50" @click="handleDelete(row)"><Trash2 class="h-3 w-3" /></button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <MasterFormModal
      v-if="formModal"
      :type="tab"
      :row="formModal.row"
      :area-options="areaOptions"
      :kategori-options="kategoriOptions"
      :document-options="documentOptions"
      @closed="formModal = null"
    />
  </AppLayout>
</template>
