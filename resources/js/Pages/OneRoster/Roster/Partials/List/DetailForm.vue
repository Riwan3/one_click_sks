<script setup>
import { Plus } from 'lucide-vue-next';
import { useForm } from '@inertiajs/vue3';
import FormInput from '@/Components/ui/FormInput.vue';
import FormSelect from '@/Components/ui/FormSelect.vue';
import FormAlert from '@/Components/ui/FormAlert.vue';
import EmployeeSearchInput from '../EmployeeSearchInput.vue';

const props = defineProps({
  roster: { type: Object, required: true },
  shifts: { type: Array, required: true },
});

const form = useForm({ nrp: '', work_date: '', shift_code: '', unit: '', section: '' });

const handleSubmit = () => form.post(`/one-roster/roster/${props.roster.id}/details`, {
  preserveScroll: true,
  onSuccess: () => form.reset(),
});
</script>

<template>
  <form class="rounded-2xl border border-slate-200 bg-white shadow-sm" autocomplete="off" @submit.prevent="handleSubmit">
    <div class="border-b border-slate-100 px-4 py-2 text-sm font-semibold text-slate-800">Tambah Detail</div>
    <div class="space-y-2 p-4">
      <FormAlert :message="form.errors.form" />
      <div class="grid grid-cols-2 gap-2 md:grid-cols-6">
        <div class="col-span-2"><EmployeeSearchInput v-model="form.nrp" /></div>
        <FormInput v-model="form.work_date" label="Tanggal" type="date" required :min="roster.period_start" :max="roster.period_end" />
        <FormSelect v-model="form.shift_code" label="Shift" required>
          <option value="">Pilih</option>
          <option v-for="shift in shifts" :key="shift.code" :value="shift.code">{{ shift.code }} - {{ shift.label }}</option>
        </FormSelect>
        <FormInput v-model="form.unit" label="Unit" maxlength="50" />
        <FormInput v-model="form.section" label="Section" maxlength="100" />
      </div>
      <div class="flex flex-wrap items-center justify-between gap-2">
        <p class="text-[11px] text-slate-500">Nama & jabatan diambil dari data karyawan (read-only) dan disimpan sebagai snapshot. Unit/section boleh dikosongkan.</p>
        <button type="submit" :disabled="form.processing" class="flex items-center gap-1 rounded-lg bg-blue-600 px-3 py-1.5 text-sm font-semibold text-white hover:bg-blue-700 disabled:opacity-60"><Plus class="h-4 w-4" />Tambah</button>
      </div>
    </div>
  </form>
</template>
