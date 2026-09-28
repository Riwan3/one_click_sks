<script setup>
import { onMounted } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft } from '@lucide/vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import ValidasiIstirahatForm from './Partials/ValidasiIstirahatForm.vue';

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
    departemen: '',
    no_surat: '',
    doc_rank: null,
    alasan_kurang_tidur: '',
    alasan_kurang_tidur_berulang: '',
    kendala_tidur: '',
    tinggal_di: 'Non Mess',
    nama_mess: '',
    alamat_non_mess: '',
    status_perkawinan: 'Single',
    jumlah_anak_serumah: '',
    anak_usia_kurang_2th: '',
    pekerjaan_sampingan: '',
    keterangan: '',
    tanggal_validasi: today,
    nama_pengawas: '',
    ttd_karyawan: '',
    ttd_pengawas: '',
    foto: null,
});

onMounted(async () => {
    try {
        const res = await fetch(`/one-power/pembinaan/validasi-istirahat/next-number?tanggal=${today}`);
        const data = await res.json();
        if (data.nomor_surat) {
            form.no_surat = data.nomor_surat;
            form.doc_rank = data.doc_rank;
        }
    } catch (e) {
        console.error(e);
    }
});

const submit = () => form.post('/one-power/pembinaan/validasi-istirahat', {
    forceFormData: true,
});
</script>

<template>
    <Head title="Buat Validasi Istirahat - OneClick" />

    <AppLayout :user="user" :notif-counts="notifCounts" :menu="menu">
        <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <Link
                    href="/one-power/pembinaan/validasi-istirahat"
                    class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-bold text-slate-700 shadow-2xs hover:bg-slate-50 transition"
                >
                    <ArrowLeft class="h-4 w-4" />
                    Kembali
                </Link>
                <h1 class="text-xl font-extrabold text-slate-800">Buat Validasi Istirahat</h1>
            </div>
        </div>
        <ValidasiIstirahatForm
            :form="form"
            :on-submit="submit"
            :is-edit="false"
            :document-settings="documentSettings"
        />
    </AppLayout>
</template>
