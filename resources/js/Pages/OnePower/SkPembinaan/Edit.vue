<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import SkPembinaanForm from './Partials/SkPembinaanForm.vue';

const props = defineProps({
    user: { type: Object, required: true },
    notifCounts: { type: Object, required: true },
    menu: { type: Array, required: true },
    targetSkPembinaan: { type: Object, required: true },
});

const form = useForm({
    employee_id: props.targetSkPembinaan.employeeId,
    nama: props.targetSkPembinaan.nama,
    perusahaan: props.targetSkPembinaan.perusahaan,
    tanggal_pelanggaran: props.targetSkPembinaan.tanggalPelanggaran ?? '',
    jam_pelanggaran: props.targetSkPembinaan.jamPelanggaran ?? '',
    lokasi: props.targetSkPembinaan.lokasi ?? '',
    berlaku_sampai: props.targetSkPembinaan.berlakuSampai ?? '',
    spdk_ref: props.targetSkPembinaan.spdkRef ?? '',
    dikeluarkan_di: props.targetSkPembinaan.dikeluarkanDi ?? '',
    dikeluarkan_tanggal: props.targetSkPembinaan.dikeluarkanTanggal ?? '',
    ttd_karyawan: props.targetSkPembinaan.ttdKaryawan ?? '',
    ttd_atasan: props.targetSkPembinaan.ttdAtasan ?? '',
    pengawas_id: props.targetSkPembinaan.pengawasId,
    pengawasName: props.targetSkPembinaan.pengawasName ?? '',
    jenis_pelanggaran: props.targetSkPembinaan.jenisPelanggaran ?? '',
    lubang: props.targetSkPembinaan.lubang ?? 0,
    keterangan: props.targetSkPembinaan.keterangan ?? '',
});

const submit = () => form.put(`/one-power/pembinaan/sk-pembinaan/${props.targetSkPembinaan.id}`);
</script>

<template>
    <Head title="Edit SK Pembinaan - OneClick" />

    <AppLayout :user="user" :notif-counts="notifCounts" :menu="menu">
        <h1 class="mb-5 text-xl font-extrabold text-slate-800">Edit SK Pembinaan</h1>
        <SkPembinaanForm :form="form" :on-submit="submit" />
    </AppLayout>
</template>
