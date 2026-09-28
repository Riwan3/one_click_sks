<script setup>
import { computed } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { Layers, X } from 'lucide-vue-next';

const props = defineProps({
  show: { type: Boolean, default: false },
  columns: { type: Array, required: true },
  groups: { type: Object, required: true },
});

const emit = defineEmits(['close']);

const baseFields = computed(() => props.columns.filter((c) => ! c.group));
const groupedFields = computed(() => {
  const byGroup = {};
  props.columns.filter((c) => c.group).forEach((c) => {
    (byGroup[c.group] ??= []).push(c);
  });
  return Object.entries(byGroup).map(([key, fields]) => ({ key, label: props.groups[key]?.label ?? key, fields }));
});

const form = useForm(Object.fromEntries(props.columns.map((c) => [c.key, ''])));

const submit = () => {
  form.post('/one-academy/monitoring-grade/store-versatility', {
    onSuccess: () => { emit('close'); form.reset(); },
  });
};
</script>

<template>
  <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 p-4 backdrop-blur-sm">
    <div class="flex max-h-[90vh] w-full max-w-4xl flex-col rounded-2xl border border-slate-200 bg-white shadow-2xl dark:border-slate-800 dark:bg-slate-900">
      <div class="flex items-center justify-between border-b border-slate-100 p-5 dark:border-slate-800">
        <h3 class="flex items-center gap-2 text-base font-bold text-slate-900 dark:text-white"><Layers class="h-5 w-5 text-indigo-600" /> Tambah Data Versatility Karyawan</h3>
        <button type="button" class="rounded-lg p-1 text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800" @click="emit('close')"><X class="h-5 w-5" /></button>
      </div>

      <form class="flex-1 overflow-y-auto p-5" @submit.prevent="submit">
        <h4 class="mb-2 border-b border-slate-100 pb-1 text-xs font-bold uppercase text-indigo-700 dark:border-slate-800">Informasi Dasar</h4>
        <div class="mb-5 grid grid-cols-2 gap-2 md:grid-cols-4">
          <div v-for="field in baseFields" :key="field.key">
            <label class="text-[11px] font-bold text-slate-500">{{ field.label }}<span v-if="field.key === 'nrp' || field.key === 'nama'" class="text-rose-500"> *</span></label>
            <input v-model="form[field.key]" type="text" :required="field.key === 'nrp' || field.key === 'nama'" class="mt-0.5 w-full rounded-lg border border-slate-200 p-1.5 text-xs dark:border-slate-700 dark:bg-slate-800">
          </div>
        </div>

        <div v-for="group in groupedFields" :key="group.key" class="mb-5">
          <h4 class="mb-2 border-b border-slate-100 pb-1 text-xs font-bold uppercase text-emerald-700 dark:border-slate-800">{{ group.label }}</h4>
          <div class="grid grid-cols-2 gap-2 md:grid-cols-4">
            <div v-for="field in group.fields" :key="field.key">
              <label class="text-[11px] font-bold text-slate-500">{{ field.label }}</label>
              <input v-model="form[field.key]" type="text" class="mt-0.5 w-full rounded-lg border border-slate-200 p-1.5 text-xs dark:border-slate-700 dark:bg-slate-800">
            </div>
          </div>
        </div>

        <div class="flex justify-end gap-2 border-t border-slate-100 pt-3 dark:border-slate-800">
          <button type="button" class="rounded-xl bg-slate-200 px-4 py-2 text-xs font-semibold dark:bg-slate-800" @click="emit('close')">Batal</button>
          <button type="submit" :disabled="form.processing" class="rounded-xl bg-indigo-600 px-4 py-2 text-xs font-bold text-white shadow disabled:opacity-60">Simpan Versality</button>
        </div>
      </form>
    </div>
  </div>
</template>
