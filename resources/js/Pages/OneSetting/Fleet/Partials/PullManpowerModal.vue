<script setup>
import { computed, ref, watch } from 'vue';
import BaseModal from '@/Components/ui/BaseModal.vue';
import ModalActions from '@/Components/ui/ModalActions.vue';

const props = defineProps({
  open: { type: Boolean, default: false },
  preview: { type: Array, default: () => [] }, // [{ name, type, total, new }]
  isProcessing: { type: Boolean, default: false },
});

const emit = defineEmits(['close', 'submit']);

const TYPE_OPTIONS = [
  { value: 'loader', label: 'Loader' },
  { value: 'hauler', label: 'Hauler' },
  { value: 'skip', label: 'Tidak ikut' },
];

const typeByCategory = ref({});

const totalNew = computed(() => props.preview.filter((c) => typeByCategory.value[c.name] !== 'skip').reduce((sum, c) => sum + c.new, 0));

const handleSubmit = () => emit('submit', props.preview.map((c) => ({ name: c.name, type: typeByCategory.value[c.name] })));

watch(() => props.open, (isOpen) => {
  if (isOpen) typeByCategory.value = Object.fromEntries(props.preview.map((c) => [c.name, c.type]));
});
</script>

<template>
  <BaseModal :open="open" title="Tarik dari Master Manpower" size="lg" @close="emit('close')">
    <form @submit.prevent="handleSubmit">
      <div class="space-y-3 px-5 py-4">
        <p class="text-xs text-slate-500">
          Kategori Master CSA dipetakan otomatis (Excavator / Wheel Loader = Loader, Dump Truck = Hauler). Ubah bila perlu.
          Unit yang kodenya sudah ada dilewati; slot spare tidak ikut.
        </p>
        <div class="max-h-[50vh] overflow-auto rounded-lg border border-slate-200">
          <table class="w-full text-left text-xs">
            <thead class="sticky top-0 bg-slate-50 text-[11px] uppercase text-slate-500">
              <tr><th class="px-3 py-2">Kategori</th><th class="px-3 py-2">Unit</th><th class="px-3 py-2">Baru</th><th class="px-3 py-2">Jadi</th></tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
              <tr v-for="category in preview" :key="category.name">
                <td class="px-3 py-2 font-semibold">{{ category.name }}</td>
                <td class="px-3 py-2">{{ category.total }}</td>
                <td class="px-3 py-2">{{ category.new }}</td>
                <td class="px-3 py-2">
                  <select v-model="typeByCategory[category.name]" class="rounded border border-slate-300 px-1.5 py-1 text-xs">
                    <option v-for="option in TYPE_OPTIONS" :key="option.value" :value="option.value">{{ option.label }}</option>
                  </select>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        <p class="text-xs font-semibold text-slate-700">{{ totalNew }} unit baru akan ditambahkan.</p>
      </div>
      <ModalActions submit-label="Tarik unit" :processing="isProcessing" :disabled="!totalNew" @cancel="emit('close')" />
    </form>
  </BaseModal>
</template>
