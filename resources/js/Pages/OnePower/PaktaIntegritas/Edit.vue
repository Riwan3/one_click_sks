<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft } from '@lucide/vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import PaktaIntegritasForm from './Partials/PaktaIntegritasForm.vue';

const props = defineProps({
    user: { type: Object, required: true },
    notifCounts: { type: Object, required: true },
    menu: { type: Array, required: true },
    targetItem: { type: Object, required: true },
});

const form = useForm({
    employee_id: props.targetItem.employeeId,
    tanggal: props.targetItem.tanggal ?? '',
    tempat: props.targetItem.tempat ?? '',
    pernyataan: props.targetItem.pernyataan ?? '',
    atasan_nama: props.targetItem.atasanNama ?? '',
    mengetahui_nama: props.targetItem.mengetahuiNama ?? '',
    ttd_karyawan: props.targetItem.ttdKaryawan ?? '',
    ttd_atasan: props.targetItem.ttdAtasan ?? '',
    ttd_mengetahui: props.targetItem.ttdMengetahui ?? '',
    foto: null,
    hapus_foto: false,
    existing_foto: props.targetItem.foto ?? null,
});

const submit = () => {
    form.transform((data) => ({
        ...data,
        _method: 'put',
    })).post(`/one-power/pembinaan/pakta-integritas/${props.targetItem.id}`, {
        forceFormData: true,
    });
};
</script>

<template>
    <Head title="Edit Pakta Integritas - OneClick" />

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
                <h1 class="text-xl font-extrabold text-slate-800">Edit Pakta Integritas</h1>
            </div>
        </div>
        <PaktaIntegritasForm :form="form" :on-submit="submit" />
    </AppLayout>
</template>
