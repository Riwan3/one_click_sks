<script setup>
import { Link } from '@inertiajs/vue3';
import { CheckCircle, Info, TriangleAlert } from 'lucide-vue-next';

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
            <Link href="/pengumuman" class="text-[0.7rem] font-bold text-blue-500 hover:underline">Lihat Semua</Link>
        </div>

        <div v-if="announcements.length === 0" class="py-4 text-center text-sm text-slate-500">
            Belum ada pengumuman baru.
        </div>

        <div
            v-for="(announcement, index) in announcements"
            :key="announcement.id || index"
            class="mb-3 rounded-xl border-l-4 border-blue-500 bg-blue-50/50 p-3 transition-colors last:mb-0 hover:bg-blue-100/60"
        >
            <div class="flex items-start gap-2.5">
                <span :class="['flex h-6 w-6 shrink-0 items-center justify-center rounded-full mt-0.5', typeIcons[announcement.type]?.class ?? typeIcons.info.class]">
                    <component :is="typeIcons[announcement.type]?.icon ?? typeIcons.info.icon" class="h-3.5 w-3.5" />
                </span>
                <div class="min-w-0 flex-1">
                    <div class="text-sm font-bold text-slate-800 leading-snug">{{ announcement.title }}</div>
                    <div class="text-[0.65rem] font-semibold text-slate-400 mt-0.5">{{ announcement.date }}</div>
                    <div class="mt-1 text-xs text-slate-600 leading-relaxed line-clamp-2">{{ announcement.content }}</div>
                    <div v-if="announcement.image_url" class="mt-2 overflow-hidden rounded-lg border border-slate-200">
                        <img :src="announcement.image_url" :alt="announcement.title" class="h-28 w-full object-cover">
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
