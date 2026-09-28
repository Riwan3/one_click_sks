<script setup>
import { reactive, computed } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { formatGmpScore } from '@/utils/gmpScore';
import GmpParetoCard from './Partials/Dashboard/GmpParetoCard.vue';
import GmpLokasiCard from './Partials/Dashboard/GmpLokasiCard.vue';
import GmpLocationGrid from './Partials/Dashboard/GmpLocationGrid.vue';

const props = defineProps({
  user: Object,
  notifCounts: Object,
  menu: Array,
  filters: { type: Object, required: true },
  typeOptions: { type: Array, default: () => [] },
  weekOptions: { type: Array, default: () => [] },
  yearOptions: { type: Array, default: () => [] },
  canViewCharts: { type: Boolean, default: true },
  stats: { type: Object, required: true },
  lokasi: { type: Array, default: () => [] },
  chartWeek: { type: String, default: null },
  pareto: { type: Array, default: () => [] },
  locationCharts: { type: Array, default: () => [] },
});

const form = reactive({ ...props.filters });

const typeLabel = computed(() => props.typeOptions.find((t) => t.key === props.filters.gmp_type)?.label ?? props.filters.gmp_type);

const avgClass = computed(() => {
  const avg = props.stats.avg_score;
  if (avg >= 80) return 'text-green-600';
  return avg >= 60 ? 'text-amber-500' : 'text-red-600';
});

const statCards = computed(() => [
  { label: 'Total Penilaian', value: props.stats.total_penilaian.toLocaleString('id-ID'), border: 'border-l-green-600', text: 'text-green-600' },
  { label: 'Rata-rata Skor', value: formatGmpScore(props.stats.avg_score), border: 'border-l-blue-600', text: avgClass.value },
  { label: 'Skor Tertinggi', value: formatGmpScore(props.stats.max_score), border: 'border-l-amber-400', text: 'text-amber-500' },
  { label: 'Skor Terendah', value: formatGmpScore(props.stats.min_score), border: 'border-l-red-600', text: 'text-red-600' },
]);

const selectClass = 'w-full rounded-lg border-0 bg-slate-100 px-3 py-1.5 text-sm';

const applyFilters = () => router.get('/one-gmp/dashboard', { ...form }, { preserveScroll: true });
</script>

<template>
  <Head title="Dashboard GMP - OneClick" />

  <AppLayout :user="user" :notif-counts="notifCounts" :menu="menu">
    <div v-if="!canViewCharts" class="rounded-2xl border border-slate-200 bg-white p-10 text-center text-sm text-slate-500">
      Dashboard grafik GMP tidak tersedia untuk role Anda.
    </div>

    <div v-else class="space-y-3">
      <div class="grid grid-cols-2 gap-2 rounded-2xl bg-white p-2 shadow-sm md:grid-cols-10">
        <div class="col-span-2 md:col-span-4">
          <label class="mb-1 block text-xs font-bold text-slate-700">Tipe GMP</label>
          <select v-model="form.gmp_type" :class="selectClass" @change="applyFilters">
            <option v-for="t in typeOptions" :key="t.key" :value="t.key">{{ t.label }}</option>
          </select>
        </div>
        <div class="md:col-span-3">
          <label class="mb-1 block text-xs font-bold text-slate-700">Week</label>
          <select v-model="form.week" :class="selectClass" @change="applyFilters">
            <option value="">Semua Week</option>
            <option v-for="w in weekOptions" :key="w" :value="w">Week {{ w }}</option>
            <option v-if="weekOptions.length === 0" value="" disabled>Belum ada data untuk tahun ini</option>
          </select>
        </div>
        <div class="md:col-span-3">
          <label class="mb-1 block text-xs font-bold text-slate-700">Tahun</label>
          <select v-model.number="form.tahun" :class="selectClass" @change="applyFilters">
            <option v-for="y in yearOptions" :key="y" :value="y">{{ y }}</option>
          </select>
        </div>
      </div>

      <div class="grid grid-cols-2 gap-2 md:grid-cols-4">
        <div v-for="card in statCards" :key="card.label" :class="['rounded-xl border-l-4 bg-white p-2 shadow-sm', card.border]">
          <p class="text-xs font-bold text-slate-500">{{ card.label }}</p>
          <h4 :class="['text-xl font-bold', card.text]">{{ card.value }}</h4>
        </div>
      </div>

      <div class="grid grid-cols-1 gap-3 xl:grid-cols-12">
        <GmpParetoCard class="xl:col-span-7" :pareto="pareto" :type-label="typeLabel" :week="chartWeek ?? ''" />
        <GmpLokasiCard class="xl:col-span-5" :lokasi="lokasi" :type-label="typeLabel" />
      </div>

      <GmpLocationGrid :charts="locationCharts" :type-label="typeLabel" />
    </div>
  </AppLayout>
</template>
