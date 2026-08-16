<script setup>
import { ShieldAlert } from '@lucide/vue';

defineProps({
    speakUp: { type: Object, required: true },
});
</script>

<template>
    <div class="flex items-center gap-3 rounded-2xl bg-gradient-to-r from-red-800 to-red-700 p-4 text-white shadow-sm">
        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-white/15">
            <ShieldAlert class="h-5 w-5" />
        </div>
        <div class="min-w-0">
            <p class="mb-0.5 truncate text-[0.65rem] font-bold uppercase tracking-wide text-red-100">{{ speakUp.label }}</p>
            <p class="m-0 truncate text-sm font-extrabold uppercase">{{ speakUp.title }}</p>
        </div>
    </div>
</template>
