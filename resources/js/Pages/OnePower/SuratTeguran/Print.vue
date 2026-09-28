<script setup>
import { computed, onMounted } from 'vue';
import { Head } from '@inertiajs/vue3';

const props = defineProps({
    suratTeguran: { type: Object, required: true },
    documentSettings: { type: Object, default: () => ({}) },
});

const dataItem = computed(() => props.suratTeguran?.data ?? props.suratTeguran ?? {});

const BULAN_INDO = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

const formatDateIndonesia = (value) => {
    if (! value || value === '0000-00-00' || value === '0000-00-00 00:00:00') return '-';
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

const formatJam = (value) => {
    if (! value) return '-';
    const str = String(value).trim();
    if (str.includes(':')) {
        const parts = str.split(':');
        return `${parts[0].padStart(2, '0')}.${parts[1].padStart(2, '0')}`;
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
    <Head :title="`Print Surat Teguran - ${dataItem.noSurat || dataItem.nrp || ''}`" />

    <div class="print-root">
        <!-- Floating Actions (Hidden on print) -->
        <div class="no-print" style="position: fixed; top: 15px; right: 15px; z-index: 9999; display: flex; gap: 8px;">
            <button
                type="button"
                @click="handlePrint"
                style="padding: 10px 20px; background: #0284c7; color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: bold; font-family: sans-serif; font-size: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); display: flex; align-items: center; gap: 6px;"
            >
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                    <path d="M5 1a2 2 0 0 0-2 2v2H2a2 2 0 0 0-2 2v3a2 2 0 0 0 2 2h1v1a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2v-1h1a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-1V3a2 2 0 0 0-2-2H5zM4 3a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2H4V3zm1 5a.5.5 0 0 0 0 1h6a.5.5 0 0 0 0-1H5zm0 2a.5.5 0 0 0 0 1h3a.5.5 0 0 0 0-1H5z"/>
                </svg>
                Cetak Sekarang
            </button>
            <button
                type="button"
                @click="handleClose"
                style="padding: 10px 16px; background: #64748b; color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: bold; font-family: sans-serif; font-size: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);"
            >
                TUTUP
            </button>
        </div>

        <div class="wrapper">
            <!-- Header Logo & Nama Perusahaan -->
            <div class="header">
                <img src="/images/images.png" alt="Logo PPA" class="logo">
                <div class="company-name">PUTRA PERKASA ABADI</div>
            </div>

            <!-- Judul Dokumen -->
            <div class="title-section">
                <div class="title-main">SURAT TEGURAN KARYAWAN</div>
                <div class="doc-number">Nomor : {{ dataItem.noSurat || '-' }}</div>
            </div>

            <!-- Tabel Identitas -->
            <table class="info-table">
                <tbody>
                    <tr>
                        <td class="label-col">Nama Karyawan / NRP</td>
                        <td class="colon-col">:</td>
                        <td class="value-col">{{ dataItem.nama }} / {{ dataItem.nrp }}</td>
                        <td style="width: 4%;"></td>
                        <td class="label-col">Jabatan / Departement</td>
                        <td class="colon-col">:</td>
                        <td class="value-col">{{ dataItem.jabatan || '-' }} / {{ dataItem.departemen || '-' }}</td>
                    </tr>
                    <tr>
                        <td class="label-col">Tanggal Kejadian</td>
                        <td class="colon-col">:</td>
                        <td class="value-col">{{ formatDateIndonesia(dataItem.tanggalKejadian) }}</td>
                        <td></td>
                        <td class="label-col">Tempat Kejadian</td>
                        <td class="colon-col">:</td>
                        <td class="value-col">{{ dataItem.tempatKejadian || '-' }}</td>
                    </tr>
                </tbody>
            </table>

            <!-- Jenis Pelanggaran -->
            <div class="section-title">JENIS PELANGGARAN</div>
            <div class="checkbox-group">
                <div class="checkbox-item">
                    <div class="box">{{ dataItem.jenisAbsen ? '✔' : '' }}</div> Absen / Terlambat
                </div>
                <div class="checkbox-item">
                    <div class="box">{{ dataItem.jenisCeroboh ? '✔' : '' }}</div> Ceroboh / Lalai
                </div>
                <div class="checkbox-item">
                    <div class="box">{{ dataItem.jenisMenolakPerintah ? '✔' : '' }}</div> Menolak Perintah
                </div>
                <div class="checkbox-item">
                    <div class="box">{{ dataItem.jenisPerilaku ? '✔' : '' }}</div> Perilaku
                </div>
                <div class="checkbox-item">
                    <div class="box">{{ dataItem.jenisHasilKerja ? '✔' : '' }}</div> Hasil Kerja
                </div>
                <div class="checkbox-item">
                    <div class="box">{{ dataItem.jenisPelanggaranPeraturan ? '✔' : '' }}</div> Pelanggaran Peraturan
                </div>
            </div>

            <!-- Penjelasan Singkat -->
            <div class="section-title">PENJELASAN SINGKAT MENGENAI KEJADIAN PELANGGARAN :</div>
            <div class="explanation-lines">
                {{ dataItem.penjelasanSingkat || '' }}
            </div>

            <!-- Waktu Kejadian -->
            <div class="detail-row">
                <span class="detail-label">Hal ini terjadi Pada Tanggal</span> : {{ formatDateIndonesia(dataItem.kejadianTanggal || dataItem.tanggalKejadian) }}
            </div>
            <div class="detail-row">
                <span class="detail-label">Jam</span> : {{ formatJam(dataItem.kejadianJam) }}
            </div>
            <div class="detail-row">
                <span class="detail-label">Lokasi Kejadian</span> : {{ dataItem.kejadianLokasi || dataItem.tempatKejadian || '-' }}
            </div>

            <!-- Sanksi -->
            <div class="sanksi-text">
                Merujuk pada peraturan perundang-undangan dan peraturan perusahaan maka dengan ini memberikan sanksi :<br>
                <strong>SURAT TEGURAN</strong> dan kepadanya, diminta untuk lebih perhatian lagi terhadap peraturan dan tata tertib perusahaan yang berlaku.
            </div>

            <!-- Lokasi & Tanggal Dikeluarkan -->
            <div class="location-date">
                Dikeluarkan di : {{ dataItem.dikeluarkanDi || documentSettings?.lokasi || 'CSA Inggris' }}<br>
                Pada tanggal : <span class="date-line">{{ formatDateIndonesia(dataItem.dikeluarkanTanggal) }}</span>
            </div>

            <!-- Signature Area -->
            <div class="signature-section">
                <div class="sig-block">
                    <div class="sig-space">
                        <img v-if="dataItem.ttdKaryawan" :src="dataItem.ttdKaryawan" alt="TTD Karyawan">
                    </div>
                    <div class="sig-name">{{ dataItem.nama }}</div>
                    <div class="sig-line"></div>
                    Karyawan Ybs
                </div>
                <div class="sig-block">
                    <div class="sig-space">
                        <img v-if="dataItem.ttdAtasan" :src="dataItem.ttdAtasan" alt="TTD Atasan">
                    </div>
                    <div class="sig-name">&nbsp;</div>
                    <div class="sig-line"></div>
                    Atasan langsung
                </div>
                <div class="sig-block">
                    <div class="sig-space">
                        <img v-if="dataItem.ttdAtasanAtasan" :src="dataItem.ttdAtasanAtasan" alt="TTD Atasan dari Atasan">
                    </div>
                    <div class="sig-name">&nbsp;</div>
                    <div class="sig-line"></div>
                    Atasan dari atasan Langsung
                </div>
            </div>

            <!-- Notes -->
            <div class="notes-container">
                <div class="notes-label">Catatan:</div>
                {{ dataItem.catatan || 'Berkomitmen untuk mengikuti Prosedur yang berlaku' }}
            </div>

            <!-- Disclaimer -->
            <div class="footer-disclaimer">
                Tanda tangan saudara diatas menandakan bahwa anda menyadari kejadian tersebut diatas dan telah diberitahukan sanksi perihal tersebut diatas dan akibatnya apabila terulang lagi.
            </div>

            <!-- Footer Address & Orange Border -->
            <div class="footer-address">
                Gedung Office 8, Lantai 8, SCBD Lot 28. Jl. Jend Sudirman<br>
                Kav. 52-53 Senayan, Kebayoran Baru, Jakarta Selatan, 12190.<br>
                Telp. +62 21 5790 3456 | www.ppa.co.id
            </div>
        </div>

        <!-- Lampiran Foto Dokumen jika ada -->
        <div v-if="dataItem.foto" class="wrapper" style="page-break-before: always; margin-top: 20px; padding: 15px;">
            <div style="display: flex; align-items: center; justify-content: center; gap: 12px; margin-bottom: 20px;">
                <img src="/assets/images/images.png" alt="Logo PPA" style="height: 48px; width: auto;">
                <div style="font-size: 16pt; font-weight: bold; letter-spacing: 0.5px;">PUTRA PERKASA ABADI</div>
            </div>
            <div style="text-align: center; font-size: 13pt; font-weight: bold; margin-bottom: 4px;">LAMPIRAN DOKUMENTASI FOTO</div>
            <div style="text-align: center; font-size: 10pt; color: #444; margin-bottom: 20px;">Surat Teguran: {{ dataItem.noSurat || '-' }}</div>
            
            <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 10px;">
                <img :src="dataItem.foto" alt="Foto Dokumentasi Surat Teguran" style="max-width: 95%; max-height: 620px; object-fit: contain; border: 1px solid #ccc; padding: 6px; background: #fff;" />
                <div style="font-size: 9pt; color: #666; margin-top: 10px;">
                    Dokumentasi Bukti: {{ dataItem.nrp }} - {{ dataItem.nama }} ({{ formatDateIndonesia(dataItem.tanggalKejadian) }})
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
@page {
    size: A4;
    margin: 10mm 15mm;
}

* {
    box-sizing: border-box;
}

.print-root {
    font-family: Arial, sans-serif !important;
    font-size: 10pt !important;
    line-height: 1.3 !important;
    color: #000 !important;
    margin: 0 !important;
    padding: 20px 0 !important;
    background-color: #f1f5f9;
    min-height: 100vh;
}

.wrapper {
    width: 180mm !important;
    max-width: 180mm !important;
    margin: 0 auto !important;
    padding: 0 !important;
    background: #fff !important;
    box-sizing: border-box !important;
    position: relative !important;
    min-height: 270mm;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
}

/* Header */
.header {
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    margin-bottom: 10px !important;
}

.logo {
    width: 50px !important;
    height: 50px !important;
    max-width: none !important;
    margin-right: 12px !important;
    display: inline-block !important;
}

.company-name {
    font-size: 20pt !important;
    font-weight: bold !important;
    letter-spacing: 1px !important;
    color: #000 !important;
}

/* Title */
.title-section {
    text-align: center !important;
    margin-bottom: 15px !important;
}

.title-main {
    font-size: 14pt !important;
    font-weight: bold !important;
    text-decoration: underline !important;
    margin-bottom: 3px !important;
    color: #000 !important;
}

.doc-number {
    font-size: 11pt !important;
    color: #000 !important;
}

/* Info Table */
.info-table {
    width: 100% !important;
    margin-bottom: 10px !important;
    border-collapse: collapse !important;
}

.info-table td {
    padding: 2px 0 !important;
    vertical-align: top !important;
    font-size: 10pt !important;
    color: #000 !important;
}

.label-col {
    width: 22% !important;
}

.colon-col {
    width: 2% !important;
    text-align: center !important;
}

.value-col {
    width: 26% !important;
    border-bottom: 1px dotted #000 !important;
}

/* Section Title */
.section-title {
    font-weight: bold !important;
    text-transform: uppercase !important;
    margin-top: 15px !important;
    margin-bottom: 5px !important;
    font-size: 10pt !important;
    color: #000 !important;
}

/* Checkbox Group */
.checkbox-group {
    display: grid !important;
    grid-template-columns: 1fr 1fr 1fr !important;
    gap: 10px !important;
    margin-bottom: 15px !important;
}

.checkbox-item {
    display: flex !important;
    align-items: center !important;
    font-size: 10pt !important;
    color: #000 !important;
}

.box {
    width: 16px !important;
    height: 16px !important;
    border: 1px solid #000 !important;
    margin-right: 8px !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    font-size: 12px !important;
    font-weight: bold !important;
    flex-shrink: 0 !important;
    line-height: 1 !important;
}

/* Penjelasan Paper Texture */
.explanation-lines {
    min-height: 88px !important;
    line-height: 22px !important;
    margin-bottom: 15px !important;
    padding-top: 0 !important;
    background-image: radial-gradient(circle, #000 1px, transparent 1px) !important;
    background-size: 6px 22px !important;
    background-position: 0 10px !important;
    background-repeat: repeat !important;
    word-wrap: break-word !important;
    text-align: justify !important;
    font-family: Arial, sans-serif !important;
    font-size: 10pt !important;
    color: #000 !important;
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
}

/* Detail Row */
.detail-row {
    margin-bottom: 3px !important;
    display: flex !important;
    font-size: 10pt !important;
    color: #000 !important;
}

.detail-label {
    display: inline-block !important;
    width: 180px !important;
}

/* Sanksi */
.sanksi-text {
    margin-top: 15px !important;
    margin-bottom: 15px !important;
    font-size: 10pt !important;
    color: #000 !important;
}

/* Location Date */
.location-date {
    text-align: left !important;
    margin-left: 50% !important;
    margin-bottom: 10px !important;
    font-size: 10pt !important;
    color: #000 !important;
}

.date-line {
    display: inline-block !important;
    border-bottom: 1px solid #000 !important;
    min-width: 180px !important;
}

/* Signatures */
.signature-section {
    display: flex !important;
    justify-content: space-between !important;
    margin-top: 10px !important;
    text-align: center !important;
    font-size: 10pt !important;
    color: #000 !important;
}

.sig-block {
    width: 30% !important;
}

.sig-space {
    height: 50px !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
}

.sig-space img {
    max-height: 50px !important;
    max-width: 130px !important;
}

.sig-name {
    display: inline-block !important;
    min-width: 150px !important;
    font-weight: bold !important;
    padding-bottom: 2px !important;
    font-size: 10pt !important;
    color: #000 !important;
}

.sig-line {
    border-top: 1px dotted #000 !important;
    width: 180px !important;
    margin: 0 auto !important;
}

/* Notes */
.notes-container {
    margin-top: 15px !important;
    border: 1px solid #000 !important;
    padding: 8px !important;
    min-height: 40px !important;
    font-size: 10pt !important;
    color: #000 !important;
}

.notes-label {
    font-size: 9pt !important;
    margin-bottom: 3px !important;
    font-weight: bold !important;
}

/* Disclaimer */
.footer-disclaimer {
    font-size: 8pt !important;
    font-style: italic !important;
    margin-top: 8px !important;
    color: #000 !important;
}

/* Address Footer */
.footer-address {
    position: absolute !important;
    bottom: 10mm !important;
    left: 0 !important;
    right: 0 !important;
    text-align: center !important;
    font-size: 7.5pt !important;
    color: #666 !important;
    border-top: 3px solid #FFA500 !important;
    padding-top: 8px !important;
}

@media print {
    .no-print {
        display: none !important;
    }
    .print-root {
        padding: 0 !important;
        background: transparent !important;
        min-height: auto !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }
    .wrapper {
        width: 100% !important;
        max-width: 100% !important;
        margin: 0 !important;
        padding: 0 !important;
        box-shadow: none !important;
    }
}
</style>
