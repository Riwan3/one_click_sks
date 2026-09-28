<script setup>
import { reactive } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { Search, Table2, Trash2, Inbox } from 'lucide-vue-next';
import { confirmDialog } from '@/utils/confirm';
import { formatSlashDate } from '@/utils/odrCompliance';
import GlParseForm from './Partials/SettingGl/GlParseForm.vue';

const props = defineProps({
  user: Object,
  notifCounts: Object,
  menu: Array,
  filters: { type: Object, required: true },
  rows: { type: Array, default: () => [] },
  history: { type: Array, default: () => [] },
  shiftOptions: { type: Array, default: () => [] },
});

const BASE_URL = '/one-reporting/setting-gl';

const form = reactive({ ...props.filters });

const historyKey = (h) => `${h.tanggal}|${h.shift}`;

const handleShow = () => router.get(BASE_URL, form, { preserveScroll: true });

const handleHistoryChange = (event) => {
  const [tanggal, shift] = event.target.value.split('|');
  if (tanggal) router.get(BASE_URL, { tanggal, shift });
};

const handleDelete = async (row) => {
  if (await confirmDialog('Hapus mapping ini?')) router.delete(`${BASE_URL}/${row.id}`, { preserveScroll: true });
};

const handleDeleteBatch = async () => {
  if (await confirmDialog('Hapus SEMUA data untuk tanggal & shift ini?')) {
    router.delete(`${BASE_URL}/batch`, { data: props.filters, preserveScroll: true });
  }
};
</script>

<template>
  <Head title="Setting GL - One Reporting" />

  <AppLayout :user="user" :notif-counts="notifCounts" :menu="menu">
    <div class="space-y-4 pb-5">
      <div>
        <nav class="mb-1 text-xs text-slate-500">
          <Link href="/" class="text-blue-500">Home</Link> / <Link href="/one-reporting/dashboard" class="text-blue-500">Reporting</Link> / <span class="text-slate-400">Setting GL</span>
        </nav>
        <h1 class="text-lg font-bold text-slate-800">Settingan PIC / Group Leader (Reporting)</h1>
        <small class="text-xs text-slate-500">Mapping Area &amp; Unit → Front PIC &amp; Disposal PIC khusus modul One Reporting. Unit tidak harus kode excavator.</small>
      </div>

      <datalist id="gl-shift-options"><option v-for="s in shiftOptions" :key="s" :value="s" /></datalist>

      <form class="flex flex-wrap items-end gap-2 rounded-2xl bg-white px-4 py-3 shadow-sm" @submit.prevent="handleShow">
        <div>
          <label class="mb-1 block text-xs font-semibold text-slate-700">Tanggal</label>
          <input v-model="form.tanggal" type="date" class="w-40 rounded-lg border border-slate-200 px-3 py-1.5 text-sm" />
        </div>
        <div>
          <label class="mb-1 block text-xs font-semibold text-slate-700">Shift</label>
          <input v-model="form.shift" type="text" list="gl-shift-options" class="w-32 rounded-lg border border-slate-200 px-3 py-1.5 text-sm" />
        </div>
        <button type="submit" class="inline-flex items-center gap-1 rounded-lg bg-blue-600 px-3 py-1.5 text-sm font-semibold text-white hover:bg-blue-700"><Search class="h-3.5 w-3.5" />Tampilkan</button>
        <div v-if="history.length" class="ml-auto">
          <label class="mb-1 block text-xs font-semibold text-slate-700">Riwayat Tersimpan</label>
          <select :value="historyKey(filters)" class="min-w-[220px] rounded-lg border border-slate-200 px-3 py-1.5 text-sm" @change="handleHistoryChange">
            <option value="">-- Pilih tanggal/shift lain --</option>
            <option v-for="h in history" :key="historyKey(h)" :value="historyKey(h)">{{ formatSlashDate(h.tanggal) }} - {{ h.shift }}</option>
          </select>
        </div>
      </form>

      <GlParseForm :key="historyKey(filters)" :tanggal="filters.tanggal" :shift="filters.shift" />

      <div class="overflow-hidden rounded-2xl bg-white shadow-sm">
        <div class="flex flex-wrap items-center justify-between gap-2 bg-[#1e5e3f] px-4 py-3">
          <h6 class="flex items-center gap-2 text-sm font-bold text-white">
            <Table2 class="h-4 w-4" />Data Tersimpan — {{ formatSlashDate(filters.tanggal) }} - {{ filters.shift }} ({{ rows.length }} baris)
          </h6>
          <button v-if="rows.length" type="button" class="inline-flex items-center gap-1 rounded-lg border border-white/70 px-3 py-1 text-xs text-white hover:bg-white/10" @click="handleDeleteBatch">
            <Trash2 class="h-3.5 w-3.5" />Hapus Semua Data Ini
          </button>
        </div>

        <div v-if="rows.length === 0" class="py-10 text-center text-slate-500">
          <Inbox class="mx-auto h-8 w-8" />
          <p class="mt-2 text-sm">Belum ada data untuk tanggal/shift ini. Paste data alokasi di atas.</p>
        </div>
        <div v-else class="overflow-x-auto">
          <table class="w-full min-w-[600px] text-left text-[13px] text-slate-700">
            <thead class="bg-slate-50 text-slate-600">
              <tr>
                <th class="w-9 px-3 py-2">#</th>
                <th class="px-3 py-2">Area</th>
                <th class="px-3 py-2">Unit</th>
                <th class="px-3 py-2">Front PIC</th>
                <th class="px-3 py-2">Disposal PIC</th>
                <th class="w-14 px-3 py-2" />
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
              <tr v-for="(row, idx) in rows" :key="row.id" class="hover:bg-slate-50">
                <td class="px-3 py-2 text-slate-400">{{ idx + 1 }}</td>
                <td class="px-3 py-2">{{ row.area }}</td>
                <td class="px-3 py-2">
                  <span v-if="row.unit" class="rounded bg-slate-800 px-2 py-0.5 text-xs font-semibold text-white">{{ row.unit }}</span>
                  <span v-else class="text-slate-400">-</span>
                </td>
                <td class="px-3 py-2">{{ row.front_pic || '-' }}</td>
                <td class="px-3 py-2">{{ row.disposal_pic || '-' }}</td>
                <td class="px-3 py-2">
                  <button type="button" title="Hapus" class="rounded-md border border-red-500 px-2 py-0.5 text-red-600 hover:bg-red-50" @click="handleDelete(row)"><Trash2 class="h-3.5 w-3.5" /></button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
