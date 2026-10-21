<script setup>
import { computed, ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import { Lock, Plus } from 'lucide-vue-next';
import AppLayout from '@/Layouts/AppLayout.vue';

import PengajuanGradeTable from './Partials/PengajuanGradeTable.vue';
import TambahPengajuanModal from './Partials/TambahPengajuanModal.vue';
import UnlockOverlay from './Partials/UnlockOverlay.vue';

const props = defineProps({
  user: Object,
  notifCounts: Object,
  menu: Array,
  isLocked: Boolean,
  year: Number,
  years: Array,
  rows: Array,
  usedTermins: Object,
});

const TERMIN_CARDS = [
  { key: 'all', label: 'Semua Termin', color: '#475569' },
  { key: 1, label: 'Termin 1', color: '#0ea5e9' },
  { key: 2, label: 'Termin 2', color: '#16a34a' },
  { key: 3, label: 'Termin 3', color: '#d97706' },
  { key: 4, label: 'Termin 4', color: '#7c3aed' },
];

const activeTermin = ref('all');
const search = ref('');
const isAddOpen = ref(false);

const countOf = (key) => (key === 'all' ? props.rows.length : props.rows.filter((r) => r.termin === key).length);

const visibleRows = computed(() => {
  const term = search.value.trim().toLowerCase();
  return props.rows.filter((r) => (activeTermin.value === 'all' || r.termin === activeTermin.value)
    && (term === '' || `${r.nrp} ${r.nama}`.toLowerCase().includes(term)));
});

const handleYearChange = (event) => router.get('/one-academy/opd/pengajuan-grade', { tahun: event.target.value });
</script>

<template>
  <AppLayout :user="user" :notif-counts="notifCounts" :menu="menu">
    <Head title="Pengajuan Grade - OPD - One Academy" />

    <div class="space-y-4 p-6">
      <div class="flex flex-wrap items-start justify-between gap-2">
        <div>
          <h1 class="text-xl font-bold text-slate-900">Pengajuan Grade/Jabatan Operator</h1>
          <p class="text-xs text-slate-500">OPD &middot; One-Academy &middot; satu tahun = 4 termin</p>
        </div>
        <button v-if="!isLocked" type="button" class="flex items-center gap-1.5 rounded-xl border border-slate-300 px-3 py-1.5 text-xs font-semibold text-slate-600 hover:bg-slate-50" title="Kunci kembali dengan password" @click="router.post('/one-academy/opd/pengajuan-grade/lock')">
          <Lock class="h-3.5 w-3.5" /> Kunci
        </button>
      </div>

      <div class="flex flex-wrap items-center gap-3">
        <label class="text-xs font-bold text-slate-500" for="pg-year">Tahun</label>
        <select id="pg-year" :value="year" class="rounded-lg border border-slate-300 px-2 py-1 text-sm font-bold" @change="handleYearChange">
          <option v-for="y in years" :key="y" :value="y">{{ y }}</option>
        </select>
        <span class="text-xs text-slate-500">Klik kartu termin untuk menyaring tabel.</span>
      </div>

      <div class="grid grid-cols-2 gap-3 lg:grid-cols-5">
        <button
          v-for="card in TERMIN_CARDS"
          :key="card.key"
          type="button"
          class="rounded-2xl border-2 border-t-4 bg-white px-4 py-3 text-left shadow-sm transition hover:-translate-y-0.5 hover:shadow-md"
          :style="{ borderColor: activeTermin === card.key ? card.color : 'transparent', borderTopColor: card.color }"
          @click="activeTermin = card.key"
        >
          <span class="block text-[11px] font-bold uppercase tracking-wide text-slate-500">{{ card.label }}</span>
          <span class="block text-3xl font-extrabold" :style="{ color: card.color }">{{ countOf(card.key) }}</span>
        </button>
      </div>

      <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
        <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
          <div class="flex items-center gap-3">
            <button type="button" class="flex items-center gap-1.5 rounded-xl bg-blue-600 px-4 py-2 text-xs font-bold text-white hover:bg-blue-500" @click="isAddOpen = true">
              <Plus class="h-4 w-4" /> Tambah Data
            </button>
            <span class="text-xs text-slate-500"><b>{{ rows.length }}</b> pengajuan tahun {{ year }}</span>
          </div>
          <input v-model="search" type="text" placeholder="Cari NRP / nama..." class="w-full max-w-xs rounded-xl border border-slate-300 bg-slate-50 px-3 py-1.5 text-xs outline-none focus:ring-2 focus:ring-blue-500">
        </div>

        <div v-if="isLocked" class="space-y-2" aria-hidden="true">
          <div v-for="n in 6" :key="n" class="h-9 rounded-lg bg-slate-200" />
        </div>
        <PengajuanGradeTable v-else :rows="visibleRows" />

        <p class="mt-3 text-[11px] leading-relaxed text-slate-500">
          Satu tahun = <b>4 termin</b>. Satu baris = satu pengajuan pada satu termin; satu karyawan hanya sekali per termin dalam satu tahun.
          Yang diisi manual hanya <b>Tujuan Posisi</b>, <b>Tujuan Grade</b>, <b>MOR</b>, dan <b>SP</b> (kotak berbingkai; tersimpan otomatis saat sel selesai diubah).
          Kolom lain ditarik otomatis dari <b>Monitoring Grade &amp; Jabatan</b>, dan <b>Remark</b> (beserta warnanya) dikirim oleh OneClick HO. Arahkan kursor ke nomor baris untuk menghapusnya.
        </p>
      </div>
    </div>

    <UnlockOverlay v-if="isLocked" />
    <TambahPengajuanModal :open="isAddOpen" :year="year" :used-termins="usedTermins" @close="isAddOpen = false" />
  </AppLayout>
</template>
