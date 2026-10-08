<script setup>
import { computed, reactive, watch } from 'vue';
import BaseModal from '@/Components/ui/BaseModal.vue';
import FormInput from '@/Components/ui/FormInput.vue';
import FormSelect from '@/Components/ui/FormSelect.vue';
import ModalActions from '@/Components/ui/ModalActions.vue';

const props = defineProps({
  target: { type: Object, default: null }, // { mode: 'add', area } | { mode: 'edit', cluster }
  nameOptions: { type: Array, default: () => [] },
  areaNames: { type: Array, default: () => [] }, // area yang ada (untuk pindah area saat ubah)
  isProcessing: { type: Boolean, default: false },
});

const emit = defineEmits(['close', 'submit']);

const form = reactive({ name: '', area: '' });

const isEdit = computed(() => props.target?.mode === 'edit');

const handleSubmit = () => emit('submit', { name: form.name, area: form.area }, props.target.cluster?.name ?? null);

watch(() => props.target, (target) => {
  if (!target) return;
  Object.assign(form, { name: target.cluster?.name ?? '', area: target.cluster?.area ?? target.area ?? '' });
});
</script>

<template>
  <BaseModal :open="!!target" :title="isEdit ? 'Ubah cluster' : `Tambah cluster${target?.area ? ' - AREA ' + target.area : ''}`" size="sm" @close="emit('close')">
    <form @submit.prevent="handleSubmit">
      <div class="space-y-3 px-5 py-4">
        <FormInput v-model="form.name" label="NAMA CLUSTER" required maxlength="100" list="fleet-cluster-names" placeholder="mis. OB IPPKH, COAL GETTING" class="uppercase" :hint="isEdit ? 'Mengubah nama ikut memperbarui seluruh loader di cluster ini.' : ''" />
        <datalist id="fleet-cluster-names"><option v-for="name in nameOptions" :key="name" :value="name" /></datalist>
        <FormSelect v-if="isEdit" v-model="form.area" label="AREA" hint="Pilih area lain untuk memindahkan cluster; kosong = tanpa area.">
          <option value="">- Tanpa area -</option>
          <option v-for="name in areaNames" :key="name" :value="name">{{ name }}</option>
        </FormSelect>
      </div>
      <ModalActions :processing="isProcessing" @cancel="emit('close')" />
    </form>
  </BaseModal>
</template>
