<script setup>
import { computed } from 'vue';
import { Head } from '@inertiajs/vue3';

const props = defineProps({
    item: { type: Object, required: true },
    isBlank: { type: Boolean, default: false },
    documentSettings: { type: Object, default: () => ({}) },
});

const dataItem = computed(() => props.item?.data ?? props.item ?? {});

const BULAN_INDO = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

const formatDateIndonesia = (value) => {
    if (! value) return '-';
    const date = new Date(value);
    if (Number.isNaN(date.getTime())) return value;
    return `${date.getDate()} ${BULAN_INDO[date.getMonth() + 1]} ${date.getFullYear()}`;
};

const tanggalFormatted = computed(() => formatDateIndonesia(dataItem.value.tanggal));

const defaultPernyataan = 'Saya berkomitmen dengan bersungguh-sungguh untuk menjaga ATR dan Kinerja sesuai dengan standar perusahaan, dan kedepannya jika saya tidak bisa menjaga ATR dan Kinerja saya, maka saya siap menerima konsekuensi sesuai peraturan perusahaan yang berlaku.';

const handlePrint = () => {
    window.print();
};

const handleClose = () => {
    window.close();
};
</script>

<template>
    <Head :title="`Pakta Integritas - ${dataItem.nama || 'Operator'} - OneClick`" />

    <div class="print-container">
        <!-- Action Buttons (Print / Close) -->
        <div class="no-print print-actions">
            <button class="print-btn" type="button" @click="handlePrint">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                    <path d="M5 1a2 2 0 0 0-2 2v2H2a2 2 0 0 0-2 2v3a2 2 0 0 0 2 2h1v1a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2v-1h1a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-1V3a2 2 0 0 0-2-2H5zM4 3a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2H4V3zm1 5a.5.5 0 0 0 0 1h6a.5.5 0 0 0 0-1H5zm0 2a.5.5 0 0 0 0 1h3a.5.5 0 0 0 0-1H5z"/>
                </svg>
                Cetak Sekarang
            </button>
            <button class="close-btn" type="button" @click="handleClose">TUTUP</button>
        </div>

        <div class="page">
            <!-- Header -->
            <div class="header">
                <img src="/images/images.png" alt="PT. PPA">
                <div class="company">PUTRA PERKASA ABADI</div>
            </div>

            <!-- Title & Nomor Surat -->
            <div class="title">PAKTA INTEGRITAS</div>
            <div v-if="dataItem.nomorSurat" class="doc-number">Nomor: {{ dataItem.nomorSurat }}</div>

            <!-- Address Block -->
            <div class="addr">
                <div class="line">Kepada Yth.</div>
                <div class="line" style="margin-top: 15px;">Pimpinan Dept. Produksi PT PPA Jobsite Adaro</div>
                <div class="line">Di tempat,</div>
            </div>

            <div class="intro">Dengan ini saya yang bertanda tangan dibawah ini :</div>

            <!-- Identity Table -->
            <table class="info-table">
                <tbody>
                    <tr>
                        <td class="label">NAMA</td>
                        <td class="sep">:</td>
                        <td class="value">{{ isBlank ? '' : (dataItem.nama || '').toUpperCase() }}</td>
                    </tr>
                    <tr>
                        <td class="label">NRP</td>
                        <td class="sep">:</td>
                        <td class="value">{{ isBlank ? '' : dataItem.nrp }}</td>
                    </tr>
                    <tr>
                        <td class="label">JABATAN</td>
                        <td class="sep">:</td>
                        <td class="value">{{ isBlank ? '' : (dataItem.jabatan || '-').toUpperCase() }}</td>
                    </tr>
                </tbody>
            </table>

            <!-- Statement / Commitment -->
            <div class="statement">
                {{ isBlank ? '' : (dataItem.pernyataan || defaultPernyataan) }}
            </div>

            <div class="place-date">
                {{ dataItem.tempat || documentSettings?.lokasi || 'Tanjung' }}, {{ isBlank ? '..................................................' : tanggalFormatted }}
            </div>

            <!-- Signature Table -->
            <table class="sign-row">
                <tbody>
                    <tr>
                        <td>
                            <div class="sign-title">Yang Bertanda Tangan,</div>
                            <div class="sign-area">
                                <img v-if="dataItem.ttdKaryawan && !isBlank" :src="dataItem.ttdKaryawan" alt="TTD">
                            </div>
                            <div class="sign-name">( {{ isBlank ? '..........................................' : ((dataItem.nama || '').toUpperCase() || '..........................................') }} )</div>
                        </td>
                        <td>
                            <div class="sign-title">Atasan Langsung</div>
                            <div class="sign-area">
                                <img v-if="dataItem.ttdAtasan && !isBlank" :src="dataItem.ttdAtasan" alt="TTD Atasan">
                            </div>
                            <div class="sign-name">( {{ isBlank ? '..........................................' : ((dataItem.atasanNama || '').toUpperCase() || '..........................................') }} )</div>
                        </td>
                        <td>
                            <div class="sign-title">Mengetahui,</div>
                            <div class="sign-area">
                                <img v-if="dataItem.ttdMengetahui && !isBlank" :src="dataItem.ttdMengetahui" alt="TTD Mengetahui">
                            </div>
                            <div class="sign-name">( {{ isBlank ? '..........................................' : ((dataItem.mengetahuiNama || '').toUpperCase() || '..........................................') }} )</div>
                        </td>
                    </tr>
                </tbody>
            </table>

            <!-- Footer Info -->
            <div class="footer-info">
                <div class="row-info"><span class="lbl">CC</span> : {{ dataItem.cc || 'Bapak Fachrul Latuconsina' }}</div>
                <div class="row-info"><span class="lbl">Arsip</span> : {{ dataItem.arsip || 'Dokumen Control MPE' }}</div>
            </div>

            <!-- Bottom Decoration -->
            <div class="bottom-decoration">
                <div class="orange-bar"></div>
                <div class="address-footer">
                    Gedung Office 8, Lantai 8, SCBD Lot 28. Jl. Jend Sudirman<br>
                    Kav. 52-53 Senayan, Kebayoran Baru, Jakarta Selatan, 12190.<br>
                    Telp. +62 21 5790 3456 | www.ppa.co.id
                </div>
            </div>
        </div>

        <!-- Lampiran Foto Dokumen jika ada -->
        <div v-if="dataItem.foto && !isBlank" class="sheet" style="page-break-before: always; margin-top: 20px;">
            <div class="header">
                <img src="/assets/images/images.png" class="logo" alt="Logo PPA">
                <div class="company-name">PUTRA PERKASA ABADI</div>
            </div>
            <div class="title" style="margin-top: 15px; margin-bottom: 5px;">LAMPIRAN DOKUMENTASI FOTO</div>
            <div class="doc-number" style="margin-bottom: 20px;">Pakta Integritas: {{ dataItem.nomorSurat || '-' }}</div>
            
            <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 10px;">
                <img :src="dataItem.foto" alt="Foto Dokumentasi Pakta" style="max-width: 95%; max-height: 600px; object-fit: contain; border: 1px solid #ccc; padding: 6px; background: #fff;" />
                <div style="font-size: 9pt; color: #666; margin-top: 8px;">
                    Dokumentasi Bukti: {{ dataItem.nrp }} - {{ dataItem.nama }} ({{ formatDateIndonesia(dataItem.tanggal) }})
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
@page {
    size: A4;
    margin: 12mm 15mm 15mm 15mm;
}

* {
    box-sizing: border-box;
}

.print-container {
    font-family: 'Times New Roman', Times, serif;
    font-size: 11pt;
    color: #000;
    margin: 0;
    padding: 20px 0;
    background: #e2e8f0;
    min-height: 100vh;
    line-height: 1.3;
}

.page {
    width: 210mm;
    min-height: 297mm;
    padding: 12mm 15mm 20mm 15mm;
    margin: auto;
    background: white;
    box-sizing: border-box;
    position: relative;
    box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);
}

.header {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 15px;
    margin-bottom: 25px;
}

.header img {
    height: 65px;
    width: auto;
}

.header .company {
    font-family: Arial, Helvetica, sans-serif;
    font-size: 18pt;
    font-weight: bold;
    letter-spacing: 0.5px;
    color: #000;
}

.title {
    text-align: center;
    font-size: 14pt;
    font-weight: bold;
    margin: 15px 0 5px 0;
    letter-spacing: 1px;
}

.doc-number {
    text-align: center;
    font-size: 10.5pt;
    font-weight: normal;
    margin-bottom: 20px;
    color: #111;
}

.addr {
    margin-bottom: 18px;
}

.addr .line {
    margin: 0;
    line-height: 1.4;
}

.intro {
    margin-bottom: 10px;
}

.info-table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 12px;
}

.info-table td {
    padding: 2px 0;
    vertical-align: top;
}

.info-table td.label {
    width: 100px;
    font-weight: normal;
}

.info-table td.sep {
    width: 20px;
    text-align: center;
}

.info-table td.value {
    font-weight: bold;
}

.statement {
    text-align: justify;
    margin: 0 0 25px 0;
    line-height: 2.2em;
    min-height: 17.6em; /* 8 rows total */
    overflow: hidden;
    background-image:
        linear-gradient(to bottom, #fff 2.3em, transparent 2.3em),
        radial-gradient(circle, #000 1.1px, transparent 1.5px);
    background-size: 100% 100%, 5px 2.2em;
    background-position: 0 0, 0 3.45em;
    background-repeat: no-repeat, repeat;
    word-wrap: break-word;
}

.place-date {
    margin-bottom: 25px;
}

.sign-row {
    width: 100%;
    border-collapse: collapse;
    margin-top: 10px;
}

.sign-row td {
    text-align: center;
    vertical-align: top;
    width: 33.33%;
    padding: 0 10px;
}

.sign-title {
    margin-bottom: 35px;
    height: 35px;
}

.sign-area {
    height: 50px;
    position: relative;
}

.sign-area img {
    max-height: 70px;
    max-width: 140px;
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -70%);
}

.sign-name {
    display: block;
    font-weight: bold;
    margin-top: 5px;
    white-space: nowrap;
    font-size: 10pt;
}

.footer-info {
    margin-top: 25px;
    font-size: 10pt;
    line-height: 1.5;
}

.footer-info .row-info {
    margin-bottom: 2px;
}

.footer-info .lbl {
    display: inline-block;
    width: 45px;
}

/* Bottom Decoration */
.bottom-decoration {
    position: absolute;
    bottom: 15mm;
    left: 15mm;
    right: 15mm;
}

.orange-bar {
    height: 7px;
    background: #f39c12;
    width: 100%;
    margin-bottom: 8px;
}

.address-footer {
    text-align: center;
    font-size: 8pt;
    color: #555;
    line-height: 1.2;
    padding-bottom: 5px;
    font-family: Arial, Helvetica, sans-serif;
}

/* Print actions */
.print-actions {
    position: fixed;
    top: 20px;
    right: 20px;
    z-index: 9999;
    display: flex;
    gap: 10px;
}

.print-btn {
    padding: 9px 18px;
    background: #0284c7;
    color: white;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-weight: bold;
    font-family: sans-serif;
    font-size: 13px;
    box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);
    display: flex;
    align-items: center;
    gap: 8px;
    transition: background 0.2s;
}

.print-btn:hover {
    background: #0369a1;
}

.close-btn {
    padding: 9px 18px;
    background: #64748b;
    color: white;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-weight: bold;
    font-family: sans-serif;
    font-size: 13px;
    box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);
    transition: background 0.2s;
}

.close-btn:hover {
    background: #475569;
}

@media print {
    .no-print {
        display: none !important;
    }
    .print-container {
        padding: 0 !important;
        background: white !important;
    }
    .page {
        box-shadow: none !important;
        padding: 0 !important;
        width: 100% !important;
        min-height: auto !important;
    }
    .bottom-decoration {
        position: fixed;
        bottom: 10mm;
        left: 0;
        right: 0;
    }
    body {
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }
}
</style>
