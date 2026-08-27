<script setup>
import { Head } from '@inertiajs/vue3';

const props = defineProps({
    item: { type: Object, required: true },
});

const materiLabel = (value) => (value ? 't' : 'n');
</script>

<template>
    <Head :title="`${item.nomorSurat || 'Coaching Counseling'} - OneClick`" />

    <div class="relative min-h-screen bg-white text-[#333] print:min-h-0" style="font-family: Arial, sans-serif;">
        <div class="fixed right-5 top-5 z-50 flex gap-2.5 print:hidden">
            <button type="button" class="rounded-lg bg-blue-500 px-4 py-2 text-sm font-bold text-white hover:bg-blue-600" @click="window.print()">Cetak</button>
        </div>

        <div class="px-10 py-4 text-2xl font-bold text-white" style="background-color: #a0a0a0; -webkit-print-color-adjust: exact; print-color-adjust: exact;">
            Coaching Counseling
        </div>

        <div class="flex gap-10 p-10">
            <div class="flex-1">
                <div class="mb-7 text-base font-bold">{{ item.nomorSurat }}</div>

                <table class="w-full">
                    <tbody>
                        <tr><td class="w-44 py-2 align-top font-bold text-sm">Created By</td><td class="w-4 py-2 align-top text-sm">:</td><td class="py-2 align-top text-sm">{{ item.coachNama }}</td></tr>
                        <tr><td class="py-2 align-top font-bold text-sm">Tanggal</td><td class="py-2 align-top text-sm">:</td><td class="py-2 align-top text-sm">{{ item.tanggal }}</td></tr>
                        <tr><td class="py-2 align-top font-bold text-sm">NRP</td><td class="py-2 align-top text-sm">:</td><td class="py-2 align-top text-sm">{{ item.nrp }}</td></tr>
                        <tr><td class="py-2 align-top font-bold text-sm">Nama</td><td class="py-2 align-top text-sm">:</td><td class="py-2 align-top text-sm">{{ item.nama }}</td></tr>
                        <tr><td class="py-2 align-top font-bold text-sm">Jabatan</td><td class="py-2 align-top text-sm">:</td><td class="py-2 align-top text-sm">{{ item.jabatan || '-' }}</td></tr>
                        <tr><td class="py-2 align-top font-bold text-sm">Lokasi</td><td class="py-2 align-top text-sm">:</td><td class="py-2 align-top text-sm">{{ item.lokasi || '-' }}</td></tr>
                        <tr><td class="py-2 align-top font-bold text-sm">Waktu Pertemuan</td><td class="py-2 align-top text-sm">:</td><td class="py-2 align-top text-sm">{{ item.waktuPertemuan || '-' }}</td></tr>
                        <tr><td class="py-2 align-top font-bold text-sm">Materi Pribadi</td><td class="py-2 align-top text-sm">:</td><td class="py-2 align-top text-sm">{{ materiLabel(item.materiPribadi) }}</td></tr>
                        <tr><td class="py-2 align-top font-bold text-sm">Materi Keluarga</td><td class="py-2 align-top text-sm">:</td><td class="py-2 align-top text-sm">{{ materiLabel(item.materiKeluarga) }}</td></tr>
                        <tr><td class="py-2 align-top font-bold text-sm">Materi Pekerjaan</td><td class="py-2 align-top text-sm">:</td><td class="py-2 align-top text-sm">{{ materiLabel(item.materiPekerjaan) }}</td></tr>
                        <tr><td class="py-2 align-top font-bold text-sm">Keterangan</td><td class="py-2 align-top text-sm">:</td><td class="whitespace-pre-line py-2 align-top text-sm">{{ item.deskripsi }}</td></tr>
                    </tbody>
                </table>
            </div>

            <div class="flex flex-[2] flex-row items-start gap-4">
                <template v-if="item.fileSlip || item.fileBukti">
                    <div v-if="item.fileSlip" class="flex-1 border border-slate-200 bg-slate-50 p-1.5 text-center">
                        <img :src="item.fileSlip" alt="Slip Coaching" class="mx-auto block max-h-[450px] max-w-full object-contain">
                    </div>
                    <div v-if="item.fileBukti" class="flex-1 border border-slate-200 bg-slate-50 p-1.5 text-center">
                        <img :src="item.fileBukti" alt="Bukti Coaching" class="mx-auto block max-h-[450px] max-w-full object-contain">
                    </div>
                </template>
                <div v-else class="flex h-[300px] flex-1 items-center justify-center border border-slate-200 bg-slate-50 text-center">
                    <p class="text-slate-400">Tidak ada foto yang diupload.</p>
                </div>
            </div>
        </div>

        <div
            class="fixed bottom-0 flex h-[50px] w-full items-center justify-center gap-8 font-bold text-white print:fixed"
            style="background-color: #e31e24; -webkit-print-color-adjust: exact; print-color-adjust: exact;"
        >
            <span>www.ppa.co.id</span>
            <span>pt.ppa.official</span>
        </div>
    </div>
</template>
