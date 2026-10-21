<script setup>
import { computed, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import BaseModal from '@/Components/ui/BaseModal.vue';

const props = defineProps({
  open: { type: Boolean, default: false },
  row: { type: Object, default: null },
});

const emit = defineEmits(['close']);

const FIELDS = [
  { key: 'unit', label: 'UNIT', required: true },
  { key: 'grade_min_max', label: 'GRADE MIN-MAX' },
  { key: 'grade_min', label: 'GRADE MIN' },
  { key: 'cluster_unit', label: 'CLUSTER UNIT' },
];

const form = useForm({ unit: '', grade_min_max: '', grade_min: '', cluster_unit: '' });
const isEdit = computed(() => !!props.row);

watch(() => props.open, (isOpen) => {
  if (!isOpen) return;
  form.clearErrors();
  form.defaults(Object.fromEntries(FIELDS.map(({ key }) => [key, props.row?.[key] ?? '']))).reset();
});

const handleSubmit = () => {
  const options = { preserveScroll: true, onSuccess: () => emit('close') };
  if (isEdit.value) return form.put(`/one-academy/opd/master-grade/minimal-grade/${props.row.id}`, options);
  form.post('/one-academy/opd/master-grade/minimal-grade', options);
};
</script>

<template>
  <BaseModal :open="open" :title="isEdit ? 'Ubah Minimal Grade' : 'Tambah Minimal Grade'" size="sm" @close="emit('close')">
    <form class="space-y-3 p-5" @submit.prevent="handleSubmit">
      <div v-for="f in FIELDS" :key="f.key">
        <label class="text-xs font-semibold text-slate-600">{{ f.label }}<span v-if="f.required" class="text-red-500"> *</span></label>
        <input v-model="form[f.key]" type="text" :required="f.required" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-blue-500">
        <p v-if="form.errors[f.key]" class="mt-1 text-[11px] text-red-600">{{ form.errors[f.key] }}</p>
      </div>
      <div class="flex justify-end gap-2 border-t border-slate-100 pt-3">
        <button type="button" class="rounded-xl bg-slate-100 px-4 py-2 text-xs font-semibold text-slate-700" @click="emit('close')">Batal</button>
        <button type="submit" :disabled="form.processing" class="rounded-xl bg-blue-600 px-4 py-2 text-xs font-bold text-white disabled:opacity-50">Simpan</button>
      </div>
    </form>
  </BaseModal>
</template>
