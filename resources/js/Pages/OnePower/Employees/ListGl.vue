<script setup>
import { ref } from 'vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { UserCheck, Search, Users, PlusCircle, CloudDownload, X } from 'lucide-vue-next';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    user: { type: Object, required: true },
    notifCounts: { type: Object, required: true },
    menu: { type: Array, required: true },
    search: { type: String, default: '' },
    gls: { type: Array, required: true },
});

const searchInput = ref(props.search);

const submitFilter = () => {
    router.get('/one-power/employees/list-gl', { search: searchInput.value }, { preserveScroll: true, preserveState: true });
};

const importModalOpen = ref(false);
const importForm = useForm({
    file: null,
    reset_lama: false,
});

const submitImport = () => {
    importForm.post('/one-power/employees/import-asuh', {
        preserveScroll: true,
        forceFormData: true,
        onSuccess: () => {
            importModalOpen.value = false;
            importForm.reset();
        },
    });
};
</script>

<template>
    <Head title="Sahabat Pengusaha - OneClick" />

    <AppLayout :user="user" :notif-counts="notifCounts" :menu="menu">
        <div class="mb-5 flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h1 class="mb-1 text-xl font-extrabold text-slate-800 flex items-center gap-2">
                    <UserCheck class="h-6 w-6 text-emerald-600" />
                    Sahabat Pengusaha (Daftar Group Leader)
                </h1>
                <p class="m-0 text-sm text-slate-500">Kelola pemetaan Group Leader (Bapak Asuh) dan Karyawan (Anak Asuh).</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <button type="button" class="flex items-center gap-1.5 rounded-lg bg-emerald-600 px-3 py-2 text-sm font-bold text-white transition hover:bg-emerald-700 shadow-sm" @click="importModalOpen = true">
                    <CloudDownload class="h-4 w-4" /> Import Sahabat Pengusaha
                </button>
            </div>
        </div>

        <!-- Filter -->
        <form class="mb-4 flex flex-wrap items-center gap-2" @submit.prevent="submitFilter">
            <div class="relative w-full max-w-xs flex-1">
                <Search class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
                <input v-model="searchInput" type="text" placeholder="Cari Nama atau NRP Group Leader..." class="w-full rounded-lg border border-slate-200 bg-white py-2 pl-9 pr-3 text-sm outline-none focus:border-emerald-500">
            </div>
            <button type="submit" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-bold text-white hover:bg-emerald-700 transition">Cari</button>
        </form>

        <!-- Table GL -->
        <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white shadow-sm">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50 text-xs uppercase text-slate-500 border-b border-slate-200">
                    <tr>
                        <th class="px-4 py-3 font-bold">Group Leader (Bapak Asuh)</th>
                        <th class="px-4 py-3 font-bold">Jabatan / Posisi</th>
                        <th class="px-4 py-3 font-bold">Unit / Lokasi</th>
                        <th class="px-4 py-3 font-bold text-center">Jumlah Anak Asuh</th>
                        <th class="px-4 py-3 font-bold text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <tr v-for="gl in gls" :key="gl.id" class="hover:bg-slate-50/80 transition">
                        <td class="px-4 py-3">
                            <div class="font-bold text-slate-800">{{ gl.name }}</div>
                            <div class="text-xs font-mono text-slate-400">NRP: {{ gl.nrp }}</div>
                        </td>
                        <td class="px-4 py-3 font-medium text-slate-700">
                            {{ gl.jabatanSs6 || gl.jabatanRoster || '-' }}
                        </td>
                        <td class="px-4 py-3 text-xs text-slate-600">
                            <div>{{ gl.unit || '-' }}</div>
                            <div class="text-slate-400">{{ gl.lokasiKerja || '-' }}</div>
                        </td>
                        <td class="px-4 py-3 text-center whitespace-nowrap">
                            <span class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-3 py-1 text-xs font-extrabold text-emerald-800">
                                <Users class="h-3.5 w-3.5" /> {{ gl.anakAsuhCount }} Karyawan
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right whitespace-nowrap">
                            <div class="flex items-center justify-end gap-2">
                                <Link :href="`/one-power/employees/${gl.id}/anak-asuh`" class="flex items-center gap-1 rounded-md bg-slate-100 px-2.5 py-1.5 text-xs font-bold text-slate-700 hover:bg-slate-200 transition">
                                    <Users class="h-3.5 w-3.5" /> Lihat Anak Asuh
                                </Link>
                                <Link :href="`/one-power/employees/${gl.id}/select-anak-asuh`" class="flex items-center gap-1 rounded-md bg-emerald-600 px-2.5 py-1.5 text-xs font-bold text-white hover:bg-emerald-700 transition">
                                    <PlusCircle class="h-3.5 w-3.5" /> Tambah Anak Asuh
                                </Link>
                            </div>
                        </td>
                    </tr>
                    <tr v-if="gls.length === 0">
                        <td colspan="5" class="px-4 py-12 text-center text-slate-400">
                            Tidak ada Group Leader yang ditemukan.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Modal Import Sahabat Pengusaha -->
        <div v-if="importModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 p-4">
            <div class="w-full max-w-md rounded-xl bg-white p-6 shadow-xl">
                <div class="mb-4 flex items-center justify-between">
                    <h3 class="text-lg font-bold text-slate-800">Import Sahabat Pengusaha</h3>
                    <button type="button" class="text-slate-400 hover:text-slate-600" @click="importModalOpen = false">
                        <X class="h-5 w-5" />
                    </button>
                </div>
                <p class="mb-4 text-xs text-slate-500">
                    Unggah file Excel/CSV penugasan Sahabat Pengusaha (Kolom B: Anak Asuh, Kolom L: Bapak Asuh).
                </p>
                <form class="space-y-4" @submit.prevent="submitImport">
                    <div>
                        <label class="mb-1 block text-xs font-bold text-slate-600">File Spreadsheet (.xlsx, .xls, .csv)</label>
                        <input type="file" accept=".xlsx,.xls,.csv" class="w-full rounded-lg border border-slate-200 p-2 text-sm text-slate-600" @change="e => importForm.file = e.target.files[0]">
                    </div>
                    <div class="flex items-center gap-2">
                        <input id="reset_lama" v-model="importForm.reset_lama" type="checkbox" class="h-4 w-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                        <label for="reset_lama" class="text-xs text-slate-700">Reset pemetaan anak asuh lama sebelum impor</label>
                    </div>
                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" class="rounded-lg border border-slate-200 px-4 py-2 text-sm font-bold text-slate-600 hover:bg-slate-50" @click="importModalOpen = false">
                            Batal
                        </button>
                        <button type="submit" :disabled="importForm.processing || !importForm.file" class="flex items-center gap-1.5 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-bold text-white hover:bg-emerald-700 disabled:opacity-50">
                            <span>{{ importForm.processing ? 'Mengimpor...' : 'Proses Import' }}</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
