<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import PemulanganKaryawanForm from './Partials/PemulanganKaryawanForm.vue';

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
    lokasi: props.targetItem.lokasi ?? '',
    tanggal: props.targetItem.tanggal ?? '',
    indikasi_fatigue: props.targetItem.indikasiFatigue ?? '',
    jam_tidur_kurang_4jam: props.targetItem.jamTidurKurang4jam,
    fatigue_berat: props.targetItem.fatigueBerat,
    deskripsi_penyebab: props.targetItem.deskripsiPenyebab ?? '',
    alamat_pengantaran: props.targetItem.alamatPengantaran ?? '',
    penanggungjawab_pengantaran: props.targetItem.penanggungjawabPengantaran ?? '',
    ttd_karyawan: props.targetItem.ttdKaryawan ?? '',
    ttd_pengawas: props.targetItem.ttdPengawas ?? '',
});

const submit = () => form.put(`/one-power/pembinaan/pemulangan-karyawan/${props.targetItem.id}`);
</script>

<template>
    <Head title="Edit Pemulangan Karyawan - OneClick" />

    <AppLayout :user="user" :notif-counts="notifCounts" :menu="menu">
        <h1 class="mb-5 text-xl font-extrabold text-slate-800">Edit Pemulangan Karyawan</h1>
        <PemulanganKaryawanForm :form="form" :on-submit="submit" />
    </AppLayout>
</template>
