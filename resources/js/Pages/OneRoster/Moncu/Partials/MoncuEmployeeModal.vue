<script setup>
import { computed, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import MoncuModal from './MoncuModal.vue';
import MoncuEmployeeSearch from './MoncuEmployeeSearch.vue';

const props = defineProps({
  open: { type: Boolean, default: false },
  employee: { type: Object, default: null }, // null = tambah; baris karyawan = ubah
  groups: { type: Array, default: () => [] },
  config: { type: Object, required: true },
});

const emit = defineEmits(['close']);

const EMPLOYEE_URL = '/one-roster/moncu/employees';

const form = useForm({ nrp: '', hiring_date: '', group_name: '', plan_interval_days: '' });
const isEditing = computed(() => props.employee !== null);
const today = new Date().toLocaleDateString('en-CA');

const fieldClass = 'w-full rounded-lg border border-slate-300 bg-white px-2 py-1.5 text-sm';

const handleSubmit = () => form.post(EMPLOYEE_URL, { preserveScroll: true, onSuccess: () => emit('close') });

watch(() => props.open, (isOpen) => {
  if (!isOpen) return;
  form.clearErrors();
  form.defaults({
    nrp: props.employee?.nrp ?? '',
    hiring_date: props.employee?.hiring_date ?? '',
    group_name: props.employee?.group_name ?? '',
    plan_interval_days: props.employee?.interval_override ?? '',
  }).reset();
});
</script>

<template>
  <MoncuModal :open="open" title="Karyawan MONCU" @close="emit('close')">
    <form id="moncu-employee-form" class="space-y-3" @submit.prevent="handleSubmit">
      <p v-if="form.errors.form" class="rounded-lg bg-red-50 px-3 py-2 text-xs text-red-700">{{ form.errors.form }}</p>
      <div>
        <span class="mb-1 block text-xs text-slate-600">NRP <span class="text-red-500">*</span></span>
        <MoncuEmployeeSearch v-model="form.nrp" :readonly="isEditing" />
      </div>
      <div class="grid grid-cols-2 gap-2">
        <label class="block">
          <span class="mb-1 block text-xs text-slate-600">Hiring Date <span class="text-red-500">*</span></span>
          <input v-model="form.hiring_date" type="date" required :max="today" :class="fieldClass" />
        </label>
        <label class="block">
          <span class="mb-1 block text-xs text-slate-600">Grup</span>
          <input v-model="form.group_name" type="text" maxlength="100" list="moncu-group-list" :class="fieldClass" />
          <datalist id="moncu-group-list"><option v-for="group in groups" :key="group" :value="group" /></datalist>
        </label>
      </div>
      <label class="block">
        <span class="mb-1 block text-xs text-slate-600">Interval PLAN (hari) — opsional</span>
        <input v-model="form.plan_interval_days" type="number" min="1" max="365" :placeholder="`Kosong = ikut aturan jabatan (default ${config.default_interval_days})`" :class="fieldClass" />
      </label>
      <p class="text-[11px] text-slate-500">Hiring Date dipakai untuk hak cuti tahunan (berlaku setelah &gt; {{ config.entitlement_min_tenure_days }} hari masa kerja).</p>
    </form>
    <template #footer>
      <button type="button" class="rounded-lg bg-slate-100 px-3 py-1.5 text-xs font-semibold text-slate-700" @click="emit('close')">Batal</button>
      <button type="submit" form="moncu-employee-form" :disabled="form.processing" class="rounded-lg bg-blue-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-blue-700 disabled:opacity-60">Simpan</button>
    </template>
  </MoncuModal>
</template>
