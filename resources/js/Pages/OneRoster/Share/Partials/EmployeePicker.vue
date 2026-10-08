<script setup>
import { computed, ref } from 'vue';
import { ChevronDown, X } from 'lucide-vue-next';

const props = defineProps({
  employees: { type: Array, required: true }, // [{ nrp, nama, section }]
  nrp: { type: String, default: '' },
  initialLabel: { type: String, default: '' },
});

const emit = defineEmits(['update:nrp']);

const MAX_SUGGESTIONS = 50;

const query = ref(props.initialLabel);
const isOpen = ref(false);
const activeIndex = ref(-1);

const suggestions = computed(() => {
  const keyword = props.nrp ? '' : query.value.toLowerCase().trim();
  const matches = (employee) => employee.nrp.toLowerCase().includes(keyword) || employee.nama.toLowerCase().includes(keyword);
  return props.employees.filter(matches).slice(0, MAX_SUGGESTIONS);
});

const handleInput = (event) => {
  query.value = event.target.value;
  emit('update:nrp', '');
  isOpen.value = true;
  activeIndex.value = -1;
};

const handleSelect = (employee) => {
  query.value = `${employee.nrp} - ${employee.nama}`;
  emit('update:nrp', employee.nrp);
  isOpen.value = false;
};

const handleClear = () => {
  query.value = '';
  emit('update:nrp', '');
};

const handleToggle = () => {
  isOpen.value = !isOpen.value;
};

const handleKeydown = (event) => {
  const handlers = {
    ArrowDown: () => { activeIndex.value = Math.min(activeIndex.value + 1, suggestions.value.length - 1); },
    ArrowUp: () => { activeIndex.value = Math.max(activeIndex.value - 1, 0); },
    Enter: () => {
      const target = suggestions.value[activeIndex.value] ?? (suggestions.value.length === 1 ? suggestions.value[0] : null);
      if (target) handleSelect(target);
    },
    Escape: () => { isOpen.value = false; },
  };
  const handler = handlers[event.key];
  if (!handler) return;
  event.preventDefault();
  handler();
};
</script>

<template>
  <div class="relative min-w-[220px] w-full flex-1 sm:w-80">
    <div class="relative flex items-center">
      <input
        type="text"
        :value="query"
        placeholder="Pilih / Cari Karyawan..."
        autocomplete="off"
        class="w-full rounded-lg border border-slate-200 bg-white py-2 pl-3 pr-14 text-xs font-semibold text-slate-800 shadow-xs outline-none transition focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
        @input="handleInput"
        @keydown="handleKeydown"
        @focus="isOpen = true"
      />
      <div class="absolute right-2 flex items-center gap-1">
        <button v-if="query" type="button" class="cursor-pointer rounded p-0.5 text-slate-400 hover:text-slate-600" title="Bersihkan" @click="handleClear">
          <X class="h-3.5 w-3.5" />
        </button>
        <button type="button" class="cursor-pointer rounded p-0.5 text-slate-400 hover:text-slate-600" title="Tampilkan Daftar Karyawan" @click="handleToggle">
          <ChevronDown class="h-3.5 w-3.5" />
        </button>
      </div>
    </div>

    <div v-if="isOpen" class="fixed inset-0 z-30" @click="isOpen = false" />

    <div v-if="isOpen" class="absolute left-0 z-40 mt-1 max-h-72 w-full min-w-[280px] overflow-y-auto rounded-xl border border-slate-200 bg-white shadow-2xl sm:min-w-[340px]">
      <div class="sticky top-0 flex items-center justify-between border-b border-slate-100 bg-slate-50 px-3 py-1.5 text-[11px] font-bold text-slate-500">
        <span>DAFTAR KARYAWAN</span>
        <span>{{ suggestions.length }} Karyawan</span>
      </div>
      <button
        v-for="(employee, index) in suggestions"
        :key="employee.nrp"
        type="button"
        :class="['block w-full cursor-pointer border-b border-slate-100 px-3 py-2 text-left text-xs transition last:border-0 hover:bg-blue-50', nrp === employee.nrp || index === activeIndex ? 'bg-blue-50/80 font-bold' : '']"
        @click="handleSelect(employee)"
      >
        <div class="flex items-center justify-between">
          <span class="font-bold text-slate-900">{{ employee.nrp }} - {{ employee.nama }}</span>
          <span v-if="nrp === employee.nrp" class="text-xs font-black text-blue-600">✓</span>
        </div>
        <div v-if="employee.section" class="mt-0.5 text-[10px] text-slate-500">{{ employee.section }}</div>
      </button>
      <div v-if="!suggestions.length" class="p-4 text-center text-xs text-slate-400">Tidak ada data karyawan.</div>
    </div>
  </div>
</template>
