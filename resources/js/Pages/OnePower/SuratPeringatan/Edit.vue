<script setup>
import { computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft } from '@lucide/vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import SuratPeringatanForm from './Partials/SuratPeringatanForm.vue';

const props = defineProps({
    user: { type: Object, required: true },
    notifCounts: { type: Object, required: true },
    menu: { type: Array, required: true },
    targetSuratPeringatan: { type: Object, required: true },
});

const target = computed(() => props.targetSuratPeringatan?.data ?? props.targetSuratPeringatan ?? {});

const form = useForm({
    id: target.value.id,
    employee_id: target.value.employeeId ?? target.value.employee_id,
    nama: target.value.nama ?? target.value.name,
    jenis_pembinaan: target.value.jenisPembinaan ?? target.value.jenis_pembinaan,
    kode_pelanggaran: target.value.kodePelanggaran ?? target.value.kode_pelanggaran ?? '',
    detail_pelanggaran: target.value.detailPelanggaran ?? target.value.detail_pelanggaran ?? '',
    tanggal_kejadian: target.value.tanggalKejadian ?? target.value.tanggal_kejadian ?? '',
    jam_kejadian: target.value.jamKejadian ?? target.value.jam_kejadian ?? '',
    tempat_kejadian: target.value.tempatKejadian ?? target.value.tempat_kejadian ?? '',
    pelanggaran_absen_terlambat: Boolean(target.value.pelanggaranAbsenTerlambat ?? target.value.pelanggaran_absen_terlambat),
    pelanggaran_perilaku: Boolean(target.value.pelanggaranPerilaku ?? target.value.pelanggaran_perilaku),
    pelanggaran_ceroboh_lalai: Boolean(target.value.pelanggaranCerobohLalai ?? target.value.pelanggaran_ceroboh_lalai),
    pelanggaran_hasil_kerja: Boolean(target.value.pelanggaranHasilKerja ?? target.value.pelanggaran_hasil_kerja),
    pelanggaran_menolak_perintah: Boolean(target.value.pelanggaranMenolakPerintah ?? target.value.pelanggaran_menolak_perintah),
    pelanggaran_peraturan: Boolean(target.value.pelanggaranPeraturan ?? target.value.pelanggaran_peraturan),
    peringatan_sebelumnya_status: target.value.peringatanSebelumnyaStatus ?? target.value.peringatan_sebelumnya_status ?? 'Tidak ada',
    peringatan_sebelumnya_jenis: target.value.peringatanSebelumnyaJenis ?? target.value.peringatan_sebelumnya_jenis ?? '',
    peringatan_sebelumnya_mengenai: target.value.peringatanSebelumnyaMengenai ?? target.value.peringatan_sebelumnya_mengenai ?? '',
    sanksi_masa_berlaku: target.value.sanksiMasaBerlaku ?? target.value.sanksi_masa_berlaku ?? 6,
    dikeluarkan_di: target.value.dikeluarkanDi ?? target.value.dikeluarkan_di ?? 'CSA Inggris',
    dikeluarkan_tanggal: target.value.dikeluarkanTanggal ?? target.value.dikeluarkan_tanggal ?? '',
    catatan_karyawan: target.value.catatanKaryawan ?? target.value.catatan_karyawan ?? '',
    ttd_karyawan: target.value.ttdKaryawan ?? target.value.ttd_karyawan ?? '',
    ttd_atasan: target.value.ttdAtasan ?? target.value.ttd_atasan ?? '',
    pengawas_id: target.value.pengawasId ?? target.value.pengawas_id,
    pengawasName: target.value.pengawasName ?? target.value.pengawas_name ?? '',
    koreksi_alpa: target.value.koreksiAlpa ?? target.value.koreksi_alpa ?? '',
    foto: null,
    hapus_foto: false,
    existing_foto: target.value.foto ?? null,
});

const submit = () => {
    form.transform((data) => ({
        ...data,
        _method: 'put',
    })).post(`/one-power/pembinaan/surat-peringatan/${target.value.id}`, {
        forceFormData: true,
    });
};
</script>

<template>
    <Head title="Edit Surat Peringatan - OneClick" />

    <AppLayout :user="user" :notif-counts="notifCounts" :menu="menu">
        <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <Link
                    href="/one-power/pembinaan/surat-peringatan"
                    class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-bold text-slate-700 shadow-2xs hover:bg-slate-50 transition"
                >
                    <ArrowLeft class="h-4 w-4" />
                    Kembali
                </Link>
                <h1 class="text-xl font-extrabold text-slate-800">Edit Surat Peringatan</h1>
            </div>
        </div>
        <SuratPeringatanForm :form="form" :on-submit="submit" />
    </AppLayout>
</template>
