<script setup>
import { computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft } from '@lucide/vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import PemanggilanKaryawanForm from './Partials/PemanggilanKaryawanForm.vue';

const props = defineProps({
    user: { type: Object, required: true },
    notifCounts: { type: Object, required: true },
    menu: { type: Array, required: true },
    targetPemanggilan: { type: Object, required: true },
    documentSettings: { type: Object, default: () => ({}) },
});

const item = computed(() => props.targetPemanggilan?.data ?? props.targetPemanggilan ?? {});

const form = useForm({
    employee_id: item.value.employeeId ?? item.value.employee_id,
    nrp: item.value.nrp,
    nama: item.value.nama,
    jabatan: item.value.jabatan ?? '',
    no_surat: item.value.noSurat ?? item.value.no_surat ?? '',
    alasan_pemanggilan: item.value.alasanPemanggilan ?? item.value.alasan_pemanggilan ?? '',
    tempat: item.value.tempat ?? 'CSA Inggris',
    jam_pemanggilan: item.value.jamPemanggilan ?? item.value.jam_pemanggilan ?? '',
    tanggal_pemanggilan: item.value.tanggalPemanggilan ?? item.value.tanggal_pemanggilan ?? '',
    ttd_pemanggilan: item.value.ttdPemanggilan ?? item.value.ttd_pemanggilan ?? '',
    nama_manpower: item.value.namaManpower ?? item.value.nama_manpower ?? 'Manpower Dept Produksi',
    fatigue_periode: item.value.fatiguePeriode ?? item.value.fatigue_periode ?? null,
    ttd_pemanggil: item.value.ttdPemanggil ?? item.value.ttd_pemanggil ?? '',
    ttd_karyawan: item.value.ttdKaryawan ?? item.value.ttd_karyawan ?? '',
    eviden_1: null,
    eviden_2: null,
    foto: null,
    existing_eviden_1: item.value.eviden1 ?? item.value.eviden_1 ?? null,
    existing_eviden_2: item.value.eviden2 ?? item.value.eviden_2 ?? null,
    existing_foto: item.value.foto ?? null,
    hapus_eviden_1: false,
    hapus_eviden_2: false,
    hapus_foto: false,
});

const submit = () => {
    form.transform((data) => ({
        ...data,
        _method: 'PUT',
    })).post(`/one-power/pembinaan/pemanggilan-karyawan/${item.value.id}`, {
        forceFormData: true,
    });
};
</script>

<template>
    <Head title="Edit Pemanggilan Karyawan - OneClick" />

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
                <h1 class="text-xl font-extrabold text-slate-800">Edit Pemanggilan Karyawan</h1>
            </div>
        </div>

        <PemanggilanKaryawanForm
            :form="form"
            :on-submit="submit"
            :is-edit="true"
            :document-settings="documentSettings"
        />
    </AppLayout>
</template>
