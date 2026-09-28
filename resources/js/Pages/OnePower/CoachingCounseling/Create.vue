<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import CoachingCounselingForm from './Partials/CoachingCounselingForm.vue';

const props = defineProps({
    user: { type: Object, required: true },
    notifCounts: { type: Object, required: true },
    menu: { type: Array, required: true },
    defaultCoach: { type: Object, default: null },
});

const today = new Date().toISOString().split('T')[0];

const form = useForm({
    employee_id: null,
    coach_employee_id: props.defaultCoach?.id ?? null,
    sp_id: null,
    sk_id: null,
    tanggal: today,
    lokasi: '',
    waktu_pertemuan: '',
    materi_pribadi: 0,
    materi_keluarga: 0,
    materi_lingkungan: 0,
    materi_pekerjaan: 0,
    deskripsi: '',
    rekomendasi: '',
    fatigue_level: 0,
    file_bukti: null,
    ttd_anak_asuh: '',
    ttd_bapa_asuh: '',
});

const submit = () => {
    form.post('/one-power/pembinaan/coaching-counseling', {
        forceFormData: true,
    });
};
</script>

<template>
    <Head title="Tambah Coaching & Counseling - OneClick" />

    <AppLayout :user="user" :notif-counts="notifCounts" :menu="menu">
        <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-black tracking-tight text-slate-800">Tambah Coaching &amp; Counseling</h1>
                <p class="mt-0.5 text-xs text-slate-500">Kelola data pembinaan karyawan (Coaching &amp; Counseling).</p>
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
            :default-coach="defaultCoach"
        />
    </AppLayout>
</template>
