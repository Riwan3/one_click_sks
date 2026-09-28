<script setup>
import { computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft } from '@lucide/vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import SuratTeguranForm from './Partials/SuratTeguranForm.vue';

const props = defineProps({
    user: { type: Object, required: true },
    notifCounts: { type: Object, required: true },
    menu: { type: Array, required: true },
    targetSuratTeguran: { type: Object, required: true },
});

const target = computed(() => props.targetSuratTeguran?.data ?? props.targetSuratTeguran ?? {});

const form = useForm({
    employee_id: target.value.employeeId ?? target.value.employee_id,
    nrp: target.value.nrp,
    nama: target.value.nama,
    jabatan: target.value.jabatan ?? '',
    departemen: target.value.departemen ?? '',
    tanggal_kejadian: target.value.tanggalKejadian ?? target.value.tanggal_kejadian ?? '',
    tempat_kejadian: target.value.tempatKejadian ?? target.value.tempat_kejadian ?? '',
    jenis_absen: Boolean(target.value.jenisAbsen ?? target.value.jenis_absen),
    jenis_perilaku: Boolean(target.value.jenisPerilaku ?? target.value.jenis_perilaku),
    jenis_ceroboh: Boolean(target.value.jenisCeroboh ?? target.value.jenis_ceroboh),
    jenis_hasil_kerja: Boolean(target.value.jenisHasilKerja ?? target.value.jenis_hasil_kerja),
    jenis_menolak_perintah: Boolean(target.value.jenisMenolakPerintah ?? target.value.jenis_menolak_perintah),
    jenis_pelanggaran_peraturan: Boolean(target.value.jenisPelanggaranPeraturan ?? target.value.jenis_pelanggaran_peraturan),
    penjelasan_singkat: target.value.penjelasanSingkat ?? target.value.penjelasan_singkat ?? '',
    kejadian_tanggal: target.value.kejadianTanggal ?? target.value.kejadian_tanggal ?? '',
    kejadian_jam: target.value.kejadianJam ?? target.value.kejadian_jam ?? '',
    kejadian_lokasi: target.value.kejadianLokasi ?? target.value.kejadian_lokasi ?? '',
    dikeluarkan_di: target.value.dikeluarkanDi ?? target.value.dikeluarkan_di ?? 'CSA Inggris',
    dikeluarkan_tanggal: target.value.dikeluarkanTanggal ?? target.value.dikeluarkan_tanggal ?? '',
    ttd_karyawan: target.value.ttdKaryawan ?? target.value.ttd_karyawan ?? '',
    ttd_atasan: target.value.ttdAtasan ?? target.value.ttd_atasan ?? '',
    ttd_atasan_atasan: target.value.ttdAtasanAtasan ?? target.value.ttd_atasan_atasan ?? '',
    catatan: target.value.catatan ?? '',
    existing_foto: target.value.foto ?? null,
    foto: null,
    hapus_foto: false,
});

const submit = () => form.transform((data) => ({
    ...data,
    _method: 'put',
})).post(`/one-power/pembinaan/surat-teguran/${target.value.id}`, {
    forceFormData: true,
});
</script>

<template>
    <Head title="Edit Surat Teguran - OneClick" />

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
                <h1 class="text-xl font-extrabold text-slate-800">Edit Surat Teguran</h1>
            </div>
        </div>
        <SuratTeguranForm :form="form" :on-submit="submit" />
    </AppLayout>
</template>
