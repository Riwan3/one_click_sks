<script setup>
import { computed, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import BaseModal from '@/Components/ui/BaseModal.vue';
import FormInput from '@/Components/ui/FormInput.vue';
import FormSelect from '@/Components/ui/FormSelect.vue';
import FormAlert from '@/Components/ui/FormAlert.vue';
import ModalActions from '@/Components/ui/ModalActions.vue';

const props = defineProps({
  roster: { type: Object, required: true },
  detail: { type: Object, default: null }, // detail yang diubah; null = modal tertutup
  shifts: { type: Array, required: true },
});

const emit = defineEmits(['close']);

const form = useForm({ work_date: '', shift_code: '', unit: '', section: '' });

const extraAllocationNote = computed(() => (props.detail?.allocations.length > 1
  ? `Detail ini punya ${props.detail.allocations.length} alokasi; form ini hanya mengubah alokasi pertama.`
  : ''));

const handleSubmit = () => form.put(`/one-roster/roster/${props.roster.id}/details/${props.detail.id}`, {
  preserveScroll: true,
  onSuccess: () => emit('close'),
});

watch(() => props.detail, (detail) => {
  if (!detail) return;
  form.clearErrors();
  form.defaults({
    work_date: detail.work_date,
    shift_code: detail.shift_code,
    unit: detail.allocations[0]?.unit ?? '',
    section: detail.allocations[0]?.section ?? '',
  }).reset();
});
</script>

<template>
  <BaseModal :open="detail !== null" title="Edit Detail" @close="emit('close')">
    <form autocomplete="off" @submit.prevent="handleSubmit">
      <div class="space-y-3 px-5 py-4">
        <FormAlert :message="form.errors.form" />
        <div class="grid grid-cols-[5fr_7fr] gap-3">
          <FormInput :model-value="detail?.nrp ?? ''" label="NRP" readonly />
          <FormInput :model-value="detail?.employee_name ?? ''" label="Nama" readonly />
        </div>
        <div class="grid grid-cols-2 gap-3">
          <FormInput v-model="form.work_date" label="Tanggal" type="date" required :min="roster.period_start" :max="roster.period_end" />
          <FormSelect v-model="form.shift_code" label="Shift" required>
            <option v-for="shift in shifts" :key="shift.code" :value="shift.code">{{ shift.code }} - {{ shift.label }}</option>
          </FormSelect>
        </div>
        <div class="grid grid-cols-2 gap-3">
          <FormInput v-model="form.unit" label="Unit" maxlength="50" />
          <FormInput v-model="form.section" label="Section" maxlength="100" />
        </div>
        <p class="text-[11px] text-slate-500">Mengosongkan Unit akan menghapus alokasi tersebut. NRP tidak dapat diubah.</p>
        <p v-if="extraAllocationNote" class="text-[11px] text-amber-700">{{ extraAllocationNote }}</p>
      </div>
      <ModalActions :processing="form.processing" @cancel="emit('close')" />
    </form>
  </BaseModal>
</template>
