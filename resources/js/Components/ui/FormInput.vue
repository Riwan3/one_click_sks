<script setup>
defineProps({
  modelValue: { type: [String, Number], default: '' },
  label: { type: String, default: '' },
  type: { type: String, default: 'text' },
  required: { type: Boolean, default: false },
  readonly: { type: Boolean, default: false },
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
    <input
      :value="modelValue"
      :type="type"
      :required="required"
      :readonly="readonly"
      v-bind="$attrs"
      class="w-full rounded-lg border border-slate-300 px-2.5 py-1.5 text-sm text-slate-800 focus:border-blue-500 focus:outline-none read-only:bg-slate-50"
      @input="emit('update:modelValue', $event.target.value)"
    />
    <span v-if="hint" class="mt-1 block text-[11px] text-slate-500">{{ hint }}</span>
  </label>
</template>
