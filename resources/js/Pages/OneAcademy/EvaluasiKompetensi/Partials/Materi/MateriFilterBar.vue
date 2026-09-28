<script setup>
import { reactive, computed } from 'vue';
import { Search, X } from 'lucide-vue-next';
import { RISK_OPTIONS, MATERI_STATUS_OPTIONS } from '@/utils/evalAcademy';

const props = defineProps({
  filters: { type: Object, required: true },
  categories: { type: Array, default: () => [] },
  total: { type: Number, default: 0 },
});

const emit = defineEmits(['filters-changed']);

const form = reactive({ ...props.filters });

let searchTimer = null;

const hasActiveFilter = computed(() => Object.values(props.filters).some(Boolean));

const fieldClass = 'rounded-full border border-slate-200 bg-white px-3 py-1.5 text-xs focus:border-rose-500 focus:ring-2 focus:ring-rose-500/20';

const applyFilters = () => emit('filters-changed', { ...form });

const handleSearchInput = () => {
  clearTimeout(searchTimer);
  searchTimer = setTimeout(applyFilters, 400);
};

const handleReset = () => {
  Object.keys(form).forEach((key) => { form[key] = ''; });
  applyFilters();
};
</script>

<template>
  <div class="flex flex-wrap items-center gap-2 rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm">
    <div class="relative w-full sm:w-56">
      <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
      <input
        v-model="form.search"
        type="text"
        placeholder="Cari judul / kategori..."
        :class="[fieldClass, 'w-full pl-9']"
        @input="handleSearchInput"
      />
    </div>
    <select v-model="form.cat" :class="fieldClass" @change="applyFilters">
      <option value="">Semua Kategori</option>
      <option v-for="c in categories" :key="c" :value="c">{{ c }}</option>
    </select>
    <select v-model="form.risk" :class="fieldClass" @change="applyFilters">
      <option value="">Semua Risiko</option>
      <option v-for="r in RISK_OPTIONS" :key="r.value" :value="r.value">{{ r.label }}</option>
    </select>
    <select v-model="form.status" :class="fieldClass" @change="applyFilters">
      <option value="">Semua Status</option>
      <option v-for="s in MATERI_STATUS_OPTIONS" :key="s.value" :value="s.value">{{ s.label }}</option>
    </select>
    <input v-model="form.date_from" type="date" title="Dari tanggal berlaku" :class="fieldClass" @change="applyFilters" />
    <input v-model="form.date_to" type="date" title="Sampai tanggal berlaku" :class="fieldClass" @change="applyFilters" />
    <button
      v-if="hasActiveFilter"
      type="button"
      class="inline-flex items-center gap-1 rounded-full border border-slate-300 px-3 py-1.5 text-xs font-semibold text-slate-600 hover:bg-slate-50"
      @click="handleReset"
    >
      <X class="h-3.5 w-3.5" /> Reset
    </button>
    <span class="ml-auto text-xs text-slate-500">{{ total }} materi</span>
  </div>
</template>
