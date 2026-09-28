<script setup>
import { CalendarCheck, Gauge, UserRoundX, HeartHandshake } from '@lucide/vue';

defineProps({
    activeTab: { type: String, required: true },
});

defineEmits(['tab-selected']);

const tabs = [
    { key: 'kehadiran', label: 'Kehadiran', icon: CalendarCheck },
    { key: 'hm', label: 'HM', icon: Gauge },
    { key: 'deviasi', label: 'Deviasi', icon: UserRoundX },
    { key: 'coaching', label: 'Coaching Counseling', icon: HeartHandshake },
];
</script>

<template>
    <div class="flex flex-wrap gap-2">
        <button
            v-for="tab in tabs"
            :key="tab.key"
            type="button"
            :class="[
                'flex flex-1 flex-col items-center gap-1 rounded-2xl px-3 py-2.5 text-xs font-bold sm:min-w-[110px] sm:flex-none',
                activeTab === tab.key
                    ? 'bg-blue-500 text-white shadow-sm'
                    : 'bg-white text-slate-500 shadow-sm hover:bg-slate-50',
            ]"
            @click="$emit('tab-selected', tab.key)"
        >
            <component :is="tab.icon" class="h-4 w-4" />
            {{ tab.label.toUpperCase() }}
        </button>
    </div>
</template>
