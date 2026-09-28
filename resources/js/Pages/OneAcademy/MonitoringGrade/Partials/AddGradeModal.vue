<script setup>
import { computed } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { BarChart3, X } from 'lucide-vue-next';

const props = defineProps({
  show: { type: Boolean, default: false },
  columns: { type: Array, required: true },
});

const emit = defineEmits(['close']);

const SECTIONS = [
  { title: 'Informasi Dasar', keys: ['nrp', 'nama', 'posisi', 'eg', 'sub_area', 'atr_6_bulan', 'join_date', 'efektif_grade', 'efektif_position', 'grade_now'] },
  { title: 'Level & Versatility', keys: ['min_level', 'max_level', 'status_level', 'suite', 'level_posisi', 'jumlah_versatility', 'requirement_versatility', 'gap_versatility', 'kesesuaian_versatility'] },
  { title: 'Masa Kerja & Sertifikasi', keys: ['masa_kerja', 'masa_kerja_grade_sekarang', 'masa_kerja_posisi_sekarang', 'masa_grade_3_th_up', 'masa_position_3th_up', 'sertifikasi', 'status_sertifikasi', 'expired_date_sertifikasi', 'remaining_time', 'remarks_grade'] },
];

const labelFor = (key) => props.columns.find((c) => c.key === key)?.label ?? key;
const sections = computed(() => SECTIONS.map((s) => ({ title: s.title, fields: s.keys.map((key) => ({ key, label: labelFor(key) })) })));

const form = useForm(Object.fromEntries(props.columns.map((c) => [c.key, ''])));

const submit = () => {
  form.post('/one-academy/monitoring-grade/store-grade', {
    onSuccess: () => { emit('close'); form.reset(); },
  });
};
</script>

<template>
  <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 p-4 backdrop-blur-sm">
    <div class="flex max-h-[90vh] w-full max-w-3xl flex-col rounded-2xl border border-slate-200 bg-white shadow-2xl dark:border-slate-800 dark:bg-slate-900">
      <div class="flex items-center justify-between border-b border-slate-100 p-5 dark:border-slate-800">
        <h3 class="flex items-center gap-2 text-base font-bold text-slate-900 dark:text-white"><BarChart3 class="h-5 w-5 text-blue-600" /> Tambah Data Grade Karyawan</h3>
        <button type="button" class="rounded-lg p-1 text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800" @click="emit('close')"><X class="h-5 w-5" /></button>
      </div>

      <form class="flex-1 overflow-y-auto p-5" @submit.prevent="submit">
        <div v-for="section in sections" :key="section.title" class="mb-5">
          <h4 class="mb-2 border-b border-slate-100 pb-1 text-xs font-bold uppercase text-blue-700 dark:border-slate-800">{{ section.title }}</h4>
          <div class="grid grid-cols-2 gap-2 md:grid-cols-3">
            <div v-for="field in section.fields" :key="field.key">
              <label class="text-[11px] font-bold text-slate-500">{{ field.label }}<span v-if="field.key === 'nrp' || field.key === 'nama'" class="text-rose-500"> *</span></label>
              <input v-model="form[field.key]" type="text" :required="field.key === 'nrp' || field.key === 'nama'" class="mt-0.5 w-full rounded-lg border border-slate-200 p-1.5 text-xs dark:border-slate-700 dark:bg-slate-800">
            </div>
          </div>
        </div>

        <div class="flex justify-end gap-2 border-t border-slate-100 pt-3 dark:border-slate-800">
          <button type="button" class="rounded-xl bg-slate-200 px-4 py-2 text-xs font-semibold dark:bg-slate-800" @click="emit('close')">Batal</button>
          <button type="submit" :disabled="form.processing" class="rounded-xl bg-blue-600 px-4 py-2 text-xs font-bold text-white shadow disabled:opacity-60">Simpan</button>
        </div>
      </form>
    </div>
  </div>
</template>
