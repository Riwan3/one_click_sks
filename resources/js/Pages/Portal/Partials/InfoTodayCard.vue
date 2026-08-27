<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { Calendar, Clock } from 'lucide-vue-next';

const props = defineProps({
    today: { type: Object, required: true },
});

const currentJam = ref(props.today.jam || '00:00:00');
const currentShift = ref(props.today.shift || 'Shift Pagi');
let timer = null;

const updateTime = () => {
    const now = new Date();
    const hours = String(now.getHours()).padStart(2, '0');
    const minutes = String(now.getMinutes()).padStart(2, '0');
    const seconds = String(now.getSeconds()).padStart(2, '0');

    currentJam.value = `${hours}:${minutes}:${seconds}`;

    const hourNum = now.getHours();
    currentShift.value = (hourNum >= 6 && hourNum < 18) ? 'Shift Pagi' : 'Shift Malam';
};

onMounted(() => {
    updateTime();
    timer = setInterval(updateTime, 1000);
});

onUnmounted(() => {
    if (timer) clearInterval(timer);
});
</script>

<template>
    <div class="w-full rounded-2xl border border-white/45 bg-white/70 p-5 shadow-[0_4px_20px_rgba(74,144,226,0.06)] backdrop-blur-md">
        <div class="mb-4 flex items-center justify-between">
            <div class="text-base font-extrabold text-slate-800">Informasi Hari Ini</div>
            <!--<span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-0.5 text-[0.65rem] font-bold text-emerald-600 border border-emerald-200">
                <span class="h-1.5 w-1.5 rounded-full bg-emerald-500 animate-pulse"></span> Live Realtime
            </span>-->
        </div>

        <div class="mb-2 flex items-center gap-2.5 text-sm font-semibold text-slate-800">
            <Calendar class="h-4 w-4 text-blue-500" />
            <span>{{ today.tanggal }}</span>
        </div>

        <div class="flex items-center gap-2.5 text-sm font-semibold text-slate-800">
            <Clock class="h-4 w-4 text-blue-500" />
            <span class="font-mono text-base font-bold text-blue-700 tracking-wider">{{ currentJam }}</span>
            <span class="rounded-full bg-blue-100 px-2.5 py-0.5 text-[0.68rem] font-bold text-blue-600">{{ currentShift }}</span>
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
