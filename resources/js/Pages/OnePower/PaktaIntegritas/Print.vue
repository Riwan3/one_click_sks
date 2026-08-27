<script setup>
import { computed } from 'vue';
import { Head } from '@inertiajs/vue3';

const props = defineProps({
    item: { type: Object, required: true },
});

const BULAN_INDO = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

const formatDateIndonesia = (value) => {
    if (! value) return '-';
    const date = new Date(value);
    if (Number.isNaN(date.getTime())) return value;
    return `${date.getDate()} ${BULAN_INDO[date.getMonth() + 1]} ${date.getFullYear()}`;
};

const tanggalFormatted = computed(() => formatDateIndonesia(props.item.tanggal));
</script>

<template>
    <Head :title="`Pakta Integritas - ${item.nama} - OneClick`" />

    <div class="mx-auto max-w-3xl bg-white p-8 font-serif text-black print:p-0">
        <div class="mb-4 flex justify-end print:hidden font-sans">
            <button type="button" class="rounded-lg bg-blue-500 px-4 py-2 text-sm font-bold text-white hover:bg-blue-600" @click="window.print()">Cetak</button>
        </div>

        <!-- Letterhead -->
        <div class="mb-6 flex items-center justify-center gap-4">
            <div class="flex h-16 w-16 items-center justify-center rounded border border-slate-400 font-sans text-[10px] font-semibold text-slate-500">LOGO</div>
            <div class="font-sans text-xl font-bold tracking-wide">PUTRA PERKASA ABADI</div>
        </div>

        <h1 class="mb-6 text-center text-base font-bold tracking-wide">PAKTA INTEGRITAS</h1>

        <div class="mb-4 leading-snug">
            <p>Kepada Yth.</p>
            <p class="mt-3">Pimpinan Dept. Produksi PT PPA Jobsite Adaro</p>
            <p>Di tempat,</p>
        </div>

        <p class="mb-2">Dengan ini saya yang bertanda tangan dibawah ini :</p>

        <table class="mb-3 w-full text-sm">
            <tbody>
                <tr><td class="w-24 py-0.5 align-top">NAMA</td><td class="w-5 py-0.5 align-top">:</td><td class="py-0.5 align-top font-bold">{{ item.nama?.toUpperCase() }}</td></tr>
                <tr><td class="py-0.5 align-top">NRP</td><td class="py-0.5 align-top">:</td><td class="py-0.5 align-top font-bold">{{ item.nrp }}</td></tr>
                <tr><td class="py-0.5 align-top">JABATAN</td><td class="py-0.5 align-top">:</td><td class="py-0.5 align-top font-bold uppercase">{{ item.jabatan || '-' }}</td></tr>
            </tbody>
        </table>

        <div class="mb-8 min-h-[220px] text-justify leading-loose">{{ item.pernyataan }}</div>

        <p class="mb-8">{{ item.tempat }}, {{ tanggalFormatted }}</p>

        <table class="w-full text-center text-sm">
            <tbody>
                <tr>
                    <td class="w-1/3 px-2 align-top">
                        <p class="mb-8">Yang Bertanda Tangan,</p>
                        <div class="relative flex h-[50px] items-center justify-center">
                            <img v-if="item.ttdKaryawan" :src="item.ttdKaryawan" alt="TTD Karyawan" class="max-h-[70px] max-w-[140px]">
                        </div>
                        <p class="mt-1 whitespace-nowrap font-bold">( {{ item.nama?.toUpperCase() || '..........................................' }} )</p>
                    </td>
                    <td class="w-1/3 px-2 align-top">
                        <p class="mb-8">Atasan Langsung</p>
                        <div class="relative flex h-[50px] items-center justify-center">
                            <img v-if="item.ttdAtasan" :src="item.ttdAtasan" alt="TTD Atasan" class="max-h-[70px] max-w-[140px]">
                        </div>
                        <p class="mt-1 whitespace-nowrap font-bold">( {{ item.atasanNama?.toUpperCase() || '..........................................' }} )</p>
                    </td>
                    <td class="w-1/3 px-2 align-top">
                        <p class="mb-8">Mengetahui,</p>
                        <div class="relative flex h-[50px] items-center justify-center">
                            <img v-if="item.ttdMengetahui" :src="item.ttdMengetahui" alt="TTD Mengetahui" class="max-h-[70px] max-w-[140px]">
                        </div>
                        <p class="mt-1 whitespace-nowrap font-bold">( {{ item.mengetahuiNama?.toUpperCase() || '..........................................' }} )</p>
                    </td>
                </tr>
            </tbody>
        </table>

        <div class="mt-8 text-sm leading-relaxed">
            <div><span class="inline-block w-12">CC</span> : {{ item.cc }}</div>
            <div><span class="inline-block w-12">Arsip</span> : {{ item.arsip }}</div>
        </div>

        <!-- Bottom decoration -->
        <div class="mt-10">
            <div class="h-[7px] w-full bg-amber-500 print:bg-amber-500" style="-webkit-print-color-adjust: exact; print-color-adjust: exact;"></div>
            <div class="mt-2 text-center font-sans text-[8pt] leading-tight text-slate-600">
                Gedung Office 8, Lantai 8, SCBD Lot 28. Jl. Jend Sudirman<br>
                Kav. 52-53 Senayan, Kebayoran Baru, Jakarta Selatan, 12190.<br>
                Telp. +62 21 5790 3456 | www.ppa.co.id
            </div>
        </div>
    </div>
</template>
