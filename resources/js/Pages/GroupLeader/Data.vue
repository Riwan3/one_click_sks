<script setup>
import { ref } from 'vue';
import { Head } from '@inertiajs/vue3';
import TopNav from './Partials/TopNav.vue';
import StatusSummaryCards from './Partials/StatusSummaryCards.vue';
import DataMenuGrid from './Partials/DataMenuGrid.vue';
import BottomNav from './Partials/BottomNav.vue';
import NomorPentingModal from './Partials/NomorPentingModal.vue';

const props = defineProps({
    user: { type: Object, required: true },
    notifCounts: { type: Object, required: true },
    isOperator: { type: Boolean, default: false },
    atr: { type: Object, required: true },
    compliance: { type: Object, required: true },
    menuItems: { type: Array, required: true },
    atasan: { type: Object, default: null },
    navItems: { type: Array, required: true },
    accentClass: { type: String, default: 'text-blue-500' },
    footerNote: { type: Object, default: null },
});

const showNomorPenting = ref(false);

const handleAction = (action) => {
    if (action === 'nomor-penting') {
        showNomorPenting.value = true;
    } else if (action === 'hubungi-atasan') {
        if (props.atasan && props.atasan.phone) {
            window.open(`https://wa.me/62${props.atasan.phone.replace(/^0/, '')}`, '_blank');
        } else {
            showNomorPenting.value = true;
        }
    }
};
</script>

<template>
    <Head title="Data - OneClick" />

    <div class="min-h-screen bg-slate-100 pb-24 pt-[84px]">
        <TopNav :user="user" :notif-counts="notifCounts" />

        <div class="mx-auto flex max-w-md flex-col gap-4 px-4">
            <StatusSummaryCards :atr="atr" :compliance="compliance" />
            <DataMenuGrid :items="menuItems" @action="handleAction" />

            <!-- Group Leader / Atasan Anda Card -->
            <div v-if="atasan" class="rounded-2xl bg-white p-4 shadow-xs border border-slate-100 flex flex-col gap-2">
                <div class="flex items-center justify-between">
                    <span class="text-[0.65rem] font-black uppercase tracking-wider text-slate-400">GROUP LEADER / ATASAN ANDA</span>
                    <a
                        v-if="atasan.phone"
                        :href="`https://wa.me/62${atasan.phone.replace(/^0/, '')}`"
                        target="_blank"
                        class="inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-[0.68rem] font-bold text-emerald-700 bg-emerald-50 border border-emerald-200/80 hover:bg-emerald-100 transition no-underline"
                    >
                        Chat WA
                    </a>
                </div>
                <div class="flex items-center gap-3 mt-1">
                    <div 
                        class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full border text-base font-extrabold transition-colors"
                        :class="isOperator ? 'border-rose-100 bg-rose-50 text-[#7B112C]' : 'border-sky-100 bg-sky-50 text-sky-600'"
                    >
                        {{ atasan.name ? atasan.name.charAt(0).toUpperCase() : 'I' }}
                    </div>
                    <div class="flex flex-col min-w-0">
                        <span class="truncate text-sm font-black uppercase text-slate-800">{{ atasan.name }}</span>
                        <span class="truncate text-[0.68rem] font-bold uppercase text-slate-400">{{ atasan.role }}</span>
                        <a
                            v-if="atasan.phone"
                            :href="`https://wa.me/62${atasan.phone.replace(/^0/, '')}`"
                            target="_blank"
                            class="text-xs font-semibold text-slate-500 hover:text-emerald-600 no-underline mt-0.5"
                        >
                            {{ atasan.phone }}
                        </a>
                    </div>
                </div>
            </div>

            <div v-if="footerNote" class="mt-2 text-center text-xs font-semibold text-slate-400">
                <div>{{ footerNote.title }}</div>
                <div>{{ footerNote.subtitle }}</div>
            </div>
        </div>

        <div class="lg:hidden">
            <BottomNav active="data" :items="navItems" :accent-class="accentClass" />
        </div>

        <NomorPentingModal :open="showNomorPenting" @close="showNomorPenting = false" />
    </div>
</template>
