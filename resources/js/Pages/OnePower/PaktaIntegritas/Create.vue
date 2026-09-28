<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft } from '@lucide/vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import PaktaIntegritasForm from './Partials/PaktaIntegritasForm.vue';

defineProps({
    user: { type: Object, required: true },
    notifCounts: { type: Object, required: true },
    menu: { type: Array, required: true },
});

const form = useForm({
    employee_id: null,
    tanggal: '',
    tempat: 'Tanjung',
    pernyataan: '',
    atasan_nama: '',
    mengetahui_nama: '',
    ttd_karyawan: '',
    ttd_atasan: '',
    ttd_mengetahui: '',
    foto: null,
});

const submit = () => form.post('/one-power/pembinaan/pakta-integritas', {
    forceFormData: true,
});
</script>

<template>
    <Head title="Buat Pakta Integritas - OneClick" />

    <AppLayout :user="user" :notif-counts="notifCounts" :menu="menu">
        <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <Link
                    href="/one-power/pembinaan/pakta-integritas"
                    class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-bold text-slate-700 shadow-2xs hover:bg-slate-50 transition"
                >
                    <ArrowLeft class="h-4 w-4" />
                    Kembali
                </Link>
                <h1 class="text-xl font-extrabold text-slate-800">Buat Pakta Integritas</h1>
            </div>
        </div>
        <PaktaIntegritasForm :form="form" :on-submit="submit" />
    </AppLayout>
</template>
