<script setup>
import { Head } from '@inertiajs/vue3';

const props = defineProps({
    suratPeringatan: { type: Object, required: true },
});

const pelanggaranLabels = {
    pelanggaranAbsenTerlambat: 'Absen / terlambat',
    pelanggaranPerilaku: 'Perilaku tidak sesuai',
    pelanggaranCerobohLalai: 'Ceroboh / lalai',
    pelanggaranHasilKerja: 'Hasil kerja tidak sesuai standar',
    pelanggaranMenolakPerintah: 'Menolak perintah atasan',
    pelanggaranPeraturan: 'Pelanggaran peraturan perusahaan',
};

const activeList = Object.entries(pelanggaranLabels).filter(([key]) => props.suratPeringatan[key]).map(([, label]) => label);
</script>

<template>
    <Head :title="`Cetak ${suratPeringatan.nomorSp || 'Surat Peringatan'} - OneClick`" />

    <div class="mx-auto max-w-2xl p-8 print:p-0">
        <div class="mb-6 flex justify-end print:hidden">
            <button type="button" class="rounded-lg bg-blue-500 px-4 py-2 text-sm font-bold text-white hover:bg-blue-600" @click="window.print()">Cetak</button>
        </div>

        <h1 class="mb-1 text-center text-lg font-extrabold uppercase text-slate-800">{{ suratPeringatan.jenisPembinaan }}</h1>
        <p class="mb-6 text-center text-sm text-slate-500">No. {{ suratPeringatan.nomorSp }}</p>

        <table class="mb-4 w-full text-sm">
            <tbody>
                <tr><td class="w-40 py-1 text-slate-500">Nama</td><td class="py-1 font-semibold text-slate-800">: {{ suratPeringatan.nama }}</td></tr>
                <tr><td class="py-1 text-slate-500">NRP</td><td class="py-1 font-semibold text-slate-800">: {{ suratPeringatan.nrp }}</td></tr>
                <tr><td class="py-1 text-slate-500">Jabatan</td><td class="py-1 font-semibold text-slate-800">: {{ suratPeringatan.jabatan || '-' }}</td></tr>
                <tr><td class="py-1 text-slate-500">Unit</td><td class="py-1 font-semibold text-slate-800">: {{ suratPeringatan.unit || '-' }}</td></tr>
                <tr><td class="py-1 text-slate-500">Tanggal Kejadian</td><td class="py-1 font-semibold text-slate-800">: {{ suratPeringatan.tanggalKejadian }}</td></tr>
                <tr><td class="py-1 text-slate-500">Tempat Kejadian</td><td class="py-1 font-semibold text-slate-800">: {{ suratPeringatan.tempatKejadian || '-' }}</td></tr>
                <tr><td class="py-1 text-slate-500">Masa Berlaku</td><td class="py-1 font-semibold text-slate-800">: {{ suratPeringatan.sanksiMasaBerlaku }} bulan</td></tr>
            </tbody>
        </table>

        <div class="mb-4">
            <p class="mb-1 text-sm font-semibold text-slate-700">Jenis Pelanggaran:</p>
            <ul class="list-inside list-disc text-sm text-slate-700">
                <li v-for="label in activeList" :key="label">{{ label }}</li>
                <li v-if="activeList.length === 0" class="list-none text-slate-400">-</li>
            </ul>
        </div>

        <div class="mb-6">
            <p class="mb-1 text-sm font-semibold text-slate-700">Detail Pelanggaran:</p>
            <p class="text-sm text-slate-700">{{ suratPeringatan.detailPelanggaran || '-' }}</p>
        </div>

        <p class="mb-6 text-right text-sm text-slate-600">
            {{ suratPeringatan.dikeluarkanDi || '-' }}, {{ suratPeringatan.dikeluarkanTanggal }}
        </p>

        <div class="grid grid-cols-2 gap-4 text-center text-sm">
            <div>
                <p class="mb-2 text-slate-500">Karyawan</p>
                <img v-if="suratPeringatan.ttdKaryawan" :src="suratPeringatan.ttdKaryawan" alt="TTD Karyawan" class="mx-auto h-16">
                <p class="mt-2 border-t border-slate-300 pt-1 font-semibold text-slate-700">{{ suratPeringatan.nama }}</p>
            </div>
            <div>
                <p class="mb-2 text-slate-500">Atasan</p>
                <img v-if="suratPeringatan.ttdAtasan" :src="suratPeringatan.ttdAtasan" alt="TTD Atasan" class="mx-auto h-16">
                <p class="mt-2 border-t border-slate-300 pt-1 font-semibold text-slate-700">{{ suratPeringatan.pengawasName || '&nbsp;' }}</p>
            </div>
        </div>
    </div>
</template>
