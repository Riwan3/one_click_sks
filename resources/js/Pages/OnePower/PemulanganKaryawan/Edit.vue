<script setup>
import { computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft } from '@lucide/vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import PemulanganKaryawanForm from './Partials/PemulanganKaryawanForm.vue';

const props = defineProps({
    user: { type: Object, required: true },
    notifCounts: { type: Object, required: true },
    menu: { type: Array, required: true },
    targetPemulangan: { type: Object, required: true },
    documentSettings: { type: Object, default: () => ({}) },
});

const item = computed(() => props.targetPemulangan?.data ?? props.targetPemulangan ?? {});

const form = useForm({
    employee_id: item.value.employeeId ?? item.value.employee_id,
    nrp: item.value.nrp,
    nama: item.value.nama,
    jabatan: item.value.jabatan ?? '',
    departemen: item.value.departemen ?? '',
    lokasi: item.value.lokasi ?? 'Wara',
    tanggal: item.value.tanggal ?? '',
    no_surat: item.value.noSurat ?? item.value.no_surat ?? '',
    doc_rank: item.value.docRank ?? item.value.doc_rank ?? null,
    indikasi_fatigue: item.value.indikasiFatigue ?? item.value.indikasi_fatigue ?? '',
    jam_tidur_kurang_4jam: Boolean(item.value.jamTidurKurang4jam ?? item.value.jam_tidur_kurang_4jam),
    fatigue_berat: Boolean(item.value.fatigueBerat ?? item.value.fatigue_berat),
    deskripsi_penyebab: item.value.deskripsiPenyebab ?? item.value.deskripsi_penyebab ?? '',
    alamat_pengantaran: item.value.alamatPengantaran ?? item.value.alamat_pengantaran ?? '',
    penanggungjawab_pengantaran: item.value.penanggungjawabPengantaran ?? item.value.penanggungjawab_pengantaran ?? '',
    ttd_karyawan: item.value.ttdKaryawan ?? item.value.ttd_karyawan ?? '',
    ttd_pengawas: item.value.ttdPengawas ?? item.value.ttd_pengawas ?? '',
});

const submit = () => {
    form.put(`/one-power/pembinaan/pemulangan-karyawan/${item.value.id}`);
};
</script>

<template>
    <Head title="Edit Pemulangan Karyawan Fatigue - OneClick" />

    <AppLayout :user="user" :notif-counts="notifCounts" :menu="menu">
        <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <Link
                    href="/one-power/pembinaan/pemulangan-karyawan"
                    class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-bold text-slate-700 shadow-2xs hover:bg-slate-50 transition"
                >
                    <ArrowLeft class="h-4 w-4" />
                    Kembali
                </Link>
                <h1 class="text-xl font-extrabold text-slate-800">Edit Pemulangan Karyawan Fatigue</h1>
            </div>
        </div>

        <PemulanganKaryawanForm
            :form="form"
            :on-submit="submit"
            :is-edit="true"
            :document-settings="documentSettings"
        />
    </AppLayout>
</template>
