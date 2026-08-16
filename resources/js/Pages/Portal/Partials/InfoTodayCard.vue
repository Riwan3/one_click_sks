<script setup>
import { Calendar, Clock } from '@lucide/vue';

defineProps({
    today: { type: Object, required: true },
});
</script>

<template>
    <div class="w-full rounded-2xl border border-white/45 bg-white/70 p-5 shadow-[0_4px_20px_rgba(74,144,226,0.06)] backdrop-blur-md">
        <div class="mb-4 text-base font-extrabold text-slate-800">Informasi Hari Ini</div>

        <div class="mb-1.5 flex items-center gap-2.5 text-sm font-semibold text-slate-800">
            <Calendar class="h-4 w-4 text-blue-500" />
            <span>{{ today.tanggal }}</span>
        </div>
        <div class="flex items-center gap-2.5 text-sm font-semibold text-slate-800">
            <Clock class="h-4 w-4 text-blue-500" />
            <span>{{ today.jam }}</span>
            <span class="rounded-full bg-blue-100 px-2 py-0.5 text-[0.68rem] font-bold text-blue-500">{{ today.shift }}</span>
        </div>

        <hr class="my-3 border-slate-200">

        <p class="m-0 text-sm font-medium leading-relaxed text-slate-500">
            Utamakan keselamatan, tingkatkan produktivitas, wujudkan zero accident.
        </p>

        <div class="mt-3 flex items-start gap-1.5 rounded-lg bg-blue-50 px-2.5 py-2">
            <span class="font-serif text-xl leading-none text-blue-500">&ldquo;</span>
            <p class="m-0 text-[0.74rem] italic leading-relaxed text-[#4a6fa5]">
                Keselamatan adalah investasi terbaik untuk masa depan kita.
            </p>
        </div>
    </div>
</template>
