<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import CoachingCounselingForm from './Partials/CoachingCounselingForm.vue';

defineProps({
    user: { type: Object, required: true },
    notifCounts: { type: Object, required: true },
    menu: { type: Array, required: true },
});

const form = useForm({
    employee_id: null,
    coach_employee_id: null,
    sp_id: null,
    sk_id: null,
    tanggal: '',
    lokasi: '',
    waktu_pertemuan: '',
    materi_pribadi: '',
    materi_keluarga: '',
    materi_lingkungan: '',
    materi_pekerjaan: '',
    deskripsi: '',
    rekomendasi: '',
    fatigue_level: 0,
    ttd_anak_asuh: '',
    ttd_bapa_asuh: '',
});

const submit = () => form.post('/one-power/pembinaan/coaching-counseling');
</script>

<template>
    <Head title="Buat Coaching & Counseling - OneClick" />

    <AppLayout :user="user" :notif-counts="notifCounts" :menu="menu">
        <h1 class="mb-5 text-xl font-extrabold text-slate-800">Buat Coaching & Counseling</h1>
        <CoachingCounselingForm :form="form" :on-submit="submit" />
    </AppLayout>
</template>
