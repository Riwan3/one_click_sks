<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import PaktaIntegritasForm from './Partials/PaktaIntegritasForm.vue';

const props = defineProps({
    user: { type: Object, required: true },
    notifCounts: { type: Object, required: true },
    menu: { type: Array, required: true },
    targetItem: { type: Object, required: true },
});

const form = useForm({
    employee_id: props.targetItem.employeeId,
    tanggal: props.targetItem.tanggal ?? '',
    tempat: props.targetItem.tempat ?? '',
    pernyataan: props.targetItem.pernyataan ?? '',
    atasan_nama: props.targetItem.atasanNama ?? '',
    mengetahui_nama: props.targetItem.mengetahuiNama ?? '',
    ttd_karyawan: props.targetItem.ttdKaryawan ?? '',
    ttd_atasan: props.targetItem.ttdAtasan ?? '',
    ttd_mengetahui: props.targetItem.ttdMengetahui ?? '',
});

const submit = () => form.put(`/one-power/pembinaan/pakta-integritas/${props.targetItem.id}`);
</script>

<template>
    <Head title="Edit Pakta Integritas - OneClick" />

    <AppLayout :user="user" :notif-counts="notifCounts" :menu="menu">
        <h1 class="mb-5 text-xl font-extrabold text-slate-800">Edit Pakta Integritas</h1>
        <PaktaIntegritasForm :form="form" :on-submit="submit" />
    </AppLayout>
</template>
