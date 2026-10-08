<script setup>
import { computed, ref, watch } from 'vue';
import BaseModal from '@/Components/ui/BaseModal.vue';
import ModalActions from '@/Components/ui/ModalActions.vue';
import { PARKING_CLASS, PARKING_LABEL } from '@/utils/fleet';

const props = defineProps({
  open: { type: Boolean, default: false },
  preview: { type: Array, default: () => [] }, // [{ csa, name, type, parking_code, total, new }]
  isProcessing: { type: Boolean, default: false },
});

const emit = defineEmits(['close', 'submit']);

const TYPE_OPTIONS = [
  { value: 'loader', label: 'Loader' },
  { value: 'hauler', label: 'Hauler' },
  { value: 'skip', label: 'Tidak ikut' },
];

const keyOf = (row) => `${row.csa}|${row.name}`;

const typeByRow = ref({});

const totalNew = computed(() => props.preview.filter((row) => typeByRow.value[keyOf(row)] !== 'skip').reduce((sum, row) => sum + row.new, 0));

const handleSubmit = () => emit('submit', props.preview.map((row) => ({ csa: row.csa, name: row.name, type: typeByRow.value[keyOf(row)] })));

watch(() => props.open, (isOpen) => {
  if (isOpen) typeByRow.value = Object.fromEntries(props.preview.map((row) => [keyOf(row), row.type]));
});
</script>

<template>
  <BaseModal :open="open" title="Tarik dari Master Manpower" size="xl" @close="emit('close')">
    <form @submit.prevent="handleSubmit">
      <div class="space-y-3 px-5 py-4">
        <p class="text-xs text-slate-500">
          Kategori Master CSA dipetakan otomatis (Excavator / Wheel Loader = Loader, Dump Truck = Hauler). Parkiran hauler diisi dari nama CSA
          (Monte = M, Gibson = G) atau kategori Jumbo Vessel (J). Unit yang kodenya sudah ada dilewati, hanya parkiran yang masih kosong diisi.
        </p>
        <div class="max-h-[50vh] overflow-auto rounded-lg border border-slate-200">
          <table class="w-full text-left text-xs">
            <thead class="sticky top-0 bg-slate-50 text-[11px] uppercase text-slate-500">
              <tr><th class="px-3 py-2">CSA</th><th class="px-3 py-2">Kategori</th><th class="px-3 py-2">Unit</th><th class="px-3 py-2">Baru</th><th class="px-3 py-2">Parkiran</th><th class="px-3 py-2">Jadi</th></tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
              <tr v-for="row in preview" :key="keyOf(row)">
                <td class="px-3 py-2">{{ row.csa }}</td>
                <td class="px-3 py-2 font-semibold">{{ row.name }}</td>
                <td class="px-3 py-2">{{ row.total }}</td>
                <td class="px-3 py-2">{{ row.new }}</td>
                <td class="px-3 py-2">
                  <span v-if="row.parking_code && typeByRow[keyOf(row)] === 'hauler'" :class="['rounded px-1.5 py-0.5 text-[10px] font-black', PARKING_CLASS[row.parking_code]]">{{ PARKING_LABEL[row.parking_code] }}</span>
                  <template v-else>-</template>
                </td>
                <td class="px-3 py-2">
                  <select v-model="typeByRow[keyOf(row)]" class="rounded border border-slate-300 px-1.5 py-1 text-xs">
                    <option v-for="option in TYPE_OPTIONS" :key="option.value" :value="option.value">{{ option.label }}</option>
                  </select>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        <p class="text-xs font-semibold text-slate-700">{{ totalNew }} unit baru akan ditambahkan.</p>
      </div>
      <ModalActions submit-label="Tarik unit" :processing="isProcessing" @cancel="emit('close')" />
    </form>
  </BaseModal>
</template>
