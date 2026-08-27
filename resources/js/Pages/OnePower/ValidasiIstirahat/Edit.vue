<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import ValidasiIstirahatForm from './Partials/ValidasiIstirahatForm.vue';

const props = defineProps({
    user: { type: Object, required: true },
    notifCounts: { type: Object, required: true },
    menu: { type: Array, required: true },
    targetItem: { type: Object, required: true },
});

const form = useForm({
    employee_id: props.targetItem.employeeId,
    nrp: props.targetItem.nrp,
    nama: props.targetItem.nama,
    jabatan: props.targetItem.jabatan ?? '',
    departemen: props.targetItem.departemen ?? '',
    alasan_kurang_tidur: props.targetItem.alasanKurangTidur ?? '',
    alasan_kurang_tidur_berulang: props.targetItem.alasanKurangTidurBerulang ?? '',
    kendala_tidur: props.targetItem.kendalaTidur ?? '',
    tinggal_di: props.targetItem.tinggalDi ?? 'Non Mess',
    nama_mess: props.targetItem.namaMess ?? '',
    alamat_non_mess: props.targetItem.alamatNonMess ?? '',
    status_perkawinan: props.targetItem.statusPerkawinan ?? '',
    jumlah_anak_serumah: props.targetItem.jumlahAnakSerumah ?? '',
    anak_usia_kurang_2th: props.targetItem.anakUsiaKurang2th ?? '',
    pekerjaan_sampingan: props.targetItem.pekerjaanSampingan ?? '',
    keterangan: props.targetItem.keterangan ?? '',
    tanggal_validasi: props.targetItem.tanggalValidasi ?? '',
    nama_pengawas: props.targetItem.namaPengawas ?? '',
    ttd_karyawan: props.targetItem.ttdKaryawan ?? '',
    ttd_pengawas: props.targetItem.ttdPengawas ?? '',
});

const submit = () => form.put(`/one-power/pembinaan/validasi-istirahat/${props.targetItem.id}`);
</script>

<template>
    <Head title="Edit Validasi Istirahat - OneClick" />

    <AppLayout :user="user" :notif-counts="notifCounts" :menu="menu">
        <h1 class="mb-5 text-xl font-extrabold text-slate-800">Edit Validasi Istirahat</h1>
        <ValidasiIstirahatForm :form="form" :on-submit="submit" />
    </AppLayout>
</template>
