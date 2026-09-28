<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft } from '@lucide/vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import BeritaAcaraForm from './Partials/BeritaAcaraForm.vue';

const props = defineProps({
    user: { type: Object, required: true },
    notifCounts: { type: Object, required: true },
    menu: { type: Array, required: true },
    defaultValues: { type: Object, default: () => ({}) },
});

const form = useForm({
    nomor_surat: props.defaultValues?.nomorSurat ?? '',
    employee_id: null,
    tanggal: props.defaultValues?.tanggal ?? '',
    kronologi: '',
    diketahui_oleh_id: null,
    ttd_karyawan: '',
    ttd_atasan: '',
});

const submit = () => form.post('/one-power/pembinaan/berita-acara');
</script>

<template>
    <Head title="Buat Berita Acara - OneClick" />

    <AppLayout :user="user" :notif-counts="notifCounts" :menu="menu">
        <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <Link
                    href="/one-power/pembinaan/berita-acara"
                    class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-bold text-slate-700 shadow-2xs hover:bg-slate-50 transition"
                >
                    <ArrowLeft class="h-4 w-4" />
                    Kembali
                </Link>
                <h1 class="text-xl font-extrabold text-slate-800">Buat Berita Acara</h1>
            </div>
        </div>
        <BeritaAcaraForm :form="form" :on-submit="submit" />
    </AppLayout>
</template>
