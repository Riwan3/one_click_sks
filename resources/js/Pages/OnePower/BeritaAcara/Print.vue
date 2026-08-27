<script setup>
import { computed, onMounted } from 'vue';
import { Head } from '@inertiajs/vue3';

const props = defineProps({
    item: { type: Object, required: true },
});

const BULAN_INDO = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
const HARI_INDO = { Sunday: 'MINGGU', Monday: 'SENIN', Tuesday: 'SELASA', Wednesday: 'RABU', Thursday: 'KAMIS', Friday: 'JUMAT', Saturday: 'SABTU' };

const formatDateIndonesia = (value) => {
    if (! value) return '-';
    const date = new Date(value);
    if (Number.isNaN(date.getTime())) return value;
    return `${date.getDate()} ${BULAN_INDO[date.getMonth() + 1]} ${date.getFullYear()}`;
};

const hariTanggal = computed(() => {
    if (! props.item.tanggal) return '-';
    const date = new Date(props.item.tanggal);
    if (Number.isNaN(date.getTime())) return props.item.tanggal;
    const hari = HARI_INDO[date.toLocaleDateString('en-US', { weekday: 'long' })] ?? '';
    return `${hari}, ${formatDateIndonesia(props.item.tanggal).toUpperCase()}`;
});

const tglEfektifFormatted = computed(() => formatDateIndonesia(props.item.tglEfektif));

onMounted(() => {
    setTimeout(() => window.print(), 500);
});
</script>

<template>
    <Head :title="`Berita Acara - ${item.nama} - OneClick`" />

    <div class="mx-auto max-w-3xl bg-white p-6 text-black print:p-0">
        <div class="mb-4 flex justify-end print:hidden">
            <button type="button" class="rounded-lg bg-blue-500 px-4 py-2 text-sm font-bold text-white hover:bg-blue-600" @click="window.print()">Cetak</button>
        </div>

        <!-- Letterhead -->
        <table class="w-full border-collapse border border-black text-sm">
            <tbody>
                <tr>
                    <td class="w-1/5 border border-black p-2 text-center align-middle">
                        <div class="mx-auto flex h-14 w-14 items-center justify-center rounded border border-slate-400 text-[10px] font-semibold text-slate-500">LOGO</div>
                        <div class="mt-1 text-sm font-bold tracking-wide">PPA</div>
                    </td>
                    <td class="w-[45%] border border-black p-2 text-center align-middle font-bold">
                        <div class="text-[15px] tracking-wide">FORMULIR</div>
                        <div class="text-lg tracking-wide">BERITA ACARA</div>
                    </td>
                    <td class="w-[35%] border border-black p-0 align-top">
                        <table class="w-full border-collapse text-[9.5pt]">
                            <tbody>
                                <tr><td class="whitespace-nowrap border-r border-black px-1.5 py-0.5">No. Dokumen</td><td class="px-1.5 py-0.5">: {{ item.noDokumen }}</td></tr>
                                <tr><td class="whitespace-nowrap border-r border-t border-black px-1.5 py-0.5">Revisi</td><td class="border-t border-black px-1.5 py-0.5">: {{ item.revisi }}</td></tr>
                                <tr><td class="whitespace-nowrap border-r border-t border-black px-1.5 py-0.5">Tgl. Efektif</td><td class="border-t border-black px-1.5 py-0.5">: {{ tglEfektifFormatted }}</td></tr>
                                <tr><td class="whitespace-nowrap border-r border-t border-black px-1.5 py-0.5">Halaman</td><td class="border-t border-black px-1.5 py-0.5">: 1 dari 1</td></tr>
                            </tbody>
                        </table>
                    </td>
                </tr>
            </tbody>
        </table>

        <!-- Body -->
        <table class="mb-4 mt-4 w-full text-sm">
            <tbody>
                <tr><td class="w-44 py-1 align-top uppercase">Nama</td><td class="w-4 py-1 align-top">:</td><td class="py-1 align-top font-semibold uppercase">{{ item.nama }}</td></tr>
                <tr><td class="py-1 align-top uppercase">Jabatan</td><td class="py-1 align-top">:</td><td class="py-1 align-top font-semibold uppercase">{{ item.jabatan || '-' }}</td></tr>
                <tr><td class="py-1 align-top uppercase">Hari, Tanggal</td><td class="py-1 align-top">:</td><td class="py-1 align-top font-semibold">{{ hariTanggal }}</td></tr>
                <tr><td class="py-1 align-top uppercase">Kronologi Kejadian</td><td class="py-1 align-top">:</td><td class="py-1 align-top"></td></tr>
            </tbody>
        </table>

        <div class="mb-8 min-h-[260px] whitespace-pre-line text-justify text-sm leading-relaxed">{{ item.kronologi }}</div>

        <!-- Signatures -->
        <table class="w-full text-center text-sm">
            <tbody>
                <tr>
                    <td class="w-1/2 px-5 align-top">
                        <p>Diketahui oleh,</p>
                        <div class="relative flex h-[75px] items-end justify-center">
                            <img v-if="item.ttdAtasan" :src="item.ttdAtasan" alt="TTD Atasan" class="max-h-[75px] max-w-[180px]">
                        </div>
                        <p class="font-bold uppercase">{{ item.diketahuiOlehNama || ' ' }}</p>
                        <p>( .................................. )</p>
                    </td>
                    <td class="w-1/2 px-5 align-top">
                        <p>Wara, {{ formatDateIndonesia(item.tanggal) }}</p>
                        <p>Dibuat Oleh,</p>
                        <div class="relative flex h-[75px] items-end justify-center">
                            <img v-if="item.ttdKaryawan" :src="item.ttdKaryawan" alt="TTD Karyawan" class="max-h-[75px] max-w-[180px]">
                        </div>
                        <p class="font-bold uppercase">{{ item.nama }}</p>
                        <p>( .................................. )</p>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>
