<script setup>
import { watch } from 'vue';
import { router, useForm } from '@inertiajs/vue3';
import { Trash2 } from 'lucide-vue-next';
import confirmDialog from '@/utils/confirm';
import { useMoncuPeriodPreview } from '@/composables/useMoncuPeriodPreview';
import MoncuModal from './MoncuModal.vue';

const props = defineProps({
  open: { type: Boolean, default: false },
  target: { type: Object, default: null }, // { nrp, name, year, period: { period_no, awal, tahunan, izin_resmi, exists } }
  config: { type: Object, required: true },
});

const emit = defineEmits(['close']);

const PERIOD_URL = '/one-roster/moncu/periods';

const form = useForm({ nrp: '', year: '', period_no: '', awal: '', tahunan: '', izin_resmi: '' });
const { result, warnings, error, refresh } = useMoncuPeriodPreview(form);

const fieldClass = 'w-full rounded-lg border border-slate-300 bg-white px-2 py-1.5 text-sm';

const handleSubmit = () => form.post(PERIOD_URL, { preserveScroll: true, onSuccess: () => emit('close') });

const handleDelete = async () => {
  const label = `periode ${form.period_no} tahun ${form.year} untuk NRP ${form.nrp}`;
  const isConfirmed = await confirmDialog(`Hapus ${label}? Cuti (CT/ID) pada roster draft akan dikembalikan ke jadwal semula.`);
  if (!isConfirmed) return;
  router.delete(PERIOD_URL, {
    data: { nrp: form.nrp, year: form.year, period_no: form.period_no },
    preserveScroll: true,
    onSuccess: () => emit('close'),
    onError: (errors) => form.setError('form', errors.form),
  });
};

watch(() => props.open, (isOpen) => {
  if (!isOpen) return;
  const { nrp, year, period } = props.target;
  form.clearErrors();
  form.defaults({ nrp, year, period_no: period.period_no, awal: period.awal ?? '', tahunan: period.tahunan ?? '', izin_resmi: period.izin_resmi ?? '' }).reset();
  refresh();
});
</script>

<template>
  <MoncuModal :open="open" title="Periode Cuti" @close="emit('close')">
    <form id="moncu-period-form" class="space-y-4" @submit.prevent="handleSubmit">
      <p v-if="form.errors.form" class="rounded-lg bg-red-50 px-3 py-2 text-xs text-red-700">{{ form.errors.form }}</p>
      <div>
        <span class="text-xs text-slate-500">Karyawan</span>
        <p class="text-sm font-semibold text-slate-800">{{ target?.name }} ({{ form.nrp }}) &middot; Periode {{ form.period_no }} / {{ form.year }}</p>
      </div>
      <div class="space-y-2 rounded-lg bg-amber-50 p-3">
        <p class="text-xs font-semibold text-slate-700">Input manual</p>
        <label class="block">
          <span class="mb-1 block text-xs text-slate-600">AWAL (tanggal aktual mulai cuti)</span>
          <input v-model="form.awal" type="date" :class="fieldClass" />
          <span class="text-[11px] text-slate-500">Kosongkan bila cuti belum berjalan: Roster tidak akan menghasilkan CT.</span>
        </label>
        <div class="grid grid-cols-2 gap-2">
          <label class="block">
            <span class="mb-1 block text-xs text-slate-600">TAHUNAN</span>
            <input v-model="form.tahunan" type="number" step="1" min="-13" :max="config.max_tahunan" :class="fieldClass" />
          </label>
          <label class="block">
            <span class="mb-1 block text-xs text-slate-600">IZIN RESMI</span>
            <input v-model="form.izin_resmi" type="number" step="1" min="0" :max="config.max_izin_resmi" :class="fieldClass" />
          </label>
        </div>
      </div>
      <div class="rounded-lg bg-slate-50 p-3">
        <p class="mb-2 text-xs font-semibold text-slate-700">Otomatis (readonly)</p>
        <div class="grid grid-cols-3 gap-2">
          <label v-for="(label, key) in { akhir: 'AKHIR', induksi: 'INDUKSI', plan: 'PLAN' }" :key="key" class="block">
            <span class="mb-1 block text-xs text-slate-600">{{ label }}</span>
            <input :value="result[key]" type="text" readonly tabindex="-1" :class="[fieldClass, 'bg-slate-100']" />
          </label>
        </div>
      </div>
      <p v-if="error" class="text-xs text-red-600">{{ error }}</p>
      <p v-else-if="warnings" class="text-xs text-amber-700">{{ warnings }}</p>
    </form>
    <template #footer>
      <button v-if="target?.period.exists" type="button" class="mr-auto flex items-center gap-1 rounded-lg border border-red-300 px-3 py-1.5 text-xs font-semibold text-red-600 hover:bg-red-50" @click="handleDelete">
        <Trash2 class="h-3.5 w-3.5" /> Hapus periode
      </button>
      <button type="button" class="rounded-lg bg-slate-100 px-3 py-1.5 text-xs font-semibold text-slate-700" @click="emit('close')">Batal</button>
      <button type="submit" form="moncu-period-form" :disabled="form.processing" class="rounded-lg bg-blue-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-blue-700 disabled:opacity-60">Simpan</button>
    </template>
  </MoncuModal>
</template>
