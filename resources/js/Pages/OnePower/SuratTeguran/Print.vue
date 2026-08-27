<script setup>
import { Head } from '@inertiajs/vue3';

const props = defineProps({
    suratTeguran: { type: Object, required: true },
});

const jenisLabels = {
    jenisAbsen: 'Absen tanpa keterangan',
    jenisPerilaku: 'Perilaku tidak sesuai',
    jenisCeroboh: 'Ceroboh dalam bekerja',
    jenisHasilKerja: 'Hasil kerja tidak sesuai standar',
    jenisMenolakPerintah: 'Menolak perintah atasan',
    jenisPelanggaranPeraturan: 'Pelanggaran peraturan perusahaan',
};

const activeJenis = Object.entries(jenisLabels).filter(([key]) => props.suratTeguran[key]).map(([, label]) => label);
</script>

<template>
    <Head :title="`Cetak ${suratTeguran.noSurat || 'Surat Teguran'} - OneClick`" />

    <div class="mx-auto max-w-2xl p-8 print:p-0">
        <div class="mb-6 flex justify-end print:hidden">
            <button type="button" class="rounded-lg bg-blue-500 px-4 py-2 text-sm font-bold text-white hover:bg-blue-600" @click="window.print()">Cetak</button>
        </div>

        <h1 class="mb-1 text-center text-lg font-extrabold uppercase text-slate-800">Surat Teguran</h1>
        <p class="mb-6 text-center text-sm text-slate-500">No. {{ suratTeguran.noSurat }}</p>

        <table class="mb-4 w-full text-sm">
            <tbody>
                <tr><td class="w-40 py-1 text-slate-500">Nama</td><td class="py-1 font-semibold text-slate-800">: {{ suratTeguran.nama }}</td></tr>
                <tr><td class="py-1 text-slate-500">NRP</td><td class="py-1 font-semibold text-slate-800">: {{ suratTeguran.nrp }}</td></tr>
                <tr><td class="py-1 text-slate-500">Jabatan</td><td class="py-1 font-semibold text-slate-800">: {{ suratTeguran.jabatan || '-' }}</td></tr>
                <tr><td class="py-1 text-slate-500">Departemen</td><td class="py-1 font-semibold text-slate-800">: {{ suratTeguran.departemen || '-' }}</td></tr>
                <tr><td class="py-1 text-slate-500">Tanggal Kejadian</td><td class="py-1 font-semibold text-slate-800">: {{ suratTeguran.tanggalKejadian }}</td></tr>
                <tr><td class="py-1 text-slate-500">Tempat Kejadian</td><td class="py-1 font-semibold text-slate-800">: {{ suratTeguran.tempatKejadian || '-' }}</td></tr>
            </tbody>
        </table>

        <div class="mb-4">
            <p class="mb-1 text-sm font-semibold text-slate-700">Jenis Pelanggaran:</p>
            <ul class="list-inside list-disc text-sm text-slate-700">
                <li v-for="label in activeJenis" :key="label">{{ label }}</li>
                <li v-if="activeJenis.length === 0" class="list-none text-slate-400">-</li>
            </ul>
        </div>

        <div class="mb-6">
            <p class="mb-1 text-sm font-semibold text-slate-700">Penjelasan Singkat:</p>
            <p class="text-sm text-slate-700">{{ suratTeguran.penjelasanSingkat || '-' }}</p>
        </div>

        <p class="mb-6 text-right text-sm text-slate-600">
            {{ suratTeguran.dikeluarkanDi || '-' }}, {{ suratTeguran.dikeluarkanTanggal }}
        </p>

        <div class="grid grid-cols-3 gap-4 text-center text-sm">
            <div>
                <p class="mb-2 text-slate-500">Karyawan</p>
                <img v-if="suratTeguran.ttdKaryawan" :src="suratTeguran.ttdKaryawan" alt="TTD Karyawan" class="mx-auto h-16">
                <p class="mt-2 border-t border-slate-300 pt-1 font-semibold text-slate-700">{{ suratTeguran.nama }}</p>
            </div>
            <div>
                <p class="mb-2 text-slate-500">Atasan</p>
                <img v-if="suratTeguran.ttdAtasan" :src="suratTeguran.ttdAtasan" alt="TTD Atasan" class="mx-auto h-16">
                <p class="mt-2 border-t border-slate-300 pt-1 font-semibold text-slate-700">&nbsp;</p>
            </div>
            <div>
                <p class="mb-2 text-slate-500">Atasan dari Atasan</p>
                <img v-if="suratTeguran.ttdAtasanAtasan" :src="suratTeguran.ttdAtasanAtasan" alt="TTD Atasan dari Atasan" class="mx-auto h-16">
                <p class="mt-2 border-t border-slate-300 pt-1 font-semibold text-slate-700">&nbsp;</p>
            </div>
        </div>
    </div>
</template>
