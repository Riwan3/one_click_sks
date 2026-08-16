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

    <div class="min-h-screen bg-gradient-to-br from-slate-50 to-blue-50/60 pb-20 pt-[92px]">
        <Navbar :user="user" :notif-counts="notifCounts" />

        <div class="mx-auto grid max-w-[1400px] grid-cols-1 gap-8 px-5 lg:grid-cols-[1fr_360px] lg:px-10">
            <div class="flex flex-col gap-6">
                <HeroBanner :user="user" :greeting="greeting" />
                <ModuleGrid :modules="modules" />
            </div>

            <aside class="flex flex-col gap-5">
                <InfoTodayCard :today="today" />
                <AnnouncementCard :announcements="announcements" />
                <RewardCard :rewards="rewards" />
                <QuickAccessCard :quick-access="quickAccess" />
            </aside>
        </div>
    </div>
</template>
