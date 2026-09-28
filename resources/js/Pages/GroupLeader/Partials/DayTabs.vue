<script setup>
import { computed } from 'vue';
import { Calendar, CalendarCheck, CalendarMinus, CalendarPlus, Clock, ClipboardList } from 'lucide-vue-next';

const props = defineProps({
    activeDay: { type: String, default: 'hari-ini' },
    dayData: { type: Object, default: () => ({}) },
    tanggal: { type: String, default: '' },
    roster: { type: String, default: '' },
    shift: { type: String, default: null },
});

const emit = defineEmits(['update:activeDay', 'change']);

const days = [
    { key: 'kemarin', label: 'Kemarin', icon: CalendarMinus },
    { key: 'hari-ini', label: 'Hari Ini', icon: CalendarCheck },
    { key: 'besok', label: 'Besok', icon: CalendarPlus },
];

const selectDay = (key) => {
    emit('update:activeDay', key);
    emit('change', key);
};

const currentTanggal = computed(() => props.dayData?.tanggal || props.tanggal);
const currentRoster = computed(() => props.dayData?.roster || props.roster);
const currentShift = computed(() => props.dayData?.shift ?? props.shift);
</script>

<template>
    <div>
        <div class="flex gap-2 rounded-2xl bg-white p-1.5 shadow-sm">
            <button
                v-for="day in days"
                :key="day.key"
                type="button"
                :class="[
                    'flex flex-1 flex-col items-center gap-1 rounded-xl px-3 py-2.5 text-[0.7rem] font-bold uppercase transition',
                    activeDay === day.key ? 'bg-slate-900 text-white shadow-sm' : 'text-slate-400 hover:text-slate-600',
                ]"
                @click="selectDay(day.key)"
            >
                <component :is="day.icon" class="h-4 w-4" />
                {{ day.label }}
            </button>
        </div>

        <div class="mt-2.5 flex items-center justify-center">
            <div class="inline-flex items-center gap-2 rounded-full border border-amber-200/80 bg-amber-50/90 px-3.5 py-1 text-[0.68rem] font-bold tracking-wider text-amber-700 uppercase shadow-xs">
                <span class="inline-flex items-center gap-1">
                    <ClipboardList class="h-3.5 w-3.5 text-amber-600" />
                    <span>ROSTER: {{ currentRoster }}</span>
                </span>
                <span class="text-amber-300">|</span>
                <span class="inline-flex items-center gap-1">
                    <Calendar class="h-3.5 w-3.5 text-amber-600" />
                    <span>{{ currentTanggal }}</span>
                </span>
                <template v-if="currentShift">
                    <span class="text-amber-300">|</span>
                    <span class="inline-flex items-center gap-1">
                        <Clock class="h-3.5 w-3.5 text-amber-600" />
                        <span>SHIFT {{ currentShift }}</span>
                    </span>
                </template>
            </div>
        </div>
    </div>
</template>
