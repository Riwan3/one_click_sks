<script setup>
import { ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import {
    Activity, ShieldAlert, CheckCircle2, AlertTriangle, FileWarning,
    Calendar, Filter, Eye, Phone, Frown, VideoOff, OctagonX, Video, UserX, Gauge, CircleSlash
} from 'lucide-vue-next';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    user: { type: Object, required: true },
    notifCounts: { type: Object, required: true },
    menu: { type: Array, required: true },
    filters: { type: Object, required: true },
    stats: { type: Object, required: true },
    tally: { type: Object, required: true },
    dailyTrend: { type: Array, default: () => [] },
    topOperators: { type: Array, default: () => [] },
});

const bulan = ref(props.filters.bulan);
const tahun = ref(props.filters.tahun);
const shift = ref(props.filters.shift);

const handleFilter = () => {
    router.get('/one-dms/dashboard', {
        bulan: bulan.value,
        tahun: tahun.value,
        shift: shift.value,
    }, { preserveState: true });
};

const alarmIcons = {
    'Driver Fatigue': Activity,
    'Driver Distraction': ShieldAlert,
    'Phone Detection': Phone,
    'Smoking Detection': CircleSlash,
    'Yawning Detection': Frown,
    'Eye Blink': Eye,
    'Camera Covering': VideoOff,
    'Forward Collision': OctagonX,
    'Video loss alarm': Video,
    'No Driver': UserX,
    'Abnormal Speed Indication': Gauge,
};

const alarmColors = {
    'Driver Fatigue': 'bg-rose-500/10 text-rose-600 border-rose-200 dark:border-rose-900',
    'Driver Distraction': 'bg-amber-500/10 text-amber-600 border-amber-200 dark:border-amber-900',
    'Phone Detection': 'bg-blue-500/10 text-blue-600 border-blue-200 dark:border-blue-900',
    'Smoking Detection': 'bg-orange-500/10 text-orange-600 border-orange-200 dark:border-orange-900',
    'Yawning Detection': 'bg-purple-500/10 text-purple-600 border-purple-200 dark:border-purple-900',
    'Eye Blink': 'bg-indigo-500/10 text-indigo-600 border-indigo-200 dark:border-indigo-900',
    'Camera Covering': 'bg-slate-500/10 text-slate-600 border-slate-200 dark:border-slate-800',
    'Forward Collision': 'bg-red-500/10 text-red-600 border-red-200 dark:border-red-900',
    'Video loss alarm': 'bg-cyan-500/10 text-cyan-600 border-cyan-200 dark:border-cyan-900',
    'No Driver': 'bg-stone-500/10 text-stone-600 border-stone-200 dark:border-stone-800',
    'Abnormal Speed Indication': 'bg-emerald-500/10 text-emerald-600 border-emerald-200 dark:border-emerald-900',
};
</script>

<template>
    <Head title="Dashboard DMS - OneClick" />

    <AppLayout :user="user" :notif-counts="notifCounts" :menu="menu">
        <!-- Banner Header -->
        <div class="mb-6 rounded-3xl p-8 text-white shadow-lg flex justify-between items-center flex-wrap gap-4" style="background: linear-gradient(135deg, #0f172a 0%, #0369a1 100%);">
            <div>
                <h1 class="mb-1 text-2xl font-extrabold flex items-center gap-3">
                    <Activity class="w-8 h-8 text-sky-400" />
                    Dashboard DMS & In Car Camera
                </h1>
                <p class="m-0 max-w-xl text-sm text-white/80">
                    Ringkasan statistik deteksi fatigue, distrasi pengemudi, dan analisis deviasi operator secara realtime.
                </p>
            </div>
        </div>

        <!-- Filter Bar -->
        <div class="mb-6 bg-white dark:bg-slate-900 p-4 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm flex flex-wrap gap-4 items-center justify-between">
            <form @submit.prevent="handleFilter" class="flex flex-wrap gap-3 items-center w-full md:w-auto">
                <select v-model="bulan" @change="handleFilter" class="border rounded-xl px-3 py-2 text-xs bg-slate-50 dark:bg-slate-800">
                    <option value="">Semua Bulan</option>
                    <option value="01">Januari</option>
                    <option value="02">Februari</option>
                    <option value="03">Maret</option>
                    <option value="04">April</option>
                    <option value="05">Mei</option>
                    <option value="06">Juni</option>
                    <option value="07">Juli</option>
                    <option value="08">Agustus</option>
                    <option value="09">September</option>
                    <option value="10">Oktober</option>
                    <option value="11">November</option>
                    <option value="12">Desember</option>
                </select>

                <select v-model="tahun" @change="handleFilter" class="border rounded-xl px-3 py-2 text-xs bg-slate-50 dark:bg-slate-800">
                    <option value="2026">2026</option>
                    <option value="2025">2025</option>
                    <option value="2024">2024</option>
                </select>

                <select v-model="shift" @change="handleFilter" class="border rounded-xl px-3 py-2 text-xs bg-slate-50 dark:bg-slate-800">
                    <option value="">Semua Shift</option>
                    <option value="1">Shift 1</option>
                    <option value="2">Shift 2</option>
                </select>

                <button type="submit" class="px-4 py-2 bg-sky-600 hover:bg-sky-700 text-white font-bold rounded-xl text-xs shadow flex items-center gap-1.5">
                    <Filter class="w-3.5 h-3.5" /> Filter Data
                </button>
            </form>
        </div>

        <!-- Metric Stat Cards -->
        <div class="mb-6 grid grid-cols-2 lg:grid-cols-5 gap-3">
            <div class="bg-white dark:bg-slate-900 p-4 rounded-2xl border border-rose-200 dark:border-rose-900/50 shadow-sm border-l-4 border-l-rose-500">
                <p class="text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Total Kasus</p>
                <h3 class="text-2xl font-black text-rose-600 dark:text-rose-400">{{ stats.total_cases }}</h3>
            </div>

            <div class="bg-white dark:bg-slate-900 p-4 rounded-2xl border border-blue-200 dark:border-blue-900/50 shadow-sm border-l-4 border-l-blue-500">
                <p class="text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Operator Terdeteksi</p>
                <h3 class="text-2xl font-black text-blue-600 dark:text-blue-400">{{ stats.unique_operators }}</h3>
            </div>

            <div class="bg-white dark:bg-slate-900 p-4 rounded-2xl border border-emerald-200 dark:border-emerald-900/50 shadow-sm border-l-4 border-l-emerald-500">
                <p class="text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Kasus Selesai (Closed)</p>
                <h3 class="text-2xl font-black text-emerald-600 dark:text-emerald-400">{{ stats.closed_cases }}</h3>
            </div>

            <div class="bg-white dark:bg-slate-900 p-4 rounded-2xl border border-amber-200 dark:border-amber-900/50 shadow-sm border-l-4 border-l-amber-500">
                <p class="text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Deviasi Ke-1</p>
                <h3 class="text-2xl font-black text-amber-600 dark:text-amber-400">{{ stats.deviasi_1 }}</h3>
            </div>

            <div class="bg-white dark:bg-slate-900 p-4 rounded-2xl border border-purple-200 dark:border-purple-900/50 shadow-sm border-l-4 border-l-purple-500">
                <p class="text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Deviasi Ke-2+</p>
                <h3 class="text-2xl font-black text-purple-600 dark:text-purple-400">{{ stats.deviasi_2 }}</h3>
            </div>
        </div>

        <!-- Alarm Categories Breakdown Grid -->
        <div class="mb-6">
            <h3 class="text-sm font-extrabold text-slate-800 dark:text-slate-200 mb-3 flex items-center gap-2">
                <ShieldAlert class="w-4 h-4 text-sky-500" /> Sebaran Deviasi Per Jenis Alarm (11 Kategori)
            </h3>
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-3">
                <div v-for="(count, key) in tally" :key="key"
                    :class="['p-3 rounded-2xl border transition shadow-sm flex flex-col justify-between', alarmColors[key] || 'bg-slate-100']">
                    <div class="flex justify-between items-start mb-2">
                        <component :is="alarmIcons[key] || ShieldAlert" class="w-5 h-5 opacity-80" />
                        <span class="text-xs font-black px-2 py-0.5 rounded-full bg-white/80 dark:bg-slate-800 shadow-xs">{{ count }}</span>
                    </div>
                    <p class="text-[11px] font-bold leading-tight">{{ key }}</p>
                </div>
            </div>
        </div>

        <!-- Top 10 Operators Table -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm p-6">
            <h3 class="text-sm font-extrabold text-slate-800 dark:text-slate-200 mb-4 flex items-center gap-2">
                <AlertTriangle class="w-4 h-4 text-rose-500" /> 10 Operator Paling Sering Terdeteksi Deviasi
            </h3>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-100 dark:bg-slate-800/80 font-bold text-slate-700 dark:text-slate-300">
                        <tr>
                            <th class="p-3">Peringkat</th>
                            <th class="p-3">Nama Operator</th>
                            <th class="p-3">NRP</th>
                            <th class="p-3 text-right">Total Kasus Deviasi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        <tr v-if="topOperators.length === 0">
                            <td colspan="4" class="p-6 text-center text-slate-400">Belum ada data deviasi terdeteksi pada periode ini.</td>
                        </tr>
                        <tr v-for="(op, idx) in topOperators" :key="idx" class="hover:bg-slate-50 dark:hover:bg-slate-800/50">
                            <td class="p-3 font-black text-slate-400">#{{ idx + 1 }}</td>
                            <td class="p-3 font-bold text-slate-900 dark:text-white">{{ op.nama_operator }}</td>
                            <td class="p-3 font-mono text-slate-500">{{ op.nrp }}</td>
                            <td class="p-3 text-right font-black text-rose-600 dark:text-rose-400">{{ op.total_cases }} kali</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AppLayout>
</template>
