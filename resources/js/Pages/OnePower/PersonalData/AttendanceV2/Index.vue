<script setup>
import { ref } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { CalendarCheck2, Search, RefreshCw, X, Trash2, CheckCircle2, Moon, Activity, Calendar, AlertCircle } from 'lucide-vue-next';
import AppLayout from '@/Layouts/AppLayout.vue';
import Pagination from '@/Components/ui/Pagination.vue';

const props = defineProps({
    user: { type: Object, required: true },
    notifCounts: { type: Object, required: true },
    menu: { type: Array, required: true },
    search: { type: String, default: '' },
    dateFrom: { type: String, default: '' },
    dateTo: { type: String, default: '' },
    category: { type: String, default: '' },
    summary: { type: Object, required: true },
    attendanceData: { type: Array, required: true },
    pagination: { type: Object, required: true },
});

const searchInput = ref(props.search);
const dateFromInput = ref(props.dateFrom);
const dateToInput = ref(props.dateTo);
const categoryInput = ref(props.category);

const submitFilter = () => {
    router.get('/one-power/personal-data/attendance-v2', {
        search: searchInput.value,
        date_from: dateFromInput.value,
        date_to: dateToInput.value,
        category: categoryInput.value,
    }, { preserveScroll: true, preserveState: true });
};

const handleDelete = (row) => {
    if (! confirm(`Hapus data Attendance V2 "${row.nrp} - ${row.name}" tanggal ${row.tanggal}?`)) return;
    router.delete(`/one-power/personal-data/attendance-v2/${row.id}`, { preserveScroll: true });
};

const importApiModalOpen = ref(false);
const importApiForm = useForm({
    bulan: new Date().getMonth() + 1,
    tahun: new Date().getFullYear(),
    dept: 'PRO',
});

const submitImportApi = () => {
    importApiForm.post('/one-power/personal-data/attendance-v2/import-api', {
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

const categoryBadgeClass = (siaCat) => {
    switch (siaCat) {
        case 'D': return 'bg-amber-100 text-amber-800 border-amber-300';
        case 'N': return 'bg-indigo-100 text-indigo-800 border-indigo-300';
        case 'S': return 'bg-blue-100 text-blue-800 border-blue-300';
        case 'I': return 'bg-purple-100 text-purple-800 border-purple-300';
        case 'A': return 'bg-rose-100 text-rose-800 border-rose-300';
        default: return 'bg-slate-100 text-slate-700 border-slate-300';
    }
};
</script>

<template>
    <Head title="Attendance V2 - OneClick" />

    <AppLayout :user="user" :notif-counts="notifCounts" :menu="menu">
        <div class="mb-5 flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h1 class="mb-1 text-xl font-extrabold text-slate-800 flex items-center gap-2">
                    <CalendarCheck2 class="h-6 w-6 text-indigo-600" />
                    Attendance V2 (Presensi Detail HO)
                </h1>
                <p class="m-0 text-sm text-slate-500">Monitoring presensi shift, jam in/out, dan statistik SIA (Siang, Malam, Sakit, Izin, Alpa).</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <button type="button" class="flex items-center gap-1.5 rounded-lg bg-indigo-600 px-3 py-2 text-sm font-bold text-white transition hover:bg-indigo-700 shadow-sm" @click="importApiModalOpen = true">
                    <RefreshCw class="h-4 w-4" /> Import dari API HO
                </button>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="mb-5 grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-6">
            <div class="rounded-xl border border-slate-200 bg-white p-3.5 shadow-sm">
                <div class="flex items-center justify-between text-xs font-bold text-amber-600 uppercase">
                    <span>Siang (D)</span>
                    <CheckCircle2 class="h-4 w-4 text-amber-500" />
                </div>
                <div class="mt-1 text-2xl font-black text-slate-800">{{ summary.totalSiang.toLocaleString('id-ID') }}</div>
                <div class="text-[11px] text-slate-400">Shift Siang</div>
            </div>

            <div class="rounded-xl border border-slate-200 bg-white p-3.5 shadow-sm">
                <div class="flex items-center justify-between text-xs font-bold text-indigo-600 uppercase">
                    <span>Malam (N)</span>
                    <Moon class="h-4 w-4 text-indigo-500" />
                </div>
                <div class="mt-1 text-2xl font-black text-slate-800">{{ summary.totalMalam.toLocaleString('id-ID') }}</div>
                <div class="text-[11px] text-slate-400">Shift Malam</div>
            </div>

            <div class="rounded-xl border border-slate-200 bg-white p-3.5 shadow-sm">
                <div class="flex items-center justify-between text-xs font-bold text-blue-600 uppercase">
                    <span>Sakit (S)</span>
                    <Activity class="h-4 w-4 text-blue-500" />
                </div>
                <div class="mt-1 text-2xl font-black text-slate-800">{{ summary.totalSakit.toLocaleString('id-ID') }}</div>
                <div class="text-[11px] text-slate-400">Izin Sakit</div>
            </div>

            <div class="rounded-xl border border-slate-200 bg-white p-3.5 shadow-sm">
                <div class="flex items-center justify-between text-xs font-bold text-purple-600 uppercase">
                    <span>Izin (I)</span>
                    <Calendar class="h-4 w-4 text-purple-500" />
                </div>
                <div class="mt-1 text-2xl font-black text-slate-800">{{ summary.totalIzin.toLocaleString('id-ID') }}</div>
                <div class="text-[11px] text-slate-400">Izin Resmi</div>
            </div>

            <div class="rounded-xl border border-slate-200 bg-white p-3.5 shadow-sm">
                <div class="flex items-center justify-between text-xs font-bold text-rose-600 uppercase">
                    <span>Alpa (A)</span>
                    <AlertCircle class="h-4 w-4 text-rose-500" />
                </div>
                <div class="mt-1 text-2xl font-black text-rose-600">{{ summary.totalAlpa.toLocaleString('id-ID') }}</div>
                <div class="text-[11px] text-slate-400">Mangkir</div>
            </div>

            <div class="rounded-xl bg-gradient-to-br from-indigo-600 to-indigo-700 p-3.5 text-white shadow-sm">
                <div class="text-xs font-bold uppercase tracking-wide opacity-80">ATR Rate</div>
                <div class="mt-1 text-2xl font-black">{{ summary.atrPercentage }}%</div>
                <div class="text-[11px] opacity-80">{{ summary.totalRecords }} Record</div>
            </div>
        </div>

        <!-- Search & Filter Form -->
        <form class="mb-4 flex flex-wrap items-center gap-2" @submit.prevent="submitFilter">
            <div class="relative w-full max-w-xs flex-1">
                <Search class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
                <input v-model="searchInput" type="text" placeholder="Cari NRP, nama, posisi, kode..." class="w-full rounded-lg border border-slate-200 bg-white py-2 pl-9 pr-3 text-sm outline-none focus:border-indigo-500">
            </div>
            <div class="flex items-center gap-1.5">
                <span class="text-xs font-semibold text-slate-500">Kategori</span>
                <select v-model="categoryInput" class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm outline-none focus:border-indigo-500">
                    <option value="">Semua Status</option>
                    <option value="D">D - Siang</option>
                    <option value="N">N - Malam</option>
                    <option value="S">S - Sakit</option>
                    <option value="I">I - Izin</option>
                    <option value="A">A - Alpa</option>
                </select>
            </div>
            <div class="flex items-center gap-1.5">
                <span class="text-xs font-semibold text-slate-500">Dari</span>
                <input v-model="dateFromInput" type="date" class="rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-indigo-500">
            </div>
            <div class="flex items-center gap-1.5">
                <span class="text-xs font-semibold text-slate-500">S/d</span>
                <input v-model="dateToInput" type="date" class="rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-indigo-500">
            </div>
            <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-bold text-white hover:bg-indigo-700 transition">Filter</button>
        </form>

        <!-- Table -->
        <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white shadow-sm">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50 text-xs uppercase text-slate-500 border-b border-slate-200">
                    <tr>
                        <th class="px-4 py-3 font-bold">Karyawan</th>
                        <th class="px-4 py-3 font-bold">Tanggal</th>
                        <th class="px-4 py-3 font-bold text-center">Status / Kode</th>
                        <th class="px-4 py-3 font-bold text-center">Jam In - Out</th>
                        <th class="px-4 py-3 font-bold">Jabatan / Job</th>
                        <th class="px-4 py-3 font-bold">Device / Tap</th>
                        <th class="px-4 py-3 font-bold text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <tr v-for="row in attendanceData" :key="row.id" class="hover:bg-slate-50/80 transition">
                        <td class="px-4 py-3">
                            <div class="font-bold text-slate-800">{{ row.name || '-' }}</div>
                            <div class="text-xs font-mono text-slate-400">NRP: {{ row.nrp }}</div>
                        </td>
                        <td class="px-4 py-3 font-medium text-slate-700 whitespace-nowrap">
                            {{ row.tanggal }}
                        </td>
                        <td class="px-4 py-3 text-center whitespace-nowrap">
                            <span :class="['inline-flex items-center gap-1 rounded-full border px-2.5 py-0.5 text-xs font-bold', categoryBadgeClass(row.siaCategory)]">
                                <span>{{ row.code || row.siaCategory || '-' }}</span>
                                <span v-if="row.siaLabel" class="opacity-75">({{ row.siaLabel }})</span>
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center font-mono text-xs whitespace-nowrap">
                            <span class="text-emerald-600 font-semibold">{{ row.jamIn || '--:--' }}</span>
                            <span class="text-slate-300 mx-1">-</span>
                            <span class="text-rose-600 font-semibold">{{ row.jamOut || '--:--' }}</span>
                        </td>
                        <td class="px-4 py-3">
                            <div class="text-xs font-semibold text-slate-700">{{ row.position || '-' }}</div>
                            <div class="text-[11px] text-slate-400">{{ row.job || row.dept || '-' }}</div>
                        </td>
                        <td class="px-4 py-3">
                            <div class="text-xs text-slate-600">{{ row.device || '-' }}</div>
                            <div v-if="row.deviceId" class="text-[10px] font-mono text-slate-400">ID: {{ row.deviceId }}</div>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <button type="button" class="rounded p-1.5 text-slate-400 hover:bg-rose-50 hover:text-rose-600 transition" title="Hapus" @click="handleDelete(row)">
                                <Trash2 class="h-4 w-4" />
                            </button>
                        </td>
                    </tr>
                    <tr v-if="attendanceData.length === 0">
                        <td colspan="7" class="px-4 py-12 text-center text-slate-400">
                            Tidak ada data Attendance V2 untuk filter ini.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            <Pagination
                :current-page="pagination.currentPage"
                :last-page="pagination.lastPage"
                base-url="/one-power/personal-data/attendance-v2"
                :query="{ search, date_from: dateFrom, date_to: dateTo, category }"
            />
        </div>

        <!-- Modal Import dari API HO -->
        <div v-if="importApiModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 p-4">
            <div class="w-full max-w-md rounded-xl bg-white p-6 shadow-xl">
                <div class="mb-4 flex items-center justify-between">
                    <h3 class="text-lg font-bold text-slate-800">Import Attendance V2 dari API HO</h3>
                    <button type="button" class="text-slate-400 hover:text-slate-600" @click="importApiModalOpen = false">
                        <X class="h-5 w-5" />
                    </button>
                </div>
                <p class="mb-4 text-xs text-slate-500">
                    Mengambil presensi detail dari HO API untuk bulan terpilih dan memperbarui tabel Attendance V2 lokal.
                </p>
                <form class="space-y-4" @submit.prevent="submitImportApi">
                    <div>
                        <label class="mb-1 block text-xs font-bold text-slate-600">Departemen Filter API</label>
                        <input v-model="importApiForm.dept" type="text" placeholder="PRO" class="w-full rounded-lg border border-slate-200 p-2.5 text-sm outline-none focus:border-indigo-500">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-bold text-slate-600">Bulan</label>
                        <select v-model="importApiForm.bulan" class="w-full rounded-lg border border-slate-200 p-2.5 text-sm font-semibold outline-none focus:border-indigo-500">
                            <option v-for="b in bulanList" :key="b.value" :value="b.value">{{ b.label }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-bold text-slate-600">Tahun</label>
                        <input v-model.number="importApiForm.tahun" type="number" min="2000" max="2099" class="w-full rounded-lg border border-slate-200 p-2.5 text-sm outline-none focus:border-indigo-500">
                    </div>
                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" class="rounded-lg border border-slate-200 px-4 py-2 text-sm font-bold text-slate-600 hover:bg-slate-50" @click="importApiModalOpen = false">
                            Batal
                        </button>
                        <button type="submit" :disabled="importApiForm.processing" class="flex items-center gap-1.5 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-bold text-white hover:bg-indigo-700 disabled:opacity-50">
                            <RefreshCw v-if="importApiForm.processing" class="h-4 w-4 animate-spin" />
                            <span>{{ importApiForm.processing ? 'Mengimpor...' : 'Proses Import' }}</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
