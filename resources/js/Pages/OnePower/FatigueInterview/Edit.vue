<script setup>
import { computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft } from '@lucide/vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import FatigueInterviewForm from './Partials/FatigueInterviewForm.vue';

const props = defineProps({
    user: { type: Object, required: true },
    notifCounts: { type: Object, required: true },
    menu: { type: Array, required: true },
    targetItem: { type: Object, required: true },
});

const target = computed(() => props.targetItem?.data ?? props.targetItem ?? {});

const form = useForm({
    employee_id: target.value.employeeId ?? null,
    employee_name: target.value.nrp ? `${target.value.nrp} - ${target.value.nama}` : '',
    pengawas_id: target.value.pengawasId ?? null,
    pengawas_name: target.value.nrpPengawas ? `${target.value.nrpPengawas} - ${target.value.namaPengawas}` : '',
    nomor_unit: target.value.nomorUnit ?? '',
    area: target.value.area ?? '',
    tanggal: target.value.tanggal ?? '',
    jam: target.value.jam ?? '',
    q1_shift_day: target.value.q1ShiftDay ?? '',
    q2_body_condition: target.value.q2BodyCondition ?? '',
    q3_reason: target.value.q3Reason ?? '',
    q3_background: target.value.q3Background ?? '',
    q4_rest_hours: target.value.q4RestHours ?? '',
    q5_rest_quality: target.value.q5RestQuality ?? '',
    q5_bad_quality_reason: target.value.q5BadQualityReason ?? '',
    q6_home_comfort: target.value.q6HomeComfort ?? '',
    q6_discomfort_reason: target.value.q6DiscomfortReason ?? '',
    q7_other_activities: target.value.q7OtherActivities ?? '',
    q7_activity_details: target.value.q7ActivityDetails ?? '',
    q8_education: target.value.q8Education ?? '',
    q9_heavy_fatigue_day: target.value.q9HeavyFatigueDay ?? '',
    q10_fit_plan: target.value.q10FitPlan ?? '',
    q11_suggestions: target.value.q11Suggestions ?? '',
    ttd_pengawas: target.value.ttdPengawas ?? '',
});

const submit = () => form.put(`/one-power/pembinaan/interview-fatigue/${target.value.id}`);
</script>

<template>
    <Head title="Edit Interview Fatigue - OneClick" />

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
                <h1 class="text-xl font-extrabold text-slate-800">Edit Interview Fatigue</h1>
            </div>
        </div>
        <FatigueInterviewForm :form="form" :on-submit="submit" />
    </AppLayout>
</template>
