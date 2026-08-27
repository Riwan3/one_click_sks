<script setup>
import { ref } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { CalendarCheck, Plus, Search, Pencil, Trash2 } from '@lucide/vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import Pagination from '@/Components/ui/Pagination.vue';
import AttendanceFormModal from './Partials/AttendanceFormModal.vue';

const props = defineProps({
    user: { type: Object, required: true },
    notifCounts: { type: Object, required: true },
    menu: { type: Array, required: true },
    search: { type: String, default: '' },
    dateFrom: { type: String, default: '' },
    dateTo: { type: String, default: '' },
    items: { type: Array, required: true },
    pagination: { type: Object, required: true },
});

const searchInput = ref(props.search);
const dateFromInput = ref(props.dateFrom);
const dateToInput = ref(props.dateTo);

const submitFilter = () => {
    router.get('/one-power/personal-data/attendance', { search: searchInput.value, date_from: dateFromInput.value, date_to: dateToInput.value }, { preserveScroll: true, preserveState: true });
};

const formModalOpen = ref(false);
const editingRow = ref(null);
const blankForm = () => ({ employee_id: null, tanggal: new Date().toISOString().slice(0, 10), status: 'Hadir', jam_masuk: '', jam_keluar: '', keterangan: '' });
const form = useForm(blankForm());

const openCreate = () => { editingRow.value = null; form.defaults(blankForm()); form.reset(); formModalOpen.value = true; };
const openEdit = (row) => {
    editingRow.value = row;
    form.defaults({ employee_id: row.employeeId, tanggal: row.tanggal, status: row.status, jam_masuk: row.jamMasuk ?? '', jam_keluar: row.jamKeluar ?? '', keterangan: row.keterangan ?? '' });
    form.reset();
    formModalOpen.value = true;
};
const closeFormModal = () => { formModalOpen.value = false; editingRow.value = null; };
const submitForm = () => {
    const options = { preserveScroll: true, onSuccess: () => closeFormModal() };
    editingRow.value ? form.put(`/one-power/personal-data/attendance/${editingRow.value.id}`, options) : form.post('/one-power/personal-data/attendance', options);
};
const handleDelete = (row) => {
    if (! confirm(`Hapus absensi "${row.nrp} - ${row.nama}" tanggal ${row.tanggal}?`)) return;
    router.delete(`/one-power/personal-data/attendance/${row.id}`, { preserveScroll: true });
};

const statusColor = { Hadir: 'bg-emerald-100 text-emerald-700', Sakit: 'bg-amber-100 text-amber-700', Ijin: 'bg-blue-100 text-blue-700', Alpa: 'bg-red-100 text-red-700' };
</script>

<template>
    <Head title="Attendance - OneClick" />

    <AppLayout :user="user" :notif-counts="notifCounts" :menu="menu">
        <div class="mb-5 flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h1 class="mb-1 flex items-center gap-2 text-xl font-extrabold text-slate-800"><CalendarCheck class="h-5 w-5 text-blue-500" /> Attendance</h1>
                <p class="m-0 text-sm text-slate-500">Total ditemukan: <strong class="text-slate-700">{{ pagination.total }}</strong> data.</p>
            </div>
            <button type="button" class="flex items-center gap-1.5 rounded-lg bg-blue-500 px-3 py-2 text-sm font-bold text-white hover:bg-blue-600" @click="openCreate"><Plus class="h-4 w-4" /> Tambah Absensi</button>
        </div>

        <form class="mb-4 flex flex-wrap items-center gap-2" @submit.prevent="submitFilter">
            <div class="relative w-full max-w-xs flex-1">
                <Search class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
                <input v-model="searchInput" type="text" placeholder="Cari NRP atau nama..." class="w-full rounded-lg border border-slate-200 bg-white py-2 pl-9 pr-3 text-sm outline-none focus:border-blue-500">
            </div>
            <input v-model="dateFromInput" type="date" class="rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500">
            <input v-model="dateToInput" type="date" class="rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500">
            <button type="submit" class="rounded-lg bg-blue-500 px-4 py-2 text-sm font-bold text-white hover:bg-blue-600">Filter</button>
        </form>

        <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white shadow-sm">
            <table class="w-full min-w-[700px] text-left text-sm">
                <thead>
                    <tr class="border-b border-slate-100 text-[0.7rem] uppercase tracking-wide text-slate-500">
                        <th class="px-4 py-3 font-semibold">Tanggal</th>
                        <th class="px-4 py-3 font-semibold">NRP / Nama</th>
                        <th class="px-4 py-3 font-semibold">Status</th>
                        <th class="px-4 py-3 font-semibold">Jam Masuk / Keluar</th>
                        <th class="px-4 py-3 font-semibold">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-if="items.length === 0"><td colspan="5" class="px-4 py-8 text-center text-slate-400">Belum ada data absensi.</td></tr>
                    <tr v-for="row in items" :key="row.id" class="border-b border-slate-50 last:border-0 hover:bg-slate-50/60">
                        <td class="px-4 py-3 align-middle font-bold text-slate-700">{{ row.tanggal }}</td>
                        <td class="px-4 py-3 align-middle">
                            <div class="font-bold text-slate-700">{{ row.nrp }}</div>
                            <div class="text-xs uppercase text-slate-500">{{ row.nama }}</div>
                        </td>
                        <td class="px-4 py-3 align-middle"><span :class="['rounded-full px-2.5 py-1 text-xs font-semibold', statusColor[row.status]]">{{ row.status }}</span></td>
                        <td class="px-4 py-3 align-middle text-xs text-slate-600">{{ row.jamMasuk || '-' }} / {{ row.jamKeluar || '-' }}</td>
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
            <Pagination :current-page="pagination.currentPage" :last-page="pagination.lastPage" base-url="/one-power/personal-data/attendance" :query="{ search, date_from: dateFrom, date_to: dateTo }" />
        </div>

        <AttendanceFormModal :open="formModalOpen" :row="editingRow" :form="form" @close="closeFormModal" @submit="submitForm" />
    </AppLayout>
</template>
