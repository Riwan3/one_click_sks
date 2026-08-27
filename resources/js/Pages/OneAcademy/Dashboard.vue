<script setup>
import { ref } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { Files, Hourglass, PlayCircle, CheckCircle2, Plus, FileText, Trash2 } from 'lucide-vue-next';
import AppLayout from '@/Layouts/AppLayout.vue';
import StatCard from './Partials/StatCard.vue';
import FilterBar from './Partials/FilterBar.vue';
import TrainingTable from './Partials/TrainingTable.vue';
import UpdateTahapanModal from './Partials/UpdateTahapanModal.vue';

const props = defineProps({
    user: { type: Object, required: true },
    notifCounts: { type: Object, required: true },
    menu: { type: Array, required: true },
    stats: { type: Object, required: true },
    filters: { type: Object, required: true },
    submissions: { type: Array, required: true },
    jenisOptions: { type: Array, default: () => [] },
    approvers: { type: Object, default: () => ({ sectionHeads: [], deptHeads: [], pjos: [], instructors: [] }) },
});

const rows = ref(props.submissions.map((row) => ({ ...row })));
const editingRow = ref(null);
const showAddModal = ref(false);

const form = useForm({
    jenis_pengajuan: 'Fresh Training',
    type_unit: '',
    diajukan_nama: '',
    diajukan_nik: '',
    diperiksa_nama: '',
    diperiksa_nik: '',
    disetujui_nama: '',
    disetujui_nik: '',
    diterima_nama: '',
    diterima_nik: '',
    peserta: [
        { nama: '', nrp: '', nik: '', no_hp: '', jabatan: '' },
        { nama: '', nrp: '', nik: '', no_hp: '', jabatan: '' },
        { nama: '', nrp: '', nik: '', no_hp: '', jabatan: '' },
    ]
});

const addPesertaRow = () => {
    form.peserta.push({ nama: '', nrp: '', nik: '', no_hp: '', jabatan: '' });
};

const removePesertaRow = (idx) => {
    if (form.peserta.length > 1) {
        form.peserta.splice(idx, 1);
    }
};

const handleSelectApprover = (role, event) => {
    const val = event.target.value;
    if (!val) return;
    const [nama, nik] = val.split('|');
    form[`${role}_nama`] = nama || '';
    form[`${role}_nik`] = nik || '';
};

const submitPengajuan = () => {
    form.post('/one-academy/store-pengajuan', {
        onSuccess: () => {
            showAddModal.value = false;
            form.reset();
        }
    });
};

const openEdit = (row) => {
    editingRow.value = row;
};

const saveEdit = ({ id, tahapan, catatan }) => {
    const target = rows.value.find((row) => row.id === id);
    if (target) {
        target.tahapan = tahapan;
        target.catatan = catatan;
    }
    editingRow.value = null;
};
</script>

<template>
    <Head title="One-Academy - OneClick" />

    <AppLayout :user="user" :notif-counts="notifCounts" :menu="menu">
        <div class="mb-6 rounded-3xl p-8 text-white shadow-lg flex justify-between items-center flex-wrap gap-4" style="background: linear-gradient(135deg, #0f172a 0%, #1e40af 100%);">
            <div>
                <h1 class="mb-1 text-2xl font-extrabold">Monitoring Training</h1>
                <p class="m-0 max-w-xl text-sm text-white/80">
                    Pantau pengajuan pelatihan karyawan, status tahapan, dan progres peserta training.
                </p>
            </div>
            <div class="flex gap-2">
                <button @click="showAddModal = true" class="btn bg-blue-500 hover:bg-blue-400 text-white font-bold px-4 py-2.5 rounded-xl flex items-center gap-2 shadow-lg transition text-xs">
                    <Plus class="w-4 h-4" /> Input Pengajuan Pelatihan
                </button>
            </div>
        </div>

        <div class="mb-5 grid grid-cols-2 gap-3 lg:grid-cols-4">
            <StatCard label="Total Pengajuan" :value="stats.total" :icon="Files" color="primary" />
            <StatCard label="Pengajuan Baru" :value="stats.baru" :icon="Hourglass" color="secondary" />
            <StatCard label="Sedang Berjalan" :value="stats.berlangsung" :icon="PlayCircle" color="warning" />
            <StatCard label="Selesai" :value="stats.selesai" :icon="CheckCircle2" color="success" />
        </div>

        <div class="mb-5">
            <FilterBar :filters="filters" />
        </div>

        <TrainingTable :submissions="rows" @edit="openEdit" />

        <UpdateTahapanModal :row="editingRow" :tahapan-options="filters.tahapanOptions" @close="editingRow = null" @save="saveEdit" />

        <!-- Modal Form Input Pengajuan Pelatihan -->
        <div v-if="showAddModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
            <div class="bg-white dark:bg-slate-900 rounded-2xl max-w-4xl w-full p-6 space-y-6 shadow-2xl border border-slate-200 dark:border-slate-800 max-h-[90vh] overflow-y-auto">
                <div class="flex justify-between items-center border-b pb-3">
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white">Form Pengajuan Pelatihan & Groundtest</h3>
                    <button @click="showAddModal = false" class="text-slate-400 hover:text-slate-600">&times;</button>
                </div>

                <form @submit.prevent="submitPengajuan" class="space-y-6 text-xs">
                    <!-- Detail Pengajuan -->
                    <div class="space-y-3">
                        <h4 class="font-bold text-slate-800 dark:text-slate-200 border-b pb-1">Detail Pengajuan</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block font-semibold mb-1">Jenis Pengajuan *</label>
                                <select v-model="form.jenis_pengajuan" class="w-full border rounded-xl p-2.5">
                                    <option v-for="opt in jenisOptions" :key="opt" :value="opt">{{ opt }}</option>
                                </select>
                            </div>
                            <div>
                                <label class="block font-semibold mb-1">Type Unit / Program *</label>
                                <input v-model="form.type_unit" placeholder="Contoh: Operator Development Program (ODP) PROFICIENT 03" required class="w-full border rounded-xl p-2.5" />
                            </div>
                        </div>
                    </div>

                    <!-- Data Peserta -->
                    <div class="space-y-3">
                        <div class="flex justify-between items-center border-b pb-1">
                            <h4 class="font-bold text-slate-800 dark:text-slate-200">Data Peserta Pelatihan</h4>
                            <button type="button" @click="addPesertaRow" class="text-blue-600 font-bold hover:underline">+ Tambah Peserta</button>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-left">
                                <thead class="bg-slate-100 dark:bg-slate-800 font-semibold">
                                    <tr>
                                        <th class="p-2">No</th>
                                        <th class="p-2">Nama</th>
                                        <th class="p-2">NRP</th>
                                        <th class="p-2">NIK KTP</th>
                                        <th class="p-2">Jabatan</th>
                                        <th class="p-2 text-center">Hapus</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="(p, idx) in form.peserta" :key="idx" class="border-b">
                                        <td class="p-2 font-bold">{{ idx + 1 }}</td>
                                        <td class="p-2"><input v-model="p.nama" placeholder="Nama Peserta *" required class="w-full border rounded-lg p-1.5" /></td>
                                        <td class="p-2"><input v-model="p.nrp" placeholder="NRP" class="w-full border rounded-lg p-1.5" /></td>
                                        <td class="p-2"><input v-model="p.nik" placeholder="NIK KTP" class="w-full border rounded-lg p-1.5" /></td>
                                        <td class="p-2"><input v-model="p.jabatan" placeholder="Jabatan" class="w-full border rounded-lg p-1.5" /></td>
                                        <td class="p-2 text-center">
                                            <button type="button" @click="removePesertaRow(idx)" class="text-rose-600 hover:text-rose-800">
                                                <Trash2 class="w-4 h-4 mx-auto" />
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Approval Section -->
                    <div class="space-y-3">
                        <h4 class="font-bold text-slate-800 dark:text-slate-200 border-b pb-1">Hirarki Approval</h4>
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
                            <div>
                                <label class="block font-semibold mb-1">Diajukan (Sect. Head)</label>
                                <select @change="handleSelectApprover('diajukan', $event)" class="w-full border rounded-xl p-2" required>
                                    <option value="">Pilih Sect. Head...</option>
                                    <option v-for="sh in approvers.sectionHeads" :key="sh.nrp" :value="`${sh.name}|${sh.nrp}`">{{ sh.name }}</option>
                                </select>
                            </div>
                            <div>
                                <label class="block font-semibold mb-1">Diperiksa (Dept. Head)</label>
                                <select @change="handleSelectApprover('diperiksa', $event)" class="w-full border rounded-xl p-2" required>
                                    <option value="">Pilih Dept. Head...</option>
                                    <option v-for="dh in approvers.deptHeads" :key="dh.nrp" :value="`${dh.name}|${dh.nrp}`">{{ dh.name }}</option>
                                </select>
                            </div>
                            <div>
                                <label class="block font-semibold mb-1">Disetujui (PJO)</label>
                                <select @change="handleSelectApprover('disetujui', $event)" class="w-full border rounded-xl p-2" required>
                                    <option value="">Pilih PJO...</option>
                                    <option v-for="pjo in approvers.pjos" :key="pjo.nrp" :value="`${pjo.name}|${pjo.nrp}`">{{ pjo.name }}</option>
                                </select>
                            </div>
                            <div>
                                <label class="block font-semibold mb-1">Diterima (OPD Training)</label>
                                <select @change="handleSelectApprover('diterima', $event)" class="w-full border rounded-xl p-2" required>
                                    <option value="">Pilih Instruktur...</option>
                                    <option v-for="inst in approvers.instructors" :key="inst.nrp" :value="`${inst.name}|${inst.nrp}`">{{ inst.name }}</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end gap-2 pt-4 border-t">
                        <button type="button" @click="showAddModal = false" class="px-4 py-2 text-xs rounded-xl bg-slate-200 dark:bg-slate-800 font-semibold">Batal</button>
                        <button type="submit" class="px-5 py-2 text-xs rounded-xl bg-blue-600 text-white font-bold shadow">Simpan Pengajuan</button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
