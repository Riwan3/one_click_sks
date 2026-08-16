<script setup>
import { CheckCircle, Info, TriangleAlert } from '@lucide/vue';

defineProps({
    announcements: { type: Array, required: true },
});

const typeIcons = {
    success: { icon: CheckCircle, class: 'bg-emerald-500/10 text-emerald-500' },
    info: { icon: Info, class: 'bg-blue-500/10 text-blue-500' },
    warning: { icon: TriangleAlert, class: 'bg-amber-500/10 text-amber-500' },
};
</script>

<template>
    <div class="w-full rounded-2xl border border-white/45 bg-white/70 p-5 shadow-[0_4px_20px_rgba(74,144,226,0.06)] backdrop-blur-md">
        <div class="mb-4 flex items-center justify-between text-base font-extrabold text-slate-800">
            Pengumuman
            <a href="#" class="text-[0.7rem] font-bold text-blue-500 no-underline">Lihat Semua</a>
        </div>

        <div v-if="announcements.length === 0" class="py-4 text-center text-sm text-slate-500">
            Belum ada pengumuman baru.
        </div>

        <div
            v-for="(announcement, index) in announcements"
            :key="index"
            class="mb-3 rounded border-l-4 border-blue-500 bg-blue-50/50 p-3 transition-colors last:mb-0 hover:bg-blue-100/60"
        >
            <div class="flex items-start gap-2">
                <span :class="['flex h-5 w-5 shrink-0 items-center justify-center rounded-full', typeIcons[announcement.type]?.class ?? typeIcons.info.class]">
                    <component :is="typeIcons[announcement.type]?.icon ?? typeIcons.info.icon" class="h-3 w-3" />
                </span>
                <div class="min-w-0 flex-1">
                    <div class="text-sm font-bold text-slate-800">{{ announcement.title }}</div>
                    <div class="text-[0.65rem] text-slate-500">{{ announcement.date }}</div>
                    <div class="mt-1 text-xs text-slate-500">{{ announcement.content }}</div>
                </div>
            </div>
        </div>
    </div>
</template>
