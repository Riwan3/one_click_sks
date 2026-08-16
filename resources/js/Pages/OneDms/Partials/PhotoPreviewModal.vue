<script setup>
defineProps({
    src: { type: String, default: null },
});

defineEmits(['close']);
</script>

<template>
    <div v-if="src" class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 p-4" @click="$emit('close')">
        <img :src="src" class="max-h-[85vh] max-w-full rounded-lg shadow-2xl">
    </div>
</template>
