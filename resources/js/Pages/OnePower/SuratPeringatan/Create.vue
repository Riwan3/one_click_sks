<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft } from '@lucide/vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import SuratPeringatanForm from './Partials/SuratPeringatanForm.vue';

defineProps({
    user: { type: Object, required: true },
    notifCounts: { type: Object, required: true },
    menu: { type: Array, required: true },
});

const today = new Date().toISOString().slice(0, 10);

const form = useForm({
    employee_id: null,
    jenis_pembinaan: 'Peringatan Pertama',
    kode_pelanggaran: 'SP 1',
    detail_pelanggaran: '',
    tanggal_kejadian: today,
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
    dikeluarkan_di: 'CSA Inggris',
    dikeluarkan_tanggal: today,
    catatan_karyawan: 'Berkomitmen untuk mengikuti Prosedur yang berlaku',
    ttd_karyawan: '',
    ttd_atasan: '',
    pengawas_id: null,
    koreksi_alpa: '',
    foto: null,
});

const submit = () => form.post('/one-power/pembinaan/surat-peringatan', {
    forceFormData: true,
});
</script>

<template>
    <Head title="Buat Surat Peringatan - OneClick" />

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
                <h1 class="text-xl font-extrabold text-slate-800">Buat Surat Peringatan</h1>
            </div>
        </div>
        <SuratPeringatanForm :form="form" :on-submit="submit" />
    </AppLayout>
</template>
