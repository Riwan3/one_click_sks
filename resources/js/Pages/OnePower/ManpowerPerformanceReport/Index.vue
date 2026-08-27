<script setup>
import { computed, ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import { BarChart2, SearchX } from '@lucide/vue';
import { Chart as ChartJS, CategoryScale, LinearScale, PointElement, LineElement, BarElement, Title, Tooltip, Legend } from 'chart.js';
import { Bar } from 'vue-chartjs';
import AppLayout from '@/Layouts/AppLayout.vue';
import EmployeeSearchInput from './Partials/EmployeeSearchInput.vue';

ChartJS.register(CategoryScale, LinearScale, PointElement, LineElement, BarElement, Title, Tooltip, Legend);

const props = defineProps({
    user: { type: Object, required: true },
    notifCounts: { type: Object, required: true },
    menu: { type: Array, required: true },
    nrp: { type: String, default: '' },
    monthsBack: { type: Number, required: true },
    employee: { type: Object, default: null },
    notFound: { type: Boolean, default: false },
    rows: { type: Array, default: () => [] },
    summary: { type: Object, default: null },
});

const searchValue = ref(props.nrp);
const monthsBackInput = ref(props.monthsBack);

const statusStyle = {
    GOOD: 'bg-green-100 text-green-700',
    AVERAGE: 'bg-yellow-100 text-yellow-700',
    POOR: 'bg-red-100 text-red-700',
};

const search = (employee) => {
    const nrp = employee?.nrp ?? searchValue.value;
    if (! nrp) return;
    router.get('/one-power/personal-data/mpr', { nrp, months_back: monthsBackInput.value }, { preserveState: true });
};

const chartData = computed(() => ({
    labels: props.rows.map((row) => row.periode),
    datasets: [
        {
            type: 'bar',
            label: 'ATR (%)',
            data: props.rows.map((row) => row.atr ?? 0),
            backgroundColor: '#4472c4',
            yAxisID: 'y',
        },
        {
            type: 'line',
            label: 'HM',
            data: props.rows.map((row) => row.hm),
            borderColor: '#ed7d31',
            backgroundColor: '#ed7d31',
            borderWidth: 2,
            yAxisID: 'y1',
            tension: 0,
        },
    ],
}));

const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: { position: 'bottom' },
    },
    scales: {
        y: { type: 'linear', position: 'left', min: 0, max: 100, ticks: { callback: (v) => `${v}%` } },
        y1: { type: 'linear', position: 'right', min: 0, grid: { drawOnChartArea: false } },
    },
};
</script>

<template>
    <Head title="Manpower Performance Report - OneClick" />

    <AppLayout :user="user" :notif-counts="notifCounts" :menu="menu">
        <h1 class="mb-1 flex items-center gap-2 text-xl font-extrabold text-slate-800"><BarChart2 class="h-5 w-5 text-blue-500" /> Manpower Performance Report</h1>
        <p class="mb-5 text-sm text-slate-500">Rekap ATR%, S-I-A, dan HM per bulan untuk satu karyawan.</p>

        <div class="mb-4 flex flex-wrap items-end gap-2 rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
            <div class="w-full max-w-xs">
                <label class="mb-1 block text-xs font-bold uppercase text-slate-500">NRP / Nama</label>
                <EmployeeSearchInput v-model="searchValue" @select="search" />
            </div>
            <div>
                <label class="mb-1 block text-xs font-bold uppercase text-slate-500">Rentang Bulan</label>
                <select v-model.number="monthsBackInput" class="rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500">
                    <option :value="6">6 Bulan Terakhir</option>
                    <option :value="12">12 Bulan Terakhir</option>
                </select>
            </div>
            <button type="button" class="rounded-lg bg-blue-500 px-4 py-2 text-sm font-bold text-white hover:bg-blue-600" @click="search()">Cari</button>
        </div>

        <div v-if="! nrp" class="flex flex-col items-center justify-center gap-2 rounded-xl border border-dashed border-slate-300 bg-white py-16 text-center text-slate-500">
            <SearchX class="h-8 w-8 text-slate-300" />
            <p class="text-sm font-semibold">Silakan cari menggunakan NRP atau Nama karyawan untuk melihat Manpower Performance Report.</p>
        </div>

        <div v-else-if="notFound" class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-6 text-center text-sm font-semibold text-amber-700">
            Karyawan dengan NRP / Nama tersebut tidak ditemukan.
        </div>

        <template v-else-if="employee">
            <div class="mb-4 grid gap-4 rounded-xl border border-slate-200 bg-white p-4 text-sm shadow-sm sm:grid-cols-3">
                <div><span class="text-xs font-bold uppercase text-slate-400">Nama</span><div class="font-bold text-slate-700">{{ employee.name }}</div></div>
                <div><span class="text-xs font-bold uppercase text-slate-400">NRP</span><div class="font-bold text-slate-700">{{ employee.nrp }}</div></div>
                <div><span class="text-xs font-bold uppercase text-slate-400">Jabatan</span><div class="font-bold text-slate-700">{{ employee.jabatan ?? '-' }}</div></div>
            </div>

            <div class="grid gap-4 lg:grid-cols-5">
                <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white shadow-sm lg:col-span-2">
                    <table class="w-full min-w-[480px] text-left text-sm">
                        <thead>
                            <tr class="border-b border-slate-100 text-[0.7rem] uppercase tracking-wide text-slate-500">
                                <th class="px-3 py-3 font-semibold">Bulan</th>
                                <th class="px-3 py-3 font-semibold">ATR%</th>
                                <th class="px-3 py-3 font-semibold">Sakit</th>
                                <th class="px-3 py-3 font-semibold">Izin</th>
                                <th class="px-3 py-3 font-semibold">Alfa</th>
                                <th class="px-3 py-3 font-semibold">HM Total</th>
                                <th class="px-3 py-3 font-semibold">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="rows.length === 0"><td colspan="7" class="px-4 py-8 text-center text-slate-400">Belum ada data untuk periode ini.</td></tr>
                            <tr v-for="row in rows" :key="row.periode" class="border-b border-slate-50 last:border-0 hover:bg-slate-50/60">
                                <td class="px-3 py-3 align-middle font-semibold text-slate-700">{{ row.periode }}</td>
                                <td class="px-3 py-3 align-middle text-slate-600">{{ row.atr !== null ? `${row.atr.toFixed(2)}%` : '-' }}</td>
                                <td class="px-3 py-3 align-middle text-slate-600">{{ row.sakit }}</td>
                                <td class="px-3 py-3 align-middle text-slate-600">{{ row.izin }}</td>
                                <td class="px-3 py-3 align-middle text-slate-600">{{ row.alfa }}</td>
                                <td class="px-3 py-3 align-middle text-slate-600">{{ row.hm.toFixed(1) }}</td>
                                <td class="px-3 py-3 align-middle">
                                    <span
                                        v-if="row.status"
                                        class="rounded-full px-2 py-1 text-xs font-bold"
                                        :class="statusStyle[row.status]"
                                    >{{ row.status }}</span>
                                    <span v-else class="text-xs text-slate-300">-</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm lg:col-span-3">
                    <h2 class="mb-3 text-center text-sm font-bold uppercase text-slate-600">ATR & HM Actual Karyawan</h2>
                    <div class="relative h-80">
                        <Bar :data="chartData" :options="chartOptions" />
                    </div>
                </div>
            </div>

            <div v-if="summary" class="mt-4 grid gap-4 rounded-xl border border-slate-200 bg-white p-4 shadow-sm sm:grid-cols-3">
                <div class="text-center">
                    <div class="text-xs font-bold uppercase text-slate-400">Rata-rata ATR</div>
                    <div class="text-xl font-extrabold text-slate-700">{{ summary.avgAtr.toFixed(2) }}%</div>
                </div>
                <div class="text-center">
                    <div class="text-xs font-bold uppercase text-slate-400">Total HM</div>
                    <div class="text-xl font-extrabold text-slate-700">{{ summary.totalHm.toFixed(1) }}</div>
                </div>
                <div class="text-center">
                    <div class="text-xs font-bold uppercase text-slate-400">Status</div>
                    <span class="inline-block rounded-full px-3 py-1 text-sm font-bold" :class="statusStyle[summary.status]">{{ summary.status }}</span>
                </div>
            </div>
        </template>
    </AppLayout>
</template>
