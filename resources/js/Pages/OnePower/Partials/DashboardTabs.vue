<script setup>
import { CalendarCheck, Gauge, UserRoundX } from '@lucide/vue';

const tabs = [
    { label: 'Kehadiran', icon: CalendarCheck, active: false },
    { label: 'HM', icon: Gauge, active: true },
    { label: 'Deviasi', icon: UserRoundX, active: false },
];
</script>

<template>
    <div class="flex flex-wrap gap-2">
        <span
            v-for="tab in tabs"
            :key="tab.label"
            :title="tab.active ? undefined : 'Segera hadir'"
            :class="[
                'flex flex-1 flex-col items-center gap-1 rounded-2xl px-3 py-2.5 text-xs font-bold sm:min-w-[110px] sm:flex-none',
                tab.active
                    ? 'bg-blue-500 text-white shadow-sm'
                    : 'cursor-not-allowed bg-white text-slate-400 shadow-sm',
            ]"
        >
            <component :is="tab.icon" class="h-4 w-4" />
            {{ tab.label.toUpperCase() }}
        </span>
    </div>
</template>
