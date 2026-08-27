<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import SuratPeringatanForm from './Partials/SuratPeringatanForm.vue';

const props = defineProps({
    user: { type: Object, required: true },
    notifCounts: { type: Object, required: true },
    menu: { type: Array, required: true },
    targetSuratPeringatan: { type: Object, required: true },
});

const form = useForm({
    id: props.targetSuratPeringatan.id,
    employee_id: props.targetSuratPeringatan.employeeId,
    nama: props.targetSuratPeringatan.nama,
    jenis_pembinaan: props.targetSuratPeringatan.jenisPembinaan,
    kode_pelanggaran: props.targetSuratPeringatan.kodePelanggaran ?? '',
    detail_pelanggaran: props.targetSuratPeringatan.detailPelanggaran ?? '',
    tanggal_kejadian: props.targetSuratPeringatan.tanggalKejadian ?? '',
    jam_kejadian: props.targetSuratPeringatan.jamKejadian ?? '',
    tempat_kejadian: props.targetSuratPeringatan.tempatKejadian ?? '',
    pelanggaran_absen_terlambat: props.targetSuratPeringatan.pelanggaranAbsenTerlambat,
    pelanggaran_perilaku: props.targetSuratPeringatan.pelanggaranPerilaku,
    pelanggaran_ceroboh_lalai: props.targetSuratPeringatan.pelanggaranCerobohLalai,
    pelanggaran_hasil_kerja: props.targetSuratPeringatan.pelanggaranHasilKerja,
    pelanggaran_menolak_perintah: props.targetSuratPeringatan.pelanggaranMenolakPerintah,
    pelanggaran_peraturan: props.targetSuratPeringatan.pelanggaranPeraturan,
    peringatan_sebelumnya_status: props.targetSuratPeringatan.peringatanSebelumnyaStatus ?? 'Tidak ada',
    peringatan_sebelumnya_jenis: props.targetSuratPeringatan.peringatanSebelumnyaJenis ?? '',
    peringatan_sebelumnya_mengenai: props.targetSuratPeringatan.peringatanSebelumnyaMengenai ?? '',
    sanksi_masa_berlaku: props.targetSuratPeringatan.sanksiMasaBerlaku ?? 6,
    dikeluarkan_di: props.targetSuratPeringatan.dikeluarkanDi ?? '',
    dikeluarkan_tanggal: props.targetSuratPeringatan.dikeluarkanTanggal ?? '',
    catatan_karyawan: props.targetSuratPeringatan.catatanKaryawan ?? '',
    ttd_karyawan: props.targetSuratPeringatan.ttdKaryawan ?? '',
    ttd_atasan: props.targetSuratPeringatan.ttdAtasan ?? '',
    pengawas_id: props.targetSuratPeringatan.pengawasId,
    pengawasName: props.targetSuratPeringatan.pengawasName ?? '',
    koreksi_alpa: props.targetSuratPeringatan.koreksiAlpa ?? '',
});

const submit = () => form.put(`/one-power/pembinaan/surat-peringatan/${props.targetSuratPeringatan.id}`);
</script>

<template>
    <Head title="Edit Surat Peringatan - OneClick" />

    <AppLayout :user="user" :notif-counts="notifCounts" :menu="menu">
        <h1 class="mb-5 text-xl font-extrabold text-slate-800">Edit Surat Peringatan</h1>
        <SuratPeringatanForm :form="form" :on-submit="submit" />
    </AppLayout>
</template>
