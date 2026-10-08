<script setup>
import { ref } from 'vue';
import { getJson } from '@/composables/useMoncuApi';

defineProps({
  modelValue: { type: String, default: '' },
  readonly: { type: Boolean, default: false },
});

const emit = defineEmits(['update:modelValue']);

const SEARCH_URL = '/one-roster/moncu/employees/search';
const MIN_QUERY_LENGTH = 2;
const DEBOUNCE_MS = 250;

const results = ref([]);
const info = ref({ text: '', isError: false });
let timer = null;

const setInfo = (text, isError = false) => {
  info.value = { text, isError };
};

const search = async (query) => {
  try {
    results.value = await getJson(SEARCH_URL, { q: query });
    if (!results.value.length) setInfo('Tidak ada karyawan yang cocok.');
  } catch (e) {
    results.value = [];
    setInfo(e.message, true);
  }
};

const handleInput = (event) => {
  const query = event.target.value.trim();
  emit('update:modelValue', event.target.value);
  clearTimeout(timer);
  results.value = [];
  setInfo('');
  if (query.length < MIN_QUERY_LENGTH) return;
  timer = setTimeout(() => search(query), DEBOUNCE_MS);
};

const pick = (employee) => {
  emit('update:modelValue', employee.nrp);
  results.value = [];
  setInfo(`${employee.nrp} - ${employee.name}${employee.jabatan ? ` (${employee.jabatan})` : ''}`);
};

</script>

<template>
  <div class="relative">
    <input
      :value="modelValue"
      type="text"
      maxlength="20"
      required
      autocomplete="off"
      placeholder="Ketik NRP atau nama"
      :readonly="readonly"
      :class="['w-full rounded-lg border border-slate-300 px-2 py-1.5 text-sm', readonly ? 'bg-slate-100' : 'bg-white']"
      @input="handleInput"
    />
    <div v-if="results.length" class="absolute z-10 mt-1 max-h-56 w-full overflow-y-auto rounded-lg border border-slate-200 bg-white shadow-lg">
      <button v-for="employee in results" :key="employee.nrp" type="button" class="block w-full px-3 py-1.5 text-left text-xs hover:bg-slate-50" @click="pick(employee)">
        <strong>{{ employee.nrp }}</strong> - {{ employee.name }}
        <span v-if="employee.jabatan" class="text-slate-500">({{ employee.jabatan }})</span>
      </button>
    </div>
    <p v-if="info.text" :class="['mt-1 text-xs', info.isError ? 'text-red-600' : 'text-emerald-600']">{{ info.text }}</p>
  </div>
</template>
