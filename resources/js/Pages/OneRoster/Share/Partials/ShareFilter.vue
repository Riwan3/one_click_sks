<script setup>
import { computed, reactive } from 'vue';
import { router } from '@inertiajs/vue3';
import { ChevronLeft, ChevronRight, Printer } from 'lucide-vue-next';
import EmployeePicker from './EmployeePicker.vue';

const props = defineProps({
  share: { type: Object, required: true },
  monthNames: { type: Object, required: true },
});

defineEmits(['print']);

const YEAR_SPAN = 3;
const MIN_YEAR = 2020;
const MAX_YEAR = 2100;

const form = reactive({ nrp: props.share.nrp, bulan: props.share.bulan, tahun: props.share.tahun });

const initialLabel = computed(() => (props.share.employee ? `${props.share.employee.nrp} - ${props.share.employee.nama}` : ''));

/** Role terbatas hanya melihat bulan yang dibuka PIC (bila ada). */
const monthOptions = computed(() => {
  const { canPick, openMonths } = props.share;
  const entries = Object.entries(props.monthNames).map(([number, name]) => ({ number: Number(number), name }));
  return canPick || !openMonths.length ? entries : entries.filter((month) => openMonths.includes(month.number));
});

const yearOptions = computed(() => {
  const first = Math.max(MIN_YEAR, props.share.tahun - YEAR_SPAN);
  const last = Math.min(MAX_YEAR, props.share.tahun + YEAR_SPAN);
  return Array.from({ length: last - first + 1 }, (_, index) => first + index);
});

const goTo = () => {
  const params = { bulan: form.bulan, tahun: form.tahun, ...(form.nrp ? { nrp: form.nrp } : {}) };
  router.get('/one-roster/share', params, { preserveScroll: true });
};

const handleShift = (step) => {
  const index = form.tahun * 12 + (form.bulan - 1) + step;
  form.tahun = Math.floor(index / 12);
  form.bulan = (index % 12) + 1;
  goTo();
};
</script>

<template>
  <div class="rounded-xl border border-slate-200/90 bg-white p-3 shadow-xs print:hidden">
    <form class="flex flex-wrap items-center gap-2" @submit.prevent="goTo">
      <EmployeePicker v-if="share.canPick" v-model:nrp="form.nrp" :employees="share.employees" :initial-label="initialLabel" />

      <div class="flex items-center gap-1">
        <button type="button" class="flex h-8 w-8 cursor-pointer items-center justify-center rounded-lg border border-slate-200 bg-slate-50 text-slate-600 transition hover:bg-slate-100 active:scale-95" title="Bulan Sebelumnya" @click="handleShift(-1)">
          <ChevronLeft class="h-4 w-4" />
        </button>
        <select v-model.number="form.bulan" class="rounded-lg border border-slate-200 bg-white px-2.5 py-1.5 text-xs font-bold text-slate-700 outline-none focus:border-blue-500" @change="goTo">
          <option v-for="month in monthOptions" :key="month.number" :value="month.number">{{ month.name }}</option>
        </select>
        <select v-model.number="form.tahun" class="rounded-lg border border-slate-200 bg-white px-2.5 py-1.5 text-xs font-bold text-slate-700 outline-none focus:border-blue-500" @change="goTo">
          <option v-for="year in yearOptions" :key="year" :value="year">{{ year }}</option>
        </select>
        <button type="button" class="flex h-8 w-8 cursor-pointer items-center justify-center rounded-lg border border-slate-200 bg-slate-50 text-slate-600 transition hover:bg-slate-100 active:scale-95" title="Bulan Berikutnya" @click="handleShift(1)">
          <ChevronRight class="h-4 w-4" />
        </button>
      </div>

      <button type="submit" class="cursor-pointer rounded-lg bg-blue-600 px-3.5 py-1.5 text-xs font-bold text-white transition hover:bg-blue-700">Tampilkan</button>

      <button type="button" class="flex cursor-pointer items-center gap-1.5 rounded-lg bg-emerald-600 px-3.5 py-1.5 text-xs font-bold text-white transition hover:bg-emerald-700" @click="$emit('print')">
        <Printer class="h-3.5 w-3.5" /> <span>Print</span>
      </button>
    </form>
  </div>
</template>
