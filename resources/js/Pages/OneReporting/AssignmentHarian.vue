<script setup>
import { ref, reactive, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { FileDown, Upload, Plus, Search, X, Calendar, Gauge, Inbox, Pencil, Trash2 } from 'lucide-vue-next';
import { confirmDialog } from '@/utils/confirm';
import { ASSIGNMENT_STATUS_LABEL, complianceStatusOf, formatLongDate, formatSlashDate } from '@/utils/odrCompliance';
import AssignmentFormModal from './Partials/Assignment/AssignmentFormModal.vue';
import ImportAssignmentModal from './Partials/Assignment/ImportAssignmentModal.vue';

const props = defineProps({
  user: Object,
  notifCounts: Object,
  menu: Array,
  filters: { type: Object, required: true },
  rows: { type: Array, default: () => [] },
  areaOptions: { type: Array, default: () => [] },
  unitOptions: { type: Array, default: () => [] },
  aktivitasOptions: { type: Array, default: () => [] },
  pengawasOptions: { type: Array, default: () => [] },
  shiftOptions: { type: Array, default: () => [] },
});

const BASE_URL = '/one-reporting/daily/assignment';
const TEMPLATE_URL = `${BASE_URL}/template`;

const form = reactive({ tanggal: props.filters.tanggal, shift: props.filters.shift, area: props.filters.area, pengawas: props.filters.pengawas });
const formModal = ref(null);
const isImportOpen = ref(false);

const dashboardUrl = computed(() => {
  const query = new URLSearchParams({ tanggal: props.filters.tanggal, ...(props.filters.shift ? { shift: props.filters.shift } : {}) });
  return `/one-reporting/dashboard?${query}`;
});

const fieldClass = 'rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-sm focus:border-blue-400 focus:ring-2 focus:ring-blue-100';
const labelClass = 'mb-1 block text-xs font-semibold text-slate-500';

const handleFilter = () => {
  const filled = Object.fromEntries(Object.entries(form).filter(([, v]) => v !== ''));
  router.get(BASE_URL, filled, { preserveScroll: true });
};

const handleDelete = async (row) => {
  if (await confirmDialog('Hapus data ini?')) router.delete(`${BASE_URL}/${row.id}`, { preserveScroll: true });
};
</script>

<template>
  <Head title="Assignment Harian - One Click" />

  <AppLayout :user="user" :notif-counts="notifCounts" :menu="menu">
    <div class="mx-auto max-w-[1300px] space-y-4">
      <div class="flex flex-wrap items-center justify-between gap-2">
        <div>
          <nav class="mb-1 text-xs text-slate-500">
            <Link href="/one-reporting/dashboard" class="text-blue-500">Dashboard</Link> / Operation Compliance Report / <span class="text-slate-400">Daily Assignment</span>
          </nav>
          <h1 class="text-xl font-bold text-slate-800">Assignment Harian</h1>
          <p class="text-[13px] text-slate-500">Kelola area dan pengawas yang bertugas setiap hari</p>
        </div>
        <div class="flex flex-wrap gap-2">
          <a :href="TEMPLATE_URL" class="inline-flex items-center gap-1 rounded-lg border border-green-600 px-3 py-1.5 text-sm text-green-700 hover:bg-green-50"><FileDown class="h-4 w-4" />Download Template</a>
          <button type="button" class="inline-flex items-center gap-1 rounded-lg border border-blue-600 px-3 py-1.5 text-sm text-blue-600 hover:bg-blue-50" @click="isImportOpen = true"><Upload class="h-4 w-4" />Upload Excel</button>
          <button type="button" class="inline-flex items-center gap-1 rounded-lg bg-red-600 px-3 py-1.5 text-sm font-semibold text-white hover:bg-red-700" @click="formModal = { assignment: null }"><Plus class="h-4 w-4" />Tambah Assignment</button>
        </div>
      </div>

      <form class="flex flex-wrap items-end gap-2 rounded-xl border border-slate-100 bg-slate-50 px-4 py-3.5" @submit.prevent="handleFilter">
        <div><label :class="labelClass">TANGGAL</label><input v-model="form.tanggal" type="date" :class="[fieldClass, 'w-40']" /></div>
        <div>
          <label :class="labelClass">SHIFT</label>
          <select v-model="form.shift" :class="[fieldClass, 'w-32']">
            <option value="">Semua Shift</option>
            <option v-for="s in shiftOptions" :key="s" :value="s">{{ s }}</option>
          </select>
        </div>
        <div>
          <label :class="labelClass">AREA</label>
          <select v-model="form.area" :class="[fieldClass, 'w-40']">
            <option value="">Semua Area</option>
            <option v-for="a in areaOptions" :key="a" :value="a">{{ a }}</option>
          </select>
        </div>
        <div><label :class="labelClass">PENGAWAS</label><input v-model="form.pengawas" type="text" placeholder="Cari pengawas..." :class="[fieldClass, 'w-44']" /></div>
        <button type="submit" class="inline-flex items-center gap-1 rounded-lg bg-blue-600 px-3 py-1.5 text-sm font-semibold text-white hover:bg-blue-700"><Search class="h-3.5 w-3.5" />Filter</button>
        <Link :href="BASE_URL" class="inline-flex items-center gap-1 rounded-lg border border-slate-400 px-3 py-1.5 text-sm font-semibold text-slate-600 hover:bg-white"><X class="h-3.5 w-3.5" />Reset</Link>
      </form>

      <div class="overflow-hidden rounded-2xl bg-white shadow-md">
        <div class="flex items-center justify-between border-b border-slate-100 bg-slate-50 px-4 py-2 text-[13px] font-bold text-slate-600">
          <span class="inline-flex items-center gap-1"><Calendar class="h-3.5 w-3.5" />{{ formatLongDate(filters.tanggal) }} · <span class="text-red-600">{{ rows.length }}</span> data</span>
          <Link :href="dashboardUrl" class="inline-flex items-center gap-1 rounded-lg border border-red-500 px-2.5 py-1 text-xs font-normal text-red-600 hover:bg-red-50"><Gauge class="h-3.5 w-3.5" />Lihat Dashboard</Link>
        </div>
        <div class="overflow-x-auto">
          <table class="w-full min-w-[900px] text-left text-[13px] text-slate-700">
            <thead class="whitespace-nowrap border-b-2 border-slate-100 text-xs font-bold uppercase tracking-wide text-slate-500">
              <tr>
                <th class="w-10 py-2 pl-4 pr-2">#</th>
                <th class="px-2 py-2">Tanggal</th>
                <th class="px-2 py-2">Shift</th>
                <th class="px-2 py-2">Area</th>
                <th class="px-2 py-2">Unit</th>
                <th class="px-2 py-2">Nama Pengawas</th>
                <th class="px-2 py-2">Keterangan</th>
                <th class="px-2 py-2 text-center">Status</th>
                <th class="py-2 pl-2 pr-4 text-center">Aksi</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
              <tr v-if="rows.length === 0">
                <td colspan="9" class="py-10 text-center">
                  <Inbox class="mx-auto h-8 w-8 text-slate-400" />
                  <div class="mt-2 text-sm text-slate-500">Belum ada data untuk tanggal ini</div>
                  <button type="button" class="mt-2 inline-flex items-center gap-1 rounded-lg bg-red-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-red-700" @click="formModal = { assignment: null }">
                    <Plus class="h-3.5 w-3.5" />Tambah Assignment
                  </button>
                </td>
              </tr>
              <tr v-for="(row, idx) in rows" :key="row.id" class="hover:bg-slate-50">
                <td class="py-2.5 pl-4 pr-2 text-slate-400">{{ idx + 1 }}</td>
                <td class="whitespace-nowrap px-2 py-2.5">{{ formatSlashDate(row.tanggal) }}</td>
                <td class="px-2 py-2.5"><span class="rounded bg-amber-100 px-2 py-0.5 text-xs font-semibold text-amber-800">{{ row.shift }}</span></td>
                <td class="px-2 py-2.5"><span class="rounded bg-blue-50 px-2 py-0.5 text-xs font-semibold text-blue-700">{{ row.area }}</span></td>
                <td class="px-2 py-2.5 text-slate-500">{{ row.unit }}</td>
                <td class="px-2 py-2.5 font-semibold">{{ row.pengawas }}</td>
                <td class="max-w-[240px] px-2 py-2.5">
                  <span v-if="row.tasks.length === 0" class="text-slate-400">—</span>
                  <div v-else class="flex flex-col gap-1">
                    <span v-for="(task, ti) in row.tasks" :key="ti" class="flex items-start gap-1.5 text-xs">
                      <span class="flex h-[18px] w-[18px] shrink-0 items-center justify-center rounded-full bg-indigo-100 text-[10px] font-bold text-indigo-800">{{ ti + 1 }}</span>
                      <span class="max-w-[195px] truncate" :title="task">{{ task }}</span>
                    </span>
                  </div>
                </td>
                <td class="px-2 py-2.5 text-center">
                  <span :class="['rounded-full px-2.5 py-1 text-xs font-bold', complianceStatusOf(row.status).badge]">{{ ASSIGNMENT_STATUS_LABEL[row.status] ?? 'Belum' }}</span>
                </td>
                <td class="whitespace-nowrap py-2.5 pl-2 pr-4 text-center">
                  <button type="button" title="Edit" class="mr-1 rounded-md border border-blue-500 px-2 py-1 text-blue-600 hover:bg-blue-50" @click="formModal = { assignment: row }"><Pencil class="h-3 w-3" /></button>
                  <button type="button" title="Hapus" class="rounded-md border border-red-500 px-2 py-1 text-red-600 hover:bg-red-50" @click="handleDelete(row)"><Trash2 class="h-3 w-3" /></button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <AssignmentFormModal
      v-if="formModal"
      :assignment="formModal.assignment"
      :defaults="filters"
      :area-options="areaOptions"
      :unit-options="unitOptions"
      :aktivitas-options="aktivitasOptions"
      :pengawas-options="pengawasOptions"
      :shift-options="shiftOptions"
      @closed="formModal = null"
    />
    <ImportAssignmentModal v-if="isImportOpen" :tanggal="filters.tanggal" :shift="filters.shift" :template-url="TEMPLATE_URL" @closed="isImportOpen = false" />
  </AppLayout>
</template>
