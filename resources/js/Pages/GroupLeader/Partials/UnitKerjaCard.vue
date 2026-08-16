<script setup>
defineProps({
    unit: { type: String, default: null },
});
</script>

<template>
    <div class="flex min-h-[70px] items-center justify-center rounded-xl bg-white p-4 shadow-sm">
        <p v-if="unit" class="m-0 text-2xl font-extrabold uppercase text-[#1e3a5f]">{{ unit }}</p>
        <div v-else class="h-2 w-24 animate-pulse rounded-full bg-slate-200"></div>
    </div>
</template>
