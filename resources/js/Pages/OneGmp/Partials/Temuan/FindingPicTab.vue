<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { Send, Info } from 'lucide-vue-next';
import { FINDING_BASE_URL, addDays } from '@/utils/gmpFinding';
import PicCombobox from './PicCombobox.vue';

const props = defineProps({
  finding: { type: Object, required: true },
  employees: { type: Array, default: () => [] },
  priorityOptions: { type: Array, default: () => [] },
  slaDays: { type: Object, default: () => ({}) },
});

const emit = defineEmits(['saved']);

const form = useForm({
  pic_nrp: props.finding.pic_user_id ?? '',
  prioritas: props.finding.priority ?? '',
  deadline: props.finding.due_date ?? '',
  observer: props.finding.observer ?? '',
  rekomendasi_awal: props.finding.recommendation ?? '',
});

const picJabatan = ref(props.employees.find((e) => e.nrp === props.finding.pic_user_id)?.jabatan ?? '');

const fieldClass = 'w-full rounded-lg border-0 bg-slate-100 px-3 py-2 text-sm';
const labelClass = 'mb-1 block text-xs font-bold text-slate-700';

/** Deadline otomatis mengikuti SLA Master GMP untuk prioritas terpilih. */
const handlePriorityChange = () => {
  if (props.slaDays[form.prioritas] !== undefined) form.deadline = addDays(props.slaDays[form.prioritas]);
};

const handleSubmit = () => form.post(`${FINDING_BASE_URL}/${props.finding.id}/pic`, { preserveScroll: true, preserveState: true, onSuccess: () => emit('saved') });
</script>

<template>
  <form class="space-y-3" @submit.prevent="handleSubmit">
    <h6 class="mb-2 text-xs font-bold uppercase tracking-wide text-blue-700">Penugasan Temuan</h6>
    <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
      <div>
        <label :class="labelClass">PIC Utama (Person In Charge) <span class="text-red-600">*</span></label>
        <PicCombobox v-model="form.pic_nrp" :employees="employees" @selected="picJabatan = $event.jabatan ?? ''" />
        <p v-if="form.errors.pic_nrp" class="mt-1 text-[11px] text-red-600">{{ form.errors.pic_nrp }}</p>
      </div>
      <div>
        <label :class="labelClass">Unit / Departemen</label>
        <input :value="picJabatan" type="text" readonly placeholder="Otomatis terisi..." :class="fieldClass" />
      </div>
      <div class="md:col-span-2">
        <label :class="labelClass">Observer / Pihak Terkait</label>
        <input v-model="form.observer" type="text" placeholder="Pisahkan dengan koma jika lebih dari satu..." :class="fieldClass" />
      </div>
      <div>
        <label :class="labelClass">Prioritas Penanganan <span class="text-red-600">*</span></label>
        <select v-model="form.prioritas" required :class="fieldClass" @change="handlePriorityChange">
          <option value="">-- Pilih Prioritas --</option>
          <option v-for="p in priorityOptions" :key="p" :value="p">{{ p }}{{ slaDays[p] !== undefined ? ` (${slaDays[p]} Hari)` : '' }}</option>
        </select>
        <p class="mt-1 flex items-center gap-1 text-[11px] text-slate-500"><Info class="h-3 w-3" />Deadline akan dihitung otomatis sesuai SLA Master GMP.</p>
      </div>
      <div>
        <label :class="labelClass">Target Penyelesaian (Deadline)</label>
        <input v-model="form.deadline" type="date" required :class="[fieldClass, 'font-bold text-red-600']" />
      </div>
      <div class="md:col-span-2">
        <label :class="labelClass">Rekomendasi Awal (Tindakan Perbaikan)</label>
        <textarea v-model="form.rekomendasi_awal" rows="3" placeholder="Saran perbaikan awal..." :class="fieldClass" />
      </div>
    </div>
    <div class="flex justify-end">
      <button type="submit" :disabled="form.processing" class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-bold text-white hover:bg-blue-700 disabled:opacity-60">
        <Send class="h-4 w-4" />Tetapkan PIC &amp; Open Temuan
      </button>
    </div>
  </form>
</template>
