<script setup>
import { reactive, computed } from 'vue';
import { X, FileSpreadsheet, FileText, ClipboardPlus } from 'lucide-vue-next';
import { MONITORING_STATUS_OPTIONS } from '@/utils/academyMonitoring';

const props = defineProps({
  filters: { type: Object, required: true },
  assessments: { type: Array, default: () => [] },
  jabatans: { type: Array, default: () => [] },
  total: { type: Number, default: 0 },
  exportQuery: { type: String, default: '' },
  canManage: { type: Boolean, default: false },
});

const emit = defineEmits(['filters-changed', 'record-result-requested']);

const form = reactive({ ...props.filters, assessment_id: props.filters.assessment_id || '' });

let searchTimer = null;

const hasActiveFilter = computed(() => Object.values(props.filters).some((v) => v !== '' && v !== 0));

const inputClass = 'w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs focus:border-rose-400 focus:outline-none focus:ring-2 focus:ring-rose-100';
const labelClass = 'mb-1 block text-[11px] font-bold text-slate-600';

const applyFilters = () => emit('filters-changed', { ...form });

const handleReset = () => {
  Object.keys(form).forEach((key) => { form[key] = ''; });
  applyFilters();
};

const onSearchInput = () => {
  clearTimeout(searchTimer);
  searchTimer = setTimeout(applyFilters, 450);
};
</script>

<template>
  <div class="space-y-3 rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm">
    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-12">
      <div class="lg:col-span-3">
        <label :class="labelClass">Assessment</label>
        <select v-model="form.assessment_id" :class="inputClass" @change="applyFilters">
          <option value="">Semua Assessment</option>
          <option v-for="a in assessments" :key="a.id" :value="a.id">{{ a.title }}</option>
        </select>
      </div>
      <div class="lg:col-span-3">
        <label :class="labelClass">Jabatan</label>
        <select v-model="form.jabatan" :class="inputClass" @change="applyFilters">
          <option value="">Semua Jabatan</option>
          <option v-for="j in jabatans" :key="j" :value="j">{{ j }}</option>
        </select>
      </div>
      <div class="lg:col-span-2">
        <label :class="labelClass">Status</label>
        <select v-model="form.status" :class="inputClass" @change="applyFilters">
          <option value="">Semua Status</option>
          <option v-for="s in MONITORING_STATUS_OPTIONS" :key="s.value" :value="s.value">{{ s.label }}</option>
        </select>
      </div>
      <div class="lg:col-span-4">
        <label :class="labelClass">Cari NRP / Nama</label>
        <input v-model="form.search" type="text" placeholder="Ketik NRP atau nama..." :class="inputClass" @input="onSearchInput" />
      </div>
    </div>

    <div class="grid grid-cols-2 gap-3 sm:flex sm:flex-wrap">
      <div>
        <label :class="labelClass">Tanggal Dari</label>
        <input v-model="form.date_from" type="date" :class="inputClass" @change="applyFilters" />
      </div>
      <div>
        <label :class="labelClass">Tanggal Sampai</label>
        <input v-model="form.date_to" type="date" :class="inputClass" @change="applyFilters" />
      </div>
      <div class="sm:w-24">
        <label :class="labelClass">Score Min</label>
        <input v-model="form.score_min" type="number" min="0" max="100" placeholder="0" :class="inputClass" @change="applyFilters" />
      </div>
      <div class="sm:w-24">
        <label :class="labelClass">Score Max</label>
        <input v-model="form.score_max" type="number" min="0" max="100" placeholder="100" :class="inputClass" @change="applyFilters" />
      </div>
    </div>

    <div class="flex flex-wrap items-center justify-between gap-2">
      <div class="flex items-center gap-2">
        <button
          v-if="hasActiveFilter"
          type="button"
          class="inline-flex items-center gap-1 rounded-full border border-slate-300 px-3 py-1.5 text-xs font-semibold text-slate-600 hover:bg-slate-50"
          @click="handleReset"
        >
          <X class="h-3.5 w-3.5" /> Reset
        </button>
        <span class="text-xs text-slate-500">{{ total }} data ditemukan</span>
      </div>
      <div class="flex gap-2">
        <button
          v-if="canManage"
          type="button"
          class="inline-flex items-center gap-1 rounded-full bg-rose-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-rose-700"
          @click="emit('record-result-requested')"
        >
          <ClipboardPlus class="h-3.5 w-3.5" /> Input Nilai Manual
        </button>
        <a
          :href="`/one-academy/evaluasi-kompetensi/monitoring/export/excel?${exportQuery}`"
          target="_blank"
          class="inline-flex items-center gap-1 rounded-full border border-emerald-500 px-3 py-1.5 text-xs font-semibold text-emerald-600 hover:bg-emerald-50"
        >
          <FileSpreadsheet class="h-3.5 w-3.5" /> Excel
        </a>
        <a
          :href="`/one-academy/evaluasi-kompetensi/monitoring/export/pdf?${exportQuery}`"
          target="_blank"
          class="inline-flex items-center gap-1 rounded-full border border-rose-500 px-3 py-1.5 text-xs font-semibold text-rose-600 hover:bg-rose-50"
        >
          <FileText class="h-3.5 w-3.5" /> PDF
        </a>
      </div>
    </div>
  </div>
</template>
