<script setup>
import { watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import BaseModal from '@/Components/ui/BaseModal.vue';
import FormInput from '@/Components/ui/FormInput.vue';
import FormSelect from '@/Components/ui/FormSelect.vue';
import FormAlert from '@/Components/ui/FormAlert.vue';
import ModalActions from '@/Components/ui/ModalActions.vue';
import EmployeeSearchInput from '../EmployeeSearchInput.vue';
import { KODE_OFF_OPTIONS, WORK_PATTERNS } from '@/utils/rosterMatrix';

const props = defineProps({
  rosterId: { type: Number, required: true },
  section: { type: Object, default: null }, // section tujuan; null = modal tertutup
});

const emit = defineEmits(['close']);

const form = useForm({ nrp: '', work_pattern: 'ROTASI', unit: '', kode_off: '' });

const handleSubmit = () => form.post(`/one-roster/roster/${props.rosterId}/sections/${props.section.id}/members`, {
  preserveScroll: true,
  onSuccess: () => emit('close'),
});

watch(() => props.section, (section) => {
  if (!section) return;
  form.reset().clearErrors();
});
</script>

<template>
  <BaseModal :open="section !== null" title="Tambah Anggota" @close="emit('close')">
    <form autocomplete="off" @submit.prevent="handleSubmit">
      <div class="space-y-3 px-5 py-4">
        <FormAlert :message="form.errors.form" />
        <FormInput :model-value="section?.name ?? ''" label="Section" readonly />
        <EmployeeSearchInput v-model="form.nrp" />
        <FormSelect v-model="form.work_pattern" label="Pola Kerja">
          <option v-for="[value, label] in WORK_PATTERNS" :key="value" :value="value">{{ label }}</option>
        </FormSelect>
        <div class="grid grid-cols-[7fr_5fr] gap-3">
          <FormInput v-model="form.unit" label="Unit" maxlength="50" placeholder="Kosong = unit dari data karyawan" />
          <FormSelect v-model="form.kode_off" label="Kode Off">
            <option value="">- manual (tanpa putaran) -</option>
            <option v-for="code in KODE_OFF_OPTIONS" :key="code" :value="code">{{ code }}</option>
          </FormSelect>
        </div>
      </div>
      <ModalActions :processing="form.processing" @cancel="emit('close')" />
    </form>
  </BaseModal>
</template>
