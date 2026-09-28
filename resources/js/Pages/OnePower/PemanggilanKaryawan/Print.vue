<script setup>
import { computed, onMounted } from 'vue';
import { Head } from '@inertiajs/vue3';

const props = defineProps({
    pemanggilan: { type: Object, required: true },
    documentSettings: { type: Object, default: () => ({}) },
});

const data = computed(() => props.pemanggilan?.data ?? props.pemanggilan ?? {});

const BULAN_INDO = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

const formatDateIndonesia = (value) => {
    if (! value) return '-';
    if (typeof value === 'string') {
        const clean = value.split('T')[0].split(' ')[0];
        const parts = clean.split('-');
        if (parts.length === 3) {
            const year = parseInt(parts[0], 10);
            const month = parseInt(parts[1], 10);
            const day = parseInt(parts[2], 10);
            if (!isNaN(day) && !isNaN(month) && !isNaN(year) && BULAN_INDO[month]) {
                return `${day} ${BULAN_INDO[month]} ${year}`;
            }
        }
    }
    const date = new Date(value);
    if (Number.isNaN(date.getTime())) return value;
    return `${date.getDate()} ${BULAN_INDO[date.getMonth() + 1]} ${date.getFullYear()}`;
};

const getHari = (dateStr) => {
    if (! dateStr) return '-';
    const date = new Date(dateStr);
    if (Number.isNaN(date.getTime())) return '-';
    const days = ['MINGGU', 'SENIN', 'SELASA', 'RABU', 'KAMIS', 'JUMAT', 'SABTU'];
    return days[date.getDay()] ?? '-';
};

const formatJam = (value) => {
    if (! value) return '-';
    const str = String(value).trim();
    if (str.includes(':')) {
        const parts = str.split(':');
        return `${parts[0].padStart(2, '0')}:${parts[1].padStart(2, '0')}`;
    }
    return str;
};

const handlePrint = () => {
    window.print();
};

const handleClose = () => {
    window.close();
};

onMounted(() => {
    setTimeout(() => {
        window.print();
    }, 500);
});
</script>

<template>
    <Head :title="`Print Pemanggilan Karyawan - ${data.nama || ''}`" />

    <div class="print-container">
        <!-- Floating Actions -->
        <div class="no-print">
            <button class="btn btn-print" @click="handlePrint">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                    <path d="M5 1a2 2 0 0 0-2 2v2H2a2 2 0 0 0-2 2v3a2 2 0 0 0 2 2h1v1a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2v-1h1a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-1V3a2 2 0 0 0-2-2H5zM4 3a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2H4V3zm1 5a.5.5 0 0 0 0 1h6a.5.5 0 0 0 0-1H5zm0 2a.5.5 0 0 0 0 1h3a.5.5 0 0 0 0-1H5z"/>
                </svg>
                Cetak
            </button>
            <button class="btn btn-close" @click="handleClose">Tutup</button>
        </div>

        <div class="page-wrap">
            <!-- Header Box -->
            <table class="header-box">
                <tr>
                    <td class="logo-cell">
                        <img src="/images/images.png" alt="PPA Logo">
                    </td>
                    <td class="title-cell">
                        <div class="t1">PT. PUTRA PERKASA ABADI</div>
                        <div class="t2">{{ documentSettings.dept || 'JOB SITE ADARO - OPERATIONAL DEPARTMENT' }}</div>
                    </td>
                </tr>
            </table>

            <!-- Content -->
            <div class="content-wrap">
                <div class="main-title">FORM PEMANGGILAN KARYAWAN</div>
                <div v-if="data.noSurat" class="doc-number">Nomor : {{ data.noSurat }}</div>

                <p class="intro-p">Sehubungan dengan adanya kepentingan operasional / kedisplinan, dengan ini kami memanggil karyawan tersebut di bawah ini :</p>

                <table class="info-table">
                    <tbody>
                        <tr>
                            <td class="label">NAMA</td>
                            <td class="colon">:</td>
                            <td>{{ (data.nama || '-').toUpperCase() }}</td>
                        </tr>
                        <tr>
                            <td class="label">NRP</td>
                            <td class="colon">:</td>
                            <td>{{ data.nrp || '-' }}</td>
                        </tr>
                        <tr>
                            <td class="label">JABATAN</td>
                            <td class="colon">:</td>
                            <td>{{ (data.jabatan || '-').toUpperCase() }}</td>
                        </tr>
                        <tr>
                            <td class="label">HARI / TANGGAL</td>
                            <td class="colon">:</td>
                            <td>{{ getHari(data.tanggalPemanggilan) }}, {{ formatDateIndonesia(data.tanggalPemanggilan)?.toUpperCase() }}</td>
                        </tr>
                        <tr>
                            <td class="label">JAM</td>
                            <td class="colon">:</td>
                            <td>{{ formatJam(data.jamPemanggilan) }} WITA</td>
                        </tr>
                        <tr>
                            <td class="label">TEMPAT</td>
                            <td class="colon">:</td>
                            <td>{{ (data.tempat || '-').toUpperCase() }}</td>
                        </tr>
                    </tbody>
                </table>

                <div class="reason-title">ALASAN PEMANGGILAN :</div>
                <div class="reason-box">{{ data.alasanPemanggilan || '-' }}</div>

                <p class="outro-p">Demikian surat pemanggilan ini dibuat untuk dapat dipergunakan sebagaimana mestinya.</p>

                <table class="sign-table">
                    <tr>
                        <td>
                            <div>Pembuat Panggilan,</div>
                            <div class="sign-space">
                                <img
                                    v-if="data.ttdPemanggil"
                                    :src="data.ttdPemanggil"
                                    alt="TTD Pemanggil"
                                    class="signature-img"
                                >
                            </div>
                            <div class="sign-name">{{ (data.ttdPemanggilan || '-').toUpperCase() }}</div>
                            <div class="sign-title">{{ data.namaManpower || 'Manpower Dept Produksi' }}</div>
                        </td>
                        <td>
                            <div>{{ documentSettings.lokasi || 'Wara' }}, {{ formatDateIndonesia(data.tanggalPemanggilan) }}</div>
                            <div>Penerima Panggilan,</div>
                            <div class="sign-space">
                                <img
                                    v-if="data.ttdKaryawan"
                                    :src="data.ttdKaryawan"
                                    alt="TTD Karyawan"
                                    class="signature-img"
                                >
                            </div>
                            <div class="sign-name">{{ (data.nama || '-').toUpperCase() }}</div>
                            <div class="sign-title">Karyawan</div>
                        </td>
                    </tr>
                </table>

                <div class="footer-note">
                    * Lembar 1: Arsip Departemen | Lembar 2: Karyawan | Lembar 3: HRD (jika diperlukan)
                </div>
            </div>
        </div>

        <!-- Lampiran Foto Dokumen jika ada -->
        <div v-if="data.foto" class="page-wrap" style="page-break-before: always; margin-top: 20px;">
            <table class="header-box">
                <tr>
                    <td class="logo-cell">
                        <img src="/assets/images/images.png" alt="Logo PPA">
                    </td>
                    <td class="title-cell">
                        <div class="t1">PT. PUTRA PERKASA ABADI</div>
                        <div class="t2">JOB SITE ADARO (ADW)</div>
                    </td>
                </tr>
            </table>

            <div class="content-wrap">
                <div class="main-title">LAMPIRAN DOKUMENTASI FOTO</div>
                <div class="sub-title" style="margin-bottom: 20px;">Surat Pemanggilan: {{ data.noSurat || '-' }}</div>

                <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 10px;">
                    <img :src="data.foto" alt="Foto Dokumentasi Pemanggilan" style="max-width: 95%; max-height: 620px; object-fit: contain; border: 1px solid #ccc; padding: 6px; background: #fff;" />
                    <div style="font-size: 9pt; color: #666; margin-top: 10px;">
                        Dokumentasi: {{ data.nrp }} - {{ data.nama }} ({{ formatDateIndonesia(data.tanggalPemanggilan) }})
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
@page {
    size: A4;
    margin: 15mm 18mm;
}

* {
    box-sizing: border-box;
}

.print-container {
    font-family: Arial, Helvetica, sans-serif;
    font-size: 11pt;
    color: #000;
    margin: 0;
    line-height: 1.5;
    background: #fff;
    min-height: 100vh;
}

.page-wrap {
    max-width: 800px;
    margin: 0 auto;
    padding: 20px;
}

.header-box {
    width: 100%;
    border: 1px solid #000;
    border-collapse: collapse;
    margin-bottom: 20px;
}

.header-box td {
    border: 1px solid #000;
    vertical-align: middle;
}

.logo-cell {
    width: 15%;
    text-align: center;
    padding: 10px;
}

.logo-cell img {
    height: 60px;
    object-fit: contain;
}

.title-cell {
    width: 85%;
    text-align: center;
    font-weight: bold;
    padding: 10px;
}

.title-cell .t1 {
    font-size: 16pt;
    letter-spacing: 1px;
}

.title-cell .t2 {
    font-size: 11pt;
    font-weight: normal;
    margin-top: 5px;
}

.content-wrap {
    padding: 10px 5px;
}

.main-title {
    text-align: center;
    font-weight: bold;
    font-size: 14pt;
    margin-bottom: 4px;
    text-decoration: underline;
}

.doc-number {
    text-align: center;
    font-size: 10pt;
    font-weight: bold;
    margin-bottom: 20px;
}

.intro-p {
    margin-top: 15px;
    margin-bottom: 15px;
}

.info-table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 20px;
}

.info-table td {
    padding: 7px 0;
    vertical-align: top;
}

.info-table td.label {
    width: 180px;
    font-weight: bold;
}

.info-table td.colon {
    width: 20px;
    text-align: center;
}

.reason-title {
    font-weight: bold;
    margin-bottom: 5px;
}

.reason-box {
    border: 1px solid #000;
    padding: 15px;
    min-height: 150px;
    margin-top: 10px;
    white-space: pre-line;
    text-align: justify;
}

.outro-p {
    margin-top: 25px;
}

.sign-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 40px;
}

.sign-table td {
    width: 50%;
    text-align: center;
    vertical-align: top;
    padding: 0 10px;
}

.sign-space {
    height: 90px;
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
}

.signature-img {
    max-height: 80px;
    max-width: 180px;
    object-fit: contain;
}

.sign-name {
    font-weight: bold;
    text-decoration: underline;
    margin-bottom: 5px;
}

.sign-title {
    font-size: 10pt;
    color: #333;
}

.footer-note {
    margin-top: 50px;
    font-size: 9pt;
    font-style: italic;
    color: #555;
    border-top: 1px solid #ccc;
    padding-top: 5px;
}

.no-print {
    position: fixed;
    top: 20px;
    right: 20px;
    z-index: 9999;
    display: flex;
    gap: 10px;
}

.btn {
    padding: 8px 16px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-weight: bold;
    text-decoration: none;
    color: white;
    display: flex;
    align-items: center;
    gap: 5px;
    font-size: 12px;
}

.btn-print {
    background: #b91c1c;
}

.btn-print:hover {
    background: #991b1b;
}

.btn-close {
    background: #6b7280;
}

.btn-close:hover {
    background: #4b5563;
}

@media print {
    .no-print {
        display: none !important;
    }
    .print-container {
        background: transparent;
    }
    .page-wrap {
        padding: 0;
        max-width: 100%;
    }
    body {
        margin: 0;
    }
}
</style>
