<script setup>
import { computed, ref, watch } from 'vue';
import BaseModal from '@/Components/ui/BaseModal.vue';
import FormInput from '@/Components/ui/FormInput.vue';
import ModalActions from '@/Components/ui/ModalActions.vue';

const props = defineProps({
  target: { type: Object, default: null }, // { mode: 'add' } | { mode: 'edit', area }
  isProcessing: { type: Boolean, default: false },
});

const emit = defineEmits(['close', 'submit']);

const name = ref('');

const isEdit = computed(() => props.target?.mode === 'edit');

const handleSubmit = () => emit('submit', name.value, props.target.area?.name ?? null);

watch(() => props.target, (target) => {
  if (target) name.value = target.area?.name ?? '';
});
</script>

<template>
  <BaseModal :open="!!target" :title="isEdit ? 'Ubah area' : 'Tambah area'" size="sm" @close="emit('close')">
    <form @submit.prevent="handleSubmit">
      <div class="space-y-3 px-5 py-4">
        <FormInput v-model="name" label="NAMA AREA" required maxlength="100" placeholder="mis. FRONT TIMUR" class="uppercase" :hint="isEdit ? 'Mengubah nama ikut memperbarui area seluruh cluster di dalamnya.' : 'Tampil sebagai pita AREA diikuti nama ini.'" />
      </div>
      <ModalActions :processing="isProcessing" @cancel="emit('close')" />
    </form>
  </BaseModal>
</template>
