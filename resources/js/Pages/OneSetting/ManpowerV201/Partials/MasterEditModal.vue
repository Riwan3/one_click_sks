<script setup>
import { reactive, watch } from 'vue';
import BaseModal from '@/Components/ui/BaseModal.vue';
import FormInput from '@/Components/ui/FormInput.vue';
import FormSelect from '@/Components/ui/FormSelect.vue';
import ModalActions from '@/Components/ui/ModalActions.vue';

const props = defineProps({
  target: { type: Object, default: null }, // { kind: csa|category|unit, id, name, col_pos? }
  isProcessing: { type: Boolean, default: false },
});

const emit = defineEmits(['close', 'submit']);

const TITLE = { csa: 'Ubah nama CSA', category: 'Ubah kategori', unit: 'Ubah kode unit' };
const NAME_LABEL = { csa: 'Nama CSA', category: 'Nama kategori', unit: 'Kode unit' };

const form = reactive({ name: '', colPos: 1 });

const handleSubmit = () => emit('submit', { ...props.target, name: form.name, col_pos: Number(form.colPos) });

watch(() => props.target, (target) => {
  if (!target) return;
  form.name = target.name;
  form.colPos = target.col_pos ?? 1;
});
</script>

<template>
  <BaseModal :open="!!target" :title="target ? TITLE[target.kind] : ''" size="sm" @close="emit('close')">
    <form @submit.prevent="handleSubmit">
      <div class="space-y-3 px-5 py-4">
        <FormInput v-model="form.name" :label="target ? NAME_LABEL[target.kind] : ''" required class="uppercase" />
        <FormSelect v-if="target?.kind === 'category'" v-model="form.colPos" label="Kolom layout">
          <option :value="1">Kolom kiri</option>
          <option :value="2">Kolom kanan</option>
        </FormSelect>
      </div>
      <ModalActions :processing="isProcessing" @cancel="emit('close')" />
    </form>
  </BaseModal>
</template>
