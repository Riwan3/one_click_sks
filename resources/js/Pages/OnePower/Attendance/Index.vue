<script setup>
import { ref } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { CalendarCheck, Plus, Search, Pencil, Trash2, CloudDownload, Info } from '@lucide/vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import Pagination from '@/Components/ui/Pagination.vue';
import AttendanceFormModal from './Partials/AttendanceFormModal.vue';
import AttendanceImportModal from './Partials/AttendanceImportModal.vue';

const props = defineProps({
    user: { type: Object, required: true },
    notifCounts: { type: Object, required: true },
    menu: { type: Array, required: true },
    search: { type: String, default: '' },
    bulan: { type: Number, required: true },
    tahun: { type: Number, required: true },
    kategori: { type: String, default: null },
    atrThreshold: { type: Number, required: true },
    filterLowAtr: { type: Boolean, default: false },
    summary: { type: Object, required: true },
    codeColumns: { type: Array, required: true },
    items: { type: Array, required: true },
    pagination: { type: Object, required: true },
});

const monthNames = [
    'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
    'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember',
];

const kategoriOptions = [
    { value: 'd', label: 'Shift Siang (D)' },
    { value: 'n', label: 'Shift Malam (N)' },
    { value: 's', label: 'Sakit (S)' },
    { value: 'i', label: 'Izin (I)' },
    { value: 'a', label: 'Alfa (A)' },
];

/** Legend for the 15 status-code columns — see AttendanceMonthlyRoster migration for V1 parity notes. */
const codeLegend = [
    { code: 'd', meaning: 'Shift Siang' },
    { code: 'n', meaning: 'Shift Malam' },
    { code: 's', meaning: 'Sakit' },
    { code: 'i', meaning: 'Izin' },
    { code: 'a', meaning: 'Alfa' },
    { code: 'io', meaning: null },
    { code: 'nc', meaning: null },
    { code: 'l', meaning: null },
    { code: 't', meaning: null },
    { code: 'o', meaning: null },
    { code: 'k', meaning: null },
    { code: 'p', meaning: null },
    { code: 'r', meaning: null },
    { code: 'h', meaning: null },
    { code: 'nr', meaning: null },
];

const searchInput = ref(props.search);
const bulanInput = ref(props.bulan);
const tahunInput = ref(props.tahun);
const kategoriInput = ref(props.kategori ?? '');
const filterLowAtrInput = ref(props.filterLowAtr);
const atrThresholdInput = ref(props.atrThreshold);
const legendOpen = ref(false);

const submitFilter = () => {
    router.get('/one-power/personal-data/attendance', {
        search: searchInput.value,
        bulan: bulanInput.value,
        tahun: tahunInput.value,
        kategori: kategoriInput.value,
        filter_low_atr: filterLowAtrInput.value ? 1 : 0,
        atr_threshold: atrThresholdInput.value,
    }, { preserveScroll: true, preserveState: true });
};

const blankRosterForm = () => {
    const codes = Object.fromEntries(props.codeColumns.map((code) => [code, 0]));
    return { employee_id: null, bulan: props.bulan, tahun: props.tahun, dept: '', atr_percentage: null, ...codes };
};

const formModalOpen = ref(false);
const editingRow = ref(null);
const form = useForm(blankRosterForm());

const openCreate = () => {
    editingRow.value = null;
    form.defaults(blankRosterForm());
    form.reset();
    formModalOpen.value = true;
};

const openEdit = (row) => {
    editingRow.value = row;
    const codes = Object.fromEntries(props.codeColumns.map((code) => [code, row[code] ?? 0]));
    form.defaults({ employee_id: row.employeeId, bulan: row.bulan, tahun: row.tahun, dept: row.dept ?? '', atr_percentage: row.atrPercent ?? null, ...codes });
    form.reset();
    formModalOpen.value = true;
};

const closeFormModal = () => {
    formModalOpen.value = false;
    editingRow.value = null;
};

const submitForm = () => {
    const options = { preserveScroll: true, onSuccess: () => closeFormModal() };
    editingRow.value ? form.put(`/one-power/personal-data/attendance/${editingRow.value.id}`, options) : form.post('/one-power/personal-data/attendance', options);
};

const handleDelete = (row) => {
    if (! confirm(`Hapus roster absensi "${row.nrp} - ${row.nama}" periode ${row.bulan}/${row.tahun}?`)) return;
    router.delete(`/one-power/personal-data/attendance/${row.id}`, { preserveScroll: true });
};

const importModalOpen = ref(false);
const importForm = useForm({ file: null, bulan: props.bulan, tahun: props.tahun });

const submitImport = () => {
    importForm.post('/one-power/personal-data/attendance/import', {
        preserveScroll: true,
        forceFormData: true,
        onSuccess: () => {
            importModalOpen.value = false;
            importForm.reset('file');
        },
    });
};

const atrColor = (value) => {
    if (value === null || value === undefined) return 'text-slate-400';
    if (value < 80) return 'text-red-600';
    if (value < 90) return 'text-amber-600';
    return 'text-emerald-600';
};
</script>

<template>
    <Head title="Attendance - OneClick" />

    <AppLayout :user="user" :notif-counts="notifCounts" :menu="menu">
        <div class="mb-5 flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h1 class="mb-1 flex items-center gap-2 text-xl font-extrabold text-slate-800"><CalendarCheck class="h-5 w-5 text-blue-500" /> Attendance</h1>
                <p class="m-0 text-sm text-slate-500">Roster absensi bulanan. Total ditemukan: <strong class="text-slate-700">{{ pagination.total }}</strong> data.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <button type="button" class="flex items-center gap-1.5 rounded-lg bg-emerald-600 px-3 py-2 text-sm font-bold text-white hover:bg-emerald-700" @click="importModalOpen = true">
                    <CloudDownload class="h-4 w-4" /> Import Excel
                </button>
                <button type="button" class="flex items-center gap-1.5 rounded-lg bg-blue-500 px-3 py-2 text-sm font-bold text-white hover:bg-blue-600" @click="openCreate">
                    <Plus class="h-4 w-4" /> Tambah Data
                </button>
            </div>
        </div>

        <form class="mb-4 flex flex-wrap items-center gap-2" @submit.prevent="submitFilter">
            <div class="relative w-full max-w-xs flex-1">
                <Search class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
                <input v-model="searchInput" type="text" placeholder="Cari NRP atau nama..." class="w-full rounded-lg border border-slate-200 bg-white py-2 pl-9 pr-3 text-sm outline-none focus:border-blue-500">
            </div>
            <select v-model.number="bulanInput" class="rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500">
                <option v-for="(name, idx) in monthNames" :key="idx" :value="idx + 1">{{ name }}</option>
            </select>
            <input v-model.number="tahunInput" type="number" min="2000" max="2100" class="w-24 rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500">
            <select v-model="kategoriInput" class="rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500">
                <option value="">Semua Ket.</option>
                <option v-for="opt in kategoriOptions" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
            </select>
            <label class="flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs font-bold text-slate-600">
                <input v-model="filterLowAtrInput" type="checkbox" class="h-4 w-4 rounded border-slate-300">
                ATR &lt;
                <input v-model.number="atrThresholdInput" type="number" step="0.1" min="0" max="100" class="w-14 rounded border border-slate-200 px-1.5 py-0.5 text-xs outline-none focus:border-blue-500">
                %
            </label>
            <button type="submit" class="rounded-lg bg-blue-500 px-4 py-2 text-sm font-bold text-white hover:bg-blue-600">Filter</button>
        </form>

        <div class="mb-4 grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-6">
            <div class="rounded-xl border border-slate-200 bg-white p-4 text-center shadow-sm">
                <div class="text-xs font-bold uppercase tracking-wide text-slate-500">Shift D</div>
                <div class="text-2xl font-extrabold text-emerald-600">{{ summary.total_d }}</div>
            </div>
            <div class="rounded-xl border border-slate-200 bg-white p-4 text-center shadow-sm">
                <div class="text-xs font-bold uppercase tracking-wide text-slate-500">Shift N</div>
                <div class="text-2xl font-extrabold text-slate-800">{{ summary.total_n }}</div>
            </div>
            <div class="rounded-xl border border-slate-200 bg-white p-4 text-center shadow-sm">
                <div class="text-xs font-bold uppercase tracking-wide text-slate-500">Sakit (S)</div>
                <div class="text-2xl font-extrabold text-amber-600">{{ summary.total_s }}</div>
            </div>
            <div class="rounded-xl border border-slate-200 bg-white p-4 text-center shadow-sm">
                <div class="text-xs font-bold uppercase tracking-wide text-slate-500">Izin (I)</div>
                <div class="text-2xl font-extrabold text-sky-600">{{ summary.total_i }}</div>
            </div>
            <div class="rounded-xl border border-slate-200 bg-white p-4 text-center shadow-sm">
                <div class="text-xs font-bold uppercase tracking-wide text-slate-500">Alfa (A)</div>
                <div class="text-2xl font-extrabold text-red-600">{{ summary.total_a }}</div>
            </div>
            <div class="rounded-xl border border-slate-200 bg-blue-50 p-4 text-center shadow-sm">
                <div class="text-xs font-bold uppercase tracking-wide text-slate-500">Avg ATR</div>
                <div class="text-2xl font-extrabold text-blue-700">{{ summary.avg_atr }}%</div>
            </div>
        </div>

        <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white shadow-sm">
            <table class="w-full min-w-[1400px] text-left text-sm">
                <thead>
                    <tr class="border-b border-slate-100 text-[0.7rem] uppercase tracking-wide text-slate-500">
                        <th class="sticky left-0 bg-white px-4 py-3 font-semibold">NRP / Nama</th>
                        <th class="px-3 py-3 font-semibold">Periode</th>
                        <th v-for="code in codeColumns" :key="code" class="px-2 py-3 text-center font-semibold uppercase">{{ code }}</th>
                        <th class="px-3 py-3 text-center font-semibold">ATR%</th>
                        <th class="px-4 py-3 font-semibold">
                            <span class="relative inline-flex items-center gap-1">
                                Aksi
                                <button
                                    type="button"
                                    title="Keterangan kode kolom"
                                    class="flex h-4 w-4 items-center justify-center rounded-full text-slate-400 hover:text-blue-600"
                                    @click="legendOpen = !legendOpen"
                                    @blur="legendOpen = false"
                                >
                                    <Info class="h-3.5 w-3.5" />
                                </button>
                                <div
                                    v-if="legendOpen"
                                    class="absolute right-0 top-6 z-20 w-64 rounded-lg border border-slate-200 bg-white p-3 text-left normal-case tracking-normal text-slate-600 shadow-lg"
                                >
                                    <p class="mb-2 text-[0.7rem] font-bold uppercase text-slate-400">Keterangan Kode</p>
                                    <ul class="space-y-1 text-xs">
                                        <li v-for="entry in codeLegend" :key="entry.code" class="flex justify-between gap-2">
                                            <span class="font-bold uppercase text-slate-700">{{ entry.code }}</span>
                                            <span :class="entry.meaning ? 'text-slate-600' : 'italic text-slate-400'">{{ entry.meaning ?? 'kode internal (belum didokumentasikan)' }}</span>
                                        </li>
                                    </ul>
                                </div>
                            </span>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-if="items.length === 0"><td :colspan="codeColumns.length + 4" class="px-4 py-8 text-center text-slate-400">Belum ada data roster absensi untuk periode ini.</td></tr>
                    <tr v-for="row in items" :key="row.id" class="border-b border-slate-50 last:border-0 hover:bg-slate-50/60">
                        <td class="sticky left-0 bg-white px-4 py-3 align-middle">
                            <div class="font-bold text-slate-700">{{ row.nrp }}</div>
                            <div class="text-xs uppercase text-slate-500">{{ row.nama }}</div>
                        </td>
                        <td class="px-3 py-3 align-middle text-xs text-slate-600">{{ monthNames[row.bulan - 1] }} {{ row.tahun }}</td>
                        <td v-for="code in codeColumns" :key="code" class="px-2 py-3 text-center align-middle text-slate-600">{{ row[code] }}</td>
                        <td class="px-3 py-3 text-center align-middle font-bold" :class="atrColor(row.atrPercent)">{{ row.atrPercent }}%</td>
                        <td class="px-4 py-3 align-middle">
                            <div class="flex gap-1.5">
                                <button type="button" title="Edit" class="flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 text-amber-500 hover:bg-amber-50" @click="openEdit(row)"><Pencil class="h-3.5 w-3.5" /></button>
                                <button type="button" title="Hapus" class="flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 text-red-500 hover:bg-red-50" @click="handleDelete(row)"><Trash2 class="h-3.5 w-3.5" /></button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            <Pagination
                :current-page="pagination.currentPage"
                :last-page="pagination.lastPage"
                base-url="/one-power/personal-data/attendance"
                :query="{ search, bulan, tahun, kategori, filter_low_atr: filterLowAtr ? 1 : 0, atr_threshold: atrThreshold }"
            />
        </div>

        <AttendanceFormModal :open="formModalOpen" :row="editingRow" :form="form" :code-columns="codeColumns" @close="closeFormModal" @submit="submitForm" />
        <AttendanceImportModal :open="importModalOpen" :form="importForm" @close="importModalOpen = false" @submit="submitImport" />
    </AppLayout>
</template>
