<script setup>
import {
    CalendarCheck, FileText, UserCheck, ClipboardCheck,
    BookOpen, BarChart, Phone,
} from '@lucide/vue';

defineProps({
    quickAccess: { type: Array, required: true },
});

const icons = {
    CalendarCheck, FileText, UserCheck, ClipboardCheck,
    BookOpen, BarChart, Phone,
};
</script>

<template>
    <div class="w-full rounded-2xl border border-white/45 bg-white/70 p-5 shadow-[0_4px_20px_rgba(74,144,226,0.06)] backdrop-blur-md">
        <h3 class="mb-2.5 flex items-center gap-1.5 text-sm font-extrabold uppercase tracking-wide text-slate-800">
            <span class="inline-block h-3.5 w-3.5 rounded bg-blue-500"></span>
            Quick Access
        </h3>
        <div class="grid grid-cols-4 gap-2">
            <a
                v-for="(item, index) in quickAccess"
                :key="index"
                href="#"
                class="flex flex-col items-center gap-1.5 rounded-lg border border-white/45 bg-white/70 px-1.5 py-2.5 text-center no-underline shadow-[0_2px_12px_rgba(74,144,226,0.04)] transition-all hover:-translate-y-0.5 hover:bg-white/80 hover:shadow-[0_8px_24px_rgba(74,144,226,0.12)]"
            >
                <component :is="icons[item.icon]" class="h-[1.1rem] w-[1.1rem] text-blue-500" />
                <span class="text-[0.65rem] font-semibold leading-tight text-slate-800">{{ item.label }}</span>
            </a>
        </div>
    </div>
</template>
