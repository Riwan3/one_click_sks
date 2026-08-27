<script setup>
import { computed } from 'vue';
import { Chart as ChartJS, ArcElement, Tooltip, Legend } from 'chart.js';
import { Pie } from 'vue-chartjs';

ChartJS.register(ArcElement, Tooltip, Legend);

const props = defineProps({
    row: { type: Object, required: true },
});

const initials = computed(() => {
    const parts = (props.row.nama ?? '').trim().split(/\s+/).filter(Boolean);
    if (parts.length === 0) return '?';
    return parts.slice(0, 2).map((part) => part[0].toUpperCase()).join('');
});

const rankBadgeClass = computed(() => {
    if (props.row.ranking === 1) return 'bg-amber-400 text-amber-950';
    if (props.row.ranking === 2) return 'bg-slate-300 text-slate-800';
    if (props.row.ranking === 3) return 'bg-orange-300 text-orange-900';
    return 'bg-blue-100 text-blue-700';
});

const pieData = computed(() => ({
    labels: ['Work Day', 'Sakit', 'Ijin', 'Alpa'],
    datasets: [
        {
            data: [props.row.workDay, props.row.sakit, props.row.ijin, props.row.alpa],
            backgroundColor: ['#4472C4', '#ED7D31', '#A5A5A5', '#FFC000'],
            borderWidth: 1,
        },
    ],
}));

const pieOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: { position: 'bottom', labels: { boxWidth: 10, font: { size: 10 } } },
    },
};

const fmt = (value, decimals = 0, suffix = '') => {
    if (value === null || value === undefined || value === '') return '-';
    return Number(value).toLocaleString('id-ID', { minimumFractionDigits: decimals, maximumFractionDigits: decimals }) + suffix;
};

const fmtPercent = (value) => (value === null || value === undefined ? '-' : `${fmt(value, 0)}%`);
</script>

<template>
    <div class="mor-card break-inside-avoid rounded-2xl border-2 border-slate-800 bg-white shadow-sm print:rounded-none print:shadow-none">
        <div class="flex items-center justify-between gap-3 border-b-2 border-slate-800 bg-slate-50 px-4 py-3">
            <div class="flex items-center gap-3">
                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-blue-500 text-base font-extrabold text-white">
                    {{ initials }}
                </div>
                <div>
                    <div class="text-sm font-extrabold uppercase text-slate-800">{{ row.nama }}</div>
                    <div class="text-xs text-slate-500">NRP {{ row.nrp }} &middot; {{ row.jabatan || row.posisi || '-' }}</div>
                    <div class="text-xs text-slate-500">Bapak Asuh: <span class="font-semibold text-slate-700">{{ row.glName || '-' }}</span></div>
                </div>
            </div>
            <div class="flex shrink-0 items-center gap-2">
                <div class="flex flex-col items-center rounded-xl border border-slate-300 px-3 py-1.5">
                    <span class="text-[0.65rem] font-bold uppercase text-slate-400">Ranking</span>
                    <span class="flex h-8 w-8 items-center justify-center rounded-full text-sm font-extrabold" :class="rankBadgeClass">
                        {{ row.ranking ?? '-' }}
                    </span>
                    <span class="text-[0.6rem] text-slate-400">dari {{ row.dariOperator ?? '-' }}</span>
                </div>
                <div class="flex flex-col items-center rounded-xl bg-blue-600 px-4 py-2 text-white">
                    <span class="text-[0.65rem] font-bold uppercase opacity-80">Total Poin</span>
                    <span class="text-2xl font-extrabold leading-none">{{ fmt(row.totalPoin, 1) }}</span>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-0 lg:grid-cols-2">
            <div class="divide-y divide-slate-200 border-slate-800 lg:border-r-2">
                <div class="bg-yellow-50 p-3">
                    <div class="mb-1 text-xs font-extrabold uppercase text-slate-600">ATR</div>
                    <dl class="grid grid-cols-2 gap-x-3 gap-y-0.5 text-xs">
                        <dt class="text-slate-500">ATR</dt><dd class="text-right font-bold text-slate-700">{{ fmtPercent(row.atrPersen) }}</dd>
                        <dt class="text-slate-500">Point ATR</dt><dd class="text-right font-bold text-slate-700">{{ fmt(row.atrPoint) }}</dd>
                        <dt class="text-slate-500">Work Day</dt><dd class="text-right font-bold text-slate-700">{{ row.workDay }}</dd>
                        <dt class="text-slate-500">Sakit</dt><dd class="text-right font-bold text-slate-700">{{ row.sakit }}</dd>
                        <dt class="text-slate-500">Ijin</dt><dd class="text-right font-bold text-slate-700">{{ row.ijin }}</dd>
                        <dt class="text-slate-500">Alpa</dt><dd class="text-right font-bold text-slate-700">{{ row.alpa }}</dd>
                    </dl>
                </div>

                <div class="bg-sky-50 p-3">
                    <div class="mb-1 text-xs font-extrabold uppercase text-slate-600">Timesheet</div>
                    <dl class="grid grid-cols-2 gap-x-3 gap-y-0.5 text-xs">
                        <dt class="text-slate-500">Timesheet</dt><dd class="text-right font-bold text-slate-700">{{ fmtPercent(row.timesheetPersen) }}</dd>
                        <dt class="text-slate-500">Point Timesheet</dt><dd class="text-right font-bold text-slate-700">{{ fmt(row.timesheetPoint) }}</dd>
                    </dl>
                </div>

                <div class="bg-orange-50 p-3">
                    <div class="mb-1 text-xs font-extrabold uppercase text-slate-600">HM</div>
                    <dl class="grid grid-cols-2 gap-x-3 gap-y-0.5 text-xs">
                        <dt class="text-slate-500">HM Ach</dt><dd class="text-right font-bold text-slate-700">{{ fmtPercent(row.hmRatio) }}</dd>
                        <dt class="text-slate-500">Point HM</dt><dd class="text-right font-bold text-slate-700">{{ fmt(row.hmPoint) }}</dd>
                        <dt class="text-slate-500">HM</dt><dd class="text-right font-bold text-slate-700">{{ fmt(row.hmJam) }} Jam</dd>
                        <dt class="text-slate-500">Target HM</dt><dd class="text-right font-bold text-slate-700">{{ fmt(row.hmTarget) }} Jam</dd>
                    </dl>
                </div>

                <div class="bg-slate-100 p-3">
                    <div class="mb-1 text-xs font-extrabold uppercase text-slate-600">Fuel</div>
                    <dl class="grid grid-cols-2 gap-x-3 gap-y-0.5 text-xs">
                        <dt class="text-slate-500">Fuel</dt><dd class="text-right font-bold text-slate-700">{{ fmtPercent(row.fuelPersen) }}</dd>
                        <dt class="text-slate-500">Point Fuel</dt><dd class="text-right font-bold text-slate-700">{{ fmt(row.fuelPoint) }}</dd>
                        <dt class="text-slate-500">Actual</dt><dd class="text-right font-bold text-slate-700">{{ fmt(row.fuelActual) }} l/jam</dd>
                        <dt class="text-slate-500">Limit</dt><dd class="text-right font-bold text-slate-700">{{ fmt(row.fuelLimit) }} l/jam</dd>
                    </dl>
                </div>

                <div v-if="row.isProduksi" class="bg-rose-50 p-3">
                    <div class="mb-1 text-xs font-extrabold uppercase text-slate-600">PDTY</div>
                    <dl class="grid grid-cols-2 gap-x-3 gap-y-0.5 text-xs">
                        <dt class="text-slate-500">PDTY (%)</dt><dd class="text-right font-bold text-slate-700">{{ fmtPercent(row.pdtyPersen) }}</dd>
                        <dt class="text-slate-500">Point PDTY</dt><dd class="text-right font-bold text-slate-700">{{ fmt(row.pdtyPoint) }}</dd>
                        <dt class="text-rose-500">Act. Coal</dt><dd class="text-right font-bold text-slate-700">{{ fmt(row.actCoal) }}</dd>
                        <dt class="text-rose-500">Targt. Coal</dt><dd class="text-right font-bold text-slate-700">{{ fmt(row.targtCoal) }}</dd>
                        <dt class="text-rose-500">Act. OB</dt><dd class="text-right font-bold text-slate-700">{{ fmt(row.actOb) }}</dd>
                        <dt class="text-rose-500">Targt. OB</dt><dd class="text-right font-bold text-slate-700">{{ fmt(row.targtOb) }}</dd>
                    </dl>
                </div>

                <div class="bg-lime-50 p-3">
                    <div class="mb-1 text-xs font-extrabold uppercase text-slate-600">Pembinaan</div>
                    <dl class="grid grid-cols-2 gap-x-3 gap-y-0.5 text-xs">
                        <dt class="text-slate-500">Pembinaan</dt><dd class="text-right font-bold text-slate-700">{{ row.pembinaan }}</dd>
                        <dt class="text-slate-500">Poin Pembinaan</dt><dd class="text-right font-bold text-slate-700">{{ fmt(row.pembinaanPoin) }}</dd>
                        <dt class="text-slate-500">Jml Poin Pembinaan</dt><dd class="text-right font-bold text-slate-700">{{ fmt(row.pembinaan + row.pembinaanPoin) }}</dd>
                        <dt class="text-slate-500">Ket</dt><dd class="text-right font-bold text-slate-700">{{ row.ketPembinaan || '-' }}</dd>
                    </dl>
                </div>
            </div>

            <div class="p-3">
                <div class="mb-1 text-center text-xs font-extrabold uppercase text-slate-600">ATR</div>
                <div class="relative h-44">
                    <Pie :data="pieData" :options="pieOptions" />
                </div>

                <div class="mt-3 overflow-x-auto rounded-lg border border-slate-300">
                    <table class="w-full min-w-[360px] text-center text-[0.65rem]">
                        <thead>
                            <tr class="bg-slate-100 font-bold text-slate-600">
                                <th class="border border-slate-300 px-1 py-1"></th>
                                <th class="border border-slate-300 px-1 py-1 text-blue-600">ATR</th>
                                <th class="border border-slate-300 px-1 py-1 text-blue-600">TS</th>
                                <th class="border border-slate-300 px-1 py-1 text-blue-600">HM</th>
                                <th class="border border-slate-300 px-1 py-1 text-blue-600">Fuel</th>
                                <th class="border border-slate-300 px-1 py-1 text-blue-600">PDTY</th>
                                <th class="border border-slate-300 px-1 py-1 text-blue-600">Pembinaan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="border border-slate-300 px-1 py-1 text-left font-bold">Target</td>
                                <td class="border border-slate-300 px-1 py-1">{{ row.targets.atr }}</td>
                                <td class="border border-slate-300 px-1 py-1">{{ row.targets.timesheet }}</td>
                                <td class="border border-slate-300 px-1 py-1">{{ row.targets.hm }}</td>
                                <td class="border border-slate-300 px-1 py-1">{{ row.targets.fuel }}</td>
                                <td class="border border-slate-300 px-1 py-1">{{ row.isProduksi ? row.targets.pdty : '-' }}</td>
                                <td class="border border-slate-300 px-1 py-1">{{ row.targets.pembinaan }}</td>
                            </tr>
                            <tr>
                                <td class="border border-slate-300 px-1 py-1 text-left font-bold text-orange-500">Achievement</td>
                                <td class="border border-slate-300 px-1 py-1 font-bold">{{ fmt(row.atrPoint, 1) }}</td>
                                <td class="border border-slate-300 px-1 py-1 font-bold">{{ fmt(row.timesheetPoint, 1) }}</td>
                                <td class="border border-slate-300 px-1 py-1 font-bold">{{ fmt(row.hmPoint, 1) }}</td>
                                <td class="border border-slate-300 px-1 py-1 font-bold">{{ fmt(row.fuelPoint, 1) }}</td>
                                <td class="border border-slate-300 px-1 py-1 font-bold">{{ row.isProduksi ? fmt(row.pdtyPoint, 1) : '-' }}</td>
                                <td class="border border-slate-300 px-1 py-1 font-bold">{{ fmt(row.pembinaan + row.pembinaanPoin, 1) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</template>
