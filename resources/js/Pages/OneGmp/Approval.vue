<script setup>
import { ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import { CheckSquare, CheckCircle2, XCircle, RotateCcw } from 'lucide-vue-next';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    user: { type: Object, required: true },
    notifCounts: { type: Object, required: true },
    menu: { type: Array, required: true },
    pendingForms: { type: Object, required: true },
});

const rejectModalId = ref(null);
const rejectionNote = ref('');

const returnModalId = ref(null);
const revisionNote = ref('');

const handleApprove = (id) => {
    if (confirm('Setujui form penilaian GMP ini?')) {
        router.post(`/one-gmp/approval/approve/${id}`);
    }
};

const handleRejectSubmit = () => {
    router.post(`/one-gmp/approval/reject/${rejectModalId.value}`, {
        rejection_note: rejectionNote.value,
    }, {
        onSuccess: () => {
            rejectModalId.value = null;
            rejectionNote.value = '';
        },
    });
};

const handleReturnSubmit = () => {
    router.post(`/one-gmp/approval/return/${returnModalId.value}`, {
        revision_note: revisionNote.value,
    }, {
        onSuccess: () => {
            returnModalId.value = null;
            revisionNote.value = '';
        },
    });
};
</script>

<template>
    <Head title="Approval GMP - OneClick" />

    <AppLayout :user="user" :notif-counts="notifCounts" :menu="menu">
        <div class="mb-6 rounded-3xl p-6 text-white shadow-md flex justify-between items-center flex-wrap gap-4" style="background: linear-gradient(135deg, #15803d 0%, #166534 100%);">
            <div>
                <h1 class="mb-1 text-2xl font-extrabold flex items-center gap-3">
                    <CheckSquare class="w-8 h-8 text-emerald-200" />
                    Approval Penilaian GMP
                </h1>
                <p class="m-0 text-sm text-white/90">
                    Persetujuan inspeksi Good Mining Practice oleh Section Head & Dept Head Produksi.
                </p>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-100 font-bold text-slate-700">
                        <tr>
                            <th class="p-3">Tanggal</th>
                            <th class="p-3">Kategori</th>
                            <th class="p-3">Lokasi / Equipment</th>
                            <th class="p-3">Penilai (NRP)</th>
                            <th class="p-3 text-center">Skor (%)</th>
                            <th class="p-3 text-right">Aksi Approval</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr v-if="pendingForms.data.length === 0">
                            <td colspan="6" class="p-6 text-center text-slate-400">Tidak ada form penilaian GMP yang menunggu persetujuan saat ini.</td>
                        </tr>
                        <tr v-for="f in pendingForms.data" :key="f.id" class="hover:bg-slate-50">
                            <td class="p-3 font-semibold text-slate-800">{{ f.tanggal_gmp }}</td>
                            <td class="p-3 font-bold text-emerald-600">{{ f.tipe_gmp }}</td>
                            <td class="p-3 font-bold text-slate-900">{{ f.lokasi }}</td>
                            <td class="p-3 text-slate-600">{{ f.penilai }} <span class="text-slate-400">({{ f.nrp }})</span></td>
                            <td class="p-3 text-center font-black text-blue-600">{{ f.total_percent }}%</td>
                            <td class="p-3 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <button @click="handleApprove(f.id)" class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl flex items-center gap-1 shadow">
                                        <CheckCircle2 class="w-3.5 h-3.5" /> Approve
                                    </button>
                                    <button @click="returnModalId = f.id" class="px-3 py-1.5 bg-purple-600 hover:bg-purple-700 text-white font-bold rounded-xl flex items-center gap-1 shadow">
                                        <RotateCcw class="w-3.5 h-3.5" /> Revisi
                                    </button>
                                    <button @click="rejectModalId = f.id" class="px-3 py-1.5 bg-rose-600 hover:bg-rose-700 text-white font-bold rounded-xl flex items-center gap-1 shadow">
                                        <XCircle class="w-3.5 h-3.5" /> Tolak
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Reject Modal -->
        <div v-if="rejectModalId" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
            <div class="w-full max-w-md bg-white rounded-2xl p-6 shadow-xl border border-slate-200">
                <h3 class="text-base font-extrabold text-slate-900 mb-3">Alasan Penolakan</h3>
                <textarea v-model="rejectionNote" rows="3" placeholder="Ketikkan catatan alasan penolakan..." required class="w-full border border-slate-300 rounded-xl p-3 text-xs bg-slate-50 mb-4 text-slate-800 outline-none focus:border-rose-500"></textarea>
                <div class="flex justify-end gap-2">
                    <button @click="rejectModalId = null" class="px-4 py-2 text-xs font-bold text-slate-500 hover:bg-slate-100 rounded-xl">Batal</button>
                    <button @click="handleRejectSubmit" class="px-4 py-2 bg-rose-600 text-white text-xs font-bold rounded-xl shadow">Kirim Penolakan</button>
                </div>
            </div>
        </div>

        <!-- Revision Modal -->
        <div v-if="returnModalId" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
            <div class="w-full max-w-md bg-white rounded-2xl p-6 shadow-xl border border-slate-200">
                <h3 class="text-base font-extrabold text-slate-900 mb-3">Catatan Revisi</h3>
                <textarea v-model="revisionNote" rows="3" placeholder="Ketikkan hal yang perlu diperbaiki..." required class="w-full border rounded-xl p-3 text-xs bg-slate-50 mb-4 text-slate-800 outline-none focus:border-purple-500"></textarea>
                <div class="flex justify-end gap-2">
                    <button @click="returnModalId = null" class="px-4 py-2 text-xs font-bold text-slate-500 hover:bg-slate-100 rounded-xl">Batal</button>
                    <button @click="handleReturnSubmit" class="px-4 py-2 bg-purple-600 text-white text-xs font-bold rounded-xl shadow">Kirim Revisi</button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
