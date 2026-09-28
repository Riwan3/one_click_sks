<script setup>
import { Head } from '@inertiajs/vue3';
import Navbar from './Partials/Navbar.vue';
import HeroBanner from './Partials/HeroBanner.vue';
import ModuleGrid from './Partials/ModuleGrid.vue';
import InfoTodayCard from './Partials/InfoTodayCard.vue';
import AnnouncementCard from './Partials/AnnouncementCard.vue';
import RewardCard from './Partials/RewardCard.vue';
import QuickAccessCard from './Partials/QuickAccessCard.vue';

defineProps({
    user: { type: Object, required: true },
    greeting: { type: String, required: true },
    today: { type: Object, required: true },
    modules: { type: Array, required: true },
    announcements: { type: Array, required: true },
    rewards: { type: Array, required: true },
    quickAccess: { type: Array, required: true },
    notifCounts: { type: Object, required: true },
});
</script>

<template>
    <Head title="Portal - OneClick" />

    <div class="min-h-screen bg-gradient-to-br from-slate-50 to-blue-50/60 pb-20 pt-[84px] lg:pt-[92px]">
        <Navbar :user="user" :notif-counts="notifCounts" />

        <div class="w-full grid grid-cols-1 gap-6 px-4 sm:px-6 lg:grid-cols-[1fr_360px] xl:grid-cols-[1fr_380px] 2xl:grid-cols-[1fr_400px] lg:px-8 xl:px-10">
            <div class="flex flex-col gap-6 min-w-0">
                <HeroBanner :user="user" :greeting="greeting" />
                <ModuleGrid :modules="modules" />
            </div>

            <aside class="flex flex-col gap-5 min-w-0">
                <InfoTodayCard :today="today" />
                <AnnouncementCard :announcements="announcements" />
                <RewardCard :rewards="rewards" :current-user-nrp="user?.username" />
                <QuickAccessCard :quick-access="quickAccess" />
            </aside>
        </div>
    </div>
</template>
