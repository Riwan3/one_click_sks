<script setup>
import { computed, ref } from 'vue';

const props = defineProps({
  employees: { type: Array, required: true }, // [{ nrp, nama, section }]
  nrp: { type: String, default: '' },
  initialLabel: { type: String, default: '' },
});

const emit = defineEmits(['update:nrp']);

const MAX_SUGGESTIONS = 20;

const query = ref(props.initialLabel);
const isOpen = ref(false);
const activeIndex = ref(-1);

const suggestions = computed(() => {
  const keyword = query.value.toLowerCase().trim();
  if (!keyword || props.nrp) return [];
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
  <div class="relative w-full sm:w-72">
    <input
      type="text"
      :value="query"
      placeholder="Ketik NRP / Nama karyawan..."
      autocomplete="off"
      class="w-full rounded-lg border border-slate-300 px-2 py-1.5 text-sm"
      @input="handleInput"
      @keydown="handleKeydown"
      @focus="$event.target.select()"
      @blur="isOpen = false"
    />
    <div v-if="isOpen && suggestions.length" class="absolute z-50 mt-1 max-h-64 w-full overflow-y-auto rounded-lg border border-slate-200 bg-white shadow-lg">
      <button
        v-for="(employee, index) in suggestions"
        :key="employee.nrp"
        type="button"
        :class="['block w-full px-2 py-1 text-left text-xs', index === activeIndex ? 'bg-blue-600 text-white' : 'hover:bg-slate-50']"
        @mousedown.prevent="handleSelect(employee)"
      >
        <strong>{{ employee.nrp }}</strong> - {{ employee.nama }}
        <small v-if="employee.section" :class="index === activeIndex ? 'text-blue-100' : 'text-slate-500'">({{ employee.section }})</small>
      </button>
    </div>
  </div>
</template>
