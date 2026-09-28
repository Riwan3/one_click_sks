<script setup>
import { confirmDialog } from '@/utils/confirm';
import { computed, ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import { Plus, FileDown, AlertTriangle, ClipboardCheck, X } from '@lucide/vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import Pagination from '@/Components/ui/Pagination.vue';
import IzinKaryawanTable from './Partials/IzinKaryawanTable.vue';
import IzinKaryawanModal from './Partials/IzinKaryawanModal.vue';

const props = defineProps({
    user: { type: Object, required: true },
    notifCounts: { type: Object, required: true },
    menu: { type: Array, required: true },
    isAdmin: { type: Boolean, required: true },
    isMedic: { type: Boolean, default: false },
    isOperator: { type: Boolean, default: false },
    isAdminOrManpower: { type: Boolean, default: false },
    currentUserEmployee: { type: Object, default: () => ({}) },
    filters: { type: Object, required: true },
    izins: { type: Array, required: true },
    pagination: { type: Object, required: true },
});

const showAddModal = ref(false);

const searchInput = ref(props.filters.search || '');
const jenisInput = ref(props.filters.jenis_izin || '');
const statusInput = ref(props.filters.status || '');
const dateFromInput = ref(props.filters.date_from || '');
const dateToInput = ref(props.filters.date_to || '');

const baseUrl = '/one-power/deviasi-operator/izin-karyawan';

const submitFilter = () => {
    router.get(baseUrl, {
        search: searchInput.value,
        jenis_izin: jenisInput.value,
        status: statusInput.value,
        date_from: dateFromInput.value,
        date_to: dateToInput.value,
    }, { preserveScroll: true, preserveState: true });
};

const resetFilter = () => {
    searchInput.value = '';
    jenisInput.value = '';
    statusInput.value = '';
    dateFromInput.value = '';
    dateToInput.value = '';
    router.get(baseUrl, {}, { preserveScroll: true, preserveState: true });
};

const hasActiveFilters = computed(() => {
    return !!(searchInput.value || jenisInput.value || statusInput.value || dateFromInput.value || dateToInput.value);
});

const emergencyIzins = computed(() => props.izins.filter((row) => row.jenisIzin === 'emergency'));
const nonEmergencyIzins = computed(() => props.izins.filter((row) => row.jenisIzin !== 'emergency'));

const showEmergency = computed(() => !props.filters.jenis_izin || props.filters.jenis_izin === 'emergency');
const showNonEmergency = computed(() => !props.filters.jenis_izin || props.filters.jenis_izin !== 'emergency');

const exportUrl = computed(() => {
    const params = new URLSearchParams();
    if (props.filters.jenis_izin) params.set('jenis_izin', props.filters.jenis_izin);
    if (props.filters.search) params.set('search', props.filters.search);
    if (props.filters.date_from) params.set('date_from', props.filters.date_from);
    if (props.filters.date_to) params.set('date_to', props.filters.date_to);
    if (props.filters.status) params.set('status', props.filters.status);
    const qs = params.toString();
    return `${baseUrl}/export${qs ? `?${qs}` : ''}`;
});

const doAction = async (action, row, confirmMsg) => {
    if (confirmMsg && ! await confirmDialog(confirmMsg)) return;
    router.post(`${baseUrl}/${row.id}/${action}`, {}, { preserveScroll: true });
};

const handleApprove = (row) => doAction('approve', row, `Setujui pengajuan izin "${row.nama}"?`);
const handleDecline = (row) => doAction('decline', row, `Tolak pengajuan izin "${row.nama}"?`);
const handleReset = (row) => doAction('reset', row, `Kembalikan status ke Menunggu Persetujuan?`);
const handleCancel = (row) => doAction('cancel', row, `Yakin ingin membatalkan data ini?`);

const handleDelete = async (row) => {
    if (! await confirmDialog(`Yakin ingin menghapus data ini? Tindakan ini tidak bisa dibatalkan.`)) return;
    router.delete(`${baseUrl}/${row.id}`, { preserveScroll: true });
};
</script>

<template>
    <Head :title="`Izin Karyawan${isMedic ? ' - Sakit' : ''} - OneClick`" />

    <AppLayout :user="user" :notif-counts="notifCounts" :menu="menu">
        <!-- HEADER (V1 style) -->
        <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h4 class="text-xl font-extrabold text-slate-800">
                    Izin Karyawan{{ isMedic ? ' - Sakit' : '' }}
                </h4>
                <p class="mt-0.5 text-xs text-slate-500">
                    <template v-if="isMedic">
                        Kelola data pemeriksaan &amp; surat sakit karyawan.
                    </template>
                    <template v-else-if="!isAdminOrManpower">
                        Kelola data surat izin dan sakit karyawan.
                    </template>
                    <template v-else>
                        Proses persetujuan data izin dan sakit karyawan.
                    </template>
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <button
                    type="button"
                    class="inline-flex items-center gap-1.5 rounded-lg bg-blue-600 px-4 py-2 text-xs font-bold text-white shadow-sm hover:bg-blue-700 active:scale-95 transition"
                    @click="showAddModal = true"
                >
                    <Plus class="h-4 w-4" /> TAMBAH PENGAJUAN
                </button>
                <a
                    v-if="isAdminOrManpower"
                    :href="exportUrl"
                    class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-600 px-3.5 py-2 text-xs font-bold text-white shadow-sm hover:bg-emerald-700 active:scale-95 transition uppercase"
                >
                    <FileDown class="h-4 w-4" /> Export Data
                </a>
            </div>
        </div>

        <!-- FILTER CARD (V1 style) -->
        <div class="mb-5 rounded-2xl border border-slate-200 bg-white p-3 shadow-xs">
            <form class="flex flex-wrap items-center gap-2" @submit.prevent="submitFilter">
                <!-- Status Filter (Admin/Manpower only) -->
                <div v-if="isAdminOrManpower" class="w-full sm:w-44">
                    <select
                        v-model="statusInput"
                        class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-xs font-medium text-slate-700 outline-none focus:border-blue-500"
                    >
                        <option value="">Semua Status</option>
                        <option value="Awaiting Approval">Menunggu Persetujuan</option>
                        <option value="Approved">Disetujui</option>
                        <option value="Declined">Ditolak</option>
                        <option value="Cancelled">Dibatalkan</option>
                    </select>
                </div>

                <!-- Jenis Filter (Hidden for medic) -->
                <div v-if="!isMedic" class="w-full sm:w-36">
                    <select
                        v-model="jenisInput"
                        class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-xs font-medium text-slate-700 outline-none focus:border-blue-500"
                    >
                        <option value="">Semua Jenis</option>
                        <option value="izin">Izin</option>
                        <option value="sakit">Sakit</option>
                        <option value="emergency">Emergency</option>
                    </select>
                </div>

                <!-- Date From -->
                <div class="flex items-center rounded-lg border border-slate-200 bg-slate-50 text-xs w-full sm:w-auto">
                    <span class="px-2.5 py-2 text-slate-400 font-bold border-r border-slate-200">Dari</span>
                    <input
                        v-model="dateFromInput"
                        type="date"
                        class="bg-transparent px-2.5 py-2 text-xs text-slate-700 outline-none"
                    >
                </div>

                <!-- Date To -->
                <div class="flex items-center rounded-lg border border-slate-200 bg-slate-50 text-xs w-full sm:w-auto">
                    <span class="px-2.5 py-2 text-slate-400 font-bold border-r border-slate-200">S/d</span>
                    <input
                        v-model="dateToInput"
                        type="date"
                        class="bg-transparent px-2.5 py-2 text-xs text-slate-700 outline-none"
                    >
                </div>

                <!-- Search Input -->
                <div class="flex-1 min-w-[160px]">
                    <input
                        v-model="searchInput"
                        type="text"
                        placeholder="Cari NRP, nama, atau no. surat..."
                        class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-xs text-slate-700 outline-none focus:border-blue-500"
                    >
                </div>

                <!-- Action Buttons -->
                <div class="flex items-center gap-1.5">
                    <button
                        type="submit"
                        class="rounded-lg bg-blue-600 px-4 py-2 text-xs font-bold text-white shadow-xs hover:bg-blue-700 transition uppercase"
                    >
                        FILTER
                    </button>
                    <button
                        v-if="hasActiveFilters"
                        type="button"
                        class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-500 hover:bg-slate-100 transition"
                        title="Reset Filter"
                        @click="resetFilter"
                    >
                        <X class="h-4 w-4" />
                    </button>
                </div>
            </form>
        </div>

        <!-- TABLE 1: DATA EMERGENCY -->
        <div v-if="showEmergency" class="mb-6">
            <h5 class="mb-3 flex items-center gap-2 text-sm font-bold text-purple-700">
                <AlertTriangle class="h-4 w-4 text-purple-600" /> Data Emergency
            </h5>
            <IzinKaryawanTable
                :izins="emergencyIzins"
                :is-admin="isAdmin"
                :is-emergency="true"
                @approve="handleApprove"
                @decline="handleDecline"
                @reset="handleReset"
                @cancel="handleCancel"
                @delete="handleDelete"
            />
        </div>

        <!-- TABLE 2: DATA IZIN & SAKIT -->
        <div v-if="showNonEmergency" class="mb-6">
            <h5 class="mb-3 flex items-center gap-2 text-sm font-bold text-slate-800">
                <ClipboardCheck class="h-4 w-4 text-blue-600" /> Data Izin &amp; Sakit
            </h5>
            <IzinKaryawanTable
                :izins="nonEmergencyIzins"
                :is-admin="isAdmin"
                :is-emergency="false"
                @approve="handleApprove"
                @decline="handleDecline"
                @reset="handleReset"
                @cancel="handleCancel"
                @delete="handleDelete"
            />
        </div>

        <!-- PAGINATION -->
        <div v-if="pagination.lastPage > 1" class="mt-6">
            <Pagination
                :current-page="pagination.currentPage"
                :last-page="pagination.lastPage"
                :base-url="baseUrl"
                :query="filters"
            />
        </div>

        <!-- MODAL TAMBAH PENGAJUAN -->
        <IzinKaryawanModal
            :show="showAddModal"
            :is-medic="isMedic"
            :is-admin-or-manpower="isAdminOrManpower"
            :current-user-employee="currentUserEmployee"
            @close="showAddModal = false"
        />
    </AppLayout>
</template>
