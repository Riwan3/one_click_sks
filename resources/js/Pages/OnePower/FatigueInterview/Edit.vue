<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import FatigueInterviewForm from './Partials/FatigueInterviewForm.vue';

const props = defineProps({
    user: { type: Object, required: true },
    notifCounts: { type: Object, required: true },
    menu: { type: Array, required: true },
    targetItem: { type: Object, required: true },
});

const form = useForm({
    employee_id: props.targetItem.employeeId,
    pengawas_id: props.targetItem.pengawasId,
    nomor_unit: props.targetItem.nomorUnit ?? '',
    area: props.targetItem.area ?? '',
    tanggal: props.targetItem.tanggal ?? '',
    jam: props.targetItem.jam ?? '',
    q1_shift_day: props.targetItem.q1ShiftDay ?? '',
    q2_body_condition: props.targetItem.q2BodyCondition ?? '',
    q3_reason: props.targetItem.q3Reason ?? '',
    q3_background: props.targetItem.q3Background ?? '',
    q4_rest_hours: props.targetItem.q4RestHours ?? '',
    q5_rest_quality: props.targetItem.q5RestQuality ?? '',
    q5_bad_quality_reason: props.targetItem.q5BadQualityReason ?? '',
    q6_home_comfort: props.targetItem.q6HomeComfort ?? '',
    q6_discomfort_reason: props.targetItem.q6DiscomfortReason ?? '',
    q7_other_activities: props.targetItem.q7OtherActivities ?? '',
    q7_activity_details: props.targetItem.q7ActivityDetails ?? '',
    q8_education: props.targetItem.q8Education ?? '',
    q9_heavy_fatigue_day: props.targetItem.q9HeavyFatigueDay ?? '',
    q10_fit_plan: props.targetItem.q10FitPlan ?? '',
    q11_suggestions: props.targetItem.q11Suggestions ?? '',
    ttd_pengawas: props.targetItem.ttdPengawas ?? '',
});

const submit = () => form.put(`/one-power/pembinaan/interview-fatigue/${props.targetItem.id}`);
</script>

<template>
    <Head title="Edit Interview Fatigue - OneClick" />

    <AppLayout :user="user" :notif-counts="notifCounts" :menu="menu">
        <h1 class="mb-5 text-xl font-extrabold text-slate-800">Edit Interview Fatigue</h1>
        <FatigueInterviewForm :form="form" :on-submit="submit" />
    </AppLayout>
</template>
