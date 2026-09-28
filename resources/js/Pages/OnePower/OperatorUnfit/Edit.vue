<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft } from '@lucide/vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import OperatorUnfitForm from './Partials/OperatorUnfitForm.vue';

const props = defineProps({
    user: { type: Object, required: true },
    notifCounts: { type: Object, required: true },
    menu: { type: Array, required: true },
    targetUnfit: { type: Object, required: true },
});

const form = useForm({
    nrp: props.targetUnfit.nrp,
    nama: props.targetUnfit.nama,
    perusahaan: props.targetUnfit.perusahaan,
    departemen: props.targetUnfit.departemen ?? '',
    contact: props.targetUnfit.contact ?? '',
    diagnosa: props.targetUnfit.diagnosa ?? '',
    dokter_rujukan: props.targetUnfit.dokterRujukan ?? '',
    tgl_mulai_unfit: props.targetUnfit.tglMulaiUnfit,
    tgl_akhir_unfit: props.targetUnfit.tglAkhirUnfit ?? '',
    laporan: props.targetUnfit.laporan ?? '',
    bukti: null,
});

const submit = () => form
    .transform((data) => ({ ...data, _method: 'put' }))
    .post(`/one-power/deviasi-operator/unfit/${props.targetUnfit.id}`);
</script>

<template>
    <Head title="Edit Operator Unfit - OneClick" />

    <AppLayout :user="user" :notif-counts="notifCounts" :menu="menu">
        <div class="mb-5 flex items-center justify-between">
            <h1 class="text-xl font-extrabold text-slate-800">Edit Operator Unfit</h1>
            <Link href="/one-power/deviasi-operator/unfit" class="flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-50">
                <ArrowLeft class="h-4 w-4" /> Kembali
            </Link>
        </div>
        <OperatorUnfitForm :form="form" :existing-bukti-url="targetUnfit.buktiUrl" :on-submit="submit" />
    </AppLayout>
</template>
