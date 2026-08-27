<script setup>
import { Head } from '@inertiajs/vue3';

defineProps({
    skPembinaan: { type: Object, required: true },
});
</script>

<template>
    <Head :title="`Cetak ${skPembinaan.nomorSurat || 'SK Pembinaan'} - OneClick`" />

    <div class="mx-auto max-w-2xl p-8 print:p-0">
        <div class="mb-6 flex justify-end print:hidden">
            <button type="button" class="rounded-lg bg-blue-500 px-4 py-2 text-sm font-bold text-white hover:bg-blue-600" @click="window.print()">Cetak</button>
        </div>

        <h1 class="mb-1 text-center text-lg font-extrabold uppercase text-slate-800">Surat Keputusan Pembinaan</h1>
        <p class="mb-6 text-center text-sm text-slate-500">No. {{ skPembinaan.nomorSurat }}</p>

        <table class="mb-4 w-full text-sm">
            <tbody>
                <tr><td class="w-40 py-1 text-slate-500">Perusahaan</td><td class="py-1 font-semibold text-slate-800">: {{ skPembinaan.perusahaan }}</td></tr>
                <tr><td class="py-1 text-slate-500">Nama</td><td class="py-1 font-semibold text-slate-800">: {{ skPembinaan.nama }}</td></tr>
                <tr><td class="py-1 text-slate-500">NRP</td><td class="py-1 font-semibold text-slate-800">: {{ skPembinaan.nrp }}</td></tr>
                <tr><td class="py-1 text-slate-500">Jabatan</td><td class="py-1 font-semibold text-slate-800">: {{ skPembinaan.jabatan || '-' }}</td></tr>
                <tr><td class="py-1 text-slate-500">Unit / Lokasi</td><td class="py-1 font-semibold text-slate-800">: {{ skPembinaan.lokasi || '-' }}</td></tr>
                <tr><td class="py-1 text-slate-500">Tanggal Pelanggaran</td><td class="py-1 font-semibold text-slate-800">: {{ skPembinaan.tanggalPelanggaran }}</td></tr>
                <tr><td class="py-1 text-slate-500">Berlaku Sampai</td><td class="py-1 font-semibold text-slate-800">: {{ skPembinaan.berlakuSampai }}</td></tr>
                <tr><td class="py-1 text-slate-500">Jumlah Lubang</td><td class="py-1 font-semibold text-slate-800">: {{ skPembinaan.lubang }}</td></tr>
            </tbody>
        </table>

        <div class="mb-4">
            <p class="mb-1 text-sm font-semibold text-slate-700">Jenis Pelanggaran:</p>
            <p class="text-sm text-slate-700">{{ skPembinaan.jenisPelanggaran || '-' }}</p>
        </div>

        <div class="mb-6">
            <p class="mb-1 text-sm font-semibold text-slate-700">Keterangan:</p>
            <p class="text-sm text-slate-700">{{ skPembinaan.keterangan || '-' }}</p>
        </div>

        <p class="mb-6 text-right text-sm text-slate-600">
            {{ skPembinaan.dikeluarkanDi || '-' }}, {{ skPembinaan.dikeluarkanTanggal }}
        </p>

        <div class="grid grid-cols-2 gap-4 text-center text-sm">
            <div>
                <p class="mb-2 text-slate-500">Karyawan</p>
                <img v-if="skPembinaan.ttdKaryawan" :src="skPembinaan.ttdKaryawan" alt="TTD Karyawan" class="mx-auto h-16">
                <p class="mt-2 border-t border-slate-300 pt-1 font-semibold text-slate-700">{{ skPembinaan.nama }}</p>
            </div>
            <div>
                <p class="mb-2 text-slate-500">Atasan</p>
                <img v-if="skPembinaan.ttdAtasan" :src="skPembinaan.ttdAtasan" alt="TTD Atasan" class="mx-auto h-16">
                <p class="mt-2 border-t border-slate-300 pt-1 font-semibold text-slate-700">{{ skPembinaan.pengawasName || '&nbsp;' }}</p>
            </div>
        </div>
    </div>
</template>
