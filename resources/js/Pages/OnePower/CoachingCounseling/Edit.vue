<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
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
    materi_pribadi: Number(props.targetItem.materiPribadi) ? 1 : 0,
    materi_keluarga: Number(props.targetItem.materiKeluarga) ? 1 : 0,
    materi_lingkungan: Number(props.targetItem.materiLingkungan) ? 1 : 0,
    materi_pekerjaan: Number(props.targetItem.materiPekerjaan) ? 1 : 0,
    deskripsi: props.targetItem.deskripsi ?? '',
    rekomendasi: props.targetItem.rekomendasi ?? '',
    fatigue_level: props.targetItem.fatigueLevel ?? 0,
    file_bukti: null,
    ttd_anak_asuh: props.targetItem.ttdAnakAsuh ?? '',
    ttd_bapa_asuh: props.targetItem.ttdBapaAsuh ?? '',
});

const submit = () => {
    form.transform((data) => ({
        ...data,
        _method: 'put',
    })).post(`/one-power/pembinaan/coaching-counseling/${props.targetItem.id}`, {
        forceFormData: true,
    });
};
</script>

<template>
    <Head title="Edit Coaching & Counseling - OneClick" />

    <AppLayout :user="user" :notif-counts="notifCounts" :menu="menu">
        <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-black tracking-tight text-slate-800">Edit Coaching &amp; Counseling</h1>
                <p class="mt-0.5 text-xs text-slate-500">Perbarui data pembinaan karyawan (Coaching &amp; Counseling).</p>
            </div>
            <Link
                href="/one-power/pembinaan/coaching-counseling"
                class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2 text-xs font-bold text-slate-700 shadow-sm transition hover:bg-slate-50"
            >
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Kembali
            </Link>
        </div>

        <CoachingCounselingForm
            :form="form"
            :on-submit="submit"
            :target-item="targetItem"
            :is-edit="true"
        />
    </AppLayout>
</template>
