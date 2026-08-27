<script setup>
import { onMounted } from 'vue';
import { Head } from '@inertiajs/vue3';

defineProps({
    item: { type: Object, required: true },
});

onMounted(() => {
    setTimeout(() => window.print(), 500);
});
</script>

<template>
    <Head :title="`Cetak ${item.nomorSurat || 'Berita Acara'} - OneClick`" />

    <div class="mx-auto max-w-2xl p-8 print:p-0">
        <div class="mb-6 flex justify-end print:hidden">
            <button type="button" class="rounded-lg bg-blue-500 px-4 py-2 text-sm font-bold text-white hover:bg-blue-600" @click="window.print()">Cetak</button>
        </div>

        <h1 class="mb-1 text-center text-lg font-extrabold uppercase text-slate-800">Berita Acara</h1>
        <p class="mb-1 text-center text-sm text-slate-500">No. {{ item.nomorSurat }}</p>
        <p class="mb-6 text-center text-xs text-slate-400">{{ item.noDokumen }} &middot; Rev. {{ item.revisi }} &middot; Efektif {{ item.tglEfektif }}</p>

        <table class="mb-4 w-full text-sm">
            <tbody>
                <tr><td class="w-40 py-1 text-slate-500">Nama</td><td class="py-1 font-semibold text-slate-800">: {{ item.nama }}</td></tr>
                <tr><td class="py-1 text-slate-500">NRP</td><td class="py-1 font-semibold text-slate-800">: {{ item.nrp }}</td></tr>
                <tr><td class="py-1 text-slate-500">Tanggal</td><td class="py-1 font-semibold text-slate-800">: {{ item.tanggal }}</td></tr>
                <tr v-if="item.diketahuiOlehNama"><td class="py-1 text-slate-500">Diketahui Oleh</td><td class="py-1 font-semibold text-slate-800">: {{ item.diketahuiOlehNama }}</td></tr>
            </tbody>
        </table>

        <div class="mb-6">
            <p class="mb-1 text-sm font-semibold text-slate-700">Kronologi:</p>
            <p class="whitespace-pre-line text-sm text-slate-700">{{ item.kronologi }}</p>
        </div>

        <div class="grid grid-cols-2 gap-4 text-center text-sm">
            <div>
                <p class="mb-2 text-slate-500">Karyawan</p>
                <img v-if="item.ttdKaryawan" :src="item.ttdKaryawan" alt="TTD Karyawan" class="mx-auto h-16">
                <p class="mt-2 border-t border-slate-300 pt-1 font-semibold text-slate-700">{{ item.nama || '( .................................. )' }}</p>
            </div>
            <div>
                <p class="mb-2 text-slate-500">Atasan</p>
                <img v-if="item.ttdAtasan" :src="item.ttdAtasan" alt="TTD Atasan" class="mx-auto h-16">
                <p class="mt-2 border-t border-slate-300 pt-1 font-semibold text-slate-700">{{ item.diketahuiOlehNama || '( .................................. )' }}</p>
            </div>
        </div>
    </div>
</template>
