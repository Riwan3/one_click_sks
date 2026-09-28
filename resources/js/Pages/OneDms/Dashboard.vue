<script setup>
import { ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import {
    Activity, ShieldAlert, CheckCircle2, AlertTriangle, FileWarning,
    Calendar, Filter, Eye, Phone, Frown, VideoOff, OctagonX, Video, UserX, Gauge, CircleSlash, BarChart3, Clock
} from 'lucide-vue-next';
import AppLayout from '@/Layouts/AppLayout.vue';
import PemanggilanStatusModal from './Partials/PemanggilanStatusModal.vue';

const props = defineProps({
    user: { type: Object, required: true },
    notifCounts: { type: Object, required: true },
    menu: { type: Array, required: true },
    filters: { type: Object, required: true },
    stats: { type: Object, required: true },
    tally: { type: Object, required: true },
    dailyTrend: { type: Array, default: () => [] },
    monthlyTrend: { type: Array, default: () => [] },
    jamFatigue: { type: Array, default: () => [] },
    izinFatigueTrend: { type: Array, default: () => [] },
    izinFatigueTotal: { type: Number, default: 0 },
    rekapPerShift: { type: Object, default: () => ({}) },
    rekapBulan: { type: Number, default: 1 },
    rekapDaysInMonth: { type: Number, default: 30 },
    rekapJenisList: { type: Array, default: () => [] },
    topIzinFatigueOperators: { type: Array, default: () => [] },
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

const showPemanggilanModal = ref(false);
const selectedOperator = ref(null);

const openPemanggilanStatus = (operator) => {
    selectedOperator.value = operator;
    showPemanggilanModal.value = true;
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
    'Video Loss Alarm': Video,
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
    'Video Loss Alarm': 'bg-cyan-500/10 text-cyan-600 border-cyan-200 dark:border-cyan-900',
    'No Driver': 'bg-stone-500/10 text-stone-600 border-stone-200 dark:border-stone-800',
    'Abnormal Speed Indication': 'bg-emerald-500/10 text-emerald-600 border-emerald-200 dark:border-emerald-900',
    'Abnormal Speed Indication': 'bg-emerald-500/10 text-emerald-600 border-emerald-200 dark:border-emerald-900',
};

import { Line, Bar } from 'vue-chartjs';
import { Chart as ChartJS, CategoryScale, LinearScale, PointElement, LineElement, BarElement, Title, Tooltip, Legend, Filler } from 'chart.js';
import { computed } from 'vue';

ChartJS.register(CategoryScale, LinearScale, PointElement, LineElement, BarElement, Title, Tooltip, Legend, Filler);

const showMonthly = computed(() => !bulan.value);

const trendChartData = computed(() => {
    if (showMonthly.value) {
        const labels = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
        const data = new Array(12).fill(0);
        
        props.monthlyTrend.forEach(item => {
            if (item.bulan >= 1 && item.bulan <= 12) {
                data[item.bulan - 1] = item.total;
            }
        });
        
        return {
            labels,
            datasets: [{
                label: 'Total Deviasi Bulanan',
                data: data,
                borderColor: '#0ea5e9',
                backgroundColor: 'rgba(14, 165, 233, 0.5)',
                borderWidth: 2,
                borderRadius: 4,
            }]
        };
    } else {
        const labels = props.dailyTrend.map(item => {
            const date = new Date(item.tanggal);
            return `${date.getDate()} ${date.toLocaleString('id-ID', { month: 'short' })}`;
        });
        const data = props.dailyTrend.map(item => item.total);
        
        return {
            labels,
            datasets: [{
                label: 'Trend Harian Deviasi',
                data: data,
                borderColor: '#ec4899',
                backgroundColor: 'rgba(236, 72, 153, 0.1)',
                borderWidth: 2,
                fill: true,
                tension: 0.3,
                pointBackgroundColor: '#ec4899',
            }]
        };
    }
});

const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    layout: {
        padding: { top: 20 }
    },
    plugins: {
        legend: { display: false }
    },
    scales: {
        y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.05)' } },
        x: { grid: { display: false } }
    }
};

function makeValueLabelPlugin(id, { color = '#000', horizontal = false } = {}) {
    return {
        id,
        afterDatasetsDraw(chart) {
            const { ctx } = chart;
            chart.getDatasetMeta(0).data.forEach((bar, index) => {
                const value = chart.data.datasets[0].data[index];
                if (!value) return;
                ctx.save();
                ctx.fillStyle = color;
                ctx.font = 'bold 10px sans-serif';
                if (horizontal) {
                    ctx.textAlign = 'left';
                    ctx.textBaseline = 'middle';
                    ctx.fillText(value, bar.x + 6, bar.y);
                } else {
                    ctx.textAlign = 'center';
                    ctx.fillText(value, bar.x, bar.y - 6);
                }
                ctx.restore();
            });
        },
    };
}

const trendBarLabelPlugin = makeValueLabelPlugin('trendBarLabel', { color: '#0369a1' });
const trendLineLabelPlugin = makeValueLabelPlugin('trendLineLabel', { color: '#be185d' });

const verticalBarOptions = {
    responsive: true,
    maintainAspectRatio: false,
    layout: {
        padding: { top: 20 }
    },
    plugins: {
        legend: { display: false }
    },
    scales: {
        y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.05)' } },
        x: { grid: { display: false } }
    }
};

const jamFatigueChartData = computed(() => ({
    labels: props.jamFatigue.map(item => item.jam),
    datasets: [{
        label: 'Jumlah Fatigue',
        data: props.jamFatigue.map(item => item.total),
        backgroundColor: '#7c3aed',
        borderRadius: 4,
    }]
}));

const jamFatigueBarLabelPlugin = makeValueLabelPlugin('jamFatigueBarLabel', { color: '#4c1d95' });

const ICC_ONLY_CATEGORIES = [
    'Driver Distraction', 'Phone Detection', 'Yawning Detection', 'Eye Blink',
    'Camera Covering', 'Forward Collision', 'Video Loss Alarm', 'No Driver',
    'Abnormal Speed Indication',
];

const iccDistribusiChartData = computed(() => ({
    labels: ICC_ONLY_CATEGORIES,
    datasets: [{
        label: 'Jumlah Deviasi',
        data: ICC_ONLY_CATEGORIES.map(key => props.tally[key] || 0),
        backgroundColor: '#22d3ee',
        borderRadius: 4,
    }]
}));

const iccDistribusiChartOptions = {
    indexAxis: 'y',
    responsive: true,
    maintainAspectRatio: false,
    layout: {
        padding: { right: 30 }
    },
    plugins: {
        legend: { display: false }
    },
    scales: {
        x: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.05)' } },
        y: { grid: { display: false } }
    }
};

const iccDistribusiLabelPlugin = makeValueLabelPlugin('iccDistribusiLabel', { color: '#0e7490', horizontal: true });

const izinFatigueChartData = computed(() => ({
    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'],
    datasets: [{
        label: 'Izin Fatigue',
        data: props.izinFatigueTrend.map(item => item.total),
        backgroundColor: '#22d3ee',
        borderRadius: 4,
    }]
}));

const izinFatigueLabelPlugin = makeValueLabelPlugin('izinFatigueLabel', { color: '#0e7490' });

const MONTH_NAMES = [
    'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
    'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember',
];
const rekapMonthLabel = computed(() => MONTH_NAMES[props.rekapBulan - 1] || '');
const rekapDays = computed(() => Array.from({ length: props.rekapDaysInMonth }, (_, i) => i + 1));
const rekapShiftKeys = computed(() => shift.value ? [shift.value] : ['1', '2']);
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

        <!-- Trend Chart & Jam Paling Sering Fatigue -->
        <div class="mb-6 grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm p-6">
                <h3 class="text-sm font-extrabold text-slate-800 dark:text-slate-200 mb-4 flex items-center gap-2">
                    <Activity class="w-4 h-4 text-pink-500" v-if="!showMonthly" />
                    <BarChart3 class="w-4 h-4 text-sky-500" v-else />
                    {{ showMonthly ? 'Trend Total Deviasi Bulanan' : 'Trend Deviasi Harian' }}
                </h3>

                <div class="h-64 w-full">
                    <Bar v-if="showMonthly" :data="trendChartData" :options="chartOptions" :plugins="[trendBarLabelPlugin]" />
                    <Line v-else :data="trendChartData" :options="chartOptions" :plugins="[trendLineLabelPlugin]" />
                </div>
            </div>

            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm p-6">
                <h3 class="text-sm font-extrabold text-slate-800 dark:text-slate-200 mb-4 flex items-center gap-2">
                    <Clock class="w-4 h-4 text-violet-500" /> Jam Paling Sering Fatigue (Tahun {{ tahun }})
                </h3>

                <div class="h-64 w-full">
                    <Bar :data="jamFatigueChartData" :options="verticalBarOptions" :plugins="[jamFatigueBarLabelPlugin]" />
                </div>
            </div>
        </div>

        <!-- Distribusi Deviasi ICC & Tren Izin Fatigue -->
        <div class="mb-6 grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm p-6">
                <h3 class="text-sm font-extrabold text-slate-800 dark:text-slate-200 mb-4 flex items-center gap-2">
                    <ShieldAlert class="w-4 h-4 text-cyan-500" /> Distribusi Deviasi ICC (Tahun {{ tahun }})
                </h3>

                <div class="h-64 w-full">
                    <Bar :data="iccDistribusiChartData" :options="iccDistribusiChartOptions" :plugins="[iccDistribusiLabelPlugin]" />
                </div>
            </div>

            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm p-6">
                <div class="mb-4 flex items-center justify-between">
                    <h3 class="text-sm font-extrabold text-slate-800 dark:text-slate-200 flex items-center gap-2">
                        <FileWarning class="w-4 h-4 text-cyan-500" /> Tren Izin Fatigue (Tahun {{ tahun }})
                    </h3>
                    <span class="text-xs font-black px-3 py-1 rounded-full bg-cyan-500 text-white shadow-sm">
                        {{ izinFatigueTotal }} Kasus
                    </span>
                </div>

                <div class="h-64 w-full">
                    <Bar :data="izinFatigueChartData" :options="verticalBarOptions" :plugins="[izinFatigueLabelPlugin]" />
                </div>
            </div>
        </div>

        <!-- Rekapitulasi Deviasi per Shift -->
        <div class="mb-6 bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm p-6">
            <h3 class="text-sm font-extrabold text-rose-600 dark:text-rose-400 mb-4">
                Rekapitulasi Deviasi per Shift — {{ rekapMonthLabel }} {{ tahun }}
            </h3>

            <div v-for="shiftKey in rekapShiftKeys" :key="shiftKey" class="mb-6 last:mb-0">
                <p class="text-xs font-black text-slate-700 dark:text-slate-300 mb-2">SHIFT {{ shiftKey }}</p>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-[11px] border-collapse">
                        <thead class="bg-slate-100 dark:bg-slate-800/80 font-bold text-slate-700 dark:text-slate-300">
                            <tr>
                                <th class="p-2 whitespace-nowrap">Jenis Deviasi</th>
                                <th v-for="day in rekapDays" :key="day" class="p-2 text-center">{{ day }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            <tr v-for="jenis in rekapJenisList" :key="jenis" class="hover:bg-slate-50 dark:hover:bg-slate-800/50">
                                <td class="p-2 font-bold text-blue-600 dark:text-blue-400 whitespace-nowrap">{{ jenis }}</td>
                                <td v-for="day in rekapDays" :key="day" class="p-2 text-center">
                                    <span v-if="(rekapPerShift[shiftKey]?.[jenis]?.[day] || 0) > 0"
                                        class="inline-flex items-center justify-center min-w-[20px] px-1.5 py-0.5 rounded-full bg-rose-600 text-white font-black">
                                        {{ rekapPerShift[shiftKey][jenis][day] }}
                                    </span>
                                    <span v-else class="text-slate-400">0</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Top 10 Operators Table -->
        <div class="mb-6 bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm p-6">
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
                        <tr v-for="(op, idx) in topOperators" :key="idx" class="cursor-pointer hover:bg-slate-50 dark:hover:bg-slate-800/50" @click="openPemanggilanStatus(op)">
                            <td class="p-3 font-black text-slate-400">#{{ idx + 1 }}</td>
                            <td class="p-3 font-bold text-slate-900 dark:text-white">{{ op.nama_operator }}</td>
                            <td class="p-3 font-mono text-slate-500">{{ op.nrp }}</td>
                            <td class="p-3 text-right font-black text-rose-600 dark:text-rose-400">{{ op.total_cases }} kali</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Top 10 Operator Izin Fatigue -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm p-6">
            <h3 class="text-sm font-extrabold text-slate-800 dark:text-slate-200 mb-4 flex items-center gap-2">
                <FileWarning class="w-4 h-4 text-cyan-500" /> 10 Operator Paling Sering Izin Fatigue
            </h3>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-100 dark:bg-slate-800/80 font-bold text-slate-700 dark:text-slate-300">
                        <tr>
                            <th class="p-3">Peringkat</th>
                            <th class="p-3">Nama Operator</th>
                            <th class="p-3">NRP</th>
                            <th class="p-3 text-right">Total Izin Fatigue</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        <tr v-if="topIzinFatigueOperators.length === 0">
                            <td colspan="4" class="p-6 text-center text-slate-400">Belum ada data Izin Fatigue pada periode ini.</td>
                        </tr>
                        <tr v-for="(op, idx) in topIzinFatigueOperators" :key="idx" class="cursor-pointer hover:bg-slate-50 dark:hover:bg-slate-800/50" @click="openPemanggilanStatus(op)">
                            <td class="p-3 font-black text-slate-400">#{{ idx + 1 }}</td>
                            <td class="p-3 font-bold text-slate-900 dark:text-white">{{ op.nama_operator }}</td>
                            <td class="p-3 font-mono text-slate-500">{{ op.nrp }}</td>
                            <td class="p-3 text-right font-black text-cyan-600 dark:text-cyan-400">{{ op.total_cases }} kali</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <PemanggilanStatusModal :open="showPemanggilanModal" :operator="selectedOperator" @close="showPemanggilanModal = false" />
    </AppLayout>
</template>
