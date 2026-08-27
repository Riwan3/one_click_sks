<script setup>
import { Head } from '@inertiajs/vue3';
import { HelpCircle, RotateCw } from 'lucide-vue-next';
import TopNav from './Partials/TopNav.vue';
import HeroCard from './Partials/HeroCard.vue';
import DayTabs from './Partials/DayTabs.vue';
import UnitKerjaCard from './Partials/UnitKerjaCard.vue';
import SpeakUpBanner from './Partials/SpeakUpBanner.vue';
import DashboardShortcutList from './Partials/DashboardShortcutList.vue';
import BottomNav from './Partials/BottomNav.vue';

defineProps({
    user: { type: Object, required: true },
    notifCounts: { type: Object, required: true },
    tanggal: { type: String, required: true },
    roster: { type: String, required: true },
    shift: { type: String, default: null },
    unitKerja: { type: String, default: null },
    speakUp: { type: Object, required: true },
    shortcuts: { type: Array, default: () => [] },
    navItems: { type: Array, required: true },
    accentClass: { type: String, default: 'text-blue-500' },
});
</script>

<template>
    <Head title="Beranda - OneClick" />

    <div class="min-h-screen bg-slate-100 pb-24 pt-[84px]">
        <TopNav :user="user" :notif-counts="notifCounts" />

        <div class="mx-auto flex max-w-md md:max-w-4xl lg:max-w-5xl flex-col gap-4 px-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <HeroCard :user="user" />
                <DayTabs :tanggal="tanggal" :roster="roster" :shift="shift" />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <h2 class="mb-2 text-center md:text-left text-xs font-extrabold uppercase tracking-wide text-slate-500">Unit Kerja Anda</h2>
                    <UnitKerjaCard :unit="unitKerja" />
                </div>
                <SpeakUpBanner :speak-up="speakUp" />
            </div>

            <div v-if="!unitKerja" class="flex items-center gap-2 rounded-xl bg-white px-4 py-3 text-xs font-semibold text-slate-500 shadow-xs">
                <HelpCircle class="h-4 w-4 shrink-0 text-slate-400" />
                Data alokasi belum tersedia untuk tanggal ini.
            </div>

            <button type="button" class="flex items-center justify-center gap-2 rounded-xl bg-slate-900 py-3.5 text-sm font-bold uppercase text-white hover:bg-slate-800 transition">
                <RotateCw class="h-4 w-4" /> Update Data
            </button>

            <DashboardShortcutList v-if="shortcuts.length" :shortcuts="shortcuts" />
        </div>

        <div class="lg:hidden">
            <BottomNav active="beranda" :items="navItems" :accent-class="accentClass" />
        </div>
    </div>
</template>
