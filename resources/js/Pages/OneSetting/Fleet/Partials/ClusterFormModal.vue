<script setup>
import { computed, reactive, watch } from 'vue';
import BaseModal from '@/Components/ui/BaseModal.vue';
import FormInput from '@/Components/ui/FormInput.vue';
import ModalActions from '@/Components/ui/ModalActions.vue';

const props = defineProps({
  target: { type: Object, default: null }, // { mode: 'add' } | { mode: 'edit', cluster }
  nameOptions: { type: Array, default: () => [] },
  areaOptions: { type: Array, default: () => [] },
  isProcessing: { type: Boolean, default: false },
});

const emit = defineEmits(['close', 'submit']);

const form = reactive({ name: '', area: '' });

const isEdit = computed(() => props.target?.mode === 'edit');

const handleSubmit = () => emit('submit', { name: form.name, area: form.area }, props.target.cluster?.name ?? null);

watch(() => props.target, (target) => {
  if (!target) return;
  Object.assign(form, { name: target.cluster?.name ?? '', area: target.cluster?.area ?? '' });
});
</script>

<template>
  <BaseModal :open="!!target" :title="isEdit ? 'Ubah cluster' : 'Tambah cluster'" size="sm" @close="emit('close')">
    <form @submit.prevent="handleSubmit">
      <div class="space-y-3 px-5 py-4">
        <FormInput v-model="form.name" label="NAMA CLUSTER" required maxlength="100" list="fleet-cluster-names" placeholder="mis. OB IPPKH, COAL GETTING" class="uppercase" :hint="isEdit ? 'Mengubah nama ikut memperbarui seluruh loader di cluster ini.' : ''" />
        <datalist id="fleet-cluster-names"><option v-for="name in nameOptions" :key="name" :value="name" /></datalist>
        <FormInput v-model="form.area" label="AREA" maxlength="100" list="fleet-cluster-areas" placeholder="mis. Timur, Tengah, Barat" />
        <datalist id="fleet-cluster-areas"><option v-for="area in areaOptions" :key="area" :value="area" /></datalist>
      </div>
      <ModalActions :processing="isProcessing" @cancel="emit('close')" />
    </form>
  </BaseModal>
</template>
