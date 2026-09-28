<script setup>
import { reactive } from 'vue';
import { Link } from '@inertiajs/vue3';
import { Search, RotateCcw } from 'lucide-vue-next';
import { FINDING_ASPECTS, FINDING_BASE_URL } from '@/utils/gmpFinding';

const props = defineProps({
  filters: { type: Object, required: true },
  yearOptions: { type: Array, default: () => [] },
  areaOptions: { type: Array, default: () => [] },
  priorityOptions: { type: Array, default: () => [] },
  statusOptions: { type: Object, default: () => ({}) },
});

const emit = defineEmits(['filters-changed']);

const form = reactive({ ...props.filters });

const fieldClass = 'w-full rounded-lg border border-slate-200 bg-white px-2.5 py-1.5 text-sm';
const labelClass = 'mb-1 block text-xs font-bold text-slate-700';
</script>

<template>
  <form class="space-y-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm" @submit.prevent="emit('filters-changed', { ...form })">
    <div class="grid grid-cols-2 gap-2 md:grid-cols-3 xl:grid-cols-6">
      <div>
        <label :class="labelClass">Tahun</label>
        <select v-model="form.tahun" :class="fieldClass">
          <option value="">Semua Tahun</option>
          <option v-for="y in yearOptions" :key="y" :value="String(y)">{{ y }}</option>
        </select>
      </div>
      <div><label :class="labelClass">Week</label><input v-model="form.week" type="text" placeholder="Contoh: Week 25" :class="fieldClass" /></div>
      <div>
        <label :class="labelClass">Area</label>
        <select v-model="form.area" :class="fieldClass">
          <option value="">Semua Area</option>
          <option v-for="a in areaOptions" :key="a" :value="a">{{ a }}</option>
        </select>
      </div>
      <div><label :class="labelClass">Lokasi</label><input v-model="form.lokasi" type="text" placeholder="Nama Lokasi..." :class="fieldClass" /></div>
      <div>
        <label :class="labelClass">Aspek GMP</label>
        <select v-model="form.aspek" :class="fieldClass">
          <option value="">Semua Aspek</option>
          <option v-for="a in FINDING_ASPECTS" :key="a" :value="a">{{ a }}</option>
        </select>
      </div>
      <div>
        <label :class="labelClass">Prioritas</label>
        <select v-model="form.prioritas" :class="fieldClass">
          <option value="">Semua Prioritas</option>
          <option v-for="p in priorityOptions" :key="p" :value="p">{{ p }}</option>
        </select>
      </div>
    </div>

    <div class="grid grid-cols-2 gap-2 md:grid-cols-3 xl:grid-cols-12">
      <div class="xl:col-span-2"><label :class="labelClass">PIC (Nama / NRP)</label><input v-model="form.pic" type="text" placeholder="Cari PIC..." :class="fieldClass" /></div>
      <div class="xl:col-span-2">
        <label :class="labelClass">Status</label>
        <select v-model="form.status" :class="fieldClass">
          <option value="">Semua Status</option>
          <option v-for="(label, value) in statusOptions" :key="value" :value="value">{{ label }}</option>
        </select>
      </div>
      <div class="xl:col-span-2">
        <label :class="labelClass">Sumber</label>
        <select v-model="form.sumber" :class="fieldClass">
          <option value="">Semua Sumber</option>
          <option value="Otomatis">Penilaian GMP (Otomatis)</option>
          <option value="Manual">Temuan Lapangan (Manual)</option>
        </select>
      </div>
      <div class="col-span-2 md:col-span-3 xl:col-span-3">
        <label :class="labelClass">Rentang Deadline</label>
        <div class="flex items-center gap-1">
          <input v-model="form.deadline_start" type="date" :class="fieldClass" />
          <span class="text-slate-400">-</span>
          <input v-model="form.deadline_end" type="date" :class="fieldClass" />
        </div>
      </div>
      <div class="col-span-2 md:col-span-3 xl:col-span-3">
        <label :class="labelClass">Pencarian Umum</label>
        <div class="flex gap-1">
          <input v-model="form.search" type="text" placeholder="Kode atau Deskripsi..." :class="fieldClass" />
          <button type="submit" class="inline-flex shrink-0 items-center gap-1 rounded-lg bg-blue-600 px-3 text-sm font-semibold text-white hover:bg-blue-700"><Search class="h-3.5 w-3.5" />Terapkan</button>
          <Link :href="FINDING_BASE_URL" title="Reset Filter" class="inline-flex shrink-0 items-center rounded-lg border border-slate-300 px-2.5 text-slate-600 hover:bg-slate-50"><RotateCcw class="h-4 w-4" /></Link>
        </div>
      </div>
    </div>
  </form>
</template>
