<script setup>
import { Head, useForm, Link, router } from '@inertiajs/vue3';
import { ArrowLeft, Info, Check, X, RotateCcw, Ban } from '@lucide/vue';
import { confirmDialog } from '@/utils/confirm';
import AppLayout from '@/Layouts/AppLayout.vue';
import IzinKaryawanForm from './Partials/IzinKaryawanForm.vue';

const props = defineProps({
    user: { type: Object, required: true },
    notifCounts: { type: Object, required: true },
    menu: { type: Array, required: true },
    targetIzin: { type: Object, required: true },
    isAdmin: { type: Boolean, default: false },
    isMedic: { type: Boolean, default: false },
    isAdminOrManpower: { type: Boolean, default: false },
});

const form = useForm({
    jenis_izin: props.targetIzin.jenisIzin,
    no_surat: props.targetIzin.noSurat || '',
    nama: props.targetIzin.nama,
    nrp: props.targetIzin.nrp,
    jabatan: props.targetIzin.jabatan ?? '',
    departemen: props.targetIzin.departemen ?? '',
    alasan: props.targetIzin.alasan ?? '',
    tujuan: props.targetIzin.tujuan ?? '',
    alamat: props.targetIzin.alamat ?? '',
    tgl_berangkat: props.targetIzin.tglBerangkat ?? '',
    tgl_kembali: props.targetIzin.tglKembali ?? '',
    no_telepon: props.targetIzin.noTelepon ?? '',
    keluhan: props.targetIzin.keluhan ?? '',
    hasil_td: props.targetIzin.hasilTd ?? '',
    hasil_rr: props.targetIzin.hasilRr ?? '',
    hasil_t: props.targetIzin.hasilT ?? '',
    hasil_p: props.targetIzin.hasilP ?? '',
    hasil_spo: props.targetIzin.hasilSpo ?? '',
    diagnosa: props.targetIzin.diagnosa ?? '',
    terapi: props.targetIzin.terapi ?? '',
    keterangan_status: props.targetIzin.keteranganStatus ?? 1,
    rujuk_ke: props.targetIzin.rujukKe ?? '',
    rumah_sakit_rujukan: props.targetIzin.rumahSakitRujukan ?? '',
    dokter_rujukan: props.targetIzin.dokterRujukan ?? '',
    dipulangkan_tidur: props.targetIzin.dipulangkanTidur ?? false,
    file_bukti: null,
});

const pageTitle = () => {
    if (props.targetIzin.jenisIzin === 'izin') return 'Surat Izin';
    if (props.targetIzin.jenisIzin === 'emergency') return 'Emergency';
    return 'Surat Sakit';
};

const statusBadge = {
    'Awaiting Approval': 'bg-amber-100 text-amber-800 border border-amber-200',
    Approved: 'bg-emerald-100 text-emerald-800 border border-emerald-200',
    Declined: 'bg-red-100 text-red-800 border border-red-200',
    Cancelled: 'bg-slate-200 text-slate-700 border border-slate-300',
};

const statusText = {
    'Awaiting Approval': 'Menunggu Persetujuan',
    Approved: 'Disetujui',
    Declined: 'Ditolak',
    Cancelled: 'Dibatalkan',
};

const submit = () => form
    .transform((data) => ({ ...data, _method: 'put' }))
    .post(`/one-power/deviasi-operator/izin-karyawan/${props.targetIzin.id}`);

const doAction = async (action, confirmMsg) => {
    if (confirmMsg && ! await confirmDialog(confirmMsg)) return;
    router.post(`/one-power/deviasi-operator/izin-karyawan/${props.targetIzin.id}/${action}`, {}, { preserveScroll: true });
};

const handleApprove = () => doAction('approve', `Setujui pengajuan izin "${props.targetIzin.nama}"?`);
const handleDecline = () => doAction('decline', `Tolak pengajuan izin "${props.targetIzin.nama}"?`);
const handleReset = () => doAction('reset', `Kembalikan status ke Menunggu Persetujuan?`);
const handleCancel = () => doAction('cancel', `Yakin ingin membatalkan pengajuan ini?`);
</script>

<template>
    <Head :title="`${pageTitle()} - ${targetIzin.nama} - OneClick`" />

    <AppLayout :user="user" :notif-counts="notifCounts" :menu="menu">
        <!-- HEADER (V1 style) -->
        <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-3">
                <Link
                    href="/one-power/deviasi-operator/izin-karyawan"
                    class="inline-flex h-9 w-9 items-center justify-center rounded-full border border-slate-200 bg-white text-slate-600 hover:bg-slate-100 transition shadow-xs"
                >
                    <ArrowLeft class="h-4 w-4" />
                </Link>
                <div>
                    <h1 class="text-xl font-extrabold text-slate-800">{{ pageTitle() }}</h1>
                    <p class="text-xs text-slate-500">{{ targetIzin.nama }} ({{ targetIzin.nrp }})</p>
                </div>
            </div>

            <!-- Quick Action Buttons on Header -->
            <div class="flex flex-wrap items-center gap-2">
                <template v-if="targetIzin.status === 'Awaiting Approval'">
                    <button
                        v-if="isAdmin"
                        type="button"
                        class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-600 px-3.5 py-1.5 text-xs font-bold text-white shadow-xs hover:bg-emerald-700 transition"
                        @click="handleApprove"
                    >
                        <Check class="h-3.5 w-3.5" /> Setujui
                    </button>
                    <button
                        v-if="isAdmin"
                        type="button"
                        class="inline-flex items-center gap-1.5 rounded-lg bg-red-600 px-3.5 py-1.5 text-xs font-bold text-white shadow-xs hover:bg-red-700 transition"
                        @click="handleDecline"
                    >
                        <X class="h-3.5 w-3.5" /> Tolak
                    </button>
                    <button
                        v-if="isAdmin || targetIzin.isOwner"
                        type="button"
                        class="inline-flex items-center gap-1.5 rounded-lg bg-amber-500 px-3.5 py-1.5 text-xs font-bold text-white shadow-xs hover:bg-amber-600 transition"
                        @click="handleCancel"
                    >
                        <Ban class="h-3.5 w-3.5" /> Batalkan
                    </button>
                </template>
                <button
                    v-if="targetIzin.status === 'Declined' && isAdmin"
                    type="button"
                    class="inline-flex items-center gap-1.5 rounded-lg border border-slate-300 bg-white px-3.5 py-1.5 text-xs font-bold text-slate-700 hover:bg-slate-50 transition"
                    @click="handleReset"
                >
                    <RotateCcw class="h-3.5 w-3.5" /> Reset ke Menunggu
                </button>
            </div>
        </div>

        <!-- STATUS CARD / BANNER (V1 style) -->
        <div class="mb-5 flex items-center justify-between rounded-xl border border-blue-200 bg-blue-50/80 px-4 py-3 shadow-xs">
            <div class="flex items-center gap-2 text-xs text-blue-900 font-medium">
                <Info class="h-4 w-4 text-blue-600 shrink-0" />
                <span><strong>Status:</strong></span>
                <span :class="['rounded px-2.5 py-0.5 text-xs font-bold', statusBadge[targetIzin.status] ?? 'bg-slate-100 text-slate-600']">
                    {{ statusText[targetIzin.status] || targetIzin.statusLabel }}
                </span>
                <span v-if="targetIzin.approverName && (targetIzin.status === 'Approved' || targetIzin.status === 'Declined')" class="text-slate-600 text-xs ml-2">
                    ({{ targetIzin.status === 'Approved' ? 'Disetujui' : 'Ditolak' }} oleh <strong>{{ targetIzin.approverName }}</strong><template v-if="targetIzin.approvedAt"> pada {{ targetIzin.approvedAt }}</template>)
                </span>
            </div>
        </div>

        <div class="max-w-3xl">
            <IzinKaryawanForm
                :form="form"
                :is-edit="true"
                :existing-bukti-url="targetIzin.buktiUrl"
                :existing-bukti-file-name="targetIzin.buktiFileName"
                :status="targetIzin.status"
                :is-medic="isMedic"
                :is-admin-or-manpower="isAdminOrManpower"
                :on-submit="submit"
            />
        </div>
    </AppLayout>
</template>
