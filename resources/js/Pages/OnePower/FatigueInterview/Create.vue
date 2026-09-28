<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft } from '@lucide/vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import FatigueInterviewForm from './Partials/FatigueInterviewForm.vue';

defineProps({
    user: { type: Object, required: true },
    notifCounts: { type: Object, required: true },
    menu: { type: Array, required: true },
});

const form = useForm({
    employee_id: null,
    employee_name: '',
    pengawas_id: null,
    pengawas_name: '',
    nomor_unit: '',
    area: '',
    tanggal: new Date().toISOString().substring(0, 10),
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
        <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <Link
                    href="/one-power/pembinaan/interview-fatigue"
                    class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-bold text-slate-700 shadow-2xs hover:bg-slate-50 transition"
                >
                    <ArrowLeft class="h-4 w-4" />
                    Kembali
                </Link>
                <h1 class="text-xl font-extrabold text-slate-800">Buat Interview Fatigue</h1>
            </div>
        </div>
        <FatigueInterviewForm :form="form" :on-submit="submit" />
    </AppLayout>
</template>
