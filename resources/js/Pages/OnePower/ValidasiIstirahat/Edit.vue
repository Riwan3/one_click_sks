<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft } from '@lucide/vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import ValidasiIstirahatForm from './Partials/ValidasiIstirahatForm.vue';

const props = defineProps({
    user: { type: Object, required: true },
    notifCounts: { type: Object, required: true },
    menu: { type: Array, required: true },
    targetItem: { type: Object, required: true },
    documentSettings: { type: Object, default: () => ({}) },
});

const form = useForm({
    employee_id: props.targetItem.employeeId,
    nrp: props.targetItem.nrp,
    nama: props.targetItem.nama,
    jabatan: props.targetItem.jabatan ?? '',
    departemen: props.targetItem.departemen ?? '',
    no_surat: props.targetItem.noSurat ?? '',
    doc_rank: props.targetItem.docRank ?? null,
    alasan_kurang_tidur: props.targetItem.alasanKurangTidur ?? '',
    alasan_kurang_tidur_berulang: props.targetItem.alasanKurangTidurBerulang ?? '',
    kendala_tidur: props.targetItem.kendalaTidur ?? '',
    tinggal_di: props.targetItem.tinggalDi ?? 'Non Mess',
    nama_mess: props.targetItem.namaMess ?? '',
    alamat_non_mess: props.targetItem.alamatNonMess ?? '',
    status_perkawinan: props.targetItem.statusPerkawinan ?? 'Single',
    jumlah_anak_serumah: props.targetItem.jumlahAnakSerumah ?? '',
    anak_usia_kurang_2th: props.targetItem.anakUsiaKurang2th ?? '',
    pekerjaan_sampingan: props.targetItem.pekerjaanSampingan ?? '',
    keterangan: props.targetItem.keterangan ?? '',
    tanggal_validasi: props.targetItem.tanggalValidasi ?? '',
    nama_pengawas: props.targetItem.namaPengawas ?? '',
    ttd_karyawan: props.targetItem.ttdKaryawan ?? '',
    ttd_pengawas: props.targetItem.ttdPengawas ?? '',
    foto: null,
    existing_foto: props.targetItem.foto ?? null,
    hapus_foto: false,
});

const submit = () => form.transform((data) => ({
    ...data,
    _method: 'put',
})).post(`/one-power/pembinaan/validasi-istirahat/${props.targetItem.id}`, {
    forceFormData: true,
});
</script>

<template>
    <Head title="Edit Validasi Istirahat - OneClick" />

    <AppLayout :user="user" :notif-counts="notifCounts" :menu="menu">
        <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <Link
                    href="/one-power/pembinaan/validasi-istirahat"
                    class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-bold text-slate-700 shadow-2xs hover:bg-slate-50 transition"
                >
                    <ArrowLeft class="h-4 w-4" />
                    Kembali
                </Link>
                <h1 class="text-xl font-extrabold text-slate-800">Edit Validasi Istirahat</h1>
            </div>
        </div>
        <ValidasiIstirahatForm
            :form="form"
            :on-submit="submit"
            :is-edit="true"
            :document-settings="documentSettings"
        />
    </AppLayout>
</template>
