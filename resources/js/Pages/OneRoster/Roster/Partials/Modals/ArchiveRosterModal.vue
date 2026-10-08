<script setup>
import { watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import BaseModal from '@/Components/ui/BaseModal.vue';
import FormInput from '@/Components/ui/FormInput.vue';
import FormAlert from '@/Components/ui/FormAlert.vue';
import ModalActions from '@/Components/ui/ModalActions.vue';

const props = defineProps({
  roster: { type: Object, default: null }, // roster yang diarsipkan; null = modal tertutup
});

const emit = defineEmits(['close']);

const form = useForm({ status: 'archived', auth_nrp: '', auth_password: '' });

// Aksi tak bisa dibatalkan: wajib verifikasi ulang NRP + password akun yang login.
const handleSubmit = () => {
  form.post(`/one-roster/roster/${props.roster.id}/status`, {
    preserveScroll: true,
    onSuccess: () => emit('close'),
    onError: () => form.reset('auth_password'),
  });
};

watch(() => props.roster, (roster) => {
  if (roster) form.reset().clearErrors();
});
</script>

<template>
  <BaseModal :open="roster !== null" title="Konfirmasi" size="sm" @close="emit('close')">
    <form autocomplete="off" @submit.prevent="handleSubmit">
      <div class="space-y-3 px-5 py-4">
        <p class="text-sm text-slate-700">Arsipkan roster ini? Roster yang diarsipkan tidak dapat diubah lagi.</p>
        <FormAlert :message="form.errors.form" />
        <FormInput v-model="form.auth_nrp" label="NRP" autocomplete="off" required />
        <FormInput v-model="form.auth_password" label="Password" type="password" autocomplete="new-password" hint="Masukkan NRP dan password akun Anda yang sedang login untuk melanjutkan." required />
      </div>
      <ModalActions submit-label="Ya, lanjutkan" tone="red" :processing="form.processing" @cancel="emit('close')" />
    </form>
  </BaseModal>
</template>
