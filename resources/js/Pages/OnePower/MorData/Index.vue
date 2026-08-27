<script setup>
import { ref } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { BarChart, LayoutGrid, Plus, Printer, Search, Table2 } from '@lucide/vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import Pagination from '@/Components/ui/Pagination.vue';
import MorDataTable from './Partials/MorDataTable.vue';
import MorDataCard from './Partials/MorDataCard.vue';
import MorDataFormModal from './Partials/MorDataFormModal.vue';

const props = defineProps({
    user: { type: Object, required: true },
    notifCounts: { type: Object, required: true },
    menu: { type: Array, required: true },
    search: { type: String, default: '' },
    filters: { type: Object, required: true },
    items: { type: Array, required: true },
    pagination: { type: Object, required: true },
});

const searchInput = ref(props.search);
const monthInput = ref(props.filters.bulan);
const yearInput = ref(props.filters.tahun);
const viewMode = ref('card');

const submitFilter = () => {
    router.get('/one-power/mor', { search: searchInput.value, bulan: monthInput.value, tahun: yearInput.value }, { preserveScroll: true, preserveState: true });
};

const printReport = () => window.print();

const formModalOpen = ref(false);
const editingRow = ref(null);

const blankForm = () => ({
    employee_id: null,
    posisi: '',
    gl_name: '',
    bulan: props.filters.bulan,
    tahun: props.filters.tahun,
    atr_persen: 0,
    atr_point: 0,
    work_day: 0,
    sakit: 0,
    ijin: 0,
    alpa: 0,
    timesheet_persen: 0,
    timesheet_point: 0,
    hm_ratio: 0,
    hm_point: 0,
    hm_jam: 0,
    hm_target: 0,
    fuel_persen: 0,
    fuel_point: 0,
    fuel_actual: 0,
    fuel_limit: 0,
    pdty_persen: null,
    pdty_point: null,
    act_coal: null,
    targt_coal: null,
    act_ob: null,
    targt_ob: null,
    pembinaan: 0,
    pembinaan_poin: 20,
    ket_pembinaan: '',
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
        employee_id: row.employeeId,
        posisi: row.posisi ?? '',
        gl_name: row.glName ?? '',
        bulan: row.bulan,
        tahun: row.tahun,
        atr_persen: row.atrPersen,
        atr_point: row.atrPoint,
        work_day: row.workDay,
        sakit: row.sakit,
        ijin: row.ijin,
        alpa: row.alpa,
        timesheet_persen: row.timesheetPersen,
        timesheet_point: row.timesheetPoint,
        hm_ratio: row.hmRatio,
        hm_point: row.hmPoint,
        hm_jam: row.hmJam,
        hm_target: row.hmTarget,
        fuel_persen: row.fuelPersen,
        fuel_point: row.fuelPoint,
        fuel_actual: row.fuelActual,
        fuel_limit: row.fuelLimit,
        pdty_persen: row.pdtyPersen,
        pdty_point: row.pdtyPoint,
        act_coal: row.actCoal,
        targt_coal: row.targtCoal,
        act_ob: row.actOb,
        targt_ob: row.targtOb,
        pembinaan: row.pembinaan,
        pembinaan_poin: row.pembinaanPoin,
        ket_pembinaan: row.ketPembinaan ?? '',
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
        form.put(`/one-power/mor/${editingRow.value.id}`, options);
    } else {
        form.post('/one-power/mor', options);
    }
};

const handleDelete = (row) => {
    if (! confirm(`Hapus data MOR "${row.nrp} - ${row.nama}"?`)) return;
    router.delete(`/one-power/mor/${row.id}`, { preserveScroll: true });
};
</script>

<template>
    <Head title="Monthly Operation Report - OneClick" />

    <AppLayout :user="user" :notif-counts="notifCounts" :menu="menu">
        <div class="mb-5 flex flex-col gap-3 print:hidden lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h1 class="mb-1 flex items-center gap-2 text-xl font-extrabold text-slate-800">
                    <BarChart class="h-5 w-5 text-blue-500" /> Monthly Operation Report
                </h1>
                <p class="m-0 text-sm text-slate-500">
                    Total ditemukan: <strong class="text-slate-700">{{ pagination.total }}</strong> data.
                </p>
            </div>
            <div class="flex items-center gap-2">
                <div class="flex rounded-lg border border-slate-200 bg-white p-1">
                    <button
                        type="button"
                        class="flex items-center gap-1.5 rounded-md px-3 py-1.5 text-sm font-bold"
                        :class="viewMode === 'card' ? 'bg-blue-500 text-white' : 'text-slate-500 hover:bg-slate-50'"
                        @click="viewMode = 'card'"
                    >
                        <LayoutGrid class="h-4 w-4" /> Card View
                    </button>
                    <button
                        type="button"
                        class="flex items-center gap-1.5 rounded-md px-3 py-1.5 text-sm font-bold"
                        :class="viewMode === 'table' ? 'bg-blue-500 text-white' : 'text-slate-500 hover:bg-slate-50'"
                        @click="viewMode = 'table'"
                    >
                        <Table2 class="h-4 w-4" /> Table View
                    </button>
                </div>
                <button type="button" class="flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm font-bold text-slate-600 hover:bg-slate-50" @click="printReport">
                    <Printer class="h-4 w-4" /> Cetak
                </button>
                <button type="button" class="flex items-center gap-1.5 rounded-lg bg-blue-500 px-3 py-2 text-sm font-bold text-white hover:bg-blue-600" @click="openCreate">
                    <Plus class="h-4 w-4" /> Tambah Data
                </button>
            </div>
        </div>

        <form class="mb-4 flex flex-wrap items-center gap-2 print:hidden" @submit.prevent="submitFilter">
            <select v-model.number="monthInput" class="rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500">
                <option v-for="(name, num) in filters.monthNames" :key="num" :value="Number(num)">{{ name }}</option>
            </select>
            <select v-model.number="yearInput" class="rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500">
                <option v-for="y in filters.years" :key="y" :value="y">{{ y }}</option>
            </select>
            <div class="relative w-full max-w-xs flex-1">
                <Search class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
                <input v-model="searchInput" type="text" placeholder="Cari NRP atau nama..." class="w-full rounded-lg border border-slate-200 bg-white py-2 pl-9 pr-3 text-sm outline-none focus:border-blue-500">
            </div>
            <button type="submit" class="rounded-lg bg-blue-500 px-4 py-2 text-sm font-bold text-white hover:bg-blue-600">Filter</button>
        </form>

        <div v-if="viewMode === 'card'" class="flex flex-col gap-4">
            <div v-if="items.length === 0" class="rounded-xl border border-dashed border-slate-300 bg-white py-16 text-center text-sm text-slate-400">
                Belum ada data MOR untuk periode ini.
            </div>
            <MorDataCard v-for="row in items" :key="row.id" :row="row" />
        </div>

        <MorDataTable v-else :items="items" @edit="openEdit" @delete="handleDelete" />

        <div class="mt-4 print:hidden">
            <Pagination
                :current-page="pagination.currentPage"
                :last-page="pagination.lastPage"
                base-url="/one-power/mor"
                :query="{ search, bulan: filters.bulan, tahun: filters.tahun }"
            />
        </div>

        <MorDataFormModal :open="formModalOpen" :row="editingRow" :form="form" @close="closeFormModal" @submit="submitForm" />
    </AppLayout>
</template>
