<script setup>
import { ref, computed } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { CloudDownload, RefreshCw, X, Loader2, Calendar, Clock, Briefcase, MapPin } from '@lucide/vue';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    user: { type: Object, required: true },
    isPersonalOnly: { type: Boolean, default: false },
    notifCounts: { type: Object, required: true },
    menu: { type: Array, required: true },
    search: { type: String, default: '' },
    dateFrom: { type: String, default: '' },
    dateTo: { type: String, default: '' },
    category: { type: String, default: '' },
    summary: { type: Object, required: true },
    items: { type: Array, required: true },
    pagination: { type: Object, required: true },
    lastSync: { type: String, default: '' },
});

const isOperator = computed(() => {
    const role = (props.user?.role || '').toLowerCase();
    const jabatan = (props.user?.employee?.jabatan_ss6 || props.user?.jabatan || '').toLowerCase();
    return role.includes('operator') || jabatan.includes('operator');
});

const isGroupLeader = computed(() => {
    const role = (props.user?.role || '').toLowerCase();
    const jabatan = (props.user?.employee?.jabatan_ss6 || props.user?.jabatan || '').toLowerCase();
    return (
        role === 'group-leader' ||
        role === 'gl' ||
        role.includes('group-leader') ||
        role.includes('group leader') ||
        role.includes('gl') ||
        jabatan.includes('group leader') ||
        jabatan.includes('gl')
    );
});

const isPersonal = computed(() => props.isPersonalOnly || isOperator.value || isGroupLeader.value);

const searchInput = ref(props.search);
const dateFromInput = ref(props.dateFrom);
const dateToInput = ref(props.dateTo);
const categoryInput = ref(props.category);

const getStatusBadge = (code, siaCat) => {
    const cat = (siaCat || '').toUpperCase();
    if (cat === 'D') return { label: 'Shift Siang (D)', bg: 'bg-blue-50 text-blue-700 border-blue-200' };
    if (cat === 'N') return { label: 'Shift Malam (N)', bg: 'bg-sky-50 text-sky-700 border-sky-200' };
    if (cat === 'S') return { label: 'Sakit (S)', bg: 'bg-amber-50 text-amber-700 border-amber-200' };
    if (cat === 'I') return { label: 'Izin (I)', bg: 'bg-slate-100 text-slate-700 border-slate-200' };
    if (cat === 'A') return { label: 'Alpa (A)', bg: 'bg-red-50 text-red-700 border-red-200' };
    return { label: code || '-', bg: 'bg-slate-50 text-slate-600 border-slate-200' };
};

const submitFilter = () => {
    router.get('/one-power/personal-data/attendance', {
        search: isPersonal.value ? '' : searchInput.value,
        date_from: dateFromInput.value,
        date_to: dateToInput.value,
        category: categoryInput.value,
    }, { preserveScroll: true, preserveState: true });
};

const goToPage = (page) => {
    if (page < 1 || page > props.pagination.lastPage || page === props.pagination.currentPage) return;
    router.get('/one-power/personal-data/attendance', {
        search: searchInput.value,
        date_from: dateFromInput.value,
        date_to: dateToInput.value,
        category: categoryInput.value,
        page: page,
    }, { preserveScroll: true, preserveState: true });
};

const importApiModalOpen = ref(false);
const importApiForm = useForm({
    bulan: new Date().getMonth() + 1,
    tahun: new Date().getFullYear(),
    dept: 'PRO',
});

const submitImportApi = () => {
    importApiForm.post('/one-power/personal-data/attendance/import-api', {
        preserveScroll: true,
        onSuccess: () => {
            importApiModalOpen.value = false;
        },
    });
};

const bulanList = [
    { value: 1, label: 'Januari' },
    { value: 2, label: 'Februari' },
    { value: 3, label: 'Maret' },
    { value: 4, label: 'April' },
    { value: 5, label: 'Mei' },
    { value: 6, label: 'Juni' },
    { value: 7, label: 'Juli' },
    { value: 8, label: 'Agustus' },
    { value: 9, label: 'September' },
    { value: 10, label: 'Oktober' },
    { value: 11, label: 'November' },
    { value: 12, label: 'Desember' },
];

const computedPages = () => {
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
            if (i - l === 2) {
                rangeWithDots.push(l + 1);
            } else if (i - l !== 1) {
                rangeWithDots.push('...');
            }
        }
        rangeWithDots.push(i);
        l = i;
    }

    return rangeWithDots;
};
</script>

<template>
    <Head :title="isPersonal ? 'Absensi Saya - OneClick' : 'Attendance - OneClick'" />

    <AppLayout :user="user" :notif-counts="notifCounts" :menu="menu">
        <!-- Header -->
        <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="mb-0.5 text-xl sm:text-2xl font-black text-slate-800 tracking-tight">
                    {{ isPersonal ? 'Absensi Saya' : 'Attendance' }}
                </h1>
                <p class="m-0 text-xs sm:text-sm text-slate-500 font-medium">
                    {{ isPersonal ? 'Riwayat dan rekapitulasi absensi harian Anda.' : 'Data absensi detail (per NRP per hari) tersimpan di database.' }}
                </p>
                <div v-if="lastSync" class="mt-1 flex items-center gap-1.5 text-xs text-slate-400 font-medium">
                    <RefreshCw class="h-3 w-3 inline text-slate-400" />
                    <span>Terakhir sync otomatis: {{ lastSync }}</span>
                </div>
            </div>

            <div v-if="!isPersonal">
                <button
                    type="button"
                    class="flex items-center gap-2 rounded-lg bg-[#107c41] px-4 py-2.5 text-xs font-black text-white uppercase tracking-wider shadow-sm hover:bg-[#0e6b37] active:scale-95 transition"
                    @click="importApiModalOpen = true"
                >
                    <CloudDownload class="h-4 w-4" />
                    <span>IMPORT DARI API</span>
                </button>
            </div>
        </div>

        <!-- Filter Card -->
        <div class="mb-4 rounded-2xl border border-slate-200/80 bg-white p-3 shadow-xs">
            <form class="flex flex-col gap-2.5 lg:flex-row lg:items-center" @submit.prevent="submitFilter">
                <!-- Search Input (Only for Non-Personal View) -->
                <div v-if="!isPersonal" class="flex-1">
                    <input
                        v-model="searchInput"
                        type="text"
                        placeholder="Cari NRP atau Nama..."
                        class="w-full rounded-xl border border-slate-200 bg-slate-50/50 px-3.5 py-2 text-xs font-semibold text-slate-700 outline-none focus:border-blue-500 focus:bg-white transition"
                    >
                </div>

                <!-- Date Range & Status Controls -->
                <div class="flex flex-wrap items-center gap-2" :class="{ 'w-full justify-between sm:justify-start': isPersonal }">
                    <div class="flex items-center gap-1.5">
                        <span class="text-xs font-bold text-slate-600">Dari</span>
                        <input
                            v-model="dateFromInput"
                            type="date"
                            class="rounded-xl border border-slate-200 bg-white px-2.5 py-2 text-xs font-bold text-slate-700 outline-none focus:border-blue-500"
                        >
                    </div>

                    <div class="flex items-center gap-1.5">
                        <span class="text-xs font-bold text-slate-600">S/d</span>
                        <input
                            v-model="dateToInput"
                            type="date"
                            class="rounded-xl border border-slate-200 bg-white px-2.5 py-2 text-xs font-bold text-slate-700 outline-none focus:border-blue-500"
                        >
                    </div>

                    <select
                        v-model="categoryInput"
                        class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-bold text-slate-700 outline-none focus:border-blue-500"
                    >
                        <option value="">Semua Status</option>
                        <option value="D">D - Shift Siang</option>
                        <option value="N">N - Shift Malam</option>
                        <option value="S">S - Sakit</option>
                        <option value="I">I - Izin</option>
                        <option value="A">A - Alpa</option>
                    </select>

                    <button
                        type="submit"
                        class="rounded-xl bg-blue-600 px-6 py-2 text-xs font-black text-white uppercase tracking-wider shadow-xs hover:bg-blue-700 active:scale-95 transition ml-auto lg:ml-0"
                    >
                        FILTER
                    </button>
                </div>
            </form>
        </div>

        <!-- Summary Bar (5 Stat Columns in Unified White Card) -->
        <div class="mb-4 rounded-2xl border border-slate-200/80 bg-white p-4 shadow-xs">
            <div class="grid grid-cols-2 gap-4 text-center sm:grid-cols-5 sm:divide-x sm:divide-slate-100">
                <div class="px-2">
                    <div class="text-[11px] font-black uppercase tracking-wider text-slate-500">SHIFT SIANG</div>
                    <div class="mt-1 text-2xl font-black text-blue-600">
                        {{ (summary.totalSiang ?? 0).toLocaleString('id-ID') }}
                    </div>
                </div>

                <div class="px-2">
                    <div class="text-[11px] font-black uppercase tracking-wider text-slate-500">SHIFT MALAM</div>
                    <div class="mt-1 text-2xl font-black text-sky-600">
                        {{ (summary.totalMalam ?? 0).toLocaleString('id-ID') }}
                    </div>
                </div>

                <div class="px-2">
                    <div class="text-[11px] font-black uppercase tracking-wider text-slate-500">SAKIT</div>
                    <div class="mt-1 text-2xl font-black text-amber-500">
                        {{ (summary.totalSakit ?? 0).toLocaleString('id-ID') }}
                    </div>
                </div>

                <div class="px-2">
                    <div class="text-[11px] font-black uppercase tracking-wider text-slate-500">IZIN</div>
                    <div class="mt-1 text-2xl font-black text-slate-600">
                        {{ (summary.totalIzin ?? 0).toLocaleString('id-ID') }}
                    </div>
                </div>

                <div class="col-span-2 px-2 sm:col-span-1">
                    <div class="text-[11px] font-black uppercase tracking-wider text-slate-500">ALPA</div>
                    <div class="mt-1 text-2xl font-black text-red-600">
                        {{ (summary.totalAlpa ?? 0).toLocaleString('id-ID') }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Mobile Card List (sm:hidden) -->
        <div class="mb-4 space-y-3 sm:hidden">
            <div v-if="items.length === 0" class="rounded-2xl border border-slate-200/80 bg-white p-8 text-center text-slate-400 text-xs font-medium">
                Tidak ada data absensi untuk filter ini.
            </div>

            <div
                v-for="row in items"
                :key="row.id"
                class="rounded-2xl border border-slate-200/80 bg-white p-4 shadow-xs"
            >
                <div class="flex items-center justify-between border-b border-slate-100 pb-2.5">
                    <div class="flex items-center gap-2">
                        <Calendar class="h-4 w-4 text-blue-600" />
                        <span class="text-xs font-bold text-slate-800">{{ row.tanggal }}</span>
                    </div>
                    <span
                        class="rounded-lg border px-2.5 py-1 text-[10px] font-black uppercase tracking-wider"
                        :class="getStatusBadge(row.code, row.siaCategory).bg"
                    >
                        {{ getStatusBadge(row.code, row.siaCategory).label }}
                    </span>
                </div>

                <div class="mt-3 grid grid-cols-2 gap-2 text-xs">
                    <div class="rounded-xl bg-slate-50 p-2.5">
                        <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Jam Masuk (IN)</div>
                        <div class="mt-1 flex items-center gap-1.5 font-mono font-bold text-emerald-600">
                            <Clock class="h-3.5 w-3.5" />
                            <span>{{ row.jamIn }}</span>
                        </div>
                    </div>

                    <div class="rounded-xl bg-slate-50 p-2.5">
                        <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Jam Keluar (OUT)</div>
                        <div class="mt-1 flex items-center gap-1.5 font-mono font-bold text-rose-600">
                            <Clock class="h-3.5 w-3.5" />
                            <span>{{ row.jamOut }}</span>
                        </div>
                    </div>
                </div>

                <div class="mt-2.5 space-y-1 text-[11px] text-slate-500">
                    <div v-if="!isPersonal" class="flex items-center justify-between">
                        <span class="text-slate-400">Karyawan:</span>
                        <span class="font-bold text-slate-700">{{ row.name }} ({{ row.nrp }})</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-400">Jabatan:</span>
                        <span class="font-semibold text-slate-700">{{ row.job || row.position }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-400">Departemen:</span>
                        <span class="font-semibold text-slate-700">{{ row.dept }} - {{ row.company }}</span>
                    </div>
                    <div v-if="row.device && row.device !== '-'" class="flex items-center justify-between">
                        <span class="text-slate-400">Perangkat:</span>
                        <span class="font-mono text-[10px] text-slate-600">{{ row.device }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Data Table (Exact Columns Matching Image 1 for Desktop) -->
        <div class="hidden sm:block overflow-x-auto rounded-2xl border border-slate-200/80 bg-white shadow-xs">
            <table class="w-full text-left text-xs whitespace-nowrap">
                <thead>
                    <tr class="border-b border-slate-200/80 bg-slate-50/70 text-[11px] font-black uppercase tracking-wider text-slate-500">
                        <th class="px-4 py-3">NRP</th>
                        <th class="px-4 py-3">NAME</th>
                        <th class="px-4 py-3">POSITION</th>
                        <th class="px-4 py-3">DATE</th>
                        <th class="px-3 py-3 text-center">IN</th>
                        <th class="px-3 py-3 text-center">OUT</th>
                        <th class="px-3 py-3">JOB</th>
                        <th class="px-3 py-3">DEPT</th>
                        <th class="px-3 py-3">COMPANY</th>
                        <th class="px-3 py-3 text-center">CODE</th>
                        <th class="px-3 py-3">DEVICE</th>
                        <th class="px-3 py-3">OWNER</th>
                        <th class="px-3 py-3 text-center">DEVICE COUNT</th>
                        <th class="px-3 py-3 text-center">DEVICE ID</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <tr v-if="items.length === 0">
                        <td colspan="14" class="px-4 py-12 text-center text-slate-400 font-medium">
                            Tidak ada data absensi untuk filter ini.
                        </td>
                    </tr>
                    <tr
                        v-for="row in items"
                        :key="row.id"
                        class="hover:bg-slate-50/60 transition"
                    >
                        <td class="px-4 py-3 align-middle font-bold text-blue-600">
                            <div class="flex items-center gap-1.5">
                                <span class="h-2 w-2 shrink-0 rounded-full bg-emerald-500 inline-block"></span>
                                <span>{{ row.nrp }}</span>
                            </div>
                        </td>
                        <td class="px-4 py-3 align-middle font-bold uppercase text-slate-800">
                            {{ row.name }}
                        </td>
                        <td class="px-4 py-3 align-middle font-semibold uppercase text-slate-600 text-[11px]">
                            {{ row.position }}
                        </td>
                        <td class="px-4 py-3 align-middle font-medium text-slate-600">
                            {{ row.tanggal }}
                        </td>
                        <td class="px-3 py-3 align-middle text-center font-mono text-[11px] text-slate-700">
                            {{ row.jamIn }}
                        </td>
                        <td class="px-3 py-3 align-middle text-center font-mono text-[11px] text-slate-700">
                            {{ row.jamOut }}
                        </td>
                        <td class="px-3 py-3 align-middle uppercase text-slate-600">
                            {{ row.job }}
                        </td>
                        <td class="px-3 py-3 align-middle uppercase font-semibold text-slate-600">
                            {{ row.dept }}
                        </td>
                        <td class="px-3 py-3 align-middle uppercase font-semibold text-slate-600">
                            {{ row.company }}
                        </td>
                        <td class="px-3 py-3 align-middle text-center uppercase font-bold text-slate-700">
                            {{ row.code }}
                        </td>
                        <td class="px-3 py-3 align-middle font-mono text-[11px] text-slate-600">
                            {{ row.device }}
                        </td>
                        <td class="px-3 py-3 align-middle font-mono text-[11px] text-slate-600">
                            {{ row.owner }}
                        </td>
                        <td class="px-3 py-3 align-middle text-center font-semibold text-slate-600">
                            {{ row.deviceCount }}
                        </td>
                        <td class="px-3 py-3 align-middle text-center text-slate-400">
                            {{ row.deviceId }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Pagination & Entries Summary -->
        <div class="mt-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between text-xs text-slate-600">
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
                        :class="[
                            p === pagination.currentPage
                                ? 'bg-blue-600 text-white shadow-xs'
                                : 'border border-slate-200 bg-white text-slate-700 hover:bg-slate-50'
                        ]"
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

        <!-- Import Modal -->
        <div
            v-if="importApiModalOpen"
            class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 p-4 backdrop-blur-xs"
            @click.self="importApiModalOpen = false"
        >
            <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl">
                <div class="mb-4 flex items-center justify-between border-b border-slate-100 pb-3">
                    <h3 class="text-base font-extrabold text-slate-800 flex items-center gap-2">
                        <CloudDownload class="h-5 w-5 text-emerald-600" />
                        Import Data dari API HO
                    </h3>
                    <button type="button" class="text-slate-400 hover:text-slate-600" @click="importApiModalOpen = false">
                        <X class="h-5 w-5" />
                    </button>
                </div>

                <form class="space-y-4" @submit.prevent="submitImportApi">
                    <div>
                        <label class="mb-1 block text-xs font-bold text-slate-600">Bulan</label>
                        <select v-model.number="importApiForm.bulan" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs font-bold text-slate-700 outline-none focus:border-blue-500">
                            <option v-for="b in bulanList" :key="b.value" :value="b.value">{{ b.label }}</option>
                        </select>
                    </div>

                    <div>
                        <label class="mb-1 block text-xs font-bold text-slate-600">Tahun</label>
                        <input v-model.number="importApiForm.tahun" type="number" min="2020" max="2035" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs font-bold text-slate-700 outline-none focus:border-blue-500">
                    </div>

                    <div>
                        <label class="mb-1 block text-xs font-bold text-slate-600">Departemen</label>
                        <input v-model="importApiForm.dept" type="text" placeholder="PRO" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs font-bold text-slate-700 outline-none focus:border-blue-500">
                    </div>

                    <div class="mt-6 flex justify-end gap-2 border-t border-slate-100 pt-4">
                        <button type="button" class="rounded-xl border border-slate-200 px-4 py-2 text-xs font-bold text-slate-600 hover:bg-slate-50" @click="importApiModalOpen = false">
                            Batal
                        </button>
                        <button
                            type="submit"
                            class="flex items-center gap-2 rounded-xl bg-emerald-600 px-4 py-2 text-xs font-bold text-white hover:bg-emerald-700 disabled:opacity-50"
                            :disabled="importApiForm.processing"
                        >
                            <Loader2 v-if="importApiForm.processing" class="h-4 w-4 animate-spin" />
                            <span>{{ importApiForm.processing ? 'Mengimport...' : 'Mulai Import' }}</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
