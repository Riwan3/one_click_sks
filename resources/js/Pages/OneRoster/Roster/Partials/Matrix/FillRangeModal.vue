<script setup>
import { watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import BaseModal from '@/Components/ui/BaseModal.vue';
import FormInput from '@/Components/ui/FormInput.vue';
import FormSelect from '@/Components/ui/FormSelect.vue';
import FormAlert from '@/Components/ui/FormAlert.vue';
import ModalActions from '@/Components/ui/ModalActions.vue';

const props = defineProps({
  roster: { type: Object, required: true },
  member: { type: Object, default: null }, // anggota yang diisi; null = modal tertutup
  shifts: { type: Array, required: true },
  monthFrom: { type: String, required: true },
  monthTo: { type: String, required: true },
});

const emit = defineEmits(['close']);

const form = useForm({ nrp: '', date_from: '', date_to: '', shift_code: '' });

const handleSubmit = () => form.post(`/one-roster/roster/${props.roster.id}/cells/fill`, {
  preserveScroll: true,
  onSuccess: () => emit('close'),
});

watch(() => props.member, (member) => {
  if (!member) return;
  form.clearErrors();
  form.defaults({ nrp: member.nrp, date_from: props.monthFrom, date_to: props.monthTo, shift_code: '' }).reset();
});
</script>

<template>
  <BaseModal :open="member !== null" title="Isi Rentang Tanggal" @close="emit('close')">
    <form @submit.prevent="handleSubmit">
      <div class="space-y-3 px-5 py-4">
        <FormAlert :message="form.errors.form" />
        <FormInput :model-value="member?.employee_name ?? ''" label="Karyawan" readonly />
        <div class="grid grid-cols-2 gap-3">
          <FormInput v-model="form.date_from" label="Dari" type="date" required :min="roster.period_start" :max="roster.period_end" />
          <FormInput v-model="form.date_to" label="Sampai" type="date" required :min="roster.period_start" :max="roster.period_end" />
        </div>
        <FormSelect v-model="form.shift_code" label="Shift" hint="Berlaku untuk semua tanggal pada rentang. Sel yang sudah terisi akan ditimpa.">
          <option value="">— Kosongkan sel —</option>
          <option v-for="shift in shifts" :key="shift.code" :value="shift.code">{{ shift.code }} - {{ shift.label }}</option>
        </FormSelect>
      </div>
      <ModalActions submit-label="Terapkan" :processing="form.processing" @cancel="emit('close')" />
    </form>
  </BaseModal>
</template>
