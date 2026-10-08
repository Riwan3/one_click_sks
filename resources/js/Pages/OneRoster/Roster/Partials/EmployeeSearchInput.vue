<script setup>
import { useEmployeeSearch } from '@/composables/useEmployeeSearch';

defineProps({
  modelValue: { type: String, default: '' },
  label: { type: String, default: 'NRP' },
});

const emit = defineEmits(['update:modelValue']);

const MESSAGE_TONE = { success: 'text-emerald-600', danger: 'text-red-600', muted: 'text-slate-500' };

const { results, message, search, pick } = useEmployeeSearch();

const handleInput = (event) => {
  emit('update:modelValue', event.target.value);
  search(event.target.value);
};

const handlePick = (employee) => {
  emit('update:modelValue', employee.nrp);
  pick(employee);
};
</script>

<template>
  <div class="relative">
    <label class="block">
      <span class="mb-1 block text-xs font-semibold text-slate-600">{{ label }} <span class="text-red-500">*</span></span>
      <input
        :value="modelValue" type="text" maxlength="20" autocomplete="off" placeholder="Ketik NRP atau nama" required
        class="w-full rounded-lg border border-slate-300 px-2.5 py-1.5 text-sm focus:border-blue-500 focus:outline-none"
        @input="handleInput"
      />
    </label>
    <div v-if="results.length" class="absolute left-0 right-0 top-full z-20 max-h-56 overflow-y-auto rounded-lg border border-slate-200 bg-white shadow-lg">
      <button v-for="employee in results" :key="employee.nrp" type="button" class="block w-full px-3 py-1.5 text-left text-sm hover:bg-blue-50" @click="handlePick(employee)">
        <strong>{{ employee.nrp }}</strong> - {{ employee.name }}
        <span v-if="employee.jabatan" class="text-xs text-slate-500">({{ employee.jabatan }})</span>
      </button>
    </div>
    <p v-if="message.text" :class="['mt-1 min-h-[1.2em] text-xs', MESSAGE_TONE[message.tone]]">{{ message.text }}</p>
  </div>
</template>
