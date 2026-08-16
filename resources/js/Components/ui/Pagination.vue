<script setup>
import { computed } from 'vue';
import { router } from '@inertiajs/vue3';
import { ChevronLeft, ChevronRight } from '@lucide/vue';

const props = defineProps({
    currentPage: { type: Number, required: true },
    lastPage: { type: Number, required: true },
    baseUrl: { type: String, required: true },
    query: { type: Object, default: () => ({}) },
});

const pages = computed(() => {
    const start = Math.max(1, props.currentPage - 2);
    const end = Math.min(props.lastPage, props.currentPage + 2);
    return Array.from({ length: end - start + 1 }, (_, i) => start + i);
});

const goTo = (page) => {
    if (page < 1 || page > props.lastPage || page === props.currentPage) return;
    router.get(props.baseUrl, { ...props.query, page }, { preserveScroll: true, preserveState: true });
};
</script>

<template>
    <div v-if="lastPage > 1" class="flex items-center justify-center gap-1">
        <button
            type="button"
            :disabled="currentPage === 1"
            class="flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 text-slate-500 disabled:cursor-not-allowed disabled:opacity-40"
            @click="goTo(currentPage - 1)"
        >
            <ChevronLeft class="h-4 w-4" />
        </button>

        <button
            v-for="page in pages"
            :key="page"
            type="button"
            :class="[
                'h-8 min-w-8 rounded-lg border px-2 text-sm font-semibold',
                page === currentPage ? 'border-blue-500 bg-blue-500 text-white' : 'border-slate-200 text-slate-500 hover:bg-slate-50',
            ]"
            @click="goTo(page)"
        >
            {{ page }}
        </button>

        <button
            type="button"
            :disabled="currentPage === lastPage"
            class="flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 text-slate-500 disabled:cursor-not-allowed disabled:opacity-40"
            @click="goTo(currentPage + 1)"
        >
            <ChevronRight class="h-4 w-4" />
        </button>
    </div>
</template>
