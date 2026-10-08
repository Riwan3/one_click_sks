<script setup>
import { reactive, watch } from 'vue';
import BaseModal from '@/Components/ui/BaseModal.vue';
import FormInput from '@/Components/ui/FormInput.vue';
import FormSelect from '@/Components/ui/FormSelect.vue';
import ModalActions from '@/Components/ui/ModalActions.vue';
import { SHIFT_OPTION_LABEL } from '@/utils/manpowerV201';
import { previousShift, yesterday } from '@/utils/fleet';

const props = defineProps({
  open: { type: Boolean, default: false },
  filters: { type: Object, required: true }, // { date, shift } tujuan
  hasData: { type: Boolean, default: false }, // tujuan sudah berisi -> akan ditimpa
  isProcessing: { type: Boolean, default: false },
});

const emit = defineEmits(['close', 'submit']);

const source = reactive({ date: '', shift: 'D' });

const SHORTCUTS = [
  { label: 'Shift sebelumnya', pick: () => previousShift(props.filters.date, props.filters.shift) },
  { label: 'Kemarin (shift sama)', pick: () => yesterday(props.filters.date, props.filters.shift) },
];

const handleShortcut = (shortcut) => Object.assign(source, shortcut.pick());

const handleSubmit = () => emit('submit', { ...source });

watch(() => props.open, (isOpen) => {
  if (isOpen) Object.assign(source, previousShift(props.filters.date, props.filters.shift));
});
</script>

<template>
  <BaseModal :open="open" title="Salin setting dari tanggal / shift lain" size="sm" @close="emit('close')">
    <form @submit.prevent="handleSubmit">
      <div class="space-y-3 px-5 py-4">
        <div class="flex flex-wrap gap-2">
          <button v-for="shortcut in SHORTCUTS" :key="shortcut.label" type="button" class="rounded-full border border-blue-200 px-3 py-1 text-xs font-semibold text-blue-600 hover:bg-blue-50" @click="handleShortcut(shortcut)">
            {{ shortcut.label }}
          </button>
        </div>
        <FormInput v-model="source.date" type="date" label="TANGGAL SUMBER" required />
        <FormSelect v-model="source.shift" label="SHIFT SUMBER">
          <option v-for="(label, code) in SHIFT_OPTION_LABEL" :key="code" :value="code">{{ label }}</option>
        </FormSelect>
        <p v-if="hasData" class="rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-800">
          Tanggal dan shift ini sudah berisi setting. Menyalin akan <strong>menggantikan</strong> seluruh isinya.
        </p>
      </div>
      <ModalActions submit-label="Salin" :processing="isProcessing" :tone="hasData ? 'red' : 'blue'" @cancel="emit('close')" />
    </form>
  </BaseModal>
</template>
