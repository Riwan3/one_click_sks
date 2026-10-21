<script setup>
import { computed, ref, toRef } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import { Info, Search, TriangleAlert } from 'lucide-vue-next';
import AppLayout from '@/Layouts/AppLayout.vue';
import { useBerandaOpd } from '@/composables/useBerandaOpd';

import AjukanGradeModal from './Partials/AjukanGradeModal.vue';
import BerandaKpis from './Partials/BerandaKpis.vue';
import BerandaPositionList from './Partials/BerandaPositionList.vue';
import BerandaTable from './Partials/BerandaTable.vue';

const props = defineProps({
  user: Object,
  notifCounts: Object,
  menu: Array,
  rows: Array,
  hasEmployeeData: Boolean,
  hasMatrix: Boolean,
  year: Number,
});

const { filter, position, search, counts, positions, visibleRows, setSort } = useBerandaOpd(toRef(props, 'rows'));

const employeeToAjukan = ref(null);

const compositionBar = computed(() => [
  { key: 'under', color: '#dc2626', count: counts.value.under },
  { key: 'high', color: '#f59e0b', count: counts.value.high },
  { key: 'ok', color: '#16a34a', count: counts.value.ok },
]);
</script>

<template>
  <AppLayout :user="user" :notif-counts="notifCounts" :menu="menu">
    <Head title="Beranda OPD - One Academy" />

    <div class="space-y-4 p-6">
      <div class="flex flex-wrap items-end justify-between gap-2">
        <div>
          <h1 class="text-xl font-bold text-slate-900">Beranda OPD</h1>
          <p class="text-xs text-slate-500">Monitoring Grade &amp; Jabatan Karyawan &middot; One-Academy</p>
        </div>
        <p class="text-xs text-slate-500">Klik kartu atau posisi untuk menyaring tabel.</p>
      </div>

      <p v-if="!hasEmployeeData" class="flex items-center gap-2 rounded-xl border border-amber-200 bg-amber-50 px-4 py-2 text-xs text-amber-800">
        <TriangleAlert class="h-4 w-4 shrink-0" />
        Data Monitoring Grade &amp; Jabatan belum ada, jadi belum ada yang bisa dimonitor.
        <Link href="/one-academy/monitoring-grade" class="font-semibold underline">Buka Monitoring Grade &amp; Jabatan</Link>
      </p>
      <p v-if="!hasMatrix" class="flex items-center gap-2 rounded-xl border border-sky-200 bg-sky-50 px-4 py-2 text-xs text-sky-800">
        <Info class="h-4 w-4 shrink-0" />
        Matriks Minimal Grade belum diisi, jadi status under grade dan kesesuaian grade belum bisa dihitung.
        <Link href="/one-academy/opd/master-grade/minimal-grade" class="font-semibold underline">Isi Minimal Grade</Link>
      </p>

      <BerandaKpis :counts="counts" :active="filter" @select="filter = $event" />

      <div v-if="counts.total" class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
        <div class="mb-1 flex flex-wrap justify-between gap-1 text-xs text-slate-500">
          <span class="font-semibold">Komposisi kesesuaian grade</span>
          <span>Under {{ counts.under }} · Di atas max {{ counts.high }} · Sesuai {{ counts.ok }} · Belum ada data {{ counts.na }}</span>
        </div>
        <div class="flex h-2.5 overflow-hidden rounded-full bg-slate-200">
          <span v-for="part in compositionBar" :key="part.key" :style="{ width: `${(part.count * 100) / counts.total}%`, background: part.color }" />
        </div>
      </div>

      <div class="grid gap-4 lg:grid-cols-4">
        <BerandaPositionList :positions="positions" :total="counts.total" :selected="position" @select="position = $event" />

        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm lg:col-span-3">
          <div class="mb-2 flex flex-wrap items-center justify-between gap-2">
            <span class="text-xs text-slate-500"><b>{{ visibleRows.length }}</b> dari {{ counts.total }} karyawan <b v-if="position" class="text-slate-800">· {{ position }}</b></span>
            <div class="relative w-full max-w-xs">
              <Search class="absolute left-3 top-2 h-4 w-4 text-slate-400" />
              <input v-model="search" type="text" placeholder="Cari NRP / nama / posisi..." class="w-full rounded-xl border border-slate-300 bg-slate-50 py-1.5 pl-9 pr-3 text-xs outline-none focus:ring-2 focus:ring-blue-500">
            </div>
          </div>

          <BerandaTable :rows="visibleRows" :year="year" @sort="setSort" @ajukan="employeeToAjukan = $event" />

          <p class="mt-2 text-[11px] leading-relaxed text-slate-500">
            <b>1.</b> Under grade bila grade saat ini lebih rendah dari grade minimal posisi.
            <b>2.</b> Grade sesuai bila berada di antara grade minimal dan maksimal posisi (Master Grade &rsaquo; Minimal Grade).
            <b>3.</b> Garis biru penuh = sudah ≥ 3 tahun di grade saat ini.
            <b>4.</b> Lama di posisi menjadi dasar penentuan training ODP (aturan menyusul).
            Klik nama karyawan untuk mengajukan grade.
          </p>
        </div>
      </div>
    </div>

    <AjukanGradeModal :employee="employeeToAjukan" :year="year" @close="employeeToAjukan = null" />
  </AppLayout>
</template>
