<script setup>
import { useForm } from '@inertiajs/vue3';
import { X, PlusCircle, Save } from 'lucide-vue-next';
import { INCIDENT_BASE_URL } from '@/utils/odrIncident';

defineProps({
  areaOptions: { type: Array, default: () => [] },
});

const emit = defineEmits(['closed']);

const form = useForm({
  tanggal_insiden: new Date().toISOString().slice(0, 10),
  area: '',
  judul_insiden: '',
  deskripsi_insiden: '',
});

const fieldClass = 'w-full rounded-lg border border-slate-200 px-3 py-1.5 text-sm focus:border-blue-400 focus:ring-2 focus:ring-blue-100';
const labelClass = 'mb-1 block text-[13px] font-semibold text-slate-700';

const handleSubmit = () => form.post(INCIDENT_BASE_URL, { preserveScroll: true, onSuccess: () => emit('closed') });
</script>

<template>
  <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 p-4">
    <form class="w-full max-w-lg space-y-3 rounded-2xl bg-white p-5 shadow-2xl" @submit.prevent="handleSubmit">
      <div class="flex items-center justify-between">
        <h6 class="flex items-center gap-2 text-sm font-bold text-slate-800"><PlusCircle class="h-4 w-4 text-red-600" />Tambah Data Insiden</h6>
        <button type="button" class="text-slate-400 hover:text-slate-600" @click="emit('closed')"><X class="h-5 w-5" /></button>
      </div>

      <p v-if="Object.keys(form.errors).length" class="rounded-lg bg-red-50 p-2 text-xs text-red-700">{{ Object.values(form.errors)[0] }}</p>

      <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
        <div>
          <label :class="labelClass">Tanggal Insiden <span class="text-red-600">*</span></label>
          <input v-model="form.tanggal_insiden" type="date" required :class="fieldClass" />
        </div>
        <div>
          <label :class="labelClass">Area</label>
          <select v-model="form.area" :class="fieldClass">
            <option value="">-- Pilih Area --</option>
            <option v-for="a in areaOptions" :key="a" :value="a">{{ a }}</option>
          </select>
        </div>
        <div class="md:col-span-2">
          <label :class="labelClass">Judul Insiden <span class="text-red-600">*</span></label>
          <input v-model="form.judul_insiden" type="text" required placeholder="Judul singkat insiden..." :class="fieldClass" />
        </div>
        <div class="md:col-span-2">
          <label :class="labelClass">Deskripsi Insiden</label>
          <textarea v-model="form.deskripsi_insiden" rows="3" :class="fieldClass" />
        </div>
      </div>

      <div class="flex justify-end gap-2">
        <button type="button" class="rounded-lg border border-slate-400 px-3 py-1.5 text-sm text-slate-600" @click="emit('closed')">Batal</button>
        <button type="submit" :disabled="form.processing" class="inline-flex items-center gap-1 rounded-lg bg-red-600 px-4 py-1.5 text-sm font-semibold text-white hover:bg-red-700 disabled:opacity-60">
          <Save class="h-3.5 w-3.5" />Simpan
        </button>
      </div>
    </form>
  </div>
</template>
