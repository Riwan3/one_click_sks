<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import ValidasiIstirahatForm from './Partials/ValidasiIstirahatForm.vue';

defineProps({
    user: { type: Object, required: true },
    notifCounts: { type: Object, required: true },
    menu: { type: Array, required: true },
});

const form = useForm({
    employee_id: null,
    nrp: '',
    nama: '',
    jabatan: '',
    departemen: '',
    alasan_kurang_tidur: '',
    alasan_kurang_tidur_berulang: '',
    kendala_tidur: '',
    tinggal_di: 'Non Mess',
    nama_mess: '',
    alamat_non_mess: '',
    status_perkawinan: '',
    jumlah_anak_serumah: '',
    anak_usia_kurang_2th: '',
    pekerjaan_sampingan: '',
    keterangan: '',
    tanggal_validasi: '',
    nama_pengawas: '',
    ttd_karyawan: '',
    ttd_pengawas: '',
});

const submit = () => form.post('/one-power/pembinaan/validasi-istirahat');
</script>

<template>
    <Head title="Buat Validasi Istirahat - OneClick" />

    <AppLayout :user="user" :notif-counts="notifCounts" :menu="menu">
        <h1 class="mb-5 text-xl font-extrabold text-slate-800">Buat Validasi Istirahat</h1>
        <ValidasiIstirahatForm :form="form" :on-submit="submit" />
    </AppLayout>
</template>
