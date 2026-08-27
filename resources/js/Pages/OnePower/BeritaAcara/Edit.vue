<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import BeritaAcaraForm from './Partials/BeritaAcaraForm.vue';

const props = defineProps({
    user: { type: Object, required: true },
    notifCounts: { type: Object, required: true },
    menu: { type: Array, required: true },
    targetItem: { type: Object, required: true },
});

const form = useForm({
    employee_id: props.targetItem.employeeId,
    tanggal: props.targetItem.tanggal ?? '',
    kronologi: props.targetItem.kronologi ?? '',
    diketahui_oleh_id: props.targetItem.diketahuiOlehId,
    ttd_karyawan: props.targetItem.ttdKaryawan ?? '',
    ttd_atasan: props.targetItem.ttdAtasan ?? '',
});

const submit = () => form.put(`/one-power/pembinaan/berita-acara/${props.targetItem.id}`);
</script>

<template>
    <Head title="Edit Berita Acara - OneClick" />

    <AppLayout :user="user" :notif-counts="notifCounts" :menu="menu">
        <h1 class="mb-5 text-xl font-extrabold text-slate-800">Edit Berita Acara</h1>
        <BeritaAcaraForm :form="form" :on-submit="submit" />
    </AppLayout>
</template>
