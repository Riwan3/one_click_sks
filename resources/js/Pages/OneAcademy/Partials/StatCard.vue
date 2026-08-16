<script setup>
defineProps({
    label: { type: String, required: true },
    value: { type: [String, Number], required: true },
    icon: { type: [Object, Function], required: true },
    color: { type: String, default: 'primary' },
});

const colorStyles = {
    primary: 'bg-blue-500',
    secondary: 'bg-slate-400',
    warning: 'bg-amber-500',
    success: 'bg-emerald-500',
};
</script>

<template>
    <div class="flex items-center gap-3 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition-transform hover:-translate-y-0.5 hover:shadow-md">
        <div :class="['flex h-[50px] w-[50px] shrink-0 items-center justify-center rounded-xl text-white', colorStyles[color]]">
            <component :is="icon" class="h-6 w-6" />
        </div>
        <div class="min-w-0">
            <div class="text-2xl font-black text-slate-800">{{ value }}</div>
            <div class="truncate text-xs font-semibold text-slate-500">{{ label }}</div>
        </div>
    </div>
</template>
