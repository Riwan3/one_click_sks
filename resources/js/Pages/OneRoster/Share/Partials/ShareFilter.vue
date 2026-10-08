<script setup>
import { computed, reactive } from 'vue';
import { router } from '@inertiajs/vue3';
import { Printer, Search } from 'lucide-vue-next';
import EmployeePicker from './EmployeePicker.vue';

const props = defineProps({
  share: { type: Object, required: true },
  monthNames: { type: Object, required: true },
});

defineEmits(['print']);

const form = reactive({ nrp: props.share.nrp, bulan: props.share.bulan, tahun: props.share.tahun });

const initialLabel = computed(() => (props.share.employee ? `${props.share.employee.nrp} - ${props.share.employee.nama}` : ''));

/** Role terbatas hanya melihat bulan yang dibuka PIC (bila ada). */
const monthOptions = computed(() => {
  const { canPick, openMonths } = props.share;
  const entries = Object.entries(props.monthNames).map(([number, name]) => ({ number: Number(number), name }));
  return canPick || !openMonths.length ? entries : entries.filter((month) => openMonths.includes(month.number));
});

const handleSubmit = () => {
  const params = { bulan: form.bulan, tahun: form.tahun, ...(form.nrp ? { nrp: form.nrp } : {}) };
  router.get('/one-roster/share', params);
};
</script>

<template>
  <form class="flex flex-wrap items-center gap-2 print:hidden" @submit.prevent="handleSubmit">
    <EmployeePicker v-if="share.canPick" v-model:nrp="form.nrp" :employees="share.employees" :initial-label="initialLabel" />
    <select v-model.number="form.bulan" class="rounded-lg border border-slate-300 px-2 py-1.5 text-sm">
      <option v-for="month in monthOptions" :key="month.number" :value="month.number">{{ month.name }}</option>
    </select>
    <input v-model.number="form.tahun" type="number" min="2020" max="2100" class="w-20 rounded-lg border border-slate-300 px-2 py-1.5 text-sm" />
    <button type="submit" class="flex items-center gap-1 rounded-lg bg-blue-600 px-3 py-1.5 text-sm font-semibold text-white hover:bg-blue-700"><Search class="h-4 w-4" /> Tampilkan</button>
    <button type="button" class="flex items-center gap-1 rounded-lg bg-emerald-600 px-3 py-1.5 text-sm font-semibold text-white hover:bg-emerald-700" @click="$emit('print')"><Printer class="h-4 w-4" /> Print</button>
  </form>
</template>
