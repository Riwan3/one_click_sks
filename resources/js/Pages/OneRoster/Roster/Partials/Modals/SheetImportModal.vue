<script setup>
import { computed, reactive, ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import { postJson } from '@/composables/useMoncuApi';
import BaseModal from '@/Components/ui/BaseModal.vue';
import FormInput from '@/Components/ui/FormInput.vue';
import FormSelect from '@/Components/ui/FormSelect.vue';
import FormAlert from '@/Components/ui/FormAlert.vue';
import MoncuSheetReport from '@/Pages/OneRoster/Moncu/Partials/MoncuSheetReport.vue';

const props = defineProps({
  open: { type: Boolean, default: false },
  rosters: { type: Array, default: () => [] }, // roster non-arsip [{ id, name, status }]
});

const emit = defineEmits(['close']);

const PREVIEW_URL = '/one-roster/roster/sheet-import/preview';
const COMMIT_URL = '/one-roster/roster/sheet-import/commit';

const STEP_HEAD = {
  preview: { text: 'Pratinjau (belum ada yang disimpan). Periksa laporan di bawah, lalu klik "Simpan ke Database".', tone: 'info' },
  done: { text: 'Impor selesai dan tersimpan. Halaman akan dimuat ulang saat ditutup.', tone: 'success' },
};

const form = reactive({ url: '', rosterId: '', syncMoncu: true });
const step = ref('input'); // input | preview | done
const token = ref(null);
const report = ref(null);
const errorMessage = ref('');
const isBusy = ref(false);

const head = computed(() => STEP_HEAD[step.value]);
const isDone = computed(() => step.value === 'done');

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

const handlePreview = () => run(async () => {
  if (!form.url.trim()) throw new Error('Isi link Google Sheets terlebih dahulu.');
  const data = await postJson(PREVIEW_URL, { url: form.url.trim(), roster_id: form.rosterId });
  token.value = data.token;
  report.value = data;
  step.value = 'preview';
});

const handleCommit = () => run(async () => {
  if (!token.value) throw new Error('Pratinjau belum ada. Baca ulang sheet-nya.');
  report.value = await postJson(COMMIT_URL, { token: token.value, roster_id: form.rosterId, sync_moncu: form.syncMoncu });
  step.value = 'done';
});

const handleClose = () => {
  emit('close');
  if (isDone.value) router.reload();
};

watch(() => props.open, (isOpen) => {
  if (!isOpen) return;
  Object.assign(form, { url: '', rosterId: props.rosters[0]?.id ?? '', syncMoncu: true });
  step.value = 'input';
  token.value = null;
  report.value = null;
  errorMessage.value = '';
});
</script>

<template>
  <BaseModal :open="open" title="Import Spreadsheet (Roster)" size="lg" @close="handleClose">
    <div class="space-y-3 px-5 py-4">
      <FormAlert :message="errorMessage" />

      <template v-if="step === 'input'">
        <FormInput
          v-model="form.url" label="Link Google Sheets (tab roster)" type="url" autocomplete="off" required
          placeholder="https://docs.google.com/spreadsheets/d/.../edit?gid=..."
          hint="Buka tab yang dimaksud lalu salin link dari address bar (harus memuat gid=). Sheet harus dibagikan sebagai Anyone with the link – Viewer."
        />
        <FormSelect
          v-model="form.rosterId" label="Roster tujuan" required
          hint="Tahun kolom tanggal sheet mengikuti tahun roster ini. Section dibuat bila belum ada; jadwal NRP yang ada di sheet pada roster ini diganti dengan isi sheet. Sel NRP lain tidak disentuh."
        >
          <option v-for="roster in rosters" :key="roster.id" :value="roster.id">{{ roster.name }} &middot; {{ roster.status }}</option>
        </FormSelect>
      </template>

      <template v-else>
        <FormAlert :message="head.text" :tone="head.tone" />
        <MoncuSheetReport v-if="report" :report="report" />
        <label v-if="step === 'preview'" class="flex items-center gap-2 text-sm text-slate-700">
          <input v-model="form.syncMoncu" type="checkbox" class="rounded border-slate-300" />
          Setelah impor, terapkan cuti dari MONCU (CT/ID) ke roster ini
        </label>
      </template>
    </div>

    <div class="flex flex-wrap items-center justify-end gap-2 border-t border-slate-100 px-5 py-3">
      <button type="button" class="rounded-lg border border-slate-300 px-3 py-1.5 text-sm text-slate-600 hover:bg-slate-50" @click="handleClose">{{ isDone ? 'Tutup' : 'Batal' }}</button>
      <button v-if="step === 'preview'" type="button" class="rounded-lg border border-slate-300 px-3 py-1.5 text-sm text-slate-600 hover:bg-slate-50" @click="step = 'input'">Kembali</button>
      <button v-if="step === 'input'" type="button" :disabled="isBusy" class="rounded-lg bg-blue-600 px-3 py-1.5 text-sm font-semibold text-white hover:bg-blue-700 disabled:opacity-60" @click="handlePreview">{{ isBusy ? 'Memproses...' : 'Baca & Pratinjau' }}</button>
      <button v-if="step === 'preview'" type="button" :disabled="isBusy" class="rounded-lg bg-emerald-600 px-3 py-1.5 text-sm font-semibold text-white hover:bg-emerald-700 disabled:opacity-60" @click="handleCommit">{{ isBusy ? 'Memproses...' : 'Simpan ke Database' }}</button>
    </div>
  </BaseModal>
</template>
