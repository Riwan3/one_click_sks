<script setup>
import { ref, computed } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import { Doughnut, Bar, Line } from 'vue-chartjs';
import { Search, X, UserPlus, ListChecks, History } from '@lucide/vue';

const props = defineProps({
    filters: { type: Object, required: true },
    coaching: { type: Object, required: true },
});

const searchInput = ref(props.coaching.search || '');
const activeSubTab = ref('never');

const submitSearch = () => {
    router.get('/one-power', {
        bulan: props.filters.bulan,
        tahun: props.filters.tahun,
        coaching_search: searchInput.value || undefined,
    }, { preserveScroll: true, preserveState: true });
};

const clearSearch = () => {
    searchInput.value = '';
    submitSearch();
};

const baseOptions = { maintainAspectRatio: false, plugins: { legend: { display: false } } };
const doughnutOptions = {
    maintainAspectRatio: false,
    cutout: '68%',
    plugins: { legend: { position: 'bottom', labels: { font: { size: 11 }, usePointStyle: true } } },
};
const gridOptions = {
    ...baseOptions,
    scales: {
        y: { beginAtZero: true, ticks: { stepSize: 1, font: { size: 10 } }, grid: { color: '#f0f0f0' } },
        x: { grid: { display: false }, ticks: { font: { size: 9 } } },
    },
};

const coverageData = computed(() => ({
    labels: ['Belum Pernah CC', 'Sudah Pernah CC'],
    datasets: [{
        data: [props.coaching.totalBelumPernah, props.coaching.totalSudahPernah],
        backgroundColor: ['#dc2626', '#16a34a'],
        borderWidth: 0,
    }],
}));

const distData = computed(() => ({
    labels: props.coaching.distStats.map((d) => d.label),
    datasets: [{
        data: props.coaching.distStats.map((d) => d.value),
        backgroundColor: ['#94a3b8', '#38bdf8', '#818cf8', '#c084fc', '#f472b6', '#f59e0b'],
        borderRadius: 5,
    }],
}));

const trendData = computed(() => ({
    labels: props.coaching.monthlyTrend.map((d) => d.label),
    datasets: [{
        data: props.coaching.monthlyTrend.map((d) => d.value),
        borderColor: '#f59e0b',
        backgroundColor: 'rgba(245,158,11,0.08)',
        borderWidth: 2,
        tension: 0.3,
        fill: true,
        pointRadius: 4,
    }],
}));

const relativeTime = (days) => {
    if (days === null || days === undefined) return '-';
    if (days < 30) return `${days} hari lalu`;
    if (days < 365) return `${Math.floor(days / 30)} bulan lalu`;
    return `${Math.floor(days / 365)} tahun lalu`;
};

const riwayatLink = (nrp) => `/one-power/pembinaan/coaching-counseling?search=${encodeURIComponent(nrp)}`;
</script>

<template>
    <div class="mb-4 flex flex-col gap-3 rounded-xl border border-slate-200 bg-white p-4 shadow-sm lg:flex-row lg:items-center lg:justify-between">
        <div>
            <h6 class="mb-1 text-sm font-bold text-slate-700">Dashboard Coaching &amp; Counseling</h6>
            <p class="m-0 text-xs text-slate-500">Monitoring pemerataan pembinaan — siapa saja yang belum atau paling jarang di-coaching.</p>
        </div>
        <div class="flex items-center gap-2">
            <form class="relative" @submit.prevent="submitSearch">
                <Search class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
                <input
                    v-model="searchInput"
                    type="text"
                    placeholder="Cari NRP, nama, jabatan..."
                    class="w-56 rounded-lg border border-slate-200 bg-white py-1.5 pl-9 pr-8 text-xs outline-none focus:border-blue-500"
                >
                <button v-if="searchInput" type="button" class="absolute right-2 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600" @click="clearSearch">
                    <X class="h-3.5 w-3.5" />
                </button>
            </form>
            <Link href="/one-power/pembinaan/coaching-counseling" class="flex items-center gap-1.5 whitespace-nowrap rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-bold text-slate-600 hover:bg-slate-50">
                <ListChecks class="h-3.5 w-3.5" /> Data Master CC
            </Link>
            <Link href="/one-power/pembinaan/coaching-counseling/create" class="flex items-center gap-1.5 whitespace-nowrap rounded-lg bg-blue-500 px-3 py-1.5 text-xs font-bold text-white hover:bg-blue-600">
                <UserPlus class="h-3.5 w-3.5" /> Buat CC Baru
            </Link>
        </div>
    </div>

    <div class="mb-4 grid grid-cols-2 gap-3 lg:grid-cols-4">
        <div class="rounded-xl border-l-4 border-blue-500 bg-white p-3 shadow-sm">
            <p class="mb-0 text-xs font-bold text-slate-500">Total Karyawan</p>
            <h4 class="mb-0 text-2xl font-bold text-blue-600">{{ coaching.totalKaryawan.toLocaleString('id-ID') }}</h4>
            <p class="mb-0 mt-0.5 text-[11px] text-slate-400">Status Karyawan Aktif</p>
        </div>
        <div class="rounded-xl border-l-4 border-red-500 bg-white p-3 shadow-sm">
            <div class="flex items-center justify-between">
                <p class="mb-0 text-xs font-bold text-slate-500">Belum Pernah CC</p>
                <span class="rounded-full bg-red-100 px-1.5 py-0.5 text-[10px] font-bold text-red-700">{{ coaching.persenBelumPernah }}%</span>
            </div>
            <h4 class="mb-0 text-2xl font-bold text-red-600">{{ coaching.totalBelumPernah.toLocaleString('id-ID') }}</h4>
            <p class="mb-0 mt-0.5 text-[11px] text-slate-400">Belum Ada Riwayat</p>
        </div>
        <div class="rounded-xl border-l-4 border-amber-500 bg-white p-3 shadow-sm">
            <p class="mb-0 text-xs font-bold text-slate-500">Paling Sedikit CC</p>
            <h4 class="mb-0 text-2xl font-bold text-amber-600">{{ coaching.leastCoachedList.length.toLocaleString('id-ID') }}</h4>
            <p class="mb-0 mt-0.5 text-[11px] text-slate-400">Coaching Terkecil &amp; Terlama</p>
        </div>
        <div class="rounded-xl border-l-4 border-emerald-500 bg-white p-3 shadow-sm">
            <div class="flex items-center justify-between">
                <p class="mb-0 text-xs font-bold text-slate-500">Pernah di-CC</p>
                <span class="rounded-full bg-emerald-100 px-1.5 py-0.5 text-[10px] font-bold text-emerald-700">{{ coaching.persenSudahPernah }}%</span>
            </div>
            <h4 class="mb-0 text-2xl font-bold text-emerald-600">{{ coaching.totalSudahPernah.toLocaleString('id-ID') }}</h4>
            <p class="mb-0 mt-0.5 text-[11px] text-slate-400">Total Sesi: {{ coaching.totalCcAll.toLocaleString('id-ID') }} CC</p>
        </div>
    </div>

    <div class="mb-4 grid grid-cols-1 gap-4 lg:grid-cols-3">
        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
            <h6 class="mb-3 text-sm font-bold text-slate-700">Cakupan Status Coaching</h6>
            <div class="h-[220px]">
                <Doughnut :data="coverageData" :options="doughnutOptions" />
            </div>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
            <h6 class="mb-3 text-sm font-bold text-slate-700">Distribusi Frekuensi CC</h6>
            <div class="h-[220px]">
                <Bar :data="distData" :options="gridOptions" />
            </div>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
            <h6 class="mb-3 text-sm font-bold text-slate-700">Tren Sesi CC ({{ coaching.tahun }})</h6>
            <div class="h-[220px]">
                <Line :data="trendData" :options="gridOptions" />
            </div>
        </div>
    </div>

    <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
        <div class="mb-3 flex items-center gap-2 border-b border-slate-100 pb-3">
            <button
                type="button"
                :class="['rounded-full px-3 py-1.5 text-xs font-bold transition', activeSubTab === 'never' ? 'bg-red-500 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200']"
                @click="activeSubTab = 'never'"
            >
                Belum Pernah di-CC ({{ coaching.neverCoachedList.length }})
            </button>
            <button
                type="button"
                :class="['rounded-full px-3 py-1.5 text-xs font-bold transition', activeSubTab === 'least' ? 'bg-amber-500 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200']"
                @click="activeSubTab = 'least'"
            >
                Paling Sedikit di-CC ({{ coaching.leastCoachedList.length }})
            </button>
        </div>

        <div v-if="activeSubTab === 'never'" class="overflow-x-auto">
            <table class="w-full min-w-[800px] text-left text-sm">
                <thead>
                    <tr class="border-b border-slate-100 text-[0.7rem] uppercase tracking-wide text-slate-500">
                        <th class="px-3 py-2 font-semibold">Karyawan</th>
                        <th class="px-3 py-2 font-semibold">Jabatan</th>
                        <th class="px-3 py-2 font-semibold">Unit</th>
                        <th class="px-3 py-2 font-semibold">Group Leader</th>
                        <th class="px-3 py-2 font-semibold">Status CC</th>
                        <th class="px-3 py-2 font-semibold">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-if="coaching.neverCoachedList.length === 0">
                        <td colspan="6" class="px-3 py-8 text-center text-slate-400">Semua karyawan sudah pernah di-CC.</td>
                    </tr>
                    <tr v-for="row in coaching.neverCoachedList" :key="row.id" class="border-b border-slate-50 last:border-0 hover:bg-slate-50/60">
                        <td class="px-3 py-2.5 align-middle">
                            <div class="font-bold text-slate-700">{{ row.name }}</div>
                            <div class="text-xs text-slate-400">{{ row.nrp }}</div>
                        </td>
                        <td class="px-3 py-2.5 align-middle text-xs text-slate-600">{{ row.jabatan || '-' }}</td>
                        <td class="px-3 py-2.5 align-middle text-xs text-slate-600">{{ row.unit || '-' }}</td>
                        <td class="px-3 py-2.5 align-middle text-xs text-slate-600">{{ row.glName || '-' }}</td>
                        <td class="px-3 py-2.5 align-middle">
                            <span class="rounded-full bg-red-100 px-2.5 py-1 text-xs font-semibold text-red-700">0 Sesi (Belum Pernah)</span>
                        </td>
                        <td class="px-3 py-2.5 align-middle">
                            <Link :href="`/one-power/pembinaan/coaching-counseling/create`" class="inline-flex items-center gap-1 rounded-lg border border-slate-200 px-2.5 py-1 text-xs font-semibold text-blue-600 hover:bg-blue-50">
                                <UserPlus class="h-3.5 w-3.5" /> Buat CC
                            </Link>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div v-else class="overflow-x-auto">
            <table class="w-full min-w-[900px] text-left text-sm">
                <thead>
                    <tr class="border-b border-slate-100 text-[0.7rem] uppercase tracking-wide text-slate-500">
                        <th class="px-3 py-2 font-semibold">Karyawan</th>
                        <th class="px-3 py-2 font-semibold">Jabatan</th>
                        <th class="px-3 py-2 font-semibold">Group Leader</th>
                        <th class="px-3 py-2 font-semibold">Total CC</th>
                        <th class="px-3 py-2 font-semibold">Coaching Terakhir</th>
                        <th class="px-3 py-2 font-semibold">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-if="coaching.leastCoachedList.length === 0">
                        <td colspan="6" class="px-3 py-8 text-center text-slate-400">Belum ada data coaching.</td>
                    </tr>
                    <tr v-for="row in coaching.leastCoachedList" :key="row.id" class="border-b border-slate-50 last:border-0 hover:bg-slate-50/60">
                        <td class="px-3 py-2.5 align-middle">
                            <div class="font-bold text-slate-700">{{ row.name }}</div>
                            <div class="text-xs text-slate-400">{{ row.nrp }}</div>
                        </td>
                        <td class="px-3 py-2.5 align-middle text-xs text-slate-600">{{ row.jabatan || '-' }}</td>
                        <td class="px-3 py-2.5 align-middle text-xs text-slate-600">{{ row.glName || '-' }}</td>
                        <td class="px-3 py-2.5 align-middle">
                            <span class="rounded-full bg-amber-100 px-2.5 py-1 text-xs font-semibold text-amber-700">{{ row.totalCc }}x Sesi</span>
                        </td>
                        <td class="px-3 py-2.5 align-middle text-xs text-slate-600">
                            <div>{{ row.lastCcDate || '-' }}</div>
                            <div class="text-slate-400">{{ relativeTime(row.daysSinceLast) }}</div>
                        </td>
                        <td class="px-3 py-2.5 align-middle">
                            <div class="flex gap-1.5">
                                <Link href="/one-power/pembinaan/coaching-counseling/create" title="Coaching" class="flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 text-blue-500 hover:bg-blue-50">
                                    <UserPlus class="h-3.5 w-3.5" />
                                </Link>
                                <Link :href="riwayatLink(row.nrp)" title="Riwayat Coaching" class="flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 text-slate-500 hover:bg-slate-50">
                                    <History class="h-3.5 w-3.5" />
                                </Link>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>
