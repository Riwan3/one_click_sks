<script setup>
import { Filter } from 'lucide-vue-next';

defineProps({
  fields: { type: Array, required: true }, // [{ key, label, type: 'select'|'text', options: [{ value, label }], allLabel }]
  filters: { type: Object, required: true },
});

const emit = defineEmits(['apply', 'reset']);

const inputClass = 'w-full rounded-lg border border-slate-300 bg-white px-2 py-1.5 text-xs';
</script>

<template>
  <form class="rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm" @submit.prevent="emit('apply')">
    <div class="grid grid-cols-2 gap-3 md:grid-cols-6">
      <label v-for="field in fields" :key="field.key" :class="['block', field.span ?? 'md:col-span-1']">
        <span class="mb-1 block text-xs text-slate-600">{{ field.label }}</span>
        <select v-if="field.type === 'select'" v-model="filters[field.key]" :class="inputClass">
          <option v-if="field.allLabel !== null" value="">{{ field.allLabel ?? 'Semua' }}</option>
          <option v-for="option in field.options" :key="option.value" :value="option.value">{{ option.label }}</option>
        </select>
        <input v-else v-model="filters[field.key]" type="text" maxlength="50" :class="inputClass" />
      </label>
    </div>
    <div class="mt-3 flex gap-2">
      <button type="submit" class="flex items-center gap-1.5 rounded-lg bg-blue-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-blue-700">
        <Filter class="h-3.5 w-3.5" /> Terapkan
      </button>
      <button type="button" class="rounded-lg border border-slate-300 px-3 py-1.5 text-xs font-semibold text-slate-600 hover:bg-slate-50" @click="emit('reset')">Reset</button>
    </div>
  </form>
</template>
