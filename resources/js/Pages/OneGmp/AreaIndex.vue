<script setup>
import { confirmDialog } from '@/utils/confirm';
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { Activity, Filter, Layers, ChevronDown, BarChart3, MapPin, Eye, Trash2, X, Plus, Trash, Droplets, Flame, Box, Camera } from 'lucide-vue-next';
import AppLayout from '@/Layouts/AppLayout.vue';
import { Bar } from 'vue-chartjs';
import {
    Chart as ChartJS, CategoryScale, LinearScale, PointElement, LineElement, BarElement,
    Title, Tooltip, Legend,
} from 'chart.js';
import DataLabelsPlugin from 'chartjs-plugin-datalabels';

ChartJS.register(CategoryScale, LinearScale, PointElement, LineElement, BarElement, Title, Tooltip, Legend, DataLabelsPlugin);

const props = defineProps({
    user: { type: Object, required: true },
    notifCounts: { type: Object, required: true },
    menu: { type: Array, required: true },
    types: { type: Array, required: true },
    filters: { type: Object, required: true },
    years: { type: Array, default: () => [] },
    weeks: { type: Array, default: () => [] },
    questions: { type: Array, default: () => [] },
    overallScore: { type: Number, default: 0 },
    totalLocations: { type: Number, default: 0 },
    pareto: { type: Array, default: () => [] },
    rekapWeighted: { type: Array, default: () => [] },
    rekapRaw: { type: Array, default: () => [] },
    rekapAverage: { type: Object, default: () => ({ weighted: { scores: {}, total: 0 }, raw: { scores: {}, total: 0 } }) },
    detailLog: { type: Array, default: () => [] },
});

const typeDisplayNames = {
    MHR: 'Mining Haul Road',
    MFOB: 'Mining Front Overburden',
    MFCO: 'Mining Front Coal Getting',
    DISPOSAL: 'Disposal Area',
    DEWATERING: 'Dewatering System',
};

const typeTabs = [
    { key: 'MHR', label: 'MHR', icon: Layers },
    { key: 'MFOB', label: 'MFOB', icon: Box },
    { key: 'MFCO', label: 'MFCO', icon: Flame },
    { key: 'DISPOSAL', label: 'DISPOSAL', icon: Trash },
    { key: 'DEWATERING', label: 'DEWATERING', icon: Droplets },
];

const selectedType = ref(props.filters.type || 'MHR');
const currentTypeLabel = computed(() => typeDisplayNames[selectedType.value] || selectedType.value);
const currentType = computed(() => props.types.find(t => t.key === selectedType.value) ?? props.types[0]);
const year = ref(props.filters.year);
const week = ref(props.filters.week);
const rekapView = ref('weighted');

const statusPerforma = computed(() => {
    if (props.overallScore >= 85) return 'Performa Baik';
    if (props.overallScore >= 70) return 'Performa Cukup';
    return 'Performa Kurang';
});

const handleFilter = () => {
    router.get('/one-gmp/area', {
        type: selectedType.value,
        year: year.value,
        week: week.value,
    }, { preserveState: true });
};

const selectType = (typeKey) => {
    selectedType.value = typeKey;
    handleFilter();
};

const sectionColors = {
    teknis: '#ea580c',
    keselamatan: '#0f766e',
    optimalisasi: '#4472C4',
    lingkungan: '#10b981',
};

const paretoChartData = computed(() => ({
    labels: props.pareto.map(p => p.label),
    datasets: [
        {
            type: 'bar',
            label: 'Performa (%)',
            data: props.pareto.map(p => p.value),
            backgroundColor: '#4172c5',
            borderColor: '#2563eb',
            borderWidth: 0,
            borderRadius: 0,
            yAxisID: 'y',
        },
        {
            type: 'line',
            label: 'Kumulatif (%)',
            data: props.pareto.map(p => p.cumulative),
            borderColor: '#C0504D',
            backgroundColor: '#C0504D',
            borderWidth: 2,
            pointBackgroundColor: '#C0504D',
            yAxisID: 'y2',
            tension: 0,
            fill: false,
        },
    ],
}));

const paretoChartOptions = computed(() => ({
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: { display: false },
        tooltip: { callbacks: { label: (ctx) => `${ctx.dataset.label}: ${ctx.raw}` } },
        datalabels: {
            color: '#ffffff',
            font: { weight: 'bold', size: 10 },
            anchor: 'center',
            align: 'center',
            rotation: -90,
            formatter: (value) => Math.round(value),
            display: (ctx) => ctx.datasetIndex === 0,
        },
    },
    scales: {
        y: { beginAtZero: true, max: 100, ticks: { stepSize: 20 }, grid: { color: '#f0f0f0' } },
        y2: { position: 'right', beginAtZero: true, max: 105, grid: { display: false } },
        x: { ticks: { maxRotation: 45, minRotation: 45, autoSkip: false, font: { size: 10 } }, grid: { display: false } },
    },
}));

const locationChartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: { display: false },
        datalabels: {
            color: '#ffffff',
            font: { weight: 'bold', size: 9 },
            anchor: 'center',
            align: 'center',
            rotation: -90,
            formatter: (value) => (value !== null && value !== undefined && value > 0) ? Math.round(value) : '',
        },
    },
    scales: {
        y: { beginAtZero: true, max: 110, ticks: { stepSize: 20, font: { size: 9 } }, grid: { color: '#f0f0f0' } },
        x: { ticks: { maxRotation: 45, minRotation: 45, autoSkip: false, font: { size: 8 } }, grid: { display: false } },
    },
};

const locationChartData = (row) => ({
    labels: props.questions.map(q => q.name),
    datasets: [{
        data: props.questions.map(q => row.percentScores?.[q.no] ?? 0),
        backgroundColor: props.questions.map(q => sectionColors[q.section_key] || '#4472C4'),
        borderRadius: 0,
    }],
});

const rekapRows = computed(() => (rekapView.value === 'weighted' ? props.rekapWeighted : props.rekapRaw));
const rekapAverageRow = computed(() => (rekapView.value === 'weighted' ? props.rekapAverage.weighted : props.rekapAverage.raw));

const scoreCellClass = (percent) => {
    if (percent === null || percent === undefined) return 'text-slate-300';
    if (percent <= 50) return 'text-rose-600 font-bold';
    if (percent <= 70) return 'text-amber-600 font-semibold';
    return 'text-emerald-600 font-semibold';
};

const rawScoreCellClass = (raw) => {
    if (raw === null || raw === undefined) return 'text-slate-300';
    if (raw <= 1) return 'text-rose-600 font-bold';
    if (raw <= 3) return 'text-amber-600 font-semibold';
    return 'text-emerald-600 font-semibold';
};

const showDetailModal = ref(false);
const selectedForm = ref(null);
const showWeightModal = ref(false);
const editingQuestion = ref(null);
const newWeight = ref(0);

const openDetail = (form) => {
    selectedForm.value = form;
    showDetailModal.value = true;
};

const deleteForm = async (id) => {
    if (!await confirmDialog('Hapus data penilaian ini? Tindakan ini tidak bisa dibatalkan.')) return;
    router.delete(`/one-gmp/penilaian/${id}`, { preserveScroll: true });
};

const openWeightModal = (question) => {
    editingQuestion.value = question;
    newWeight.value = question.weight;
    showWeightModal.value = true;
};

const isGroupLeader = computed(() => {
    const role = String(props.user?.role || '').toLowerCase();
    const jabatan = String(props.user?.jabatan || '').toUpperCase();
    return role === 'group-leader' || role === 'groupleader' || role === 'gl' || jabatan.includes('GROUP LEADER');
});

const saveWeight = () => {
    if (newWeight.value < 0 || newWeight.value > 100) {
        alert('Bobot harus antara 0-100');
        return;
    }
    router.post(`/one-gmp/questions/${editingQuestion.value.no}/weight`, {
        weight: newWeight.value,
        type: selectedType.value,
    }, {
        preserveScroll: true,
        onSuccess: () => {
            showWeightModal.value = false;
        },
    });
};
</script>

<template>
    <Head :title="`GMP Area: ${currentType?.key ?? selectedType} - OneClick`" />

    <AppLayout :user="user" :notif-counts="notifCounts" :menu="menu" :back-url="isGroupLeader ? '/portal/data' : undefined">
        <!-- Top Nav Action Header -->
        <div class="mb-4 flex items-center justify-between">
            <h2 class="text-lg sm:text-xl font-black text-slate-800 tracking-tight">GMP - Good Mining Practice</h2>
            <Link :href="`/one-gmp/penilaian/${selectedType}`" class="px-3.5 py-2 sm:px-4 sm:py-2 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl text-xs shadow-sm flex items-center gap-1.5 transition-colors">
                <Plus class="w-3.5 h-3.5" /> PENILAIAN
            </Link>
        </div>

        <!-- Green Hero Banner (GMP Area - Image 2) -->
        <div class="mb-5 rounded-2xl md:rounded-3xl p-5 md:p-8 text-white shadow-md relative overflow-hidden" style="background: linear-gradient(135deg, #066044 0%, #087956 100%);">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-5 md:gap-6 relative z-10">
                <div class="space-y-3">
                    <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider bg-white/15 text-emerald-100 border border-white/20">
                        <Activity class="w-3.5 h-3.5" /> CHART DASHBOARD
                    </div>

                    <h1 class="text-2xl sm:text-3xl md:text-4xl font-black tracking-tight text-white">
                        GMP {{ currentType?.key ?? selectedType }}
                    </h1>

                    <p class="text-xs text-emerald-100/90 max-w-xl">
                        Dashboard chart {{ currentTypeLabel }} untuk membaca performa tiap aspek teknis dan keselamatan per minggu.
                    </p>

                    <div class="flex flex-wrap items-center gap-1.5 sm:gap-2 pt-1">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] sm:text-xs font-semibold bg-white/10 text-white border border-white/20">
                            <Layers class="w-3 h-3" /> {{ currentTypeLabel }}
                        </span>
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] sm:text-xs font-semibold bg-white/10 text-white border border-white/20">
                            <BarChart3 class="w-3 h-3" /> Pareto + Grid Chart
                        </span>
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] sm:text-xs font-semibold bg-white/10 text-white border border-white/20">
                            <Filter class="w-3 h-3" /> Filter per Week
                        </span>
                    </div>
                </div>

                <div class="w-full md:w-auto md:min-w-[220px] rounded-2xl bg-white/10 backdrop-blur-md border border-white/20 p-4 md:p-5 flex flex-row md:flex-col items-center md:items-start justify-between md:justify-center gap-2">
                    <div>
                        <div class="text-2xl sm:text-3xl md:text-4xl font-black text-white leading-tight">
                            {{ overallScore }}
                        </div>
                        <div class="text-[11px] sm:text-xs font-bold text-emerald-100">
                            {{ statusPerforma }}
                        </div>
                    </div>
                    <span class="text-[10px] sm:text-[11px] font-bold px-2.5 py-1 rounded-full bg-emerald-500/20 text-emerald-200 border border-emerald-400/30">
                        {{ totalLocations }} Area Dinilai
                    </span>
                </div>
            </div>
        </div>

        <!-- Sub-Area Tabs (MHR, MFOB, MFCO, DISPOSAL, DEWATERING) - Fit 5 on mobile -->
        <div class="mb-4 grid grid-cols-5 gap-1 sm:flex sm:flex-wrap sm:gap-2">
            <button
                v-for="tab in typeTabs"
                :key="tab.key"
                @click="selectType(tab.key)"
                :class="[
                    'w-full sm:w-auto flex flex-col sm:flex-row items-center justify-center gap-1 sm:gap-2 px-1 py-2 sm:px-4 sm:py-2.5 rounded-xl text-[9.5px] sm:text-xs font-bold transition-all select-none active:scale-95 text-center min-w-0',
                    selectedType === tab.key
                        ? 'bg-emerald-600 text-white shadow-sm shadow-emerald-600/30 ring-1 ring-emerald-600'
                        : 'bg-white text-slate-700 border border-slate-200/90 hover:bg-slate-50'
                ]"
            >
                <component :is="tab.icon" class="w-3.5 h-3.5 sm:w-4 sm:h-4 shrink-0" />
                <span class="tracking-tight sm:tracking-normal truncate max-w-full leading-tight">{{ tab.label }}</span>
            </button>
        </div>

        <!-- Filter Bar (FILTER CHART) -->
        <div class="mb-5 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-3.5">
            <div>
                <h4 class="text-xs font-bold text-slate-700 uppercase tracking-wider">FILTER CHART</h4>
                <p class="text-[11px] text-slate-400">Pilih minggu untuk menampilkan data penilaian aktual atau fallback ke data default.</p>
            </div>

            <div class="flex flex-wrap items-center gap-2.5 sm:gap-3 w-full sm:w-auto">
                <span class="text-xs font-bold text-slate-600 hidden sm:inline">Filter:</span>

                <div class="flex items-center gap-2 flex-1 sm:flex-initial">
                    <select v-model="year" @change="handleFilter" class="flex-1 sm:flex-initial rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-xs font-bold text-slate-700 outline-none focus:border-emerald-500">
                        <option v-for="y in years" :key="y" :value="String(y)">{{ y }}</option>
                        <option v-if="years.length === 0" :value="year">{{ year }}</option>
                    </select>

                    <select v-model="week" @change="handleFilter" class="flex-1 sm:flex-initial rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-xs font-bold text-slate-700 outline-none focus:border-emerald-500 min-w-[130px]">
                        <option v-if="weeks.length === 0" value="">Tidak ada data</option>
                        <option v-for="w in weeks" :key="w" :value="w">{{ String(w).toLowerCase().includes('week') ? w : `Week ${w}` }}</option>
                    </select>
                </div>

                <div class="flex items-center justify-between sm:justify-start gap-2 w-full sm:w-auto pt-2 sm:pt-0 border-t sm:border-t-0 border-slate-100">
                    <span class="px-2.5 py-1 rounded-lg text-[11px] font-bold bg-emerald-700 text-white shadow-2xs">
                        Data Penilaian
                    </span>

                    <div class="flex items-center gap-3 pl-2 sm:border-l border-slate-200">
                        <span class="inline-flex items-center gap-1.5 text-xs font-bold text-slate-700">
                            <span class="w-2.5 h-2.5 rounded-sm bg-emerald-600"></span> Teknis
                        </span>
                        <span class="inline-flex items-center gap-1.5 text-xs font-bold text-slate-700">
                            <span class="w-2.5 h-2.5 rounded-sm bg-[#064e3b]"></span> Keselamatan
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <div v-if="!week || pareto.length === 0" class="mb-6 rounded-2xl border border-slate-200 bg-white p-10 text-center text-sm text-slate-400">
            Belum ada data penilaian {{ currentTypeLabel }} yang disetujui untuk minggu ini.
        </div>

        <template v-else>
            <!-- Full-Width Pareto Chart -->
            <div class="mb-5 bg-white rounded-2xl border border-slate-200 shadow-sm p-4 sm:p-6">
                <div class="flex items-center justify-between mb-4 flex-wrap gap-2">
                    <h3 class="text-xs sm:text-sm font-bold text-slate-700">
                        GMP {{ currentType?.key ?? selectedType }} - {{ week ? (String(week).toLowerCase().includes('week') ? week : 'Week ' + week) : '' }}{{ !week || String(week).includes(String(year)) ? '' : ' - ' + year }}
                    </h3>
                    <span class="text-[11px] text-slate-400 font-medium md:hidden">Geser chart &rarr;</span>
                </div>
                <div class="overflow-x-auto pb-2 -mx-2 px-2 md:mx-0 md:px-0">
                    <div class="min-w-[650px] md:min-w-0" style="height: 340px;">
                        <Bar :data="paretoChartData" :options="paretoChartOptions" />
                    </div>
                </div>
            </div>

            <!-- Grid Mini-Charts per Lokasi (3 Columns) -->
            <div class="mb-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-4">
                <div
                    v-for="row in rekapWeighted"
                    :key="row.lokasi"
                    class="bg-white rounded-2xl border border-slate-200 shadow-sm p-3.5 sm:p-4"
                >
                    <div class="flex items-center justify-between mb-2">
                        <h4 class="text-xs font-bold text-slate-800 flex items-center gap-1.5 truncate">
                            <MapPin class="w-3.5 h-3.5 text-sky-500 shrink-0" />
                            <span class="truncate">{{ row.lokasi }}</span>
                        </h4>
                        <span class="text-xs font-black text-emerald-700 shrink-0 bg-emerald-50 px-2 py-0.5 rounded-lg border border-emerald-100">
                            {{ row.total }}
                        </span>
                    </div>
                    <div style="height: 180px;">
                        <Bar :data="locationChartData(row)" :options="locationChartOptions" />
                    </div>
                </div>
            </div>

            <!-- Rekap Table -->
            <div class="mb-6 rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 px-6 py-4">
                    <div>
                        <h3 class="text-sm font-extrabold text-slate-800">Rekap Nilai Tiap Aspek — {{ currentTypeLabel }}</h3>
                        <p class="text-[11px] text-slate-400">{{ week }} &bull; {{ totalLocations }} Lokasi dinilai</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="flex rounded-xl border border-slate-200 bg-slate-50 p-1 text-xs font-bold">
                            <button @click="rekapView = 'weighted'" :class="['px-3 py-1 rounded-lg transition-colors', rekapView === 'weighted' ? 'bg-white text-emerald-700 shadow-sm' : 'text-slate-500 hover:text-slate-800']">
                                Bobot (%)
                            </button>
                            <button @click="rekapView = 'raw'" :class="['px-3 py-1 rounded-lg transition-colors', rekapView === 'raw' ? 'bg-white text-emerald-700 shadow-sm' : 'text-slate-500 hover:text-slate-800']">
                                Raw (1-5)
                            </button>
                        </div>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50 font-bold text-slate-700">
                            <tr>
                                <th class="p-3">Lokasi</th>
                                <th v-for="q in questions" :key="q.no" class="p-3 text-center cursor-pointer hover:bg-slate-100" @click="openWeightModal(q)" :title="`Klik untuk edit bobot (Bobot: ${q.weight}%)`">
                                    <div class="flex flex-col items-center">
                                        <span class="font-extrabold text-slate-800">Q{{ q.no }}</span>
                                        <span class="text-[10px] text-slate-400 font-normal">({{ q.weight }}%)</span>
                                    </div>
                                </th>
                                <th class="p-3 text-center">Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="row in rekapRows" :key="row.lokasi" class="hover:bg-slate-50">
                                <td class="p-3 font-bold text-slate-800">{{ row.lokasi }}</td>
                                <td v-for="q in questions" :key="q.no" class="p-3 text-center" :class="rekapView === 'weighted' ? scoreCellClass(row.percentScores?.[q.no]) : rawScoreCellClass(row.scores?.[q.no])">
                                    {{ row.scores?.[q.no] ?? '—' }}
                                </td>
                                <td class="p-3 text-center font-black" :class="rekapView === 'weighted' ? scoreCellClass(row.total) : 'text-slate-900'">
                                    {{ row.total }}
                                </td>
                            </tr>
                            <tr v-if="rekapAverageRow" class="bg-slate-100/70 font-bold">
                                <td class="p-3 text-slate-800">Rata-rata</td>
                                <td v-for="q in questions" :key="q.no" class="p-3 text-center" :class="rekapView === 'weighted' ? scoreCellClass(rekapAverageRow.percentScores?.[q.no]) : rawScoreCellClass(rekapAverageRow.scores?.[q.no])">
                                    {{ rekapAverageRow.scores?.[q.no] ?? '—' }}
                                </td>
                                <td class="p-3 text-center font-black text-emerald-700">
                                    {{ rekapAverageRow.total }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Detail Log Table -->
            <div class="mb-6 rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
                <div class="border-b border-slate-100 px-6 py-4">
                    <h3 class="text-sm font-extrabold text-slate-800">Data Detail Penilaian — {{ currentTypeLabel }}</h3>
                    <p class="text-[11px] text-slate-400">Daftar inspeksi aktual yang telah disetujui untuk minggu ini</p>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50 font-bold text-slate-700">
                            <tr>
                                <th class="p-2.5">Tanggal</th>
                                <th class="p-2.5">Lokasi</th>
                                <th class="p-2.5">Penilai</th>
                                <th v-for="q in questions" :key="q.no" class="p-2.5 text-center">Q{{ q.no }}</th>
                                <th class="p-2.5 text-center">Total</th>
                                <th class="p-2.5 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="form in detailLog" :key="form.id" class="hover:bg-slate-50">
                                <td class="p-2.5 text-slate-500">{{ form.tanggal }}</td>
                                <td class="p-2.5 font-bold text-slate-800">
                                    <div class="flex items-center gap-1.5">
                                        <span>{{ form.lokasi }}</span>
                                        <a v-if="form.foto_area" :href="form.foto_area" target="_blank" title="Lihat foto dokumentasi area" class="inline-flex items-center text-emerald-600 hover:text-emerald-800">
                                            <Camera class="w-3.5 h-3.5" />
                                        </a>
                                    </div>
                                </td>
                                <td class="p-2.5 text-slate-500">{{ form.penilai }}</td>
                                <td v-for="q in questions" :key="q.no" class="p-2.5 text-center" :class="rawScoreCellClass(form.scores[q.no])">
                                    {{ form.scores[q.no] ?? '—' }}
                                </td>
                                <td class="p-2.5 text-center font-black text-slate-900">{{ form.total_percent }}</td>
                                <td class="p-2.5 text-center">
                                    <div class="flex items-center justify-center gap-1">
                                        <button @click="openDetail(form)" title="Lihat detail" class="p-1.5 text-sky-600 hover:bg-sky-50 rounded-lg">
                                            <Eye class="w-4 h-4" />
                                        </button>
                                        <button @click="deleteForm(form.id)" title="Hapus" class="p-1.5 text-rose-600 hover:bg-rose-50 rounded-lg">
                                            <Trash2 class="w-4 h-4" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </template>

        <!-- Detail Modal -->
        <div v-if="showDetailModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4" @click.self="showDetailModal = false">
            <div class="max-h-[85vh] w-full max-w-lg overflow-y-auto rounded-2xl bg-white shadow-2xl">
                <div class="sticky top-0 flex items-center justify-between border-b border-slate-100 bg-white px-5 py-4">
                    <div>
                        <h3 class="text-sm font-extrabold text-slate-800">{{ selectedForm?.lokasi }}</h3>
                        <p class="text-[11px] text-slate-400">{{ selectedForm?.tanggal }} &bull; {{ selectedForm?.penilai }}</p>
                    </div>
                    <button @click="showDetailModal = false" class="p-1 text-slate-400 hover:text-slate-600">
                        <X class="w-5 h-5" />
                    </button>
                </div>

                <!-- Foto Area jika ada -->
                <div v-if="selectedForm?.foto_area" class="px-5 py-3 border-b border-slate-100 bg-slate-50/50">
                    <p class="text-[11px] font-bold uppercase text-slate-600 mb-1.5 flex items-center gap-1.5">
                        <Camera class="w-3.5 h-3.5 text-emerald-600" />
                        Foto Dokumentasi Area
                    </p>
                    <a :href="selectedForm.foto_area" target="_blank" class="block group relative overflow-hidden rounded-xl border border-slate-200 shadow-sm bg-white">
                        <img :src="selectedForm.foto_area" alt="Foto Area" class="w-full max-h-56 object-cover group-hover:scale-105 transition duration-300" />
                        <div class="absolute bottom-2 right-2 bg-black/60 backdrop-blur-sm text-white text-[10px] font-medium px-2 py-1 rounded-lg">
                            Buka Ukuran Penuh ↗
                        </div>
                    </a>
                </div>

                <div class="divide-y divide-slate-100 px-5">
                    <div v-for="q in questions" :key="q.no" class="py-2.5 text-xs">
                        <div class="flex items-center justify-between">
                            <span class="text-slate-700 font-medium">Q{{ q.no }} — {{ q.name }}</span>
                            <span class="font-bold" :class="rawScoreCellClass(selectedForm?.scores?.[q.no])">{{ selectedForm?.scores?.[q.no] ?? '—' }}</span>
                        </div>
                        <div v-if="selectedForm?.question_photos?.[q.no]" class="mt-2">
                            <a :href="selectedForm.question_photos[q.no]" target="_blank" class="inline-flex items-center gap-2 p-1.5 rounded-xl border border-emerald-200 bg-emerald-50/50 hover:bg-emerald-100/50 transition">
                                <img :src="selectedForm.question_photos[q.no]" alt="Foto Temuan" class="w-12 h-10 object-cover rounded-lg border border-emerald-200" />
                                <span class="text-[11px] font-bold text-emerald-800 flex items-center gap-1">
                                    <Camera class="w-3.5 h-3.5 text-emerald-600" />
                                    Foto Temuan Q{{ q.no }} ↗
                                </span>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="flex items-center justify-between border-t border-slate-100 px-5 py-4">
                    <span class="text-xs font-bold text-slate-500">Total Skor</span>
                    <span class="text-base font-black text-emerald-700">{{ selectedForm?.total_percent }}</span>
                </div>
            </div>
        </div>

        <!-- Weight Modal -->
        <div v-if="showWeightModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4" @click.self="showWeightModal = false">
            <div class="w-full max-w-sm rounded-2xl bg-white shadow-2xl">
                <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
                    <div>
                        <h3 class="text-sm font-extrabold text-slate-800">Edit Bobot</h3>
                        <p class="text-[11px] text-slate-400">Q{{ editingQuestion?.no }} — {{ editingQuestion?.name }}</p>
                    </div>
                    <button @click="showWeightModal = false" class="p-1 text-slate-400 hover:text-slate-600">
                        <X class="w-5 h-5" />
                    </button>
                </div>
                <div class="px-6 py-4 space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-600 mb-2">Bobot (%)</label>
                        <input v-model.number="newWeight" type="number" min="0" max="100" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm font-bold text-slate-800 focus:border-emerald-500 focus:outline-none" placeholder="Contoh: 25" />
                    </div>
                    <div class="flex gap-2">
                        <button @click="showWeightModal = false" class="flex-1 px-4 py-2 border border-slate-300 rounded-lg text-sm font-bold text-slate-600 hover:bg-slate-50">
                            Batal
                        </button>
                        <button @click="saveWeight" class="flex-1 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-sm font-bold">
                            Simpan
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
