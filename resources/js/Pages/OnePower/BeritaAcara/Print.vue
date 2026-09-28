<script setup>
import { computed, onMounted } from 'vue';
import { Head } from '@inertiajs/vue3';

const props = defineProps({
    item: { type: Object, required: true },
    documentSettings: { type: Object, default: () => ({}) },
});

const printItem = computed(() => props.item?.data ?? props.item ?? {});

const effectiveNoDokumen = computed(() => props.documentSettings?.noDokumen || printItem.value?.noDokumen || 'PPA-ADRO-F-SHE-24D');
const effectiveRevisi = computed(() => props.documentSettings?.revisi || printItem.value?.revisi || '0');
const effectiveLokasi = computed(() => props.documentSettings?.lokasi || 'Wara');

const BULAN_INDO = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
const HARI_INDO = { Sunday: 'MINGGU', Monday: 'SENIN', Tuesday: 'SELASA', Wednesday: 'RABU', Thursday: 'KAMIS', Friday: 'JUMAT', Saturday: 'SABTU' };

const formatDateIndonesia = (value) => {
    if (! value) return '-';
    const date = new Date(value);
    if (Number.isNaN(date.getTime())) return value;
    return `${date.getDate()} ${BULAN_INDO[date.getMonth() + 1]} ${date.getFullYear()}`;
};

const hariTanggal = computed(() => {
    const rawDate = printItem.value?.tanggal;
    if (! rawDate) return '-';
    const date = new Date(rawDate);
    if (Number.isNaN(date.getTime())) return rawDate;
    const dayName = date.toLocaleDateString('en-US', { weekday: 'long' });
    const hari = HARI_INDO[dayName] ?? dayName;
    return `${hari}, ${formatDateIndonesia(rawDate).toUpperCase()}`;
});

const formattedTglEfektif = computed(() => formatDateIndonesia(props.documentSettings?.tglEfektif || printItem.value?.tglEfektif || '2022-04-07'));

const handlePrint = () => {
    window.print();
};

const handleClose = () => {
    if (window.opener) {
        window.close();
    } else {
        window.history.back();
    }
};

onMounted(() => {
    setTimeout(() => {
        window.print();
    }, 500);
});
</script>

<template>
    <Head :title="`Berita Acara - ${printItem.nama || ''} - OneClick`" />

    <div class="print-body">
        <div class="no-print" style="position: fixed; top: 20px; right: 20px; z-index: 9999; display: flex; gap: 10px;">
            <button class="print-btn" type="button" @click="handlePrint">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                    <path d="M5 1a2 2 0 0 0-2 2v2H2a2 2 0 0 0-2 2v3a2 2 0 0 0 2 2h1v1a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2v-1h1a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-1V3a2 2 0 0 0-2-2H5zM4 3a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2H4V3zm1 5a.5.5 0 0 0 0 1h6a.5.5 0 0 0 0-1H5zm0 2a.5.5 0 0 0 0 1h3a.5.5 0 0 0 0-1H5z" />
                </svg>
                Cetak Sekarang
            </button>
            <button class="close-btn" type="button" @click="handleClose">TUTUP</button>
        </div>

        <div class="page-wrap">
            <table class="header-box">
                <tbody>
                    <tr>
                        <td class="logo-cell">
                            <img src="/images/images.png" alt="PPA">
                            <div class="logo-text">PPA</div>
                        </td>
                        <td class="title-cell">
                            <div class="t1">FORMULIR</div>
                            <div class="t2">BERITA ACARA</div>
                        </td>
                        <td class="meta-cell">
                            <table>
                                <tbody>
                                    <tr><td>No. Dokumen</td><td>: {{ effectiveNoDokumen }}</td></tr>
                                    <tr><td>Revisi</td><td>: {{ effectiveRevisi }}</td></tr>
                                    <tr><td>Tgl. Efektif</td><td>: {{ formattedTglEfektif }}</td></tr>
                                    <tr><td>Halaman</td><td>: 1 dari 1</td></tr>
                                </tbody>
                            </table>
                        </td>
                    </tr>
                </tbody>
            </table>

            <div class="body-wrap">
                <table class="info-table">
                    <tbody>
                        <tr>
                            <td class="label">NAMA</td><td class="colon">:</td>
                            <td>{{ (printItem.nama || '').toUpperCase() }}</td>
                        </tr>
                        <tr>
                            <td class="label">JABATAN</td><td class="colon">:</td>
                            <td>{{ (printItem.jabatan || '-').toUpperCase() }}</td>
                        </tr>
                        <tr>
                            <td class="label">HARI, TANGGAL</td><td class="colon">:</td>
                            <td>{{ hariTanggal }}</td>
                        </tr>
                        <tr>
                            <td class="label">KRONOLOGI KEJADIAN</td><td class="colon">:</td>
                            <td></td>
                        </tr>
                    </tbody>
                </table>

                <div style="height: auto; min-height: 420px; -webkit-print-color-adjust: exact; print-color-adjust: exact; margin-top: 0;">
                    <div class="kronologi" style="line-height: 1.6; margin: 0; position: relative; z-index: 2;">{{ printItem.kronologi }}</div>
                </div>
                <div class="spacer"></div>

                <table class="sign-table">
                    <tbody>
                        <tr>
                            <td>
                                <div class="sign-placeholder">{{ effectiveLokasi }}, {{ formatDateIndonesia(printItem.tanggal) }}</div>
                                <div>Diketahui oleh,</div>
                                <div class="sign-space">
                                    <img v-if="printItem.ttdAtasan" class="sign-img-abs" :src="printItem.ttdAtasan" alt="TTD Atasan">
                                </div>
                                <div class="sign-name">{{ printItem.diketahuiOlehNama || '&nbsp;' }}</div>
                                <div class="sign-line">( .................................. )</div>
                            </td>
                            <td>
                                <div>{{ effectiveLokasi }}, {{ formatDateIndonesia(printItem.tanggal) }}</div>
                                <div>Dibuat Oleh,</div>
                                <div class="sign-space">
                                    <img v-if="printItem.ttdKaryawan" class="sign-img-abs" :src="printItem.ttdKaryawan" alt="TTD Karyawan">
                                </div>
                                <div class="sign-name">{{ printItem.nama }}</div>
                                <div class="sign-line">( .................................. )</div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>

<style scoped>
@page {
    size: A4;
    margin: 15mm 18mm;
}

.print-body {
    font-family: Arial, Helvetica, sans-serif;
    font-size: 11pt;
    color: #000;
    line-height: 1.45;
    background-color: #fff;
    min-height: 100vh;
    padding: 20px 0;
}

.page-wrap {
    max-width: 210mm;
    margin: 0 auto;
    background: #fff;
    padding: 15mm 18mm;
    box-sizing: border-box;
    display: flex;
    flex-direction: column;
}

.header-box {
    width: 100%;
    border: 1px solid #000;
    border-collapse: collapse;
}

.header-box td {
    border: 1px solid #000;
    vertical-align: middle;
    padding: 0;
}

.logo-cell {
    width: 20%;
    text-align: center;
    padding: 6px 4px;
}

.logo-cell img {
    height: 65px;
    display: block;
    margin: 0 auto 3px auto;
    object-fit: contain;
}

.logo-cell .logo-text {
    font-weight: bold;
    font-size: 14pt;
    letter-spacing: 1px;
}

.title-cell {
    width: 45%;
    text-align: center;
    font-weight: bold;
    padding: 10px;
}

.title-cell .t1 {
    font-size: 15pt;
    letter-spacing: 1px;
}

.title-cell .t2 {
    font-size: 18pt;
    letter-spacing: 1px;
}

.meta-cell {
    width: 35%;
    padding: 0;
}

.meta-cell table {
    width: 100%;
    border-collapse: collapse;
    font-size: 9.5pt;
}

.meta-cell table td {
    border: none;
    padding: 3px 6px;
    white-space: nowrap;
}

.meta-cell table tr + tr td {
    border-top: 1px solid #000;
}

.meta-cell table td:first-child {
    border-right: 1px solid #000;
    width: 80px;
}

.body-wrap {
    padding: 10px 5px;
    flex: 1 0 auto;
    display: flex;
    flex-direction: column;
}

.info-table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 12px;
}

.info-table td {
    padding: 4px 0;
    vertical-align: top;
    font-size: 11pt;
}

.info-table td.label {
    width: 180px;
    text-transform: uppercase;
}

.info-table td.colon {
    width: 20px;
}

.kronologi {
    text-align: justify;
    white-space: pre-line;
    font-size: 11pt;
}

.spacer {
    flex: 1 1 auto;
}

.sign-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 10px;
    margin-bottom: 0;
}

.sign-table td {
    width: 50%;
    vertical-align: top;
    font-size: 11pt;
    text-align: center;
    padding: 0 20px;
}

.sign-space {
    height: 75px;
    position: relative;
}

.sign-img-abs {
    position: absolute;
    left: 50%;
    transform: translateX(-50%);
    bottom: 0;
    max-height: 75px;
    max-width: 180px;
}

.sign-line {
    display: inline-block;
    min-width: 220px;
    text-align: center;
}

.sign-name {
    font-weight: bold;
    text-transform: uppercase;
    margin-bottom: 2px;
}

.sign-placeholder {
    visibility: hidden;
}

.print-btn {
    padding: 10px 20px;
    background: #0284c7;
    color: white;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-weight: bold;
    box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);
    display: flex;
    align-items: center;
    gap: 8px;
}

.print-btn:hover {
    background: #0369a1;
}

.close-btn {
    padding: 10px 20px;
    background: #6c757d;
    color: white;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-weight: bold;
    box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);
}

.close-btn:hover {
    background: #5a6268;
}

@media print {
    .no-print {
        display: none !important;
    }
    .print-body {
        padding: 0 !important;
        background: transparent !important;
    }
    .page-wrap {
        padding: 0 !important;
        margin: 0 !important;
        max-width: 100% !important;
        box-shadow: none !important;
    }
}
</style>
