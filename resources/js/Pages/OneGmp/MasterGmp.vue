<script setup>
import { confirmDialog } from '@/utils/confirm';
import { ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import { Database, Plus, Trash2 } from 'lucide-vue-next';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    user: { type: Object, required: true },
    notifCounts: { type: Object, required: true },
    menu: { type: Array, required: true },
    type: { type: String, required: true },
    locations: { type: Array, default: () => [] },
});

const currentType = ref(props.type);
const showAddModal = ref(false);
const newLocationName = ref('');

const handleTypeChange = () => {
    router.get('/one-gmp/master', { type: currentType.value }, { preserveState: true });
};

const handleAddLocation = () => {
    if (!newLocationName.value.trim()) return;
    router.post('/one-gmp/master/store', {
        tipe_gmp: currentType.value,
        nama_lokasi: newLocationName.value,
    }, {
        onSuccess: () => {
            showAddModal.value = false;
            newLocationName.value = '';
        },
    });
};

const handleDeleteLocation = async (id) => {
    if (await confirmDialog('Hapus master lokasi ini?')) {
        router.delete(`/one-gmp/master/delete/${id}`);
    }
};
</script>

<template>
    <Head title="Master Data GMP - OneClick" />

    <AppLayout :user="user" :notif-counts="notifCounts" :menu="menu">
        <!-- Banner Header -->
        <div class="mb-6 rounded-3xl p-6 text-white shadow-md flex justify-between items-center flex-wrap gap-4" style="background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%);">
            <div>
                <h1 class="mb-1 text-2xl font-extrabold flex items-center gap-3">
                    <Database class="w-8 h-8 text-sky-200" />
                    Master Data Lokasi GMP
                </h1>
                <p class="m-0 text-sm text-white/90">
                    Pengelolaan daftar opsi lokasi inspeksi Good Mining Practice per kategori area.
                </p>
            </div>
            <button @click="showAddModal = true" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold rounded-2xl text-xs shadow flex items-center gap-2">
                <Plus class="w-4 h-4" /> Tambah Master Lokasi
            </button>
        </div>

        <!-- Category Selector Filter Bar -->
        <div class="mb-6 bg-white p-4 rounded-2xl border border-slate-200 shadow-sm flex items-center gap-4">
            <label class="text-xs font-bold text-slate-600 uppercase">Kategori Area:</label>
            <select v-model="currentType" @change="handleTypeChange" class="border border-slate-300 rounded-xl px-3 py-2 text-xs bg-slate-50 text-slate-800 font-bold outline-none focus:border-sky-500">
                <option value="MHR">MHR (Mining Haul Road)</option>
                <option value="MFOB">MFOB (Mine Front OB)</option>
                <option value="MFCO">MFCO (Mine Front Coal)</option>
                <option value="DISPOSAL">DISPOSAL</option>
                <option value="DEWATERING">DEWATERING</option>
            </select>
        </div>

        <!-- Master Locations List Table -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-100 font-bold text-slate-700">
                        <tr>
                            <th class="p-3">#</th>
                            <th class="p-3">Kategori Tipe</th>
                            <th class="p-3">Nama Lokasi / Equipment</th>
                            <th class="p-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr v-if="locations.length === 0">
                            <td colspan="4" class="p-6 text-center text-slate-400">Belum ada master lokasi terdaftar untuk kategori {{ currentType }}.</td>
                        </tr>
                        <tr v-for="(loc, idx) in locations" :key="loc.id" class="hover:bg-slate-50">
                            <td class="p-3 font-mono text-slate-400">{{ idx + 1 }}</td>
                            <td class="p-3 font-bold text-slate-500">{{ loc.tipe_gmp }}</td>
                            <td class="p-3 font-extrabold text-slate-900">{{ loc.nama_lokasi }}</td>
                            <td class="p-3 text-right">
                                <button @click="handleDeleteLocation(loc.id)" class="p-1.5 text-rose-600 hover:bg-rose-50 rounded-lg">
                                    <Trash2 class="w-4 h-4" />
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Add Master Location Modal -->
        <div v-if="showAddModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
            <div class="w-full max-w-md bg-white rounded-2xl p-6 shadow-xl border border-slate-200">
                <h3 class="text-base font-extrabold text-slate-900 mb-3">Tambah Master Lokasi ({{ currentType }})</h3>
                <input v-model="newLocationName" type="text" placeholder="Nama lokasi atau unit equipment..." required class="w-full border border-slate-300 rounded-xl p-3 text-xs bg-slate-50 mb-4 text-slate-800 outline-none focus:border-sky-500" />
                <div class="flex justify-end gap-2">
                    <button @click="showAddModal = false" class="px-4 py-2 text-xs font-bold text-slate-500 hover:bg-slate-100 rounded-xl">Batal</button>
                    <button @click="handleAddLocation" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow">Simpan</button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
