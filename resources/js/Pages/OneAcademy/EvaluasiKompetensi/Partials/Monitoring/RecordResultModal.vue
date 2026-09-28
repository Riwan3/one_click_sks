<script setup>
import { useForm } from '@inertiajs/vue3';
import { X, ClipboardPlus } from 'lucide-vue-next';

const props = defineProps({
  assessments: { type: Array, default: () => [] },
  defaultAssessmentId: { type: [Number, String], default: '' },
});

const emit = defineEmits(['closed']);

const form = useForm({
  assessment_id: props.defaultAssessmentId || '',
  nrp: '',
  score: '',
  time_taken: '',
});

const inputClass = 'w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:border-rose-500 focus:ring-2 focus:ring-rose-500/20';
const labelClass = 'mb-1 block text-[13px] font-semibold text-slate-700';

const handleSubmit = () => {
  form.post('/one-academy/evaluasi-kompetensi/record-result', {
    preserveScroll: true,
    onSuccess: () => emit('closed'),
  });
};
</script>

<template>
  <div class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-slate-900/60 p-4">
    <form class="my-8 w-full max-w-md space-y-4 rounded-2xl bg-white p-6 shadow-2xl" @submit.prevent="handleSubmit">
      <div class="flex items-center justify-between">
        <h5 class="flex items-center gap-2 text-base font-bold text-slate-800"><ClipboardPlus class="h-4 w-4 text-rose-600" />Input Nilai Manual</h5>
        <button type="button" class="text-slate-400 hover:text-slate-600" @click="emit('closed')"><X class="h-5 w-5" /></button>
      </div>
      <p class="text-xs text-slate-500">Untuk mencatat hasil evaluasi yang dinilai langsung oleh instruktur/GL (mis. tes tertulis/praktik), tanpa melalui sesi ujian mandiri.</p>

      <div>
        <label :class="labelClass">Assessment <span class="text-rose-600">*</span></label>
        <select v-model="form.assessment_id" required :class="inputClass">
          <option value="">-- Pilih Assessment --</option>
          <option v-for="a in assessments" :key="a.id" :value="a.id">{{ a.title }}</option>
        </select>
        <p v-if="form.errors.assessment_id" class="mt-1 text-[11px] text-rose-600">{{ form.errors.assessment_id }}</p>
      </div>

      <div>
        <label :class="labelClass">NRP Karyawan <span class="text-rose-600">*</span></label>
        <input v-model="form.nrp" type="text" required placeholder="Contoh: 12345678" :class="inputClass" />
        <p v-if="form.errors.nrp" class="mt-1 text-[11px] text-rose-600">{{ form.errors.nrp }}</p>
      </div>

      <div class="grid grid-cols-2 gap-3">
        <div>
          <label :class="labelClass">Score (0-100) <span class="text-rose-600">*</span></label>
          <input v-model.number="form.score" type="number" min="0" max="100" required :class="inputClass" />
          <p v-if="form.errors.score" class="mt-1 text-[11px] text-rose-600">{{ form.errors.score }}</p>
        </div>
        <div>
          <label :class="labelClass">Waktu (detik)</label>
          <input v-model.number="form.time_taken" type="number" min="0" placeholder="Opsional" :class="inputClass" />
        </div>
      </div>

      <div class="flex justify-end gap-2 border-t border-slate-100 pt-3">
        <button type="button" class="rounded-full border border-slate-300 px-4 py-1.5 text-xs font-semibold text-slate-600" @click="emit('closed')">Batal</button>
        <button type="submit" :disabled="form.processing" class="rounded-full bg-rose-600 px-5 py-1.5 text-xs font-semibold text-white hover:bg-rose-700 disabled:opacity-60">
          Simpan Hasil
        </button>
      </div>
    </form>
  </div>
</template>
