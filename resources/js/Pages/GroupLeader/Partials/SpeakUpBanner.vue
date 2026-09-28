<script setup>
import { ShieldCheck } from 'lucide-vue-next';

defineProps({
    speakUp: { type: Object, required: true },
});
</script>

<template>
    <div class="flex items-center gap-3 rounded-2xl bg-gradient-to-r from-[#7f1d1d] to-[#b91c1c] p-3.5 text-white shadow-xs border border-[#7f1d1d]">
        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-white/20">
            <ShieldCheck class="h-5 w-5 text-white" />
        </div>
        <div class="min-w-0 text-left leading-tight">
            <p class="mb-0.5 truncate text-[0.6rem] font-bold uppercase tracking-wider text-white/85">{{ speakUp.label }}</p>
            <p class="m-0 truncate text-base font-bold text-white tracking-wide">{{ speakUp.title }}</p>
        </div>
    </div>
</template>
