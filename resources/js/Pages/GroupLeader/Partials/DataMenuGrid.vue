<script setup>
import { Link } from '@inertiajs/vue3';
import {
    Activity, BarChart2, Camera, CalendarCheck, ClipboardCheck,
    CreditCard, FileBarChart2, FileEdit, Gauge, MessageCircle, PenLine, Phone, ShieldCheck, Users, Wallet, Wrench,
} from 'lucide-vue-next';

defineProps({
    items: { type: Array, required: true },
});

const emit = defineEmits(['action']);

const icons = {
    Activity, BarChart2, Camera, CalendarCheck, ClipboardCheck,
    CreditCard, FileBarChart2, FileEdit, Gauge, MessageCircle, PenLine, Phone, ShieldCheck, Users, Wallet, Wrench,
};

const handleItemClick = (item) => {
    if (item.action) {
        emit('action', item.action);
    }
};
</script>

<template>
    <div class="grid grid-cols-2 gap-3">
        <component
            :is="item.href ? Link : 'button'"
            v-for="item in items"
            :key="item.label"
            :type="item.href ? undefined : 'button'"
            :href="item.href ?? undefined"
            :title="item.href || item.action ? undefined : 'Segera hadir'"
            :class="[
                'flex flex-col items-center justify-center gap-2.5 rounded-2xl bg-white p-5 text-center no-underline shadow-xs border border-slate-100/80 hover:shadow-md active:scale-[0.98] transition cursor-pointer',
                !item.href && !item.action ? 'cursor-not-allowed opacity-60' : '',
            ]"
            @click="handleItemClick(item)"
        >
            <div :class="['flex h-12 w-12 items-center justify-center rounded-2xl shadow-xs', item.color]">
                <component :is="icons[item.icon] || CreditCard" class="h-6 w-6 stroke-[1.8]" />
            </div>
            <p class="m-0 text-xs font-black text-slate-800 tracking-tight">{{ item.label }}</p>
        </component>
    </div>
</template>
