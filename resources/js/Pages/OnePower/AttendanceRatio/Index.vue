<script setup>
import { ref, computed } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { PieChart, Settings2, X, Loader2, MessageCircle, UserCheck } from '@lucide/vue';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    user: { type: Object, required: true },
    isPersonalOnly: { type: Boolean, default: false },
    isAdminOrPimpinan: { type: Boolean, default: false },
    notifCounts: { type: Object, required: true },
    menu: { type: Array, required: true },
    search: { type: String, default: '' },
    bulan: { type: Number, required: true },
    tahun: { type: Number, required: true },
    kategori: { type: String, default: '' },
    filterLowAtr: { type: Boolean, default: false },
    threshold: { type: Number, required: true },
    quartalInfo: { type: Object, default: null },
    stats: { type: Object, required: true },
    items: { type: Array, required: true },
    pagination: { type: Object, default: null },
});

const searchInput = ref(props.search);
const bulanInput = ref(props.bulan);
const tahunInput = ref(props.tahun);
const kategoriInput = ref(props.kategori);
const lowAtrInput = ref(props.filterLowAtr);

const bulanList = [
    { value: 1, label: 'Januari' }, { value: 2, label: 'Februari' }, { value: 3, label: 'Maret' },
    { value: 4, label: 'April' }, { value: 5, label: 'Mei' }, { value: 6, label: 'Juni' },
    { value: 7, label: 'Juli' }, { value: 8, label: 'Agustus' }, { value: 9, label: 'September' },
    { value: 10, label: 'Oktober' }, { value: 11, label: 'November' }, { value: 12, label: 'Desember' },
];

const submitFilter = () => {
    router.get('/one-power/personal-data/attendance-ratio', {
        search: props.isPersonalOnly ? '' : searchInput.value,
        bulan: bulanInput.value,
        tahun: tahunInput.value,
        kategori: kategoriInput.value,
        filter_low_atr: lowAtrInput.value ? 1 : 0,
    }, { preserveScroll: true, preserveState: true });
};

const goToPage = (page) => {
    if (!props.pagination || page < 1 || page > props.pagination.lastPage || page === props.pagination.currentPage) return;
    router.get('/one-power/personal-data/attendance-ratio', {
        search: searchInput.value,
        bulan: bulanInput.value,
        tahun: tahunInput.value,
        kategori: kategoriInput.value,
        page,
    }, { preserveScroll: true, preserveState: true });
};

const computedPages = () => {
    if (!props.pagination) return [];
    const current = props.pagination.currentPage;
    const last = props.pagination.lastPage;
    const delta = 2;
    const range = [];
    const rangeWithDots = [];
    let l;

    for (let i = 1; i <= last; i++) {
        if (i === 1 || i === last || (i >= current - delta && i <= current + delta)) {
            range.push(i);
        }
    }
    for (const i of range) {
        if (l) {
            if (i - l === 2) rangeWithDots.push(l + 1);
            else if (i - l !== 1) rangeWithDots.push('...');
        }
        rangeWithDots.push(i);
        l = i;
    }
    return rangeWithDots;
};

const atrColor = (atr) => {
    if (atr >= props.threshold) return 'text-emerald-600';
    if (atr >= props.threshold - 10) return 'text-amber-500';
    return 'text-red-600';
};

const waLink = (row) => {
    let phone = (row.employeePhone || '').replace(/\D/g, '');
    if (phone.startsWith('0')) {
        phone = '62' + phone.slice(1);
    } else if (phone && !phone.startsWith('62')) {
        phone = '62' + phone;
    }
    const text = `Halo *${row.name}* (${row.nrp}),\n\nATR (Attendance Ratio) Anda pada ${props.quartalInfo?.label ?? ''} tercatat *${row.atr}%*, di bawah target minimum ${props.threshold}%. Mohon perhatian dan perbaikan kehadirannya. Terima kasih.`;
    return `https://wa.me/${phone}?text=${encodeURIComponent(text)}`;
};

const thresholdModalOpen = ref(false);
const thresholdForm = useForm({ atr_threshold: props.threshold });

const submitThreshold = () => {
    thresholdForm.post('/one-power/personal-data/attendance-ratio/threshold', {
        preserveScroll: true,
        onSuccess: () => { thresholdModalOpen.value = false; },
    });
};
</script>

<template>
    <Head title="Attendance Ratio - OneClick" />

    <AppLayout :user="user" :notif-counts="notifCounts" :menu="menu">
        <!-- Header -->
        <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="mb-0.5 text-xl sm:text-2xl font-black text-slate-800 tracking-tight">
                    {{ isPersonalOnly ? 'ATR Saya' : 'Attendance Ratio' }}
                </h1>
                <p class="m-0 text-xs sm:text-sm text-slate-500 font-medium">
                    Rasio kehadiran (ATR) dihitung otomatis dari data yang ditarik pada menu Attendance.
                </p>
            </div>

            <div v-if="isAdminOrPimpinan">
                <button
                    type="button"
                    class="flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2.5 text-xs font-black text-white uppercase tracking-wider shadow-sm hover:bg-blue-700 active:scale-95 transition"
                    @click="thresholdModalOpen = true"
                >
                    <Settings2 class="h-4 w-4" />
                    <span>ATUR THRESHOLD ({{ threshold }}%)</span>
                </button>
            </div>
        </div>

        <!-- Filter Card -->
        <div class="mb-4 rounded-2xl border border-slate-200/80 bg-white p-3 shadow-xs">
            <form class="flex flex-col gap-2.5 lg:flex-row lg:items-center" @submit.prevent="submitFilter">
                <div v-if="!isPersonalOnly" class="flex-1">
                    <input
                        v-model="searchInput"
                        type="text"
                        placeholder="Cari NRP atau Nama..."
                        class="w-full rounded-xl border border-slate-200 bg-slate-50/50 px-3.5 py-2 text-xs font-semibold text-slate-700 outline-none focus:border-blue-500 focus:bg-white transition"
                    >
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <select v-model.number="bulanInput" class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-bold text-slate-700 outline-none focus:border-blue-500">
                        <option v-for="b in bulanList" :key="b.value" :value="b.value">{{ b.label }}</option>
                    </select>

                    <input
                        v-model.number="tahunInput"
                        type="number" min="2020" max="2035"
                        class="w-24 rounded-xl border border-slate-200 bg-white px-2.5 py-2 text-xs font-bold text-slate-700 outline-none focus:border-blue-500"
                    >

                    <select
                        v-model="kategoriInput"
                        class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-bold text-slate-700 outline-none focus:border-blue-500"
                    >
                        <option value="">Semua Status</option>
                        <option value="D">D - Shift Siang</option>
                        <option value="N">N - Shift Malam</option>
                        <option value="S">S - Sakit</option>
                        <option value="I">I - Izin</option>
                        <option value="A">A - Alpa</option>
                    </select>

                    <label class="flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-bold text-slate-600 cursor-pointer">
                        <input v-model="lowAtrInput" type="checkbox" class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                        <span>ATR &lt; {{ threshold }}% (Kuartal)</span>
                    </label>

                    <button
                        type="submit"
                        class="rounded-xl bg-blue-600 px-6 py-2 text-xs font-black text-white uppercase tracking-wider shadow-xs hover:bg-blue-700 active:scale-95 transition ml-auto lg:ml-0"
                    >
                        FILTER
                    </button>
                </div>
            </form>
        </div>

        <div v-if="filterLowAtr && quartalInfo" class="mb-4 rounded-xl border border-amber-200 bg-amber-50 px-4 py-2.5 text-xs font-semibold text-amber-700">
            Menampilkan karyawan dengan ATR &lt; {{ threshold }}% pada {{ quartalInfo.label }} {{ quartalInfo.tahun }}.
        </div>

        <!-- Summary Bar -->
        <div class="mb-4 rounded-2xl border border-slate-200/80 bg-white p-4 shadow-xs">
            <div class="grid grid-cols-2 gap-4 text-center sm:grid-cols-6 sm:divide-x sm:divide-slate-100">
                <div class="px-2">
                    <div class="text-[11px] font-black uppercase tracking-wider text-slate-500">SHIFT SIANG</div>
                    <div class="mt-1 text-2xl font-black text-blue-600">{{ (stats.total_d ?? 0).toLocaleString('id-ID') }}</div>
                </div>
                <div class="px-2">
                    <div class="text-[11px] font-black uppercase tracking-wider text-slate-500">SHIFT MALAM</div>
                    <div class="mt-1 text-2xl font-black text-sky-600">{{ (stats.total_n ?? 0).toLocaleString('id-ID') }}</div>
                </div>
                <div class="px-2">
                    <div class="text-[11px] font-black uppercase tracking-wider text-slate-500">SAKIT</div>
                    <div class="mt-1 text-2xl font-black text-amber-500">{{ (stats.total_s ?? 0).toLocaleString('id-ID') }}</div>
                </div>
                <div class="px-2">
                    <div class="text-[11px] font-black uppercase tracking-wider text-slate-500">IZIN</div>
                    <div class="mt-1 text-2xl font-black text-slate-600">{{ (stats.total_i ?? 0).toLocaleString('id-ID') }}</div>
                </div>
                <div class="px-2">
                    <div class="text-[11px] font-black uppercase tracking-wider text-slate-500">ALPA</div>
                    <div class="mt-1 text-2xl font-black text-red-600">{{ (stats.total_a ?? 0).toLocaleString('id-ID') }}</div>
                </div>
                <div class="col-span-2 px-2 sm:col-span-1">
                    <div class="text-[11px] font-black uppercase tracking-wider text-slate-500">AVG ATR</div>
                    <div class="mt-1 text-2xl font-black" :class="atrColor(stats.avg_atr ?? 0)">{{ stats.avg_atr ?? 0 }}%</div>
                </div>
            </div>
        </div>

        <!-- Low-ATR Quarterly Table -->
        <template v-if="filterLowAtr">
            <div class="mb-4 space-y-3 sm:hidden">
                <div v-if="items.length === 0" class="rounded-2xl border border-slate-200/80 bg-white p-8 text-center text-slate-400 text-xs font-medium">
                    Tidak ada karyawan dengan ATR di bawah threshold pada kuartal ini.
                </div>
                <div v-for="row in items" :key="row.nrp" class="rounded-2xl border border-slate-200/80 bg-white p-4 shadow-xs">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-2.5">
                        <div>
                            <div class="text-xs font-bold text-slate-800">{{ row.name }}</div>
                            <div class="text-[11px] text-slate-400">{{ row.nrp }} &middot; {{ row.jabatan }}</div>
                        </div>
                        <span class="text-lg font-black" :class="atrColor(row.atr)">{{ row.atr }}%</span>
                    </div>
                    <div class="mt-2.5 grid grid-cols-4 gap-2 text-center text-[11px]">
                        <div><div class="text-slate-400">Hadir</div><div class="font-bold text-slate-700">{{ row.hadir }}</div></div>
                        <div><div class="text-slate-400">Sakit</div><div class="font-bold text-slate-700">{{ row.sakit }}</div></div>
                        <div><div class="text-slate-400">Izin</div><div class="font-bold text-slate-700">{{ row.izin }}</div></div>
                        <div><div class="text-slate-400">Alpa</div><div class="font-bold text-slate-700">{{ row.alpa }}</div></div>
                    </div>
                    <a v-if="row.employeePhone" :href="waLink(row)" target="_blank" class="mt-3 flex items-center justify-center gap-2 rounded-xl bg-emerald-50 py-2 text-xs font-bold text-emerald-700 hover:bg-emerald-100">
                        <MessageCircle class="h-3.5 w-3.5" /> Ingatkan via WhatsApp
                    </a>
                </div>
            </div>

            <div class="hidden sm:block overflow-x-auto rounded-2xl border border-slate-200/80 bg-white shadow-xs">
                <table class="w-full text-left text-xs whitespace-nowrap">
                    <thead>
                        <tr class="border-b border-slate-200/80 bg-slate-50/70 text-[11px] font-black uppercase tracking-wider text-slate-500">
                            <th class="px-4 py-3">NRP</th>
                            <th class="px-4 py-3">NAMA</th>
                            <th class="px-3 py-3">GROUP LEADER</th>
                            <th class="px-3 py-3 text-center">HADIR</th>
                            <th class="px-3 py-3 text-center">SAKIT</th>
                            <th class="px-3 py-3 text-center">IZIN</th>
                            <th class="px-3 py-3 text-center">ALPA</th>
                            <th class="px-3 py-3 text-center">TRAINING</th>
                            <th class="px-3 py-3 text-center">ATR</th>
                            <th class="px-3 py-3 text-center">AKSI</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr v-if="items.length === 0">
                            <td colspan="10" class="px-4 py-12 text-center text-slate-400 font-medium">
                                Tidak ada karyawan dengan ATR di bawah threshold pada kuartal ini.
                            </td>
                        </tr>
                        <tr v-for="row in items" :key="row.nrp" class="hover:bg-slate-50/60 transition">
                            <td class="px-4 py-3 align-middle font-bold text-blue-600">{{ row.nrp }}</td>
                            <td class="px-4 py-3 align-middle font-bold uppercase text-slate-800">{{ row.name }}</td>
                            <td class="px-3 py-3 align-middle uppercase text-slate-600">{{ row.glName }}</td>
                            <td class="px-3 py-3 align-middle text-center font-semibold text-slate-700">{{ row.hadir }}</td>
                            <td class="px-3 py-3 align-middle text-center font-semibold text-slate-700">{{ row.sakit }}</td>
                            <td class="px-3 py-3 align-middle text-center font-semibold text-slate-700">{{ row.izin }}</td>
                            <td class="px-3 py-3 align-middle text-center font-semibold text-slate-700">{{ row.alpa }}</td>
                            <td class="px-3 py-3 align-middle text-center font-semibold text-slate-700">{{ row.training }}</td>
                            <td class="px-3 py-3 align-middle text-center font-black" :class="atrColor(row.atr)">{{ row.atr }}%</td>
                            <td class="px-3 py-3 align-middle text-center">
                                <a v-if="row.employeePhone" :href="waLink(row)" target="_blank" class="inline-flex items-center gap-1 rounded-lg bg-emerald-50 px-2.5 py-1.5 text-[11px] font-bold text-emerald-700 hover:bg-emerald-100">
                                    <MessageCircle class="h-3.5 w-3.5" /> WA
                                </a>
                                <span v-else class="text-slate-300">-</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </template>

        <!-- Monthly Table -->
        <template v-else>
            <div class="mb-4 space-y-3 sm:hidden">
                <div v-if="items.length === 0" class="rounded-2xl border border-slate-200/80 bg-white p-8 text-center text-slate-400 text-xs font-medium">
                    Tidak ada data ATR untuk filter ini.
                </div>
                <div v-for="row in items" :key="row.nrp" class="rounded-2xl border border-slate-200/80 bg-white p-4 shadow-xs">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-2.5">
                        <div class="flex items-center gap-2">
                            <UserCheck class="h-4 w-4 text-blue-600" />
                            <span class="text-xs font-bold text-slate-800">{{ row.name }} ({{ row.nrp }})</span>
                        </div>
                        <span class="text-lg font-black" :class="atrColor(row.atr)">{{ row.atr }}%</span>
                    </div>
                    <div class="mt-2.5 grid grid-cols-4 gap-2 text-center text-[11px]">
                        <div><div class="text-slate-400">D</div><div class="font-bold text-slate-700">{{ row.d }}</div></div>
                        <div><div class="text-slate-400">N</div><div class="font-bold text-slate-700">{{ row.n }}</div></div>
                        <div><div class="text-slate-400">S/I</div><div class="font-bold text-slate-700">{{ row.s }}/{{ row.i }}</div></div>
                        <div><div class="text-slate-400">A</div><div class="font-bold text-slate-700">{{ row.a }}</div></div>
                    </div>
                    <div class="mt-2 text-[11px] text-slate-500">
                        <span class="text-slate-400">Dept:</span> <span class="font-semibold text-slate-700">{{ row.dept }}</span>
                        &middot; <span class="text-slate-400">Jabatan:</span> <span class="font-semibold text-slate-700">{{ row.jabatan }}</span>
                    </div>
                </div>
            </div>

            <div class="hidden sm:block overflow-x-auto rounded-2xl border border-slate-200/80 bg-white shadow-xs">
                <table class="w-full text-left text-xs whitespace-nowrap">
                    <thead>
                        <tr class="border-b border-slate-200/80 bg-slate-50/70 text-[11px] font-black uppercase tracking-wider text-slate-500">
                            <th v-if="!isPersonalOnly" class="px-4 py-3">NRP</th>
                            <th v-if="!isPersonalOnly" class="px-4 py-3">NAMA</th>
                            <th v-if="!isPersonalOnly" class="px-3 py-3">GROUP LEADER</th>
                            <th class="px-3 py-3">DEPT</th>
                            <th class="px-3 py-3">JABATAN</th>
                            <th class="px-3 py-3 text-center">ATR</th>
                            <th class="px-3 py-3 text-center">D</th>
                            <th class="px-3 py-3 text-center">N</th>
                            <th class="px-3 py-3 text-center">S</th>
                            <th class="px-3 py-3 text-center">I</th>
                            <th class="px-3 py-3 text-center">A</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr v-if="items.length === 0">
                            <td :colspan="isPersonalOnly ? 8 : 11" class="px-4 py-12 text-center text-slate-400 font-medium">
                                Tidak ada data ATR untuk filter ini.
                            </td>
                        </tr>
                        <tr v-for="row in items" :key="row.nrp" class="hover:bg-slate-50/60 transition">
                            <td v-if="!isPersonalOnly" class="px-4 py-3 align-middle font-bold text-blue-600">{{ row.nrp }}</td>
                            <td v-if="!isPersonalOnly" class="px-4 py-3 align-middle font-bold uppercase text-slate-800">{{ row.name }}</td>
                            <td v-if="!isPersonalOnly" class="px-3 py-3 align-middle uppercase text-slate-600">{{ row.glName }}</td>
                            <td class="px-3 py-3 align-middle uppercase font-semibold text-slate-600">{{ row.dept }}</td>
                            <td class="px-3 py-3 align-middle uppercase text-slate-600">{{ row.jabatan }}</td>
                            <td class="px-3 py-3 align-middle text-center font-black" :class="atrColor(row.atr)">{{ row.atr }}%</td>
                            <td class="px-3 py-3 align-middle text-center font-semibold text-slate-700">{{ row.d }}</td>
                            <td class="px-3 py-3 align-middle text-center font-semibold text-slate-700">{{ row.n }}</td>
                            <td class="px-3 py-3 align-middle text-center font-semibold text-slate-700">{{ row.s }}</td>
                            <td class="px-3 py-3 align-middle text-center font-semibold text-slate-700">{{ row.i }}</td>
                            <td class="px-3 py-3 align-middle text-center font-semibold text-slate-700">{{ row.a }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div v-if="pagination" class="mt-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between text-xs text-slate-600">
                <div>
                    Showing
                    <strong class="text-slate-800">{{ pagination.from }}</strong>
                    to
                    <strong class="text-slate-800">{{ pagination.to }}</strong>
                    of
                    <strong class="text-slate-800">{{ (pagination.total ?? 0).toLocaleString('id-ID') }}</strong>
                    entries
                </div>

                <div v-if="pagination.lastPage > 1" class="flex items-center gap-1 overflow-x-auto">
                    <button
                        type="button"
                        class="rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-600 hover:bg-slate-50 disabled:opacity-40"
                        :disabled="pagination.currentPage <= 1"
                        @click="goToPage(pagination.currentPage - 1)"
                    >
                        Previous
                    </button>

                    <template v-for="(p, idx) in computedPages()" :key="idx">
                        <span v-if="p === '...'" class="px-2 text-slate-400">...</span>
                        <button
                            v-else
                            type="button"
                            class="h-8 min-w-8 rounded-lg px-2 text-xs font-bold transition"
                            :class="[p === pagination.currentPage ? 'bg-blue-600 text-white shadow-xs' : 'border border-slate-200 bg-white text-slate-700 hover:bg-slate-50']"
                            @click="goToPage(p)"
                        >
                            {{ p }}
                        </button>
                    </template>

                    <button
                        type="button"
                        class="rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-600 hover:bg-slate-50 disabled:opacity-40"
                        :disabled="pagination.currentPage >= pagination.lastPage"
                        @click="goToPage(pagination.currentPage + 1)"
                    >
                        Next
                    </button>
                </div>
            </div>
        </template>

        <!-- Threshold Modal -->
        <div
            v-if="thresholdModalOpen"
            class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 p-4 backdrop-blur-xs"
            @click.self="thresholdModalOpen = false"
        >
            <div class="w-full max-w-sm rounded-2xl bg-white p-6 shadow-2xl">
                <div class="mb-4 flex items-center justify-between border-b border-slate-100 pb-3">
                    <h3 class="text-base font-extrabold text-slate-800 flex items-center gap-2">
                        <PieChart class="h-5 w-5 text-blue-600" />
                        Atur Threshold ATR
                    </h3>
                    <button type="button" class="text-slate-400 hover:text-slate-600" @click="thresholdModalOpen = false">
                        <X class="h-5 w-5" />
                    </button>
                </div>

                <form class="space-y-4" @submit.prevent="submitThreshold">
                    <div>
                        <label class="mb-1 block text-xs font-bold text-slate-600">Minimum ATR (%)</label>
                        <input
                            v-model.number="thresholdForm.atr_threshold"
                            type="number" min="0" max="100" step="0.1"
                            class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs font-bold text-slate-700 outline-none focus:border-blue-500"
                        >
                        <p v-if="thresholdForm.errors.atr_threshold" class="mt-1 text-[11px] text-red-600">{{ thresholdForm.errors.atr_threshold }}</p>
                    </div>

                    <div class="mt-6 flex justify-end gap-2 border-t border-slate-100 pt-4">
                        <button type="button" class="rounded-xl border border-slate-200 px-4 py-2 text-xs font-bold text-slate-600 hover:bg-slate-50" @click="thresholdModalOpen = false">
                            Batal
                        </button>
                        <button
                            type="submit"
                            class="flex items-center gap-2 rounded-xl bg-blue-600 px-4 py-2 text-xs font-bold text-white hover:bg-blue-700 disabled:opacity-50"
                            :disabled="thresholdForm.processing"
                        >
                            <Loader2 v-if="thresholdForm.processing" class="h-4 w-4 animate-spin" />
                            <span>Simpan</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
