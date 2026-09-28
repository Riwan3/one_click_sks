<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft } from '@lucide/vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import SuratTeguranForm from './Partials/SuratTeguranForm.vue';

defineProps({
    user: { type: Object, required: true },
    notifCounts: { type: Object, required: true },
    menu: { type: Array, required: true },
});

const today = new Date().toISOString().slice(0, 10);

const form = useForm({
    employee_id: null,
    nrp: '',
    nama: '',
    jabatan: '',
    departemen: '',
    tanggal_kejadian: today,
    tempat_kejadian: '',
    jenis_absen: false,
    jenis_perilaku: false,
    jenis_ceroboh: false,
    jenis_hasil_kerja: false,
    jenis_menolak_perintah: false,
    jenis_pelanggaran_peraturan: false,
    penjelasan_singkat: '',
    kejadian_tanggal: today,
    kejadian_jam: '',
    kejadian_lokasi: '',
    dikeluarkan_di: 'CSA Inggris',
    dikeluarkan_tanggal: today,
    ttd_karyawan: '',
    ttd_atasan: '',
    ttd_atasan_atasan: '',
    catatan: 'Berkomitmen untuk mengikuti Prosedur yang berlaku',
    foto: null,
});

const submit = () => form.post('/one-power/pembinaan/surat-teguran', {
    forceFormData: true,
});
</script>

<template>
    <Head title="Buat Surat Teguran - OneClick" />

    <AppLayout :user="user" :notif-counts="notifCounts" :menu="menu">
        <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <Link
                    href="/one-power/pembinaan/surat-teguran"
                    class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-bold text-slate-700 shadow-2xs hover:bg-slate-50 transition"
                >
                    <ArrowLeft class="h-4 w-4" />
                    Kembali
                </Link>
                <h1 class="text-xl font-extrabold text-slate-800">Buat Surat Teguran</h1>
            </div>
        </div>
        <SuratTeguranForm :form="form" :on-submit="submit" />
    </AppLayout>
</template>
