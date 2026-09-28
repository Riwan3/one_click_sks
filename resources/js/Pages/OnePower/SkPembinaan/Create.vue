<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft } from '@lucide/vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import SkPembinaanForm from './Partials/SkPembinaanForm.vue';

defineProps({
    user: { type: Object, required: true },
    notifCounts: { type: Object, required: true },
    menu: { type: Array, required: true },
});

const today = new Date().toISOString().split('T')[0];
const nextYear = new Date();
nextYear.setFullYear(nextYear.getFullYear() + 1);
const nextYearDate = nextYear.toISOString().split('T')[0];
const currentHourMin = new Date().toTimeString().substring(0, 5);

const form = useForm({
    employee_id: null,
    nama: '',
    nrp: '',
    jabatan: '',
    unit: '',
    perusahaan: 'PT. PUTRA PERKASA ABADI',
    nomor_surat: '',
    tanggal_pelanggaran: today,
    jam_pelanggaran: currentHourMin,
    lokasi: 'Adaro',
    berlaku_sampai: nextYearDate,
    spdk_ref: '',
    dikeluarkan_di: 'Adaro',
    dikeluarkan_tanggal: today,
    ttd_karyawan: '',
    ttd_atasan: '',
    pengawas_id: null,
    pengawasName: '',
    jenis_pelanggaran: '',
    lubang: 0,
    keterangan: '',
    foto: null,
});

const submit = () => form.post('/one-power/pembinaan/sk-pembinaan', {
    forceFormData: true,
});
</script>

<template>
    <Head title="Buat SK Pembinaan & Sanksi - OneClick" />

    <AppLayout :user="user" :notif-counts="notifCounts" :menu="menu">
        <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <Link
                    href="/one-power/pembinaan/sk-pembinaan"
                    class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-bold text-slate-700 shadow-2xs hover:bg-slate-50 transition"
                >
                    <ArrowLeft class="h-4 w-4" />
                    Kembali
                </Link>
                <h1 class="text-xl font-extrabold text-slate-800">Buat SK Pembinaan &amp; Sanksi Pelubangan</h1>
            </div>
        </div>
        <SkPembinaanForm :form="form" :on-submit="submit" :is-edit="false" />
    </AppLayout>
</template>
