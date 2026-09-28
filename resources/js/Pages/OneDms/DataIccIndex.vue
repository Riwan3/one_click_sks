<script setup>
import { confirmDialog } from '@/utils/confirm';
import { ref, watch } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import { Search, Calendar, Database, CheckCircle2, ShieldAlert, Plus, Pencil, Trash2, FileSpreadsheet } from 'lucide-vue-next';
import AppLayout from '@/Layouts/AppLayout.vue';
import IzinFatigueModal from './Partials/IzinFatigueModal.vue';
import GenericIccModal from './Partials/GenericIccModal.vue';
import Pagination from '@/Components/ui/Pagination.vue';

const props = defineProps({
    user: { type: Object, required: true },
    notifCounts: { type: Object, required: true },
    menu: { type: Array, required: true },
    category: { type: String, required: true },
    title: { type: String, required: true },
    filters: { type: Object, required: true },
    records: { type: Object, required: true },
    nrpLookup: { type: Object, default: () => ({}) },
});

const search = ref(props.filters.search || '');
const tanggal = ref(props.filters.tanggal || new Date().toISOString().substring(0, 7));

const showIzinModal = ref(false);
const editingIzinRow = ref(null);

const showGenericModal = ref(false);
const editingGenericRow = ref(null);

const fileInput = ref(null);

// DataIccIndex.vue dipakai ulang oleh Inertia untuk semua kategori (component instance-nya
// tidak remount saat pindah kategori lewat sidebar), jadi state modal harus di-reset manual
// setiap kali kategori berubah — supaya edit/tambah tidak nyangkut ke kategori yang salah.
watch(() => props.category, () => {
    showIzinModal.value = false;
    editingIzinRow.value = null;
    showGenericModal.value = false;
    editingGenericRow.value = null;
});

const handleFilter = () => {
    router.get(`/one-dms/data-icc/${props.category}`, {
        tanggal: tanggal.value,
        q: search.value,
    }, { preserveState: true });
};

const addIzin = () => {
    editingIzinRow.value = null;
    showIzinModal.value = true;
};

const editIzin = (row) => {
    editingIzinRow.value = row;
    showIzinModal.value = true;
};

const addIcc = () => {
    editingGenericRow.value = null;
    showGenericModal.value = true;
};

const editIcc = (row) => {
    editingGenericRow.value = row;
    showGenericModal.value = true;
};

const deleteIcc = async (id) => {
    if (await confirmDialog('Yakin ingin menghapus data ini?')) {
        router.delete(`/one-dms/data-icc/delete/${id}`, {
            data: { category: props.category },
            preserveScroll: true,
            preserveState: false,
        });
    }
};

const handleImport = async (e) => {
    const file = e.target.files[0];
    if (!file) return;

    if (await confirmDialog('Import data dari Excel? Pastikan urutan kolom sesuai dengan tabel yang ada di halaman ini.')) {
        router.post('/one-dms/data-icc/import', {
            file: file,
            category: props.category
        }, {
            forceFormData: true,
            preserveScroll: true,
            preserveState: false,
            onSuccess: () => {
                if (fileInput.value) fileInput.value.value = '';
            }
        });
    } else {
        e.target.value = '';
    }
};
</script>

<template>
    <Head :title="`${title} - OneClick`" />

    <AppLayout :user="user" :notif-counts="notifCounts" :menu="menu">
        <!-- Header -->
        <div class="mb-6 rounded-3xl p-8 text-white shadow-lg flex justify-between items-center flex-wrap gap-4" style="background: linear-gradient(135deg, #0f172a 0%, #0d9488 100%);">
            <div>
                <h1 class="mb-1 text-2xl font-extrabold flex items-center gap-3">
                    <Database class="w-7 h-7 text-emerald-400" />
                    {{ title }}
                </h1>
                <p class="m-0 max-w-xl text-sm text-white/80">
                    Monitoring data ICC ter-sync otomatis dari Formulir DMS yang berstatus Closed.
                </p>
            </div>
        </div>

        <!-- Filter Bar -->
        <div class="mb-6 bg-white dark:bg-slate-900 p-4 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm flex flex-wrap gap-4 items-center justify-between">
            <form @submit.prevent="handleFilter" class="flex flex-wrap gap-3 items-center w-full md:w-auto">
                <div class="relative min-w-[180px]">
                    <Calendar class="w-4 h-4 absolute left-3 top-3 text-slate-400" />
                    <input type="month" v-model="tanggal" @change="handleFilter" class="w-full border rounded-xl pl-9 pr-3 py-2 text-xs bg-slate-50 dark:bg-slate-800" />
                </div>
                <div class="relative min-w-[240px]">
                    <Search class="w-4 h-4 absolute left-3 top-3 text-slate-400" />
                    <input type="text" v-model="search" placeholder="Cari nama, nrp, unit..." class="w-full border rounded-xl pl-9 pr-3 py-2 text-xs bg-slate-50 dark:bg-slate-800" />
                </div>
                <button type="submit" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-xs shadow">
                    Filter Data
                </button>
                <button v-if="category === 'izin-fatigue'" type="button" @click="addIzin" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl text-xs shadow flex items-center gap-2">
                    <Plus class="w-4 h-4" /> Tambah Data
                </button>
                <button v-else type="button" @click="addIcc" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl text-xs shadow flex items-center gap-2">
                    <Plus class="w-4 h-4" /> Tambah Data
                </button>
                <button type="button" @click="$refs.fileInput.click()" class="px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white font-bold rounded-xl text-xs shadow flex items-center gap-2">
                    <FileSpreadsheet class="w-4 h-4" /> Import Excel
                </button>
                <input type="file" ref="fileInput" accept=".xlsx,.xls,.csv" class="hidden" @change="handleImport">
            </form>
        </div>

        <!-- Data Table for Izin Fatigue -->
        <div v-if="category === 'izin-fatigue'" class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-100 dark:bg-slate-800/80 text-slate-700 dark:text-slate-300 font-bold border-b">
                        <tr>
                            <th class="p-3">No</th>
                            <th class="p-3">Tanggal</th>
                            <th class="p-3">Shift</th>
                            <th class="p-3">NRP</th>
                            <th class="p-3">Nama Operator</th>
                            <th class="p-3 text-center">Jam Mulai</th>
                            <th class="p-3 text-center">Jam Akhir</th>
                            <th class="p-3 text-center">Total Jam</th>
                            <th class="p-3 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        <tr v-if="records.data.length === 0">
                            <td colspan="9" class="p-8 text-center text-slate-400">
                                Belum ada data Izin Fatigue untuk tanggal ini.
                            </td>
                        </tr>
                        <tr v-for="(row, idx) in records.data" :key="row.id" class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition">
                            <td class="p-3 font-bold">{{ records.from + idx }}</td>
                            <td class="p-3">{{ row.tanggal }}</td>
                            <td class="p-3"><span class="px-2 py-0.5 bg-slate-200 dark:bg-slate-700 rounded font-semibold">{{ row.shift }}</span></td>
                            <td class="p-3 font-mono text-slate-500">{{ row.nrp || '-' }}</td>
                            <td class="p-3 font-bold text-slate-900 dark:text-white">{{ row.nama_operator || '-' }}</td>
                            <td class="p-3 text-center font-bold text-blue-600">{{ row.jam_mulai || '-' }}</td>
                            <td class="p-3 text-center font-bold text-orange-600">{{ row.jam_akhir || '-' }}</td>
                            <td class="p-3 text-center font-bold bg-slate-50">{{ row.total_jam || '-' }}</td>
                            <td class="p-3 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <button @click="editIzin(row)" class="p-1.5 bg-blue-100 text-blue-600 rounded hover:bg-blue-200">
                                        <Pencil class="w-4 h-4" />
                                    </button>
                                    <button @click="deleteIcc(row.id)" class="p-1.5 bg-red-100 text-red-600 rounded hover:bg-red-200">
                                        <Trash2 class="w-4 h-4" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Default Data Table for Other Categories -->
        <div v-else class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-100 dark:bg-slate-800/80 text-slate-700 dark:text-slate-300 font-bold border-b">
                        <tr>
                            <th class="p-3">No</th>
                            <th class="p-3">Tanggal</th>
                            <th class="p-3">Shift</th>
                            <th class="p-3">Nama Operator</th>
                            <th class="p-3">NRP</th>
                            <th class="p-3">No. Unit</th>
                            <th class="p-3">Area</th>
                            <th class="p-3" v-if="category !== 'smoking-deviasi'">Waktu</th>
                            <th class="p-3" v-if="category !== 'smoking-deviasi'">Deviasi Ke</th>
                            <th class="p-3" v-if="category === 'smoking-deviasi'">Evidence</th>
                            <th class="p-3" v-if="category === 'smoking-deviasi'">Follow Up</th>
                            <th class="p-3" v-else>Intervensi</th>
                            <th class="p-3">Group Leader</th>
                            <th class="p-3 text-center">Status</th>
                            <th class="p-3 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        <tr v-if="records.data.length === 0">
                            <td colspan="15" class="p-8 text-center text-slate-400">
                                Belum ada record data ICC ter-sync untuk tanggal ini.
                            </td>
                        </tr>
                        <tr v-for="(row, idx) in records.data" :key="row.id" class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition">
                            <td class="p-3 font-bold">{{ records.from + idx }}</td>
                            <td class="p-3">{{ row.tanggal }}</td>
                            <td class="p-3"><span class="px-2 py-0.5 bg-slate-200 dark:bg-slate-700 rounded font-semibold">{{ row.shift }}</span></td>
                            <td class="p-3 font-bold text-slate-900 dark:text-white">{{ row.nama_operator || '-' }}</td>
                            <td class="p-3 font-mono text-slate-500">{{ row.nrp || '-' }}</td>
                            <td class="p-3 font-bold text-blue-600 dark:text-blue-400">{{ row.unit || '-' }}</td>
                            <td class="p-3">{{ row.area || '-' }}</td>
                            <td class="p-3" v-if="category !== 'smoking-deviasi'">{{ row.waktu || '-' }}</td>
                            <td class="p-3" v-if="category !== 'smoking-deviasi'"><span class="px-2 py-0.5 bg-amber-100 text-amber-800 rounded font-bold">#{{ row.deviasi_ke || 1 }}</span></td>
                            <td class="p-3" v-if="category === 'smoking-deviasi'">{{ row.evidence || '-' }}</td>
                            <td class="p-3" v-if="category === 'smoking-deviasi'">{{ row.fu || '-' }}</td>
                            <td class="p-3" v-else>{{ row.intervensi || '-' }}</td>
                            <td class="p-3">{{ row.group_leader || '-' }}</td>
                            <td class="p-3 text-center">
                                <span class="px-2.5 py-1 bg-emerald-100 text-emerald-800 font-bold rounded-full text-[10px] inline-flex items-center gap-1">
                                    <CheckCircle2 class="w-3 h-3" /> {{ row.status || 'Close' }}
                                </span>
                            </td>
                            <td class="p-3 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <button @click="editIcc(row)" class="p-1.5 bg-blue-100 text-blue-600 rounded hover:bg-blue-200" title="Edit Data">
                                        <Pencil class="w-4 h-4" />
                                    </button>
                                    <button @click="deleteIcc(row.id)" class="p-1.5 bg-red-100 text-red-600 rounded hover:bg-red-200" title="Hapus Data">
                                        <Trash2 class="w-4 h-4" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-4">
            <Pagination
                :current-page="records.current_page"
                :last-page="records.last_page"
                :base-url="`/one-dms/data-icc/${category}`"
                :query="{ tanggal, q: search }"
            />
        </div>

        <IzinFatigueModal :open="showIzinModal" :row="editingIzinRow" :nrp-lookup="nrpLookup" @close="showIzinModal = false; editingIzinRow = null" />
        <GenericIccModal :open="showGenericModal" :row="editingGenericRow" :category="category" :nrp-lookup="nrpLookup" @close="showGenericModal = false; editingGenericRow = null" />
    </AppLayout>
</template>
