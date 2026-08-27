<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import SuratPeringatanForm from './Partials/SuratPeringatanForm.vue';

defineProps({
    user: { type: Object, required: true },
    notifCounts: { type: Object, required: true },
    menu: { type: Array, required: true },
});

const form = useForm({
    employee_id: null,
    jenis_pembinaan: 'Peringatan Pertama',
    kode_pelanggaran: '',
    detail_pelanggaran: '',
    tanggal_kejadian: '',
    jam_kejadian: '',
    tempat_kejadian: '',
    pelanggaran_absen_terlambat: false,
    pelanggaran_perilaku: false,
    pelanggaran_ceroboh_lalai: false,
    pelanggaran_hasil_kerja: false,
    pelanggaran_menolak_perintah: false,
    pelanggaran_peraturan: false,
    peringatan_sebelumnya_status: 'Tidak ada',
    peringatan_sebelumnya_jenis: '',
    peringatan_sebelumnya_mengenai: '',
    sanksi_masa_berlaku: 6,
    dikeluarkan_di: '',
    dikeluarkan_tanggal: '',
    catatan_karyawan: '',
    ttd_karyawan: '',
    ttd_atasan: '',
    pengawas_id: null,
    koreksi_alpa: '',
});

const submit = () => form.post('/one-power/pembinaan/surat-peringatan');
</script>

<template>
    <Head title="Buat Surat Peringatan - OneClick" />

    <AppLayout :user="user" :notif-counts="notifCounts" :menu="menu">
        <h1 class="mb-5 text-xl font-extrabold text-slate-800">Buat Surat Peringatan</h1>
        <SuratPeringatanForm :form="form" :on-submit="submit" />
    </AppLayout>
</template>
