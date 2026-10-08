<script setup>
import { computed, ref, watch } from 'vue';
import BaseModal from '@/Components/ui/BaseModal.vue';
import FormInput from '@/Components/ui/FormInput.vue';
import ModalActions from '@/Components/ui/ModalActions.vue';

const props = defineProps({
  open: { type: Boolean, default: false },
  preselectedNrp: { type: String, default: null },
  people: { type: Array, default: () => [] }, // Daftar Sisa
  isProcessing: { type: Boolean, default: false },
});

const emit = defineEmits(['close', 'submit']);

const LABEL_SUGGESTIONS = ['INDUKSI', 'TRAINING', 'INDUKSI PERMIT', 'STANDBY', 'DINAS LUAR'];

const search = ref('');
const selectedNrp = ref(null);
const label = ref('');

const visible = computed(() => {
  const q = search.value.toLowerCase().trim();

  return props.people.filter((p) => !q || `${p.name} ${p.nrp} ${p.home_label}`.toLowerCase().includes(q));
});
const canSubmit = computed(() => !!selectedNrp.value && label.value.trim() !== '');

const handleSubmit = () => emit('submit', selectedNrp.value, label.value.trim());

watch(() => props.open, (isOpen) => {
  if (!isOpen) return;
  search.value = '';
  label.value = '';
  selectedNrp.value = props.preselectedNrp;
});
</script>

<template>
  <BaseModal :open="open" title="Pindahkan ke Lain - Lain" @close="emit('close')">
    <form @submit.prevent="handleSubmit">
      <div class="space-y-3 px-5 py-4">
        <div>
          <span class="mb-1 block text-xs font-semibold text-slate-600">ORANG (DARI DAFTAR SISA)</span>
          <input v-model="search" type="search" placeholder="Cari nama / NRP..." class="mb-2 w-full rounded-lg border border-slate-300 px-2.5 py-1.5 text-sm focus:border-blue-500 focus:outline-none" />
          <div class="max-h-[38vh] divide-y divide-slate-100 overflow-y-auto rounded-lg border border-slate-200 text-sm">
            <p v-if="!visible.length" class="p-3 italic text-slate-400">Daftar Sisa kosong.</p>
            <button
              v-for="p in visible"
              :key="p.nrp"
              type="button"
              :class="['block w-full px-3 py-2 text-left', p.nrp === selectedNrp ? 'bg-blue-600 text-white' : 'hover:bg-slate-50']"
              @click="selectedNrp = p.nrp"
            >
              <span class="font-bold">{{ p.name }}</span> <span :class="p.nrp === selectedNrp ? '' : 'text-slate-400'">{{ p.nrp }}</span>
              <span :class="['block text-xs', p.nrp === selectedNrp ? '' : 'text-slate-500']">{{ p.home_label }}</span>
            </button>
          </div>
        </div>

        <FormInput v-model="label" label="KETERANGAN" maxlength="40" list="manpower-lain-labels" placeholder="mis. INDUKSI, TRAINING, STANDBY" class="uppercase" hint="Ketik bebas atau pilih saran. Orang ini hilang dari Daftar Sisa dan tampil di kotak Lain - Lain." />
        <datalist id="manpower-lain-labels">
          <option v-for="suggestion in LABEL_SUGGESTIONS" :key="suggestion" :value="suggestion" />
        </datalist>
      </div>
      <ModalActions submit-label="Pindahkan" :processing="isProcessing" :disabled="!canSubmit" @cancel="emit('close')" />
    </form>
  </BaseModal>
</template>
