<script setup>
defineProps({
  modelValue: { type: [String, Number], default: '' },
  label: { type: String, default: '' },
  required: { type: Boolean, default: false },
  hint: { type: String, default: '' },
});

defineOptions({ inheritAttrs: false });

const emit = defineEmits(['update:modelValue']);
</script>

<template>
  <label class="block">
    <span v-if="label" class="mb-1 block text-xs font-semibold text-slate-600">
      {{ label }} <span v-if="required" class="text-red-500">*</span>
    </span>
    <select
      :value="modelValue"
      :required="required"
      v-bind="$attrs"
      class="w-full rounded-lg border border-slate-300 bg-white px-2.5 py-1.5 text-sm text-slate-800 focus:border-blue-500 focus:outline-none"
      @change="emit('update:modelValue', $event.target.value)"
    >
      <slot />
    </select>
    <span v-if="hint" class="mt-1 block text-[11px] text-slate-500">{{ hint }}</span>
  </label>
</template>
