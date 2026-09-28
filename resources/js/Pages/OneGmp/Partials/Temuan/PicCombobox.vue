<script setup>
import { ref, computed, watch } from 'vue';

/** Cari PIC berdasarkan nama/NRP (min. 3 huruf), sama seperti combobox PIC v1. */
const props = defineProps({
  modelValue: { type: String, default: '' },
  employees: { type: Array, default: () => [] },
});

const emit = defineEmits(['update:modelValue', 'selected']);

const MIN_CHARS = 3;

const labelOf = (nrp) => {
  const emp = props.employees.find((e) => String(e.nrp) === String(nrp));
  return nrp ? (emp ? `${emp.nrp} - ${emp.nama}` : nrp) : '';
};

const query = ref(labelOf(props.modelValue));
const isOpen = ref(false);

const matches = computed(() => {
  const term = query.value.trim().toLowerCase();
  if (term.length < MIN_CHARS) return [];
  return props.employees.filter((e) => String(e.nrp).toLowerCase().includes(term) || String(e.nama).toLowerCase().includes(term)).slice(0, 50);
});

const handleInput = () => {
  emit('update:modelValue', '');
  isOpen.value = true;
};

/** Tunda sebentar agar klik pada item sempat diproses sebelum menu ditutup. */
const handleBlur = () => setTimeout(() => { isOpen.value = false; }, 150);

const handleSelect = (emp) => {
  emit('update:modelValue', emp.nrp);
  emit('selected', emp);
  query.value = `${emp.nrp} - ${emp.nama}`;
  isOpen.value = false;
};

watch(() => props.modelValue, (nrp) => { if (nrp) query.value = labelOf(nrp); });
</script>

<template>
  <div class="relative">
    <input
      v-model="query"
      type="text"
      autocomplete="off"
      placeholder="Ketik min. 3 huruf nama / NRP..."
      class="w-full rounded-lg border-0 bg-slate-100 px-3 py-2 text-sm"
      @input="handleInput"
      @focus="isOpen = query.trim().length >= MIN_CHARS"
      @blur="handleBlur"
    />
    <div v-if="isOpen" class="absolute z-20 mt-1 max-h-56 w-full overflow-y-auto rounded-lg border border-slate-200 bg-white text-sm shadow-lg">
      <div v-if="query.trim().length < MIN_CHARS" class="px-3 py-2 text-slate-400">Ketik minimal 3 huruf untuk mencari...</div>
      <div v-else-if="matches.length === 0" class="px-3 py-2 text-slate-400">Tidak ada PIC yang cocok.</div>
      <button v-for="emp in matches" :key="emp.nrp" type="button" class="block w-full px-3 py-1.5 text-left hover:bg-slate-50" @mousedown.prevent="handleSelect(emp)">
        <span class="font-semibold">{{ emp.nrp }}</span> <span class="text-slate-500">- {{ emp.nama }}</span>
      </button>
    </div>
  </div>
</template>
