<script setup>
import { Head } from '@inertiajs/vue3';

defineProps({
    item: { type: Object, required: true },
});
</script>

<template>
    <Head :title="`Cetak ${item.noSurat || 'Pemulangan Karyawan'} - OneClick`" />

    <div class="mx-auto max-w-2xl p-8 print:p-0">
        <div class="mb-6 flex justify-end print:hidden">
            <button type="button" class="rounded-lg bg-blue-500 px-4 py-2 text-sm font-bold text-white hover:bg-blue-600" @click="window.print()">Cetak</button>
        </div>

        <h1 class="mb-1 text-center text-lg font-extrabold uppercase text-slate-800">Surat Pemulangan Karyawan</h1>
        <p class="mb-6 text-center text-sm text-slate-500">No. {{ item.noSurat }}</p>

        <table class="mb-4 w-full text-sm">
            <tbody>
                <tr><td class="w-40 py-1 text-slate-500">Nama</td><td class="py-1 font-semibold text-slate-800">: {{ item.nama }}</td></tr>
                <tr><td class="py-1 text-slate-500">NRP</td><td class="py-1 font-semibold text-slate-800">: {{ item.nrp }}</td></tr>
                <tr><td class="py-1 text-slate-500">Jabatan</td><td class="py-1 font-semibold text-slate-800">: {{ item.jabatan || '-' }}</td></tr>
                <tr><td class="py-1 text-slate-500">Departemen</td><td class="py-1 font-semibold text-slate-800">: {{ item.departemen || '-' }}</td></tr>
                <tr><td class="py-1 text-slate-500">Lokasi</td><td class="py-1 font-semibold text-slate-800">: {{ item.lokasi || '-' }}</td></tr>
                <tr><td class="py-1 text-slate-500">Tanggal</td><td class="py-1 font-semibold text-slate-800">: {{ item.tanggal }}</td></tr>
            </tbody>
        </table>

        <div class="mb-4">
            <p class="mb-1 text-sm font-semibold text-slate-700">Indikasi Fatigue:</p>
            <p class="text-sm text-slate-700">{{ item.indikasiFatigue || '-' }}</p>
            <ul class="mt-2 list-inside list-disc text-sm text-slate-700">
                <li v-if="item.jamTidurKurang4jam">Jam tidur kurang dari 4 jam</li>
                <li v-if="item.fatigueBerat">Fatigue berat</li>
            </ul>
        </div>

        <div class="mb-4">
            <p class="mb-1 text-sm font-semibold text-slate-700">Deskripsi Penyebab:</p>
            <p class="text-sm text-slate-700">{{ item.deskripsiPenyebab || '-' }}</p>
        </div>

        <div class="mb-6">
            <p class="mb-1 text-sm font-semibold text-slate-700">Alamat Pengantaran:</p>
            <p class="text-sm text-slate-700">{{ item.alamatPengantaran || '-' }}</p>
            <p class="mt-1 text-sm text-slate-700">Penanggung Jawab: {{ item.penanggungjawabPengantaran || '-' }}</p>
        </div>

        <div class="grid grid-cols-2 gap-4 text-center text-sm">
            <div>
                <p class="mb-2 text-slate-500">Karyawan</p>
                <img v-if="item.ttdKaryawan" :src="item.ttdKaryawan" alt="TTD Karyawan" class="mx-auto h-16">
                <p class="mt-2 border-t border-slate-300 pt-1 font-semibold text-slate-700">{{ item.nama }}</p>
            </div>
            <div>
                <p class="mb-2 text-slate-500">Pengawas</p>
                <img v-if="item.ttdPengawas" :src="item.ttdPengawas" alt="TTD Pengawas" class="mx-auto h-16">
                <p class="mt-2 border-t border-slate-300 pt-1 font-semibold text-slate-700">&nbsp;</p>
            </div>
        </div>
    </div>
</template>
