<script setup>
import { useForm } from '@inertiajs/vue3';
import { X, PenLine } from 'lucide-vue-next';
import SignaturePad from '@/Components/ui/SignaturePad.vue';
import { ASSESSMENT_BASE_URL } from '@/utils/procedureAssessment';

const props = defineProps({
  assessment: { type: Object, required: true },
});

const emit = defineEmits(['closed']);

const form = useForm({ signature: '' });

const handleSubmit = () => {
  form.post(`${ASSESSMENT_BASE_URL}/${props.assessment.id}/sign`, {
    preserveScroll: true,
    onSuccess: () => emit('closed'),
  });
};
</script>

<template>
  <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 p-4">
    <form class="w-full max-w-md space-y-4 rounded-2xl bg-white p-5 shadow-2xl" @submit.prevent="handleSubmit">
      <div class="flex items-center justify-between">
        <h6 class="flex items-center gap-2 text-sm font-bold text-slate-800"><PenLine class="h-4 w-4 text-red-700" /> Tanda Tangan Sertifikat</h6>
        <button type="button" class="text-slate-400 hover:text-slate-600" @click="emit('closed')"><X class="h-5 w-5" /></button>
      </div>
      <p class="text-xs text-slate-500">{{ assessment.title }}</p>
      <SignaturePad v-model="form.signature" label="Tanda Tangan" />
      <p v-if="form.errors.signature" class="text-[11px] text-rose-600">{{ form.errors.signature }}</p>
      <div class="flex justify-end gap-2">
        <button type="button" class="rounded-full border border-slate-300 px-4 py-1.5 text-xs font-semibold text-slate-600" @click="emit('closed')">Batal</button>
        <button type="submit" :disabled="form.processing || !form.signature" class="rounded-full bg-red-700 px-4 py-1.5 text-xs font-semibold text-white disabled:opacity-60">
          Simpan TTD
        </button>
      </div>
    </form>
  </div>
</template>
