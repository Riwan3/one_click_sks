<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import IzinKaryawanForm from './Partials/IzinKaryawanForm.vue';

const props = defineProps({
    user: { type: Object, required: true },
    notifCounts: { type: Object, required: true },
    menu: { type: Array, required: true },
    targetIzin: { type: Object, required: true },
    isMedic: { type: Boolean, default: false },
});

const form = useForm({
    jenis_izin: props.targetIzin.jenisIzin,
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

const submit = () => form
    .transform((data) => ({ ...data, _method: 'put' }))
    .post(`/one-power/deviasi-operator/izin-karyawan/${props.targetIzin.id}`);
</script>

<template>
    <Head title="Edit Izin Karyawan - OneClick" />

    <AppLayout :user="user" :notif-counts="notifCounts" :menu="menu">
        <h1 class="mb-5 text-xl font-extrabold text-slate-800">Edit Data Izin Karyawan</h1>
        <IzinKaryawanForm :form="form" :existing-bukti-url="targetIzin.buktiUrl" :on-submit="submit" :is-medic="isMedic" />
    </AppLayout>
</template>
