<script setup>
import { Link } from '@inertiajs/vue3';
import {
    Activity, BarChart2, Camera, CalendarCheck, ClipboardCheck,
    FileBarChart2, Gauge, PenLine, Phone, ShieldCheck, Users, Wallet, Wrench,
} from '@lucide/vue';

defineProps({
    items: { type: Array, required: true },
});

const icons = {
    Activity, BarChart2, Camera, CalendarCheck, ClipboardCheck,
    FileBarChart2, Gauge, PenLine, Phone, ShieldCheck, Users, Wallet, Wrench,
};
</script>

<template>
    <div class="grid grid-cols-2 gap-3">
        <component
            :is="item.href ? Link : 'div'"
            v-for="item in items"
            :key="item.label"
            :href="item.href ?? undefined"
            :title="item.href ? undefined : 'Segera hadir'"
            :class="[
                'flex flex-col items-center gap-2 rounded-2xl bg-white p-5 text-center no-underline shadow-sm',
                item.href ? '' : 'cursor-not-allowed opacity-60',
            ]"
        >
            <div :class="['flex h-11 w-11 items-center justify-center rounded-2xl', item.color]">
                <component :is="icons[item.icon]" class="h-5 w-5" />
            </div>
            <p class="m-0 text-xs font-bold text-slate-700">{{ item.label }}</p>
        </component>
    </div>
</template>
