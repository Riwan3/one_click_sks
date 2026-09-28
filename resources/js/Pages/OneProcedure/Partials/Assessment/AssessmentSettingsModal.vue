<script setup>
import { useForm } from '@inertiajs/vue3';
import { X, Save, Award, PenLine } from 'lucide-vue-next';
import { ASSESSMENT_BASE_URL, RETRY_OPTIONS, VALIDITY_OPTIONS } from '@/utils/procedureAssessment';

const props = defineProps({
  assessment: { type: Object, required: true },
  signers: { type: Array, default: () => [] },
});

const emit = defineEmits(['closed']);

const form = useForm({
  title: props.assessment.title,
  passing_grade: props.assessment.passing_grade,
  timer_minutes: props.assessment.timer_minutes,
  retry_limit: props.assessment.retry_limit,
  validity_months: props.assessment.validity_months,
  signed_by: props.assessment.signed_by ?? '',
  randomize_questions: props.assessment.randomize_questions,
  mandatory_read: props.assessment.mandatory_read,
});

const inputClass = 'w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:border-rose-500 focus:ring-2 focus:ring-rose-500/20';
const labelClass = 'mb-1 block text-[13px] font-semibold text-slate-700';

const handleSubmit = () => {
  form.transform((data) => ({ ...data, signed_by: data.signed_by || null }))
    .put(`${ASSESSMENT_BASE_URL}/${props.assessment.id}`, { preserveScroll: true, onSuccess: () => emit('closed') });
};
</script>

<template>
  <div class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-slate-900/60 p-4">
    <form class="my-8 w-full max-w-3xl space-y-4 rounded-2xl bg-white p-6 shadow-2xl" @submit.prevent="handleSubmit">
      <div class="flex items-center justify-between">
        <h5 class="text-base font-bold text-slate-800">Edit Settings Assessment</h5>
        <button type="button" class="text-slate-400 hover:text-slate-600" @click="emit('closed')"><X class="h-5 w-5" /></button>
      </div>

      <div class="grid grid-cols-1 gap-3 md:grid-cols-3">
        <div class="md:col-span-3">
          <label :class="labelClass">Judul</label>
          <input v-model="form.title" type="text" required :class="inputClass" />
          <p v-if="form.errors.title" class="mt-1 text-[11px] text-rose-600">{{ form.errors.title }}</p>
        </div>
        <div>
          <label :class="labelClass">Passing Grade (%)</label>
          <input v-model.number="form.passing_grade" type="number" min="0" max="100" :class="inputClass" />
        </div>
        <div>
          <label :class="labelClass">Timer (menit)</label>
          <input v-model.number="form.timer_minutes" type="number" min="1" :class="inputClass" />
        </div>
        <div>
          <label :class="labelClass">Retry Limit</label>
          <select v-model.number="form.retry_limit" :class="inputClass">
            <option v-for="o in RETRY_OPTIONS" :key="o.value" :value="o.value">{{ o.label }}</option>
          </select>
        </div>
        <div>
          <label :class="[labelClass, 'inline-flex items-center gap-1']"><Award class="h-3.5 w-3.5 text-amber-500" />Masa Berlaku Sertifikat</label>
          <select v-model.number="form.validity_months" :class="inputClass">
            <option v-for="o in VALIDITY_OPTIONS" :key="o.value" :value="o.value">{{ o.label }}</option>
          </select>
        </div>
        <div class="md:col-span-3">
          <label :class="[labelClass, 'inline-flex items-center gap-1']"><PenLine class="h-3.5 w-3.5 text-blue-600" />Penandatangan Sertifikat</label>
          <select v-model="form.signed_by" :class="inputClass">
            <option value="">— Pilih Penandatangan —</option>
            <option v-for="s in signers" :key="s.id" :value="s.id">{{ s.name }} — {{ s.jabatan_ss6 }}</option>
          </select>
          <small class="text-xs text-slate-500">Nama yang muncul sebagai penandatangan di sertifikat</small>
        </div>
        <label class="inline-flex items-center gap-2 text-[13px]"><input v-model="form.randomize_questions" type="checkbox" class="rounded" />Acak Urutan Soal</label>
        <label class="inline-flex items-center gap-2 text-[13px]"><input v-model="form.mandatory_read" type="checkbox" class="rounded" />Wajib Baca Prosedur Dulu</label>
      </div>

      <div class="flex justify-end gap-2">
        <button type="button" class="rounded-full border border-slate-300 px-4 py-1.5 text-xs font-semibold text-slate-600" @click="emit('closed')">Batal</button>
        <button type="submit" :disabled="form.processing" class="inline-flex items-center gap-1 rounded-full bg-rose-600 px-5 py-1.5 text-xs font-semibold text-white hover:bg-rose-700 disabled:opacity-60">
          <Save class="h-3.5 w-3.5" />Simpan
        </button>
      </div>
    </form>
  </div>
</template>
