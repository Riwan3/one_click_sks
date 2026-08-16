<script setup>
import { computed, ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { BadgeCheck, FileText, Home, LayoutGrid, Phone, UserRound } from '@lucide/vue';
import NomorPentingModal from './NomorPentingModal.vue';

const props = defineProps({
    items: { type: Array, required: true },
    active: { type: String, required: true },
    accentClass: { type: String, default: 'text-blue-500' },
});

const icons = { Home, LayoutGrid, BadgeCheck, FileText, UserRound, Phone };

const barClass = computed(() => props.accentClass.replace('text-', 'bg-'));

const showNomorPenting = ref(false);

const handleClick = (item) => {
    if (item.key === 'no-penting') {
        showNomorPenting.value = true;
        return;
    }
    if (item.href) router.visit(item.href);
};
</script>

<template>
    <nav class="fixed inset-x-0 bottom-0 z-40 flex h-16 items-center justify-around border-t border-slate-200 bg-white">
        <button
            v-for="item in items"
            :key="item.key"
            type="button"
            :title="item.href || item.key === 'no-penting' ? undefined : 'Segera hadir'"
            :class="[
                'relative flex flex-col items-center gap-1 text-[0.65rem] font-bold',
                !item.href && item.key !== 'no-penting' ? 'cursor-not-allowed text-slate-300' : active === item.key ? accentClass : 'text-slate-400',
            ]"
            @click="handleClick(item)"
        >
            <span v-if="active === item.key" :class="['absolute -top-px h-0.5 w-8 rounded-full', barClass]"></span>
            <component :is="icons[item.icon]" class="h-5 w-5" />
            {{ item.label }}
        </button>
    </nav>

    <NomorPentingModal :open="showNomorPenting" @close="showNomorPenting = false" />
</template>
