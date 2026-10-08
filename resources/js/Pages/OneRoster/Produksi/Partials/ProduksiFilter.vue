<script setup>
import { reactive } from 'vue';
import { router } from '@inertiajs/vue3';
import { Printer } from 'lucide-vue-next';

const props = defineProps({
  filters: { type: Object, required: true },
  categories: { type: Array, default: () => [] },
  jabatanList: { type: Array, default: () => [] },
  monthNames: { type: Object, required: true },
  years: { type: Array, required: true },
});

defineEmits(['print']);

const form = reactive({ ...props.filters });

const handleSubmit = () => {
  const params = Object.fromEntries(Object.entries(form).filter(([, value]) => value !== ''));
  router.get('/one-roster/monitoring-departemen', params);
};

const handleSelectFilter = (key, value) => {
  form[key] = value;
  handleSubmit();
};
</script>

<template>
  <form class="flex flex-wrap items-center gap-2" @submit.prevent="handleSubmit">
    <select :value="form.category" class="min-w-40 rounded-lg border border-slate-300 px-2 py-1.5 text-sm" @change="handleSelectFilter('category', $event.target.value)">
      <option value="">Semua Kategori</option>
      <option v-for="category in categories" :key="category" :value="category">{{ category }}</option>
    </select>
    <select :value="form.jabatan" class="min-w-44 rounded-lg border border-slate-300 px-2 py-1.5 text-sm" @change="handleSelectFilter('jabatan', $event.target.value)">
      <option value="">Semua Jabatan</option>
      <option v-for="jabatan in jabatanList" :key="jabatan" :value="jabatan">{{ jabatan }}</option>
    </select>
    <select v-model.number="form.bulan" class="rounded-lg border border-slate-300 px-2 py-1.5 text-sm">
      <option v-for="(name, number) in monthNames" :key="number" :value="Number(number)">{{ name }}</option>
    </select>
    <select v-model.number="form.tahun" class="rounded-lg border border-slate-300 px-2 py-1.5 text-sm">
      <option v-for="year in years" :key="year" :value="year">{{ year }}</option>
    </select>
    <button type="submit" class="rounded-lg bg-blue-600 px-3 py-1.5 text-sm font-semibold text-white hover:bg-blue-700">Tampilkan</button>
    <button type="button" class="flex items-center gap-1 rounded-lg bg-emerald-600 px-3 py-1.5 text-sm font-semibold text-white hover:bg-emerald-700" @click="$emit('print')">
      <Printer class="h-4 w-4" /> Print
    </button>
  </form>
</template>
