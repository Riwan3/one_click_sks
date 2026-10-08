<script setup>
import { computed, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
  chart: { type: Object, default: null }, // grafik section yang diatur; null = modal tertutup
});

const emit = defineEmits(['close']);

const form = useForm({ roster_id: null, section_id: null, target_siang: '', target_malam: '' });

const isOpen = computed(() => props.chart !== null);
const placeholderOf = (value) => `ikut roster: ${value ?? 'tanpa target'}`;

const handleSubmit = () => {
  form.post('/one-roster/analisis-harian/target', { preserveScroll: true, onSuccess: () => emit('close') });
};

watch(() => props.chart, (chart) => {
  if (!chart) return;
  form.clearErrors();
  form.roster_id = chart.rid;
  form.section_id = chart.sid;
  form.target_siang = chart.mSiang ? chart.tSiang : '';
  form.target_malam = chart.mMalam ? chart.tMalam : '';
});
</script>

<template>
  <div v-if="isOpen" class="fixed inset-0 z-[60] flex items-center justify-center bg-slate-900/40 p-4" @click.self="emit('close')">
    <form class="w-full max-w-sm rounded-2xl bg-white shadow-xl" autocomplete="off" @submit.prevent="handleSubmit">
      <div class="flex items-center justify-between border-b border-slate-100 px-4 py-2.5">
        <h6 class="text-sm font-semibold text-slate-800">Target analisis: {{ chart.title }}</h6>
        <button type="button" class="text-slate-400 hover:text-slate-600" @click="emit('close')">&times;</button>
      </div>
      <div class="space-y-3 p-4">
        <p v-if="form.errors.target" class="text-xs text-red-600">{{ form.errors.target }}</p>
        <p v-if="form.errors.roster_id || form.errors.section_id" class="text-xs text-red-600">{{ form.errors.roster_id || form.errors.section_id }}</p>
        <div>
          <label class="mb-1 block text-xs font-semibold text-slate-600">Target orang Siang (D) per hari</label>
          <input v-model="form.target_siang" type="number" min="0" max="999" :placeholder="placeholderOf(chart.rSiang)" class="w-full rounded-lg border border-slate-300 px-2 py-1.5 text-sm" />
        </div>
        <div>
          <label class="mb-1 block text-xs font-semibold text-slate-600">Target orang Malam (N) per hari</label>
          <input v-model="form.target_malam" type="number" min="0" max="999" :placeholder="placeholderOf(chart.rMalam)" class="w-full rounded-lg border border-slate-300 px-2 py-1.5 text-sm" />
        </div>
        <p class="text-xs text-slate-500">Awalnya mengikuti target section di roster (tab Matriks). Isi angka untuk mengubah <strong>hanya di Analisis Harian</strong> (target di roster tidak berubah). Kosongkan = ikuti roster lagi.</p>
      </div>
      <div class="flex justify-end gap-2 border-t border-slate-100 px-4 py-2.5">
        <button type="button" class="rounded-lg border border-slate-300 px-3 py-1.5 text-sm text-slate-600 hover:bg-slate-50" @click="emit('close')">Batal</button>
        <button type="submit" :disabled="form.processing" class="rounded-lg bg-blue-600 px-3 py-1.5 text-sm font-semibold text-white hover:bg-blue-700 disabled:opacity-60">Simpan</button>
      </div>
    </form>
  </div>
</template>
