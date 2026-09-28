<script setup>
import { computed } from 'vue';
import { Head } from '@inertiajs/vue3';

const props = defineProps({
    skPembinaan: { type: Object, required: true },
});

const BULAN_INDO = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

const formatDateIndonesia = (value) => {
    if (! value) return '-';
    const date = new Date(value);
    if (Number.isNaN(date.getTime())) return value;
    return `${date.getDate()} ${BULAN_INDO[date.getMonth() + 1]} ${date.getFullYear()}`;
};

const formatDateSlash = (value) => {
    if (! value) return '-';
    const date = new Date(value);
    if (Number.isNaN(date.getTime())) return value;
    const pad = (n) => String(n).padStart(2, '0');
    return `${pad(date.getDate())} - ${pad(date.getMonth() + 1)} - ${date.getFullYear()}`;
};

const dikeluarkanTanggalFormatted = computed(() => formatDateIndonesia(props.skPembinaan.dikeluarkanTanggal));
</script>

<template>
    <Head :title="`Cetak ${skPembinaan.nomorSurat || 'SK Pembinaan'} - OneClick`" />

    <div class="mx-auto max-w-3xl bg-white p-8 text-black print:p-0">
        <div class="mb-4 flex justify-end print:hidden">
            <button type="button" class="rounded-lg bg-blue-500 px-4 py-2 text-sm font-bold text-white hover:bg-blue-600" @click="window.print()">Cetak</button>
        </div>

        <!-- Dual logo header -->
        <div class="mb-2 flex items-start justify-between">
            <div class="flex h-16 w-24 items-center justify-center rounded border border-slate-400 text-[10px] font-semibold text-slate-500">ADARO LOGO</div>
            <div class="flex h-16 w-16 items-center justify-center rounded border border-slate-400 text-[10px] font-semibold text-slate-500">HSE LOGO</div>
        </div>

        <h1 class="mb-4 text-center text-base font-bold uppercase leading-tight">
            Berita Acara<br>
            Pembinaan Dan Pemberian Sanksi
        </h1>

        <p class="mb-2 text-sm">Saya yang bertanda tangan dibawah ini :</p>

        <table class="mb-4 ml-8 w-[calc(100%-2rem)] text-sm">
            <tbody>
                <tr><td class="w-48 py-0.5 align-top">Nama</td><td class="w-5 py-0.5 align-top">:</td><td class="py-0.5 align-top font-bold">{{ skPembinaan.nama }}</td></tr>
                <tr><td class="py-0.5 align-top">No Karyawan</td><td class="py-0.5 align-top">:</td><td class="py-0.5 align-top font-bold">{{ skPembinaan.nrp }}</td></tr>
                <tr><td class="py-0.5 align-top">Jabatan</td><td class="py-0.5 align-top">:</td><td class="py-0.5 align-top font-bold">{{ skPembinaan.jabatan || '-' }}</td></tr>
                <tr><td class="py-0.5 align-top">Perusahaan</td><td class="py-0.5 align-top">:</td><td class="py-0.5 align-top font-bold">{{ skPembinaan.perusahaan }}</td></tr>
                <tr><td class="py-0.5 align-top">Lokasi</td><td class="py-0.5 align-top">:</td><td class="py-0.5 align-top font-bold">{{ skPembinaan.lokasi || '-' }}</td></tr>
                <tr><td class="py-0.5 align-top">Tanggal Pelanggaran</td><td class="py-0.5 align-top">:</td><td class="py-0.5 align-top font-bold">{{ formatDateSlash(skPembinaan.tanggalPelanggaran) }}</td></tr>
                <tr><td class="py-0.5 align-top">Jam</td><td class="py-0.5 align-top">:</td><td class="py-0.5 align-top font-bold">{{ skPembinaan.jamPelanggaran || '-' }}</td></tr>
                <tr><td class="py-0.5 align-top">Jenis Pelanggaran</td><td class="py-0.5 align-top">:</td><td class="py-0.5 align-top font-bold">{{ skPembinaan.jenisPelanggaran || '-' }}</td></tr>
                <tr><td class="py-0.5 align-top">Berlaku Sampai Dengan</td><td class="py-0.5 align-top">:</td><td class="py-0.5 align-top font-bold">{{ formatDateSlash(skPembinaan.berlakuSampai) }}</td></tr>
                <tr><td class="py-0.5 align-top">Sesuai Dengan SPDK</td><td class="py-0.5 align-top">:</td><td class="py-0.5 align-top font-bold">{{ skPembinaan.spdkRef || '-' }} - {{ skPembinaan.keterangan || '-' }}</td></tr>
                <tr><td class="py-0.5 align-top">Jumlah Pelubangan</td><td class="py-0.5 align-top">:</td><td class="py-0.5 align-top font-bold">{{ skPembinaan.lubang || 0 }} Lubang</td></tr>
            </tbody>
        </table>

        <p class="mb-3 text-justify text-[10.5pt] leading-relaxed">
            dan saya berkomitmen tidak akan mengulangi kembali aturan yang berlaku di area kerja PT Adaro Indonesia, dan mengulangi akan disesuaikan dengan SPDK yang berlaku.
        </p>

        <!-- Signature row 1: Karyawan / Atasan Langsung -->
        <table class="mb-2 mt-3 w-full text-center text-sm">
            <tbody>
                <tr>
                    <td class="w-1/2 px-2 pt-7 align-top">
                        <p>Saya Yang Menyatakan</p>
                        <div class="relative flex h-[70px] items-end justify-center">
                            <img v-if="skPembinaan.ttdKaryawan" :src="skPembinaan.ttdKaryawan" alt="TTD Karyawan" class="max-h-[70px] max-w-[170px]">
                        </div>
                        <p class="inline-block min-w-[140px] border-b-[1.5px] border-black px-2 pb-0.5 font-bold capitalize">{{ skPembinaan.nama }}</p>
                        <p class="mt-1 text-[9.5pt]">Karyawan</p>
                    </td>
                    <td class="w-1/2 px-2 align-top">
                        <p class="mb-1.5">Adaro, {{ dikeluarkanTanggalFormatted }}</p>
                        <p>Mengetahui</p>
                        <div class="relative flex h-[70px] items-end justify-center">
                            <img v-if="skPembinaan.ttdAtasan" :src="skPembinaan.ttdAtasan" alt="TTD Atasan" class="max-h-[70px] max-w-[170px]">
                        </div>
                        <p class="inline-block min-w-[140px] border-b-[1.5px] border-black px-2 pb-0.5 font-bold capitalize">{{ skPembinaan.pengawasName || '&nbsp;' }}</p>
                        <p class="mt-1 text-[9.5pt]">Atasan Langsung</p>
                    </td>
                </tr>
            </tbody>
        </table>

        <p class="mt-4 text-center text-sm">Mengetahui, PT Adaro Indonesia</p>

        <!-- Signature row 2: Perwakilan PJA / HSE Operation -->
        <table class="mt-3 w-full text-center text-sm">
            <tbody>
                <tr>
                    <td class="w-1/2 px-2 align-top">
                        <div class="relative flex h-[70px] items-end justify-center">
                            <img v-if="skPembinaan.ttdPja" :src="skPembinaan.ttdPja" alt="TTD Perwakilan PJA" class="max-h-[70px] max-w-[170px]">
                        </div>
                        <p class="inline-block min-w-[140px] border-b-[1.5px] border-black px-2 pb-0.5 font-bold">&nbsp;</p>
                        <p class="mt-1 text-[9.5pt]">Perwakilan PJA</p>
                    </td>
                    <td class="w-1/2 px-2 align-top">
                        <div class="relative flex h-[70px] items-end justify-center">
                            <img v-if="skPembinaan.ttdHse" :src="skPembinaan.ttdHse" alt="TTD HSE Operation" class="max-h-[70px] max-w-[170px]">
                        </div>
                        <p class="inline-block min-w-[140px] border-b-[1.5px] border-black px-2 pb-0.5 font-bold">&nbsp;</p>
                        <p class="mt-1 text-[9.5pt]">HSE Operation</p>
                    </td>
                </tr>
            </tbody>
        </table>

        <!-- Catatan -->
        <div class="mt-4 text-[9.5pt]">
            <p class="mb-1 italic underline">Catatan:</p>
            <ul class="list-none pl-5">
                <li class="relative mb-0.5 text-justify before:absolute before:-left-[15px] before:content-['*']">Jika pembinaan/ sanksi didapatkan pasca temuan sidak/ inspeksi/ lainnya oleh perwakilan PT Adaro Indonesia, maka kolom Perwakilan PJA wajib menandatangani Berita Acara</li>
                <li class="relative mb-0.5 text-justify before:absolute before:-left-[15px] before:content-['*']">Jika pembinaan/ sanksi didapatkan pasca temuan sidak/ inspeksi/ lainnya oleh internal perusahaan mitra kerja, maka Berita Acara cukup ditandatangani oleh HSE Operation</li>
                <li class="relative mb-0.5 text-justify before:absolute before:-left-[15px] before:content-['*']">Lampiran dokumentasi mine permit sebelum dan sesudah pelubangan</li>
            </ul>
        </div>

        <!-- Doc code footer -->
        <div class="mt-4 flex justify-between border-t border-black pt-1 text-[9.5pt]">
            <div>F-AI-IMS-18-034</div>
            <div>Rev. 01</div>
        </div>

        <!-- Lampiran Dokumentasi Foto jika ada -->
        <div v-if="skPembinaan.foto" class="mt-8 pt-6 border-t-2 border-dashed border-slate-300 print:border-none print:pt-0 print:mt-0" style="page-break-before: always;">
            <div class="mb-4 flex items-start justify-between">
                <div class="flex h-12 w-20 items-center justify-center rounded border border-slate-400 text-[9px] font-semibold text-slate-500">ADARO LOGO</div>
                <div class="flex h-12 w-12 items-center justify-center rounded border border-slate-400 text-[9px] font-semibold text-slate-500">HSE LOGO</div>
            </div>

            <h2 class="mb-1 text-center text-sm font-bold uppercase leading-tight">
                Lampiran Dokumentasi Foto Pelanggaran / Mine Permit
            </h2>
            <div class="text-center text-xs text-slate-600 mb-6">
                Nomor Surat: <span class="font-bold">{{ skPembinaan.nomorSurat || '-' }}</span> &bull; 
                Karyawan: <span class="font-bold">{{ skPembinaan.nrp }} - {{ skPembinaan.nama }}</span>
            </div>

            <div class="flex flex-col items-center justify-center">
                <div class="max-w-md w-full border border-slate-300 rounded-lg p-2 bg-white shadow-xs">
                    <img 
                        :src="skPembinaan.foto" 
                        alt="Dokumentasi Pelanggaran" 
                        class="w-full max-h-[520px] object-contain rounded"
                    />
                </div>
                <p class="mt-2 text-xs text-slate-500 italic">
                    Dokumentasi fisik pelanggaran / sanksi pelubangan mine permit
                </p>
            </div>
        </div>
    </div>
</template>
