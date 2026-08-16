<script setup>
import { ref } from 'vue';
import { Calendar, CalendarCheck, CalendarMinus, CalendarPlus, Clock, ClipboardList } from '@lucide/vue';

defineProps({
    tanggal: { type: String, required: true },
    roster: { type: String, required: true },
    shift: { type: String, default: null },
});

const activeDay = ref('hari-ini');

const days = [
    { key: 'kemarin', label: 'Kemarin', icon: CalendarMinus },
    { key: 'hari-ini', label: 'Hari Ini', icon: CalendarCheck },
    { key: 'besok', label: 'Besok', icon: CalendarPlus },
];
</script>

<template>
    <div>
        <div class="flex gap-2 rounded-2xl bg-white p-1.5 shadow-sm">
            <button
                v-for="day in days"
                :key="day.key"
                type="button"
                :class="[
                    'flex flex-1 flex-col items-center gap-1 rounded-xl px-3 py-2.5 text-[0.7rem] font-bold uppercase',
                    activeDay === day.key ? 'bg-slate-900 text-white' : 'text-slate-400',
                ]"
                @click="activeDay = day.key"
            >
                <component :is="day.icon" class="h-4 w-4" />
                {{ day.label }}
            </button>
        </div>

        <div class="mt-3 flex flex-wrap items-center justify-center gap-2 text-xs font-bold">
            <span class="flex items-center gap-1.5 rounded-full bg-amber-100 px-3 py-1.5 text-amber-700">
                <ClipboardList class="h-3.5 w-3.5" /> Roster: {{ roster }}
            </span>
            <span class="flex items-center gap-1.5 rounded-full bg-slate-100 px-3 py-1.5 text-slate-600">
                <Calendar class="h-3.5 w-3.5" /> {{ tanggal }}
            </span>
            <span v-if="shift" class="flex items-center gap-1.5 rounded-full bg-slate-100 px-3 py-1.5 text-slate-600">
                <Clock class="h-3.5 w-3.5" /> Shift {{ shift }}
            </span>
        </div>
    </div>
</template>
