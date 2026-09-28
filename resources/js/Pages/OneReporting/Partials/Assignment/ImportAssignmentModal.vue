<script setup>
import { useForm } from '@inertiajs/vue3';
import { X, Upload, Info, FileDown } from 'lucide-vue-next';

const props = defineProps({
  tanggal: { type: String, required: true },
  shift: { type: String, default: '' },
  templateUrl: { type: String, required: true },
});

const emit = defineEmits(['closed']);

const form = useForm({ file_excel: null, tanggal_back: props.tanggal, shift_back: props.shift });

const COLUMNS = [
  { letter: 'A', label: 'Tanggal', hint: '(YYYY-MM-DD)' },
  { letter: 'B', label: 'Area' },
  { letter: 'C', label: 'Unit' },
  { letter: 'D', label: 'Nama Pengawas' },
  { letter: 'E', label: 'Keterangan', hint: '(opsional)' },
  { letter: 'F', label: 'Shift', hint: '(opsional, default Shift 1)', isShift: true },
];

const handleSubmit = () => form.post('/one-reporting/daily/assignment/import', { forceFormData: true, onSuccess: () => emit('closed') });
</script>

<template>
  <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 p-4">
    <form class="w-full max-w-md space-y-3 rounded-2xl bg-white p-5 shadow-2xl" @submit.prevent="handleSubmit">
      <div class="flex items-center justify-between">
        <h6 class="flex items-center gap-2 text-sm font-bold text-slate-800"><Upload class="h-4 w-4 text-blue-600" />Upload Excel Assignment</h6>
        <button type="button" class="text-slate-400 hover:text-slate-600" @click="emit('closed')"><X class="h-5 w-5" /></button>
      </div>

      <div class="rounded-xl border border-sky-200 bg-sky-50 p-3">
        <div class="mb-2 flex items-center gap-1 text-[13px] font-bold text-sky-700"><Info class="h-3.5 w-3.5" />Panduan Upload</div>
        <ol class="list-decimal space-y-0.5 pl-4 text-xs leading-relaxed text-sky-900">
          <li>Download template terlebih dahulu</li>
          <li>Isi data sesuai format kolom yang tersedia</li>
          <li>Simpan file dalam format <strong>.xlsx</strong> atau <strong>.csv</strong></li>
          <li>Upload file yang sudah diisi</li>
        </ol>
      </div>

      <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
        <div class="mb-2 text-xs font-bold uppercase tracking-wide text-slate-600">Format Kolom</div>
        <div class="grid grid-cols-2 gap-1 text-xs">
          <div v-for="col in COLUMNS" :key="col.letter" :class="col.hint?.includes('opsional') ? 'col-span-2' : ''">
            <span :class="['mr-1 rounded px-1.5 font-semibold', col.isShift ? 'bg-amber-100 text-amber-800' : 'bg-blue-50 text-blue-700']">{{ col.letter }}</span>
            {{ col.label }} <span v-if="col.hint" class="text-slate-400">{{ col.hint }}</span>
          </div>
        </div>
      </div>

      <div>
        <label class="mb-1 block text-[13px] font-semibold text-slate-700">Pilih File <span class="text-red-600">*</span> <span class="font-normal text-slate-400">(xlsx / csv)</span></label>
        <input type="file" accept=".xlsx,.csv" required class="w-full rounded-lg border border-slate-200 p-1.5 text-sm" @change="form.file_excel = $event.target.files[0] ?? null" />
        <p v-if="form.errors.file_excel" class="mt-1 text-[11px] text-red-600">{{ form.errors.file_excel }}</p>
      </div>
      <a :href="templateUrl" class="flex w-full items-center justify-center gap-1 rounded-lg border border-green-600 py-1.5 text-sm text-green-700 hover:bg-green-50">
        <FileDown class="h-4 w-4" />Download Template Excel
      </a>

      <div class="flex justify-end gap-2">
        <button type="button" class="rounded-lg border border-slate-400 px-3 py-1.5 text-sm text-slate-600" @click="emit('closed')">Batal</button>
        <button type="submit" :disabled="form.processing" class="inline-flex items-center gap-1 rounded-lg bg-blue-600 px-4 py-1.5 text-sm font-semibold text-white hover:bg-blue-700 disabled:opacity-60">
          <Upload class="h-3.5 w-3.5" />Import Data
        </button>
      </div>
    </form>
  </div>
</template>
