<script setup>
import { onMounted } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft } from '@lucide/vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import PemanggilanKaryawanForm from './Partials/PemanggilanKaryawanForm.vue';

const props = defineProps({
    user: { type: Object, required: true },
    notifCounts: { type: Object, required: true },
    menu: { type: Array, required: true },
    documentSettings: { type: Object, default: () => ({}) },
});

const today = new Date().toISOString().split('T')[0];

const form = useForm({
    employee_id: null,
    nrp: '',
    nama: '',
    jabatan: '',
    no_surat: '',
    alasan_pemanggilan: '',
    tempat: props.documentSettings?.lokasi || 'CSA Inggris',
    jam_pemanggilan: '',
    tanggal_pemanggilan: today,
    ttd_pemanggilan: '',
    nama_manpower: 'Manpower Dept Produksi',
    fatigue_periode: null,
    ttd_pemanggil: '',
    ttd_karyawan: '',
    eviden_1: null,
    eviden_2: null,
    foto: null,
});

onMounted(async () => {
    try {
        const res = await fetch(`/one-power/pembinaan/pemanggilan-karyawan/next-number?tanggal=${today}`);
        const data = await res.json();
        if (data.nomor_surat) {
            form.no_surat = data.nomor_surat;
        }
    } catch (e) {
        console.error(e);
    }
});

const submit = () => {
    form.post('/one-power/pembinaan/pemanggilan-karyawan', {
        forceFormData: true,
    });
};
</script>

<template>
    <Head title="Buat Pemanggilan Karyawan - OneClick" />

    <AppLayout :user="user" :notif-counts="notifCounts" :menu="menu">
        <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <Link
                    href="/one-power/pembinaan/pemanggilan-karyawan"
                    class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-bold text-slate-700 shadow-2xs hover:bg-slate-50 transition"
                >
                    <ArrowLeft class="h-4 w-4" />
                    Kembali
                </Link>
                <h1 class="text-xl font-extrabold text-slate-800">Buat Pemanggilan Karyawan</h1>
            </div>
        </div>

        <PemanggilanKaryawanForm
            :form="form"
            :on-submit="submit"
            :is-edit="false"
            :document-settings="documentSettings"
        />
    </AppLayout>
</template>
