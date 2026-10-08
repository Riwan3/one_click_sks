<script setup>
import { computed, ref, watch } from 'vue';
import BaseModal from '@/Components/ui/BaseModal.vue';
import FormAlert from '@/Components/ui/FormAlert.vue';
import { getJson } from '@/composables/useMoncuApi';
import { confirmDialog } from '@/utils/confirm';
import { BASE_URL } from '@/utils/manpowerV201';

const props = defineProps({
  target: { type: Object, default: null }, // { unit, category } bila terbuka
  filters: { type: Object, required: true }, // { date, shift }
});

const emit = defineEmits(['close', 'pick']);

const candidates = ref([]);
const search = ref('');
const isLoading = ref(false);
const errorMessage = ref('');

const visible = computed(() => {
  const q = search.value.toLowerCase().trim();

  return candidates.value.filter((p) => !q || `${p.name} ${p.nrp} ${p.home_label}`.toLowerCase().includes(q));
});

const loadCandidates = async () => {
  isLoading.value = true;
  errorMessage.value = '';
  try {
    const data = await getJson(`${BASE_URL}/candidates`, { ...props.filters, unit_id: props.target.unit.id });
    // Yang cocok jenisnya tampil lebih dulu.
    candidates.value = [...data].sort((a, b) => Number(b.compatible) - Number(a.compatible));
  } catch (error) {
    errorMessage.value = error.message;
  } finally {
    isLoading.value = false;
  }
};

const handlePick = async (person) => {
  const { unit, category } = props.target;
  const isConfirmed = person.compatible
    || await confirmDialog(`${person.name} berjenis "${person.home_label}", sedangkan ${unit.unit} termasuk "${category.name}". Tetap tempatkan?`);
  if (isConfirmed) emit('pick', unit, person);
};

watch(() => props.target, (target) => {
  search.value = '';
  candidates.value = [];
  if (target) loadCandidates();
});
</script>

<template>
  <BaseModal :open="!!target" :title="target ? `Plot ke ${target.unit.unit}` : ''" @close="emit('close')">
    <div class="space-y-2 px-5 py-4">
      <p class="text-xs text-slate-500">{{ target?.category.name }}</p>
      <input v-model="search" type="search" placeholder="Cari nama / NRP / jenis..." class="w-full rounded-lg border border-slate-300 px-2.5 py-1.5 text-sm focus:border-blue-500 focus:outline-none" />
      <FormAlert :message="errorMessage" />
      <div class="max-h-[50vh] divide-y divide-slate-100 overflow-y-auto rounded-lg border border-slate-200 text-sm">
        <p v-if="isLoading" class="p-3 text-slate-500">Memuat...</p>
        <p v-else-if="!visible.length" class="p-3 italic text-slate-400">Tidak ada kandidat.</p>
        <button v-for="p in visible" :key="p.nrp" type="button" class="flex w-full items-center gap-2 px-3 py-2 text-left hover:bg-slate-50" @click="handlePick(p)">
          <span>
            <span class="font-bold">{{ p.name }}</span> <span class="text-slate-400">{{ p.nrp }}</span>
            <span class="block text-xs text-slate-500">{{ p.home_label }}</span>
          </span>
          <span v-if="!p.compatible" class="ml-auto rounded bg-amber-400 px-1.5 py-0.5 text-[10px] font-bold">jenis beda</span>
        </button>
      </div>
    </div>
  </BaseModal>
</template>
