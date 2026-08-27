<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import FatigueInterviewForm from './Partials/FatigueInterviewForm.vue';

defineProps({
    user: { type: Object, required: true },
    notifCounts: { type: Object, required: true },
    menu: { type: Array, required: true },
});

const form = useForm({
    employee_id: null,
    pengawas_id: null,
    nomor_unit: '',
    area: '',
    tanggal: '',
    jam: '',
    q1_shift_day: '',
    q2_body_condition: '',
    q3_reason: '',
    q3_background: '',
    q4_rest_hours: '',
    q5_rest_quality: '',
    q5_bad_quality_reason: '',
    q6_home_comfort: '',
    q6_discomfort_reason: '',
    q7_other_activities: '',
    q7_activity_details: '',
    q8_education: '',
    q9_heavy_fatigue_day: '',
    q10_fit_plan: '',
    q11_suggestions: '',
    ttd_pengawas: '',
});

const submit = () => form.post('/one-power/pembinaan/interview-fatigue');
</script>

<template>
    <Head title="Buat Interview Fatigue - OneClick" />

    <AppLayout :user="user" :notif-counts="notifCounts" :menu="menu">
        <h1 class="mb-5 text-xl font-extrabold text-slate-800">Buat Interview Fatigue</h1>
        <FatigueInterviewForm :form="form" :on-submit="submit" />
    </AppLayout>
</template>
