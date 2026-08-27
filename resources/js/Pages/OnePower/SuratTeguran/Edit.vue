<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import SuratTeguranForm from './Partials/SuratTeguranForm.vue';

const props = defineProps({
    user: { type: Object, required: true },
    notifCounts: { type: Object, required: true },
    menu: { type: Array, required: true },
    targetSuratTeguran: { type: Object, required: true },
});

const form = useForm({
    employee_id: props.targetSuratTeguran.employeeId,
    nrp: props.targetSuratTeguran.nrp,
    nama: props.targetSuratTeguran.nama,
    jabatan: props.targetSuratTeguran.jabatan ?? '',
    departemen: props.targetSuratTeguran.departemen ?? '',
    tanggal_kejadian: props.targetSuratTeguran.tanggalKejadian ?? '',
    tempat_kejadian: props.targetSuratTeguran.tempatKejadian ?? '',
    jenis_absen: props.targetSuratTeguran.jenisAbsen,
    jenis_perilaku: props.targetSuratTeguran.jenisPerilaku,
    jenis_ceroboh: props.targetSuratTeguran.jenisCeroboh,
    jenis_hasil_kerja: props.targetSuratTeguran.jenisHasilKerja,
    jenis_menolak_perintah: props.targetSuratTeguran.jenisMenolakPerintah,
    jenis_pelanggaran_peraturan: props.targetSuratTeguran.jenisPelanggaranPeraturan,
    penjelasan_singkat: props.targetSuratTeguran.penjelasanSingkat ?? '',
    kejadian_tanggal: props.targetSuratTeguran.kejadianTanggal ?? '',
    kejadian_jam: props.targetSuratTeguran.kejadianJam ?? '',
    kejadian_lokasi: props.targetSuratTeguran.kejadianLokasi ?? '',
    dikeluarkan_di: props.targetSuratTeguran.dikeluarkanDi ?? '',
    dikeluarkan_tanggal: props.targetSuratTeguran.dikeluarkanTanggal ?? '',
    ttd_karyawan: props.targetSuratTeguran.ttdKaryawan ?? '',
    ttd_atasan: props.targetSuratTeguran.ttdAtasan ?? '',
    ttd_atasan_atasan: props.targetSuratTeguran.ttdAtasanAtasan ?? '',
    catatan: props.targetSuratTeguran.catatan ?? '',
});

const submit = () => form.put(`/one-power/pembinaan/surat-teguran/${props.targetSuratTeguran.id}`);
</script>

<template>
    <Head title="Edit Surat Teguran - OneClick" />

    <AppLayout :user="user" :notif-counts="notifCounts" :menu="menu">
        <h1 class="mb-5 text-xl font-extrabold text-slate-800">Edit Surat Teguran</h1>
        <SuratTeguranForm :form="form" :on-submit="submit" />
    </AppLayout>
</template>
