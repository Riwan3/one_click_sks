<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft } from '@lucide/vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import SkPembinaanForm from './Partials/SkPembinaanForm.vue';

const props = defineProps({
    user: { type: Object, required: true },
    notifCounts: { type: Object, required: true },
    menu: { type: Array, required: true },
    targetSkPembinaan: { type: Object, required: true },
});

const sk = props.targetSkPembinaan?.data ?? props.targetSkPembinaan;

const form = useForm({
    employee_id: sk.employeeId,
    nama: sk.nama ?? '',
    nrp: sk.nrp ?? '',
    jabatan: sk.jabatan ?? '',
    unit: sk.unit ?? '',
    perusahaan: sk.perusahaan ?? 'PT. PUTRA PERKASA ABADI',
    nomor_surat: sk.nomorSurat ?? '',
    tanggal_pelanggaran: sk.tanggalPelanggaran ?? '',
    jam_pelanggaran: sk.jamPelanggaran ?? '',
    lokasi: sk.lokasi ?? 'Adaro',
    berlaku_sampai: sk.berlakuSampai ?? '',
    spdk_ref: sk.spdkRef ?? '',
    dikeluarkan_di: sk.dikeluarkanDi ?? 'Adaro',
    dikeluarkan_tanggal: sk.dikeluarkanTanggal ?? '',
    ttd_karyawan: sk.ttdKaryawan ?? '',
    ttd_atasan: sk.ttdAtasan ?? '',
    pengawas_id: sk.pengawasId,
    pengawasName: sk.pengawasName ?? '',
    jenis_pelanggaran: sk.jenisPelanggaran ?? '',
    lubang: sk.lubang ?? 0,
    keterangan: sk.keterangan ?? '',
    foto: null,
    hapus_foto: false,
    existing_foto: sk.foto ?? null,
});

const submit = () => {
    form.transform((data) => ({
        ...data,
        _method: 'put',
    })).post(`/one-power/pembinaan/sk-pembinaan/${sk.id}`, {
        forceFormData: true,
    });
};
</script>

<template>
    <Head title="Edit SK Pembinaan & Sanksi - OneClick" />

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
                <h1 class="text-xl font-extrabold text-slate-800">Edit SK Pembinaan &amp; Sanksi Pelubangan</h1>
            </div>
        </div>
        <SkPembinaanForm :form="form" :on-submit="submit" :is-edit="true" />
    </AppLayout>
</template>
