<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft } from '@lucide/vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import OperatorUnfitForm from './Partials/OperatorUnfitForm.vue';

const props = defineProps({
    user: { type: Object, required: true },
    notifCounts: { type: Object, required: true },
    menu: { type: Array, required: true },
});

const form = useForm({
    nrp: '',
    nama: '',
    perusahaan: '',
    departemen: '',
    contact: '',
    diagnosa: '',
    dokter_rujukan: '',
    tgl_mulai_unfit: '',
    tgl_akhir_unfit: '',
    laporan: '',
    bukti: null,
});

const submit = () => form.post('/one-power/deviasi-operator/unfit');
</script>

<template>
    <Head title="Tambah Operator Unfit - OneClick" />

    <AppLayout :user="user" :notif-counts="notifCounts" :menu="menu">
        <div class="mb-5 flex items-center justify-between">
            <h1 class="text-xl font-extrabold text-slate-800">Tambah Operator Unfit</h1>
            <Link href="/one-power/deviasi-operator/unfit" class="flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-50">
                <ArrowLeft class="h-4 w-4" /> Kembali
            </Link>
        </div>
        <OperatorUnfitForm :form="form" :on-submit="submit" />
    </AppLayout>
</template>
