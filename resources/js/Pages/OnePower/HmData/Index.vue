<script setup>
import { ref } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { Gauge, CloudDownload, Search, RefreshCw, X } from 'lucide-vue-next';
import AppLayout from '@/Layouts/AppLayout.vue';
import Pagination from '@/Components/ui/Pagination.vue';
import HmDataTable from './Partials/HmDataTable.vue';
import HmDataFormModal from './Partials/HmDataFormModal.vue';
import HmDataImportModal from './Partials/HmDataImportModal.vue';

const props = defineProps({
    user: { type: Object, required: true },
    notifCounts: { type: Object, required: true },
    menu: { type: Array, required: true },
    search: { type: String, default: '' },
    dateFrom: { type: String, default: '' },
    dateTo: { type: String, default: '' },
    summary: { type: Object, required: true },
    hmData: { type: Array, required: true },
    pagination: { type: Object, required: true },
});

const searchInput = ref(props.search);
const dateFromInput = ref(props.dateFrom);
const dateToInput = ref(props.dateTo);

const submitFilter = () => {
    router.get('/one-power/personal-data/hm', {
        search: searchInput.value,
        date_from: dateFromInput.value,
        date_to: dateToInput.value,
    }, { preserveScroll: true, preserveState: true });
};

const formModalOpen = ref(false);
const editingRow = ref(null);

const blankForm = () => ({
    tanggal: new Date().toISOString().slice(0, 10),
    shift: 'DS',
    employee_id: null,
    unit: '',
    dept: '',
    hm_start: null,
    hm_stop: null,
    activity: '',
    parking: '',
});

const form = useForm(blankForm());

const openCreate = () => {
    editingRow.value = null;
    form.defaults(blankForm());
    form.reset();
    formModalOpen.value = true;
};

const openEdit = (row) => {
    editingRow.value = row;
    form.defaults({
        tanggal: row.tanggal,
        shift: row.shift,
        employee_id: row.employeeId,
        unit: row.unit,
        dept: row.dept ?? '',
        hm_start: row.hmStart,
        hm_stop: row.hmStop,
        activity: row.activity ?? '',
        parking: row.parking ?? '',
    });
    form.reset();
    formModalOpen.value = true;
};

const closeFormModal = () => {
    formModalOpen.value = false;
    editingRow.value = null;
};

const submitForm = () => {
    const options = { preserveScroll: true, onSuccess: () => closeFormModal() };

    if (editingRow.value) {
        form.put(`/one-power/personal-data/hm/${editingRow.value.id}`, options);
    } else {
        form.post('/one-power/personal-data/hm', options);
    }
};

const handleDelete = (row) => {
    if (! confirm(`Hapus data HM "${row.nrp} - ${row.nama}" tanggal ${row.tanggal}?`)) return;
    router.delete(`/one-power/personal-data/hm/${row.id}`, { preserveScroll: true });
};

const importModalOpen = ref(false);
const importForm = useForm({ file: null });

const submitImport = () => {
    importForm.post('/one-power/personal-data/hm/import', {
        preserveScroll: true,
        forceFormData: true,
        onSuccess: () => {
            importModalOpen.value = false;
            importForm.reset();
        },
    });
};

const importApiModalOpen = ref(false);
const importApiForm = useForm({
    bulan: new Date().getMonth() + 1,
    tahun: new Date().getFullYear(),
});

const submitImportApi = () => {
    importApiForm.post('/one-power/personal-data/hm/import-api', {
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
</script>

<template>
    <Head title="Database HM - OneClick" />

    <AppLayout :user="user" :notif-counts="notifCounts" :menu="menu">
        <div class="mb-5 flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h1 class="mb-1 text-xl font-extrabold text-slate-800">Database HM (Hour Meter)</h1>
                <p class="m-0 text-sm text-slate-500">Kelola dan monitor data penggunaan jam kerja alat.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <button type="button" class="flex items-center gap-1.5 rounded-lg bg-indigo-600 px-3 py-2 text-sm font-bold text-white transition hover:bg-indigo-700" @click="importApiModalOpen = true">
                    <RefreshCw class="h-4 w-4" /> Import dari API HO
                </button>
                <button type="button" class="flex items-center gap-1.5 rounded-lg bg-emerald-600 px-3 py-2 text-sm font-bold text-white transition hover:bg-emerald-700" @click="importModalOpen = true">
                    <CloudDownload class="h-4 w-4" /> Import Excel
                </button>
                <button type="button" class="flex items-center gap-1.5 rounded-lg bg-blue-500 px-3 py-2 text-sm font-bold text-white transition hover:bg-blue-600" @click="openCreate">
                    <Gauge class="h-4 w-4" /> Tambah Data
                </button>
            </div>
        </div>

        <form class="mb-4 flex flex-wrap items-center gap-2" @submit.prevent="submitFilter">
            <div class="relative w-full max-w-xs flex-1">
                <Search class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
                <input v-model="searchInput" type="text" placeholder="Cari NRP, nama, atau unit..." class="w-full rounded-lg border border-slate-200 bg-white py-2 pl-9 pr-3 text-sm outline-none focus:border-blue-500">
            </div>
            <div class="flex items-center gap-1.5">
                <span class="text-xs font-semibold text-slate-500">Dari</span>
                <input v-model="dateFromInput" type="date" class="rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500">
            </div>
            <div class="flex items-center gap-1.5">
                <span class="text-xs font-semibold text-slate-500">S/d</span>
                <input v-model="dateToInput" type="date" class="rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500">
            </div>
            <button type="submit" class="rounded-lg bg-blue-500 px-4 py-2 text-sm font-bold text-white hover:bg-blue-600">Filter</button>
        </form>

        <div class="mb-4 grid grid-cols-1 gap-3 sm:grid-cols-3">
            <div class="rounded-xl bg-blue-500 p-4 text-white shadow-sm">
                <div class="text-xs font-bold uppercase tracking-wide opacity-75">Total HM</div>
                <div class="text-2xl font-extrabold">{{ summary.totalHm.toLocaleString('id-ID', { minimumFractionDigits: 1, maximumFractionDigits: 1 }) }}</div>
                <div class="text-xs opacity-75">Periode terfilter</div>
            </div>
            <div class="rounded-xl border border-slate-200 bg-white p-4 text-center shadow-sm">
                <div class="text-xs font-bold uppercase tracking-wide text-slate-500">Rata-rata HM</div>
                <div class="text-2xl font-extrabold text-blue-600">{{ summary.avgHm.toLocaleString('id-ID', { minimumFractionDigits: 1, maximumFractionDigits: 1 }) }}</div>
                <div class="text-xs text-slate-400">Per aktivitas</div>
            </div>
            <div class="rounded-xl border border-slate-200 bg-white p-4 text-center shadow-sm">
                <div class="text-xs font-bold uppercase tracking-wide text-slate-500">Total Record</div>
                <div class="text-2xl font-extrabold text-slate-800">{{ summary.totalRecords }}</div>
                <div class="text-xs text-slate-400">Data terfilter</div>
            </div>
        </div>

        <HmDataTable :hm-data="hmData" @edit="openEdit" @delete="handleDelete" />

        <div class="mt-4">
            <Pagination
                :current-page="pagination.currentPage"
                :last-page="pagination.lastPage"
                base-url="/one-power/personal-data/hm"
                :query="{ search, date_from: dateFrom, date_to: dateTo }"
            />
        </div>

        <HmDataFormModal :open="formModalOpen" :row="editingRow" :form="form" @close="closeFormModal" @submit="submitForm" />
        <HmDataImportModal :open="importModalOpen" :form="importForm" @close="importModalOpen = false" @submit="submitImport" />

        <!-- Modal Import dari API HO -->
        <div v-if="importApiModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 p-4">
            <div class="w-full max-w-md rounded-xl bg-white p-6 shadow-xl">
                <div class="mb-4 flex items-center justify-between">
                    <h3 class="text-lg font-bold text-slate-800">Import HM dari API HO</h3>
                    <button type="button" class="text-slate-400 hover:text-slate-600" @click="importApiModalOpen = false">
                        <X class="h-5 w-5" />
                    </button>
                </div>
                <p class="mb-4 text-xs text-slate-500">
                    Sistem akan mengambil data HM dari Head Office (HO) API untuk bulan terpilih dan menggantikan data HM pada periode tersebut.
                </p>
                <form class="space-y-4" @submit.prevent="submitImportApi">
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
