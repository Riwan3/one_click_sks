<script setup>
import { ref } from 'vue';
import { Head } from '@inertiajs/vue3';
import { Files, Hourglass, PlayCircle, CheckCircle2 } from '@lucide/vue';
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
});

const rows = ref(props.submissions.map((row) => ({ ...row })));
const editingRow = ref(null);

const openEdit = (row) => {
    editingRow.value = row;
};

// TODO: kirim ke backend begitu logic update tahapan siap — untuk sekarang hanya mengubah state lokal.
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
        <div class="mb-6 rounded-3xl p-8 text-white shadow-lg" style="background: linear-gradient(135deg, #0f172a 0%, #1e40af 100%);">
            <h1 class="mb-1 text-2xl font-extrabold">Monitoring Training</h1>
            <p class="m-0 max-w-xl text-sm text-white/80">
                Pantau pengajuan pelatihan karyawan, status tahapan, dan progres peserta training.
            </p>
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
    </AppLayout>
</template>
