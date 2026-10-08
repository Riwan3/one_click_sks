<script setup>
import { computed, reactive, ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import { postJson } from '@/composables/useMoncuApi';
import MoncuModal from './MoncuModal.vue';
import MoncuSheetReport from './MoncuSheetReport.vue';

const props = defineProps({
  open: { type: Boolean, default: false },
  rosters: { type: Array, default: () => [] }, // [{ id, name, status }]
});

const emit = defineEmits(['close']);

const PREVIEW_URL = '/one-roster/moncu/sheet-import/preview';
const COMMIT_URL = '/one-roster/moncu/sheet-import/commit';

const STEP_HEAD = {
  preview: { text: 'Pratinjau (belum ada yang disimpan). Periksa laporan di bawah, lalu klik "Simpan ke Database".', tone: 'bg-sky-50 text-sky-800' },
  done: { text: 'Impor selesai dan tersimpan. Halaman akan dimuat ulang saat ditutup.', tone: 'bg-emerald-50 text-emerald-800' },
};

const form = reactive({ url: '', rosterId: 0 });
const step = ref('input'); // input | preview | done
const token = ref(null);
const report = ref(null);
const errorMessage = ref('');
const isBusy = ref(false);

const head = computed(() => STEP_HEAD[step.value]);

const run = async (action) => {
  errorMessage.value = '';
  isBusy.value = true;
  try {
    await action();
  } catch (e) {
    errorMessage.value = e.message;
  } finally {
    isBusy.value = false;
  }
};

const handlePreview = () => run(async () => {
  if (!form.url.trim()) throw new Error('Isi link Google Sheets terlebih dahulu.');
  const data = await postJson(PREVIEW_URL, { url: form.url.trim(), roster_id: form.rosterId });
  token.value = data.token;
  report.value = data;
  step.value = 'preview';
});

const handleCommit = () => run(async () => {
  if (!token.value) throw new Error('Pratinjau belum ada. Baca ulang sheet-nya.');
  report.value = await postJson(COMMIT_URL, { token: token.value });
  step.value = 'done';
});

const handleClose = () => {
  const isDone = step.value === 'done';
  emit('close');
  if (isDone) router.reload();
};

watch(() => props.open, (isOpen) => {
  if (!isOpen) return;
  Object.assign(form, { url: '', rosterId: 0 });
  step.value = 'input';
  token.value = null;
  report.value = null;
  errorMessage.value = '';
});
</script>

<template>
  <MoncuModal :open="open" title="Import Spreadsheet (MONCU)" wide @close="handleClose">
    <p v-if="errorMessage" class="mb-3 rounded-lg bg-red-50 px-3 py-2 text-xs text-red-700">{{ errorMessage }}</p>

    <div v-if="step === 'input'" class="space-y-3">
      <label class="block">
        <span class="mb-1 block text-xs text-slate-600">Link Google Sheets (tab MONCU) <span class="text-red-500">*</span></span>
        <input v-model="form.url" type="url" autocomplete="off" placeholder="https://docs.google.com/spreadsheets/d/.../edit?gid=..." class="w-full rounded-lg border border-slate-300 px-2 py-1.5 text-sm" />
        <span class="text-[11px] text-slate-500">Buka tab yang dimaksud lalu salin link dari address bar (harus memuat <code>gid=</code>). Sheet harus dibagikan sebagai <strong>Anyone with the link – Viewer</strong>.</span>
      </label>
      <label class="block">
        <span class="mb-1 block text-xs text-slate-600">Roster tujuan</span>
        <select v-model="form.rosterId" class="w-full rounded-lg border border-slate-300 px-2 py-1.5 text-sm">
          <option :value="0">Semua roster aktif (tanpa tujuan tertentu)</option>
          <option v-for="roster in rosters" :key="roster.id" :value="roster.id">{{ roster.name }} &middot; {{ roster.status }}</option>
        </select>
        <span class="text-[11px] text-slate-500">Grup MONCU karyawan mengikuti grup roster ini. Cuti (CT/ID) diterapkan ke roster aktif tempat karyawan menjadi anggota, termasuk roster tujuan. Karyawan yang belum anggota roster tujuan tetap masuk MONCU dan dilaporkan.</span>
      </label>
      <p class="rounded-lg bg-amber-50 px-3 py-2 text-xs text-amber-800">
        Periode cuti karyawan yang ada di sheet <strong>diganti</strong> dengan isi sheet (AWAL, TAHUNAN, IZIN RESMI; AKHIR/INDUKSI/PLAN dihitung otomatis). Cuti pada roster aktif ikut disesuaikan. Karyawan yang tidak ada di sheet tidak disentuh.
      </p>
    </div>

    <div v-else class="space-y-3">
      <p :class="['rounded-lg px-3 py-2 text-xs', head.tone]">{{ head.text }}</p>
      <MoncuSheetReport v-if="report" :report="report" />
    </div>

    <template #footer>
      <button type="button" class="rounded-lg bg-slate-100 px-3 py-1.5 text-xs font-semibold text-slate-700" @click="handleClose">{{ step === 'done' ? 'Tutup' : 'Batal' }}</button>
      <button v-if="step === 'preview'" type="button" class="rounded-lg border border-slate-300 px-3 py-1.5 text-xs font-semibold text-slate-700" @click="step = 'input'">Kembali</button>
      <button v-if="step === 'input'" type="button" :disabled="isBusy" class="rounded-lg bg-blue-600 px-3 py-1.5 text-xs font-semibold text-white disabled:opacity-60" @click="handlePreview">{{ isBusy ? 'Memproses...' : 'Baca & Pratinjau' }}</button>
      <button v-if="step === 'preview'" type="button" :disabled="isBusy" class="rounded-lg bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white disabled:opacity-60" @click="handleCommit">{{ isBusy ? 'Memproses...' : 'Simpan ke Database' }}</button>
    </template>
  </MoncuModal>
</template>
