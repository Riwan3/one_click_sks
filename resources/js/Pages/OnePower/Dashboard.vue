<script setup>
import { computed, ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import { Line, Bar } from 'vue-chartjs';
import AppLayout from '@/Layouts/AppLayout.vue';
import StatCard from '@/Components/ui/StatCard.vue';
import DashboardTabs from './Partials/DashboardTabs.vue';
import MonthYearFilter from './Partials/MonthYearFilter.vue';
import CoachingTab from './Partials/CoachingTab.vue';

const props = defineProps({
    user: { type: Object, required: true },
    notifCounts: { type: Object, required: true },
    menu: { type: Array, required: true },
    filters: { type: Object, required: true },
    stats: { type: Object, required: true },
    dailyTrend: { type: Array, required: true },
    yearlyTrend: { type: Array, required: true },
    topOperators: { type: Array, required: true },
    attendance: { type: Object, required: true },
    deviasi: { type: Object, required: true },
    coaching: { type: Object, required: true },
});

const CHART_COLOR = '#006699';
const DANGER_COLOR = '#dc2626';

const baseOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: { legend: { display: false } },
    scales: {
        x: { grid: { color: '#f0f0f0' }, ticks: { font: { size: 10 } } },
        y: { grid: { color: '#f0f0f0' }, ticks: { font: { size: 10 } } },
    },
};

const trendChartData = computed(() => ({
    labels: props.dailyTrend.map((point) => point.label),
    datasets: [{
        data: props.dailyTrend.map((point) => point.value),
        borderColor: CHART_COLOR,
        backgroundColor: 'rgba(0,102,153,0.05)',
        borderWidth: 2,
        tension: 0.3,
        fill: true,
        pointRadius: 4,
    }],
}));

const yearlyChartData = computed(() => ({
    labels: props.yearlyTrend.map((point) => point.label),
    datasets: [{
        data: props.yearlyTrend.map((point) => point.value),
        backgroundColor: CHART_COLOR,
        borderRadius: 5,
        barThickness: 20,
    }],
}));

const topOperatorsChartData = computed(() => ({
    labels: props.topOperators.map((point) => point.label),
    datasets: [{
        data: props.topOperators.map((point) => point.value),
        backgroundColor: CHART_COLOR,
        borderRadius: 4,
        barThickness: 20,
    }],
}));

const topOperatorsOptions = { ...baseOptions, indexAxis: 'y' };

const attendanceTrendChartData = computed(() => ({
    labels: props.attendance.dailyTrend.map((point) => point.label),
    datasets: [{
        data: props.attendance.dailyTrend.map((point) => point.value),
        borderColor: DANGER_COLOR,
        backgroundColor: 'rgba(220,38,38,0.05)',
        borderWidth: 2,
        tension: 0.3,
        fill: true,
        pointRadius: 4,
    }],
}));

const topAbsentChartData = computed(() => ({
    labels: props.attendance.topAbsent.map((point) => point.label),
    datasets: [{
        data: props.attendance.topAbsent.map((point) => point.value),
        backgroundColor: DANGER_COLOR,
        borderRadius: 4,
        barThickness: 20,
    }],
}));

const topAbsentOptions = { ...baseOptions, indexAxis: 'y' };

const emergencyByMonthChartData = computed(() => ({
    labels: props.deviasi.emergencyByMonth.map((point) => point.label),
    datasets: [{
        data: props.deviasi.emergencyByMonth.map((point) => point.value),
        backgroundColor: '#f59e0b',
        borderRadius: 5,
        barThickness: 20,
    }],
}));

const unfitByLokasiChartData = computed(() => ({
    labels: props.deviasi.unfitByLokasi.map((point) => point.label),
    datasets: [{
        data: props.deviasi.unfitByLokasi.map((point) => point.value),
        backgroundColor: DANGER_COLOR,
        borderRadius: 4,
        barThickness: 20,
    }],
}));

const unfitByLokasiOptions = { ...baseOptions, indexAxis: 'y' };

const activeTab = ref('hm');
</script>

<template>
    <Head title="Dashboard ATR, HM, Deviasi Operator - OneClick" />

    <AppLayout :user="user" :notif-counts="notifCounts" :menu="menu">
        <nav class="mb-2 text-sm text-slate-500">
            <Link href="/portal" class="text-blue-500 no-underline hover:underline">Home</Link>
            <span class="mx-1.5">&gt;</span>
            <Link href="/one-power" class="text-blue-500 no-underline hover:underline">One-Power</Link>
            <span class="mx-1.5">&gt;</span>
            <span class="font-semibold text-slate-700">Dashboard ATR, HM, Deviasi Operator</span>
        </nav>

        <div class="mb-5">
            <h1 class="mb-1 text-2xl font-extrabold text-slate-800">Dashboard ATR, HM, Deviasi Operator</h1>
            <p class="m-0 text-sm text-slate-500">Ringkasan data hour meter (HM), kehadiran, dan deviasi operator</p>
        </div>

        <div class="mb-4 flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
            <DashboardTabs :active-tab="activeTab" @tab-selected="activeTab = $event" />
            <MonthYearFilter :filters="filters" />
        </div>

        <template v-if="activeTab === 'hm'">
            <div class="mb-4 grid grid-cols-2 gap-3 lg:grid-cols-4">
                <StatCard label="Total HM (Bulan Ini)" :value="stats.totalHm.toLocaleString('id-ID')" color="primary" />
                <StatCard label="Rata-rata HM" :value="stats.avgHm.toLocaleString('id-ID')" color="warning" />
                <StatCard label="Operator Aktif" :value="stats.activeOperators.toLocaleString('id-ID')" color="success" />
                <StatCard label="Total Data" :value="stats.totalRecords.toLocaleString('id-ID')" color="danger" />
            </div>

            <div class="mb-4 grid grid-cols-1 gap-4 xl:grid-cols-2">
                <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                    <h6 class="mb-3 text-sm font-bold text-slate-700">Tren Hour Meter Harian</h6>
                    <div class="h-[250px]">
                        <Line v-if="dailyTrend.length" :data="trendChartData" :options="baseOptions" />
                        <p v-else class="text-sm text-slate-400">Belum ada data untuk periode ini.</p>
                    </div>
                </div>
                <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                    <h6 class="mb-3 text-sm font-bold text-slate-700">Tren HM Bulanan (Tahun {{ filters.tahun }})</h6>
                    <div class="h-[250px]">
                        <Bar :data="yearlyChartData" :options="baseOptions" />
                    </div>
                </div>
            </div>

            <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                <h6 class="mb-3 text-sm font-bold text-slate-700">10 Operator dengan HM Tertinggi</h6>
                <div class="h-[300px]">
                    <Bar v-if="topOperators.length" :data="topOperatorsChartData" :options="topOperatorsOptions" />
                    <p v-else class="text-sm text-slate-400">Belum ada data untuk periode ini.</p>
                </div>
            </div>
        </template>

        <template v-else-if="activeTab === 'kehadiran'">
            <div class="mb-4 grid grid-cols-2 gap-3 lg:grid-cols-4">
                <StatCard label="Total Data Kehadiran" :value="attendance.stats.totalRecords.toLocaleString('id-ID')" color="primary" />
                <StatCard label="Hadir" :value="attendance.stats.hadirCount.toLocaleString('id-ID')" color="success" />
                <StatCard label="Non-Hadir" :value="attendance.stats.nonHadirCount.toLocaleString('id-ID')" color="danger" />
                <StatCard label="Karyawan Tercatat" :value="attendance.stats.totalEmployees.toLocaleString('id-ID')" color="warning" />
            </div>

            <div class="mb-4 grid grid-cols-1 gap-4 xl:grid-cols-2">
                <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                    <h6 class="mb-3 text-sm font-bold text-slate-700">Tren Non-Hadir Harian</h6>
                    <div class="h-[250px]">
                        <Line v-if="attendance.dailyTrend.length" :data="attendanceTrendChartData" :options="baseOptions" />
                        <p v-else class="text-sm text-slate-400">Belum ada data untuk periode ini.</p>
                    </div>
                </div>
                <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                    <h6 class="mb-3 text-sm font-bold text-slate-700">10 Karyawan dengan Non-Hadir Terbanyak</h6>
                    <div class="h-[250px]">
                        <Bar v-if="attendance.topAbsent.length" :data="topAbsentChartData" :options="topAbsentOptions" />
                        <p v-else class="text-sm text-slate-400">Belum ada data untuk periode ini.</p>
                    </div>
                </div>
            </div>

            <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                <h6 class="mb-3 text-sm font-bold text-slate-700">Rekap Status Kehadiran</h6>
                <div v-if="attendance.byStatus.length" class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-slate-200 text-left text-xs font-bold uppercase text-slate-500">
                                <th class="py-2">Status</th>
                                <th class="py-2 text-right">Jumlah</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="row in attendance.byStatus" :key="row.label" class="border-b border-slate-100">
                                <td class="py-2 text-slate-700">{{ row.label }}</td>
                                <td class="py-2 text-right font-semibold text-slate-800">{{ row.value.toLocaleString('id-ID') }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <p v-else class="text-sm text-slate-400">Belum ada data untuk periode ini.</p>
            </div>
        </template>

        <template v-else-if="activeTab === 'coaching'">
            <CoachingTab :filters="filters" :coaching="coaching" />
        </template>

        <template v-else>
            <div class="mb-4 grid grid-cols-2 gap-3 lg:grid-cols-4">
                <StatCard label="Total Operator Unfit" :value="deviasi.unfitTotal.toLocaleString('id-ID')" color="danger" />
                <StatCard label="Total Emergency" :value="deviasi.emergencyTotal.toLocaleString('id-ID')" color="warning" />
                <StatCard label="Emergency Bulan Ini" :value="deviasi.emergencyBulanIni.toLocaleString('id-ID')" color="primary" />
                <StatCard label="Lokasi Terdampak" :value="deviasi.unfitByLokasi.length.toLocaleString('id-ID')" color="success" />
            </div>

            <div class="mb-4 grid grid-cols-1 gap-4 xl:grid-cols-2">
                <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                    <h6 class="mb-3 text-sm font-bold text-slate-700">Operator Unfit per Lokasi</h6>
                    <div class="h-[250px]">
                        <Bar v-if="deviasi.unfitByLokasi.length" :data="unfitByLokasiChartData" :options="unfitByLokasiOptions" />
                        <p v-else class="text-sm text-slate-400">Tidak ada operator unfit saat ini.</p>
                    </div>
                </div>
                <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                    <h6 class="mb-3 text-sm font-bold text-slate-700">Tren Emergency 12 Bulan Terakhir</h6>
                    <div class="h-[250px]">
                        <Bar :data="emergencyByMonthChartData" :options="baseOptions" />
                    </div>
                </div>
            </div>

            <div class="mb-4 rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                <h6 class="mb-3 text-sm font-bold text-slate-700">Daftar Operator Unfit</h6>
                <div v-if="deviasi.unfitList.length" class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-slate-200 text-left text-xs font-bold uppercase text-slate-500">
                                <th class="py-2">NRP</th>
                                <th class="py-2">Nama</th>
                                <th class="py-2">Jabatan</th>
                                <th class="py-2">Lokasi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="row in deviasi.unfitList" :key="row.nrp" class="border-b border-slate-100">
                                <td class="py-2 text-slate-700">{{ row.nrp }}</td>
                                <td class="py-2 font-semibold text-slate-800">{{ row.nama }}</td>
                                <td class="py-2 text-slate-600">{{ row.jabatan ?? '-' }}</td>
                                <td class="py-2 text-slate-600">{{ row.lokasi ?? '-' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <p v-else class="text-sm text-slate-400">Tidak ada operator unfit saat ini.</p>
            </div>

            <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                <h6 class="mb-3 text-sm font-bold text-slate-700">Pengajuan Emergency Terbaru</h6>
                <div v-if="deviasi.recentEmergency.length" class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-slate-200 text-left text-xs font-bold uppercase text-slate-500">
                                <th class="py-2">No. Surat</th>
                                <th class="py-2">Nama</th>
                                <th class="py-2">NRP</th>
                                <th class="py-2">Berangkat</th>
                                <th class="py-2">Kembali</th>
                                <th class="py-2">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="row in deviasi.recentEmergency" :key="row.noSurat + row.createdAt" class="border-b border-slate-100">
                                <td class="py-2 text-slate-700">{{ row.noSurat ?? '-' }}</td>
                                <td class="py-2 font-semibold text-slate-800">{{ row.nama ?? '-' }}</td>
                                <td class="py-2 text-slate-600">{{ row.nrp ?? '-' }}</td>
                                <td class="py-2 text-slate-600">{{ row.tglBerangkat ?? '-' }}</td>
                                <td class="py-2 text-slate-600">{{ row.tglKembali ?? '-' }}</td>
                                <td class="py-2 text-slate-600">{{ row.status ?? '-' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <p v-else class="text-sm text-slate-400">Belum ada pengajuan emergency.</p>
            </div>
        </template>
    </AppLayout>
</template>
