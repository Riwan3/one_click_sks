<script setup>
import { ref, reactive, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import { Search, X } from 'lucide-vue-next';
import RaportMatrixTable from './RaportMatrixTable.vue';
import RaportDetailModal from './RaportDetailModal.vue';

const props = defineProps({
  raport: { type: Object, required: true },
});

const form = reactive({ jabatan: props.raport.filters.jabatan || '', search: props.raport.search || '' });
const detailNrp = ref(null);
let searchTimer = null;

const hasActiveFilter = computed(() => !!form.jabatan || !!form.search);

const applyFilters = () => {
  const filled = Object.fromEntries(Object.entries(form).filter(([, v]) => v !== ''));
  router.get('/one-academy/evaluasi-kompetensi', { tab: 'raport', ...filled }, { preserveState: true, preserveScroll: true, replace: true });
};

const handleSearchInput = () => {
  clearTimeout(searchTimer);
  searchTimer = setTimeout(applyFilters, 400);
};

const handleReset = () => {
  form.jabatan = '';
  form.search = '';
  applyFilters();
};
</script>

<template>
  <div class="space-y-4">
    <div class="flex flex-wrap items-center gap-2 rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm">
      <select v-model="form.jabatan" class="rounded-full border border-slate-200 bg-white px-3 py-1.5 text-xs focus:border-rose-500 focus:ring-2 focus:ring-rose-500/20" @change="applyFilters">
        <option value="">Semua Jabatan</option>
        <option v-for="j in raport.jabatans" :key="j" :value="j">{{ j }}</option>
      </select>
      <div class="relative w-full sm:w-56">
        <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
        <input
          v-model="form.search"
          type="text"
          placeholder="Cari NRP / Nama..."
          class="w-full rounded-full border border-slate-200 bg-white py-1.5 pl-9 pr-3 text-xs focus:border-rose-500 focus:ring-2 focus:ring-rose-500/20"
          @input="handleSearchInput"
        />
      </div>
      <button
        v-if="hasActiveFilter"
        type="button"
        class="inline-flex items-center gap-1 rounded-full border border-slate-300 px-3 py-1.5 text-xs font-semibold text-slate-600 hover:bg-slate-50"
        @click="handleReset"
      >
        <X class="h-3.5 w-3.5" /> Reset
      </button>
      <span class="ml-auto text-xs text-slate-500">{{ raport.rows.length }} karyawan · {{ raport.assessments.length }} assessment</span>
    </div>

    <RaportMatrixTable :rows="raport.rows" :assessments="raport.assessments" @detail-requested="detailNrp = $event" />

    <RaportDetailModal v-if="detailNrp" :nrp="detailNrp" @closed="detailNrp = null" />
  </div>
</template>
