<script setup>
import { computed } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import RosterBreadcrumb from '../Partials/RosterBreadcrumb.vue';
import ProduksiFilter from './Partials/ProduksiFilter.vue';
import RosterMatrix from './Partials/RosterMatrix.vue';
import EmployeeAccumulation from './Partials/EmployeeAccumulation.vue';
import ShiftLegend from './Partials/ShiftLegend.vue';

const props = defineProps({
  user: Object,
  notifCounts: Object,
  menu: Array,
  monitoring: { type: Object, required: true },
  filters: { type: Object, required: true },
  monthNames: { type: Object, required: true },
  years: { type: Array, required: true },
});

const heading = computed(() => (props.filters.category ? ` - ${props.filters.category}` : ''));

const sheetTitle = computed(() => {
  const category = props.filters.category ? props.filters.category.toUpperCase() : 'DEPARTEMEN PRODUKSI';
  const jabatan = props.filters.jabatan ? ` - ${props.filters.jabatan.toUpperCase()}` : '';
  return `ROSTER KERJA ${category}${jabatan}`;
});

const periodTitle = computed(() => `PERIODE ${props.monthNames[props.filters.bulan].toUpperCase()} ${props.filters.tahun}`);

const emptyMessage = computed(() => props.monitoring.error || 'Belum ada roster yang dipublish untuk periode ini.');

const handleClearJabatan = () => {
  router.get('/one-roster/monitoring-departemen', { category: props.filters.category, bulan: props.filters.bulan, tahun: props.filters.tahun });
};

const handlePrint = () => window.print();
</script>

<template>
  <Head title="Monitoring Roster Departement - One-Roster" />

  <AppLayout :user="user" :notif-counts="notifCounts" :menu="menu">
    <div class="space-y-4">
      <div class="flex flex-col gap-3 print:hidden lg:flex-row lg:items-start lg:justify-between">
        <div>
          <RosterBreadcrumb :trail="[{ label: 'Monitoring Roster Departement' }]" />
          <h1 class="flex flex-wrap items-center gap-2 text-xl font-bold tracking-tight text-slate-800">
            Monitoring Roster Departement{{ heading }}
            <span v-if="filters.jabatan" class="rounded-full bg-blue-600 px-2 py-0.5 text-xs font-semibold text-white">{{ filters.jabatan }}</span>
          </h1>
          <p class="mt-0.5 text-xs text-slate-500">
            Matriks roster dari <a href="/one-roster/roster" class="text-blue-600 underline">Roster V2</a> yang sudah dipublish
            <template v-if="filters.jabatan"> &mdash; <button type="button" class="text-red-600" @click="handleClearJabatan">× Hapus filter jabatan</button></template>
          </p>
        </div>
        <ProduksiFilter :filters="filters" :categories="monitoring.categories" :jabatan-list="monitoring.jabatanList" :month-names="monthNames" :years="years" @print="handlePrint" />
      </div>

      <div class="rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm print:border-0 print:p-0 print:shadow-none">
        <div class="mb-3 text-center">
          <h5 class="font-bold text-slate-800">{{ sheetTitle }}</h5>
          <h6 class="text-sm text-slate-700">{{ periodTitle }}</h6>
        </div>

        <RosterMatrix :monitoring="monitoring" :empty-message="emptyMessage" />
        <EmployeeAccumulation v-if="monitoring.rows.length" :rows="monitoring.rows" :totals="monitoring.employeeTotals" :grand-total="monitoring.grandTotal" />
        <ShiftLegend />
      </div>
    </div>
  </AppLayout>
</template>
