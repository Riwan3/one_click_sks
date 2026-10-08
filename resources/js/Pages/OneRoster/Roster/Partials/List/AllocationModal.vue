<script setup>
import { watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import BaseModal from '@/Components/ui/BaseModal.vue';
import FormInput from '@/Components/ui/FormInput.vue';
import FormAlert from '@/Components/ui/FormAlert.vue';
import ModalActions from '@/Components/ui/ModalActions.vue';

const props = defineProps({
  rosterId: { type: Number, required: true },
  detail: { type: Object, default: null }, // detail yang dialokasikan; null = modal tertutup
});

const emit = defineEmits(['close']);

const form = useForm({ unit: '', section: '' });

const handleSubmit = () => form.post(`/one-roster/roster/${props.rosterId}/details/${props.detail.id}/allocations`, {
  preserveScroll: true,
  onSuccess: () => emit('close'),
});

watch(() => props.detail, (detail) => {
  if (!detail) return;
  form.reset().clearErrors();
});
</script>

<template>
  <BaseModal :open="detail !== null" title="Tambah Alokasi" @close="emit('close')">
    <form autocomplete="off" @submit.prevent="handleSubmit">
      <div class="space-y-3 px-5 py-4">
        <FormAlert :message="form.errors.form" />
        <FormInput v-model="form.unit" label="Unit" maxlength="50" required />
        <FormInput v-model="form.section" label="Section" maxlength="100" />
      </div>
      <ModalActions :processing="form.processing" @cancel="emit('close')" />
    </form>
  </BaseModal>
</template>
