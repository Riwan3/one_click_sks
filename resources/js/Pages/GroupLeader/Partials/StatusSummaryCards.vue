<script setup>
import { CheckCircle2 } from '@lucide/vue';

defineProps({
    atr: { type: Object, required: true },
    compliance: { type: Object, required: true },
});
</script>

<template>
    <div class="grid grid-cols-2 gap-3">
        <div class="flex flex-col items-center gap-1.5 rounded-2xl bg-white p-5 text-center shadow-sm">
            <div class="flex h-11 w-11 items-center justify-center rounded-full bg-emerald-500 text-white">
                <CheckCircle2 class="h-5 w-5" />
            </div>
            <p class="m-0 text-sm font-extrabold text-emerald-600">{{ atr.label }}</p>
            <p class="m-0 text-xs text-slate-400">{{ atr.period }}</p>
        </div>
        <div class="flex flex-col items-center justify-center gap-1 rounded-2xl bg-white p-5 text-center shadow-sm">
            <p class="m-0 text-sm font-extrabold text-emerald-600">{{ compliance.label }}</p>
            <p class="m-0 text-xs text-slate-400">{{ compliance.desc }}</p>
        </div>
    </div>
</template>
