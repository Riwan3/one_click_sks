<script setup>
import { ref, computed } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import { HelpCircle, RotateCw } from 'lucide-vue-next';
import TopNav from './Partials/TopNav.vue';
import HeroCard from './Partials/HeroCard.vue';
import DayTabs from './Partials/DayTabs.vue';
import UnitKerjaCard from './Partials/UnitKerjaCard.vue';
import SpeakUpBanner from './Partials/SpeakUpBanner.vue';
import DashboardShortcutList from './Partials/DashboardShortcutList.vue';
import BottomNav from './Partials/BottomNav.vue';
import RewardCard from '@/Pages/Portal/Partials/RewardCard.vue';

const props = defineProps({
    user: { type: Object, required: true },
    notifCounts: { type: Object, required: true },
    tanggal: { type: String, required: true },
    roster: { type: String, required: true },
    shift: { type: String, default: null },
    unitKerja: { type: String, default: null },
    daysData: { type: Object, default: () => ({}) },
    speakUp: { type: Object, required: true },
    lokasiRow: { type: Object, default: null },
    glRow: { type: Object, default: null },
    fleet: { type: Object, default: null },
    diggerFleet: { type: Array, default: () => [] },
    shortcuts: { type: Array, default: () => [] },
    rewards: { type: Array, default: () => [] },
    navItems: { type: Array, required: true },
    accentClass: { type: String, default: 'text-blue-500' },
});

const activeDay = ref('hari-ini');
const isUpdating = ref(false);

const handleUpdateData = () => {
    isUpdating.value = true;
    window.location.reload();
};

const currentDayData = computed(() => {
    if (props.daysData && props.daysData[activeDay.value]) {
        return props.daysData[activeDay.value];
    }
    return {
        tanggal: props.tanggal,
        roster: props.roster,
        shift: props.shift,
        unit: props.unitKerja,
        csa: activeDay.value === 'kemarin' ? 'CSA MONTE BARU' : null,
    };
});
</script>

<template>
    <Head title="Beranda - OneClick" />

    <div class="min-h-screen bg-slate-100 pb-24 pt-[84px]">
        <TopNav :user="user" :notif-counts="notifCounts" />

        <div class="mx-auto flex max-w-md flex-col gap-4 px-4">
            <!-- ONECLICK REWARD CENTER (Tampil di atas Selamat Datang) -->
            <RewardCard v-if="rewards && rewards.length > 0" :rewards="rewards" :current-user-nrp="user?.username" :show-view-all="false" />

            <HeroCard :user="user" />

            <!-- Alokasi Saya (Satu Card Terpadu seperti di one-click) -->
            <div class="overflow-hidden rounded-[25px] bg-white p-4 shadow-sm border border-slate-100 flex flex-col gap-3">
                <DayTabs
                    :active-day="activeDay"
                    :day-data="currentDayData"
                    :tanggal="tanggal"
                    :roster="roster"
                    :shift="shift"
                    @update:active-day="activeDay = $event"
                />

                <div class="mt-1">
                    <h2 class="mb-2 text-center text-xs font-extrabold uppercase tracking-wider text-slate-500">Unit Kerja Anda</h2>
                    <UnitKerjaCard
                        :unit="currentDayData.unit"
                        :csa="currentDayData.csa"
                        :speak-up="currentDayData.speakUp || speakUp"
                        :lokasi-row="currentDayData.lokasiRow"
                        :gl-row="currentDayData.glRow"
                        :fleet="currentDayData.fleet"
                        :digger-fleet="currentDayData.diggerFleet"
                    />
                </div>
            </div>

            <div v-if="!currentDayData.unit" class="flex items-center gap-2 rounded-xl bg-white px-4 py-3 text-xs font-semibold text-slate-500 shadow-xs">
                <HelpCircle class="h-4 w-4 shrink-0 text-slate-400" />
                Data alokasi belum tersedia untuk tanggal ini.
            </div>

            <button
                type="button"
                :disabled="isUpdating"
                class="flex w-full items-center justify-center gap-2 rounded-2xl bg-slate-900 py-3.5 text-sm font-bold uppercase text-white hover:bg-slate-800 active:scale-[0.99] transition shadow-sm disabled:opacity-75 cursor-pointer disabled:cursor-not-allowed"
                @click="handleUpdateData"
            >
                <RotateCw :class="['h-4 w-4', isUpdating ? 'animate-spin' : '']" />
                <span>{{ isUpdating ? 'Memperbarui...' : 'Update Data' }}</span>
            </button>

            <DashboardShortcutList v-if="shortcuts.length" :shortcuts="shortcuts" />
        </div>

        <div class="lg:hidden">
            <BottomNav active="beranda" :items="navItems" :accent-class="accentClass" />
        </div>
    </div>
</template>
