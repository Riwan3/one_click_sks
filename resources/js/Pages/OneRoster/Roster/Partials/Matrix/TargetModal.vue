<script setup>
import { watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import BaseModal from '@/Components/ui/BaseModal.vue';
import FormInput from '@/Components/ui/FormInput.vue';
import FormAlert from '@/Components/ui/FormAlert.vue';
import ModalActions from '@/Components/ui/ModalActions.vue';

const props = defineProps({
  rosterId: { type: Number, required: true },
  section: { type: Object, default: null }, // section yang diatur; null = modal tertutup
});

const emit = defineEmits(['close']);

const form = useForm({ target_siang: '', target_malam: '' });

const handleSubmit = () => form.post(`/one-roster/roster/${props.rosterId}/sections/${props.section.id}/target`, {
  preserveScroll: true,
  onSuccess: () => emit('close'),
});

watch(() => props.section, (section) => {
  if (!section) return;
  form.clearErrors();
  form.defaults({ target_siang: section.targets.SIANG ?? '', target_malam: section.targets.MALAM ?? '' }).reset();
});
</script>

<template>
  <BaseModal :open="section !== null" title="Target Jumlah Orang per Hari" @close="emit('close')">
    <form @submit.prevent="handleSubmit">
      <div class="space-y-3 px-5 py-4">
        <FormAlert :message="form.errors.form" />
        <FormInput :model-value="section?.name ?? ''" label="Section" readonly />
        <div class="grid grid-cols-2 gap-3">
          <FormInput v-model="form.target_siang" label="Target SIANG" type="number" min="0" max="999" placeholder="kosong = tanpa target" />
          <FormInput v-model="form.target_malam" label="Target MALAM" type="number" min="0" max="999" placeholder="kosong = tanpa target" />
        </div>
        <p class="text-[11px] text-slate-500">
          Jumlah orang per hari <strong class="text-red-600">di bawah target = merah</strong>, <strong class="text-amber-600">sama dengan target = kuning</strong>,
          <strong class="text-emerald-600">di atas target = hijau</strong>. Contoh target 2: 1 orang merah, 2 kuning, 3 hijau.
        </p>
      </div>
      <ModalActions :processing="form.processing" @cancel="emit('close')" />
    </form>
  </BaseModal>
</template>
