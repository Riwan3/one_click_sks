<script setup>
import { computed, ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import { Download } from 'lucide-vue-next';
import { postJson } from '@/composables/useMoncuApi';
import { confirmDialog } from '@/utils/confirm';
import BaseModal from '@/Components/ui/BaseModal.vue';
import FormAlert from '@/Components/ui/FormAlert.vue';

const props = defineProps({
  open: { type: Boolean, default: false },
  rosterId: { type: Number, required: true },
});

const emit = defineEmits(['close']);

const MAX_SHOWN = 500;
const STATUS_CLASS = { VALID: 'bg-emerald-600 text-white', WARNING: 'bg-amber-400 text-slate-900', ERROR: 'bg-red-600 text-white' };

const step = ref('upload'); // upload | preview | done
const file = ref(null);
const token = ref(null);
const summary = ref(null);
const rows = ref([]);
const ignoredHeaders = ref([]);
const doneMessage = ref('');
const onlyProblem = ref(false);
const errorMessage = ref('');
const isBusy = ref(false);

const baseUrl = computed(() => `/one-roster/roster/${props.rosterId}/import`);
const importable = computed(() => (summary.value ? summary.value.valid + summary.value.warning : 0));
const visibleRows = computed(() => (onlyProblem.value ? rows.value.filter((r) => r.status !== 'VALID') : rows.value));
const note = computed(() => (summary.value?.error > 0
  ? `${summary.value.error} baris ERROR akan dilewati (tidak diimport). ${importable.value} baris valid akan diimport.`
  : 'Semua baris valid.'));

const run = async (action) => {
  errorMessage.value = '';
  isBusy.value = true;
  try {
    await action();
  } catch (error) {
    errorMessage.value = error.message;
  } finally {
    isBusy.value = false;
  }
};

const handleFile = (event) => { file.value = event.target.files[0] ?? null; };

const handlePreview = () => run(async () => {
  if (!file.value) throw new Error('Pilih file .xlsx terlebih dahulu.');
  const payload = new FormData();
  payload.append('file', file.value);
  const data = await postJson(`${baseUrl.value}/preview`, payload);
  token.value = data.token;
  summary.value = data.summary;
  rows.value = data.rows;
  ignoredHeaders.value = data.ignored_headers;
  step.value = 'preview';
});

const handleCommit = async () => {
  const text = `Import ${importable.value} baris ke roster ini?${summary.value.error ? ` ${summary.value.error} baris ERROR akan dilewati.` : ''} Semua baris disimpan dalam satu transaksi (gagal = tidak ada yang tersimpan).`;
  if (!(await confirmDialog(text, { confirmButtonText: 'Import Data' }))) return;
  run(async () => {
    const data = await postJson(`${baseUrl.value}/commit`, { token: token.value });
    doneMessage.value = data.message;
    step.value = 'done';
  });
};

const handleClose = () => {
  emit('close');
  if (step.value === 'done') router.reload();
};

watch(() => props.open, (isOpen) => {
  if (!isOpen) return;
  token.value = null;
  step.value = 'upload';
  file.value = null;
  summary.value = null;
  rows.value = [];
  errorMessage.value = '';
  onlyProblem.value = false;
});
</script>

<template>
  <BaseModal :open="open" title="Import Roster dari Excel" size="xl" @close="handleClose">
    <div class="space-y-3 px-5 py-4">
      <FormAlert :message="errorMessage" />

      <div v-if="step === 'upload'" class="space-y-3">
        <label class="block">
          <span class="mb-1 block text-xs font-semibold text-slate-600">File Excel (.xlsx, maks 2 MB, maks 5000 baris)</span>
          <input type="file" accept=".xlsx" class="w-full rounded-lg border border-slate-300 px-2 py-1.5 text-sm" @change="handleFile" />
        </label>
        <p class="text-xs text-slate-500">
          Kolom: <code>NRP | Nama | Jabatan | Tanggal | Shift | Unit | Section</code>. NRP, Tanggal (YYYY-MM-DD), dan Shift wajib.
          Nama/Jabatan hanya informasi; snapshot diambil dari data karyawan. Data tidak disimpan sebelum Anda mengonfirmasi.
        </p>
        <a href="/one-roster/roster/import-template" class="inline-flex items-center gap-1 rounded-lg border border-slate-300 px-2.5 py-1.5 text-xs font-semibold text-slate-600 hover:bg-slate-50"><Download class="h-3.5 w-3.5" />Download Template</a>
      </div>

      <div v-else-if="step === 'preview'" class="space-y-3">
        <div class="grid grid-cols-4 gap-2 text-center">
          <div class="rounded-xl border border-slate-200 p-2"><div class="text-lg font-bold">{{ summary.total }}</div><div class="text-[11px] uppercase text-slate-500">Total row</div></div>
          <div class="rounded-xl border border-slate-200 p-2"><div class="text-lg font-bold text-emerald-600">{{ summary.valid }}</div><div class="text-[11px] uppercase text-slate-500">Valid</div></div>
          <div class="rounded-xl border border-slate-200 p-2"><div class="text-lg font-bold text-amber-500">{{ summary.warning }}</div><div class="text-[11px] uppercase text-slate-500">Warning</div></div>
          <div class="rounded-xl border border-slate-200 p-2"><div class="text-lg font-bold text-red-600">{{ summary.error }}</div><div class="text-[11px] uppercase text-slate-500">Error</div></div>
        </div>
        <div class="flex flex-wrap items-center justify-between gap-2 text-sm">
          <span>{{ note }}</span>
          <label class="flex items-center gap-1.5 text-xs"><input v-model="onlyProblem" type="checkbox" class="rounded border-slate-300" />Hanya tampilkan warning/error</label>
        </div>
        <p v-if="ignoredHeaders.length" class="text-xs text-slate-500">Kolom diabaikan: {{ ignoredHeaders.join(', ') }}</p>
        <div class="max-h-[45vh] overflow-auto rounded-lg border border-slate-200">
          <table class="w-full min-w-[800px] text-xs">
            <thead class="sticky top-0 bg-slate-50 text-left"><tr>
              <th class="px-2 py-1.5">No</th><th class="px-2 py-1.5">Row Excel</th><th class="px-2 py-1.5">NRP</th><th class="px-2 py-1.5">Nama</th><th class="px-2 py-1.5">Tanggal</th>
              <th class="px-2 py-1.5">Shift</th><th class="px-2 py-1.5">Unit</th><th class="px-2 py-1.5">Section</th><th class="px-2 py-1.5">Status</th><th class="px-2 py-1.5">Message</th>
            </tr></thead>
            <tbody class="divide-y divide-slate-100">
              <tr v-for="(row, index) in visibleRows.slice(0, MAX_SHOWN)" :key="`${row.row}-${index}`" :class="row.status === 'ERROR' ? 'bg-red-50' : ''">
                <td class="px-2 py-1">{{ index + 1 }}</td><td class="px-2 py-1">{{ row.row }}</td><td class="px-2 py-1">{{ row.nrp }}</td><td class="px-2 py-1">{{ row.nama || '-' }}</td>
                <td class="px-2 py-1">{{ row.tanggal }}</td><td class="px-2 py-1">{{ row.shift }}</td><td class="px-2 py-1">{{ row.unit }}</td><td class="px-2 py-1">{{ row.section }}</td>
                <td class="px-2 py-1"><span :class="['rounded px-1.5 py-0.5 text-[10px] font-semibold', STATUS_CLASS[row.status]]">{{ row.status }}</span></td>
                <td class="px-2 py-1">{{ row.message }}</td>
              </tr>
            </tbody>
          </table>
        </div>
        <p v-if="visibleRows.length > MAX_SHOWN" class="text-xs text-slate-500">Menampilkan {{ MAX_SHOWN }} dari {{ visibleRows.length }} baris. Gunakan filter "hanya bermasalah" untuk mempersempit.</p>
      </div>

      <p v-else class="py-6 text-center text-sm font-semibold text-emerald-700">{{ doneMessage }}</p>
    </div>

    <div class="flex flex-wrap items-center justify-end gap-2 border-t border-slate-100 px-5 py-3">
      <button type="button" class="rounded-lg border border-slate-300 px-3 py-1.5 text-sm text-slate-600 hover:bg-slate-50" @click="handleClose">Tutup</button>
      <button v-if="step === 'preview'" type="button" class="rounded-lg border border-slate-300 px-3 py-1.5 text-sm text-slate-600 hover:bg-slate-50" @click="step = 'upload'">Ganti File</button>
      <button v-if="step === 'upload'" type="button" :disabled="isBusy" class="rounded-lg bg-blue-600 px-3 py-1.5 text-sm font-semibold text-white hover:bg-blue-700 disabled:opacity-60" @click="handlePreview">{{ isBusy ? 'Memproses...' : 'Upload & Preview' }}</button>
      <button v-if="step === 'preview'" type="button" :disabled="isBusy || importable === 0" class="rounded-lg bg-emerald-600 px-3 py-1.5 text-sm font-semibold text-white hover:bg-emerald-700 disabled:opacity-60" @click="handleCommit">Import Data</button>
    </div>
  </BaseModal>
</template>
