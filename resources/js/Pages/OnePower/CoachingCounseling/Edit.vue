<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import CoachingCounselingForm from './Partials/CoachingCounselingForm.vue';

const props = defineProps({
    user: { type: Object, required: true },
    notifCounts: { type: Object, required: true },
    menu: { type: Array, required: true },
    targetItem: { type: Object, required: true },
});

const form = useForm({
    employee_id: props.targetItem.employeeId,
    coach_employee_id: props.targetItem.coachEmployeeId,
    sp_id: props.targetItem.spId,
    sk_id: props.targetItem.skId,
    tanggal: props.targetItem.tanggal ?? '',
    lokasi: props.targetItem.lokasi ?? '',
    waktu_pertemuan: props.targetItem.waktuPertemuan ?? '',
    materi_pribadi: props.targetItem.materiPribadi ?? '',
    materi_keluarga: props.targetItem.materiKeluarga ?? '',
    materi_lingkungan: props.targetItem.materiLingkungan ?? '',
    materi_pekerjaan: props.targetItem.materiPekerjaan ?? '',
    deskripsi: props.targetItem.deskripsi ?? '',
    rekomendasi: props.targetItem.rekomendasi ?? '',
    fatigue_level: props.targetItem.fatigueLevel ?? 0,
    ttd_anak_asuh: props.targetItem.ttdAnakAsuh ?? '',
    ttd_bapa_asuh: props.targetItem.ttdBapaAsuh ?? '',
});

const submit = () => form.put(`/one-power/pembinaan/coaching-counseling/${props.targetItem.id}`);
</script>

<template>
    <Head title="Edit Coaching & Counseling - OneClick" />

    <AppLayout :user="user" :notif-counts="notifCounts" :menu="menu">
        <h1 class="mb-5 text-xl font-extrabold text-slate-800">Edit Coaching & Counseling</h1>
        <CoachingCounselingForm
            :form="form"
            :on-submit="submit"
            :initial-employee-label="`${targetItem.nrp} - ${targetItem.nama}`"
            :initial-coach-label="targetItem.coachNama"
        />
    </AppLayout>
</template>
