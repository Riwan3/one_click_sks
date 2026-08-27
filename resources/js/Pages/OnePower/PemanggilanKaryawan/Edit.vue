<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import PemanggilanKaryawanForm from './Partials/PemanggilanKaryawanForm.vue';

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
    alasan_pemanggilan: props.targetItem.alasanPemanggilan ?? '',
    tempat: props.targetItem.tempat ?? '',
    jam_pemanggilan: props.targetItem.jamPemanggilan ?? '',
    tanggal_pemanggilan: props.targetItem.tanggalPemanggilan ?? '',
    nama_manpower: props.targetItem.namaManpower ?? '',
    fatigue_periode: props.targetItem.fatiguePeriode ?? '',
    ttd_pemanggil: props.targetItem.ttdPemanggil ?? '',
    ttd_karyawan: props.targetItem.ttdKaryawan ?? '',
});

const submit = () => form.put(`/one-power/pembinaan/pemanggilan-karyawan/${props.targetItem.id}`);
</script>

<template>
    <Head title="Edit Pemanggilan Karyawan - OneClick" />

    <AppLayout :user="user" :notif-counts="notifCounts" :menu="menu">
        <h1 class="mb-5 text-xl font-extrabold text-slate-800">Edit Pemanggilan Karyawan</h1>
        <PemanggilanKaryawanForm :form="form" :on-submit="submit" />
    </AppLayout>
</template>
