<script setup>
import { Head } from '@inertiajs/vue3';
import TopNav from './Partials/TopNav.vue';
import StatusSummaryCards from './Partials/StatusSummaryCards.vue';
import DataMenuGrid from './Partials/DataMenuGrid.vue';
import BottomNav from './Partials/BottomNav.vue';

defineProps({
    user: { type: Object, required: true },
    notifCounts: { type: Object, required: true },
    atr: { type: Object, required: true },
    compliance: { type: Object, required: true },
    menuItems: { type: Array, required: true },
    navItems: { type: Array, required: true },
    accentClass: { type: String, default: 'text-blue-500' },
    footerNote: { type: Object, default: null },
});
</script>

<template>
    <Head title="Data - OneClick" />

    <div class="min-h-screen bg-slate-100 pb-24 pt-[84px]">
        <TopNav :user="user" :notif-counts="notifCounts" />

        <div class="mx-auto flex max-w-md flex-col gap-4 px-4">
            <StatusSummaryCards :atr="atr" :compliance="compliance" />
            <DataMenuGrid :items="menuItems" />

            <p v-if="footerNote" class="mt-4 text-center text-xs font-semibold text-slate-400">
                {{ footerNote.title }}<br>{{ footerNote.subtitle }}
            </p>
        </div>

        <BottomNav active="data" :items="navItems" :accent-class="accentClass" />
    </div>
</template>
