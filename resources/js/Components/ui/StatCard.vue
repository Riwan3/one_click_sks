<script setup>
defineProps({
    label: { type: String, required: true },
    value: { type: [String, Number], required: true },
    color: { type: String, default: 'primary' },
});

const colorStyles = {
    primary: { border: 'border-blue-500', text: 'text-blue-600' },
    warning: { border: 'border-amber-500', text: 'text-amber-600' },
    success: { border: 'border-emerald-500', text: 'text-emerald-600' },
    danger: { border: 'border-red-500', text: 'text-red-600' },
};
</script>

<template>
    <div :class="['h-full rounded-xl border-l-4 bg-white p-3 shadow-sm', colorStyles[color].border]">
        <p class="mb-0 text-xs font-bold text-slate-500">{{ label }}</p>
        <h4 :class="['mb-0 text-2xl font-bold', colorStyles[color].text]">{{ value }}</h4>
    </div>
</template>
