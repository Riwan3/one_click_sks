<script setup>
import { computed, onMounted } from 'vue';
import { Head } from '@inertiajs/vue3';

const props = defineProps({
    suratPeringatan: { type: Object, required: true },
    documentSettings: { type: Object, default: () => ({}) },
});

const dataItem = computed(() => props.suratPeringatan?.data ?? props.suratPeringatan ?? {});

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

const isPrevTidakAda = computed(() => {
    const s = (dataItem.value.peringatanSebelumnyaStatus || '').toLowerCase().trim();
    return s === 'tidak ada' || s === 'tidak';
});

const isPrevAda = computed(() => {
    const s = (dataItem.value.peringatanSebelumnyaStatus || '').toLowerCase().trim();
    return s === 'ada' || s === 'ya';
});

const isPrevPertama = computed(() => {
    const j = (dataItem.value.peringatanSebelumnyaJenis || '').toLowerCase().trim();
    return j.includes('pertama') || j.includes('sp 1') || j.includes('sp1');
});

const isPrevKedua = computed(() => {
    const j = (dataItem.value.peringatanSebelumnyaJenis || '').toLowerCase().trim();
    return j.includes('kedua') || j.includes('sp 2') || j.includes('sp2');
});

const isPrevTerakhir = computed(() => {
    const j = (dataItem.value.peringatanSebelumnyaJenis || '').toLowerCase().trim();
    return j.includes('terakhir') || j.includes('ketiga') || j.includes('sp 3') || j.includes('sp3');
});

const isSpPertama = computed(() => {
    const j = (dataItem.value.jenisPembinaan || '').toLowerCase().trim();
    return j.includes('pertama') || j.includes('sp 1') || j.includes('sp1');
});

const isSpKedua = computed(() => {
    const j = (dataItem.value.jenisPembinaan || '').toLowerCase().trim();
    return j.includes('kedua') || j.includes('sp 2') || j.includes('sp2');
});

const isSpTerakhir = computed(() => {
    const j = (dataItem.value.jenisPembinaan || '').toLowerCase().trim();
    return j.includes('terakhir') || j.includes('ketiga') || j.includes('sp 3') || j.includes('sp3');
});

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
    <Head :title="`Cetak Surat Peringatan - ${dataItem.nrp || ''}`" />

    <div class="print-root">
        <!-- Floating Print Actions (Hidden on Print) -->
        <div class="no-print" style="position: fixed; top: 10px; right: 10px; z-index: 1000; display: flex; gap: 8px;">
            <button
                type="button"
                @click="handlePrint"
                style="padding: 10px 20px; background: #e31e24; color: #fff; border: none; border-radius: 4px; cursor: pointer; font-weight: bold; font-family: sans-serif; font-size: 12px; box-shadow: 0 2px 5px rgba(0,0,0,0.2);"
            >
                PRINT FORM
            </button>
            <button
                type="button"
                @click="handleClose"
                style="padding: 10px 16px; background: #64748b; color: #fff; border: none; border-radius: 4px; cursor: pointer; font-weight: bold; font-family: sans-serif; font-size: 12px; box-shadow: 0 2px 5px rgba(0,0,0,0.2);"
            >
                TUTUP
            </button>
        </div>

        <div class="wrapper">
            <!-- Header -->
            <div class="header">
                <img src="/images/images.png" alt="Logo PPA" class="header-logo">
                <div class="company-name">PUTRA PERKASA ABADI</div>
            </div>

            <!-- Title -->
            <div class="title-block" style="margin-top: 10px; margin-bottom: 10px;">
                <div class="title-text" style="font-size: 14pt;">SURAT PERINGATAN KARYAWAN</div>
                <div class="doc-number">Nomor : {{ dataItem.nomorSp || '.../HCGA/PPA-ADARO/SP/....../20.....' }}</div>
            </div>

            <!-- Info Info -->
            <table class="info-table" style="margin-bottom: 5px;">
                <tbody>
                    <tr>
                        <td class="label">Nama Karyawan / NRP</td>
                        <td class="sep">:</td>
                        <td class="val-bold" style="width: 35%;">{{ dataItem.nama }} / {{ dataItem.nrp }}</td>
                        <td class="label" style="width: 150px;">Jabatan / Departement</td>
                        <td class="sep">:</td>
                        <td class="val-bold">{{ dataItem.jabatan || '-' }} / {{ dataItem.unit || '' }}</td>
                    </tr>
                    <tr>
                        <td class="label">Tanggal Kejadian</td>
                        <td class="sep">:</td>
                        <td>{{ formatDateIndonesia(dataItem.tanggalKejadian) }}</td>
                        <td class="label">Tempat Kejadian</td>
                        <td class="sep">:</td>
                        <td>{{ dataItem.tempatKejadian || '-' }}</td>
                    </tr>
                </tbody>
            </table>

            <div class="thick-line" style="margin-top: 2px; margin-bottom: 5px;"></div>

            <!-- Jenis Pelanggaran -->
            <div class="section-header" style="margin-top: 2px; margin-bottom: 5px;">JENIS PELANGGARAN</div>
            <div class="cb-container" style="margin-bottom: 10px;">
                <div class="cb-column">
                    <div class="cb-item">
                        <div class="box">{{ dataItem.pelanggaranAbsenTerlambat ? '✔' : '' }}</div>
                        <div class="cb-label">Absen / Terlambat</div>
                    </div>
                    <div class="cb-item">
                        <div class="box">{{ dataItem.pelanggaranPerilaku ? '✔' : '' }}</div>
                        <div class="cb-label">Perilaku</div>
                    </div>
                </div>
                <div class="cb-column">
                    <div class="cb-item">
                        <div class="box">{{ dataItem.pelanggaranCerobohLalai ? '✔' : '' }}</div>
                        <div class="cb-label">Ceroboh / Lalai</div>
                    </div>
                    <div class="cb-item">
                        <div class="box">{{ dataItem.pelanggaranHasilKerja ? '✔' : '' }}</div>
                        <div class="cb-label">Hasil Kerja</div>
                    </div>
                </div>
                <div class="cb-column">
                    <div class="cb-item">
                        <div class="box">{{ dataItem.pelanggaranMenolakPerintah ? '✔' : '' }}</div>
                        <div class="cb-label">Menolak Perintah</div>
                    </div>
                    <div class="cb-item">
                        <div class="box">{{ dataItem.pelanggaranPeraturan ? '✔' : '' }}</div>
                        <div class="cb-label">Pelanggaran Peraturan</div>
                    </div>
                </div>
            </div>

            <!-- Penjelasan -->
            <div class="explanation-block" style="margin-top: 5px;">
                <div class="exp-label">PENJELASAN SINGKAT MENGENAI KEJADIAN PELANGGARAN :</div>
                <div class="dotted-line">{{ dataItem.detailPelanggaran || '' }}</div>
                <div class="dotted-line"></div>
            </div>

            <!-- Waktu Kejadian -->
            <table class="occ-table" style="margin-top: 10px;">
                <tbody>
                    <tr>
                        <td style="width: 130px;">Terjadi pada Tanggal</td>
                        <td class="sep">:</td>
                        <td>{{ formatDateIndonesia(dataItem.tanggalKejadian) }}</td>
                    </tr>
                    <tr>
                        <td>Jam</td>
                        <td class="sep">:</td>
                        <td>{{ formatJam(dataItem.jamKejadian) }}</td>
                    </tr>
                    <tr>
                        <td>Lokasi Kejadian</td>
                        <td class="sep">:</td>
                        <td>{{ dataItem.tempatKejadian || '-' }}</td>
                    </tr>
                </tbody>
            </table>

            <!-- Peringatan Sebelumnya -->
            <div class="section-header" style="margin-top: 10px;">PERINGATAN YANG DITERIMA SEBELUMNYA</div>
            <div class="prev-sp-block" style="margin-top: 5px;">
                <div style="display: flex; align-items: center; margin-bottom: 5px;">
                    <span class="p-box">( {{ isPrevTidakAda ? '✔' : ' ' }} )</span> Tidak ada /
                    <span class="p-box" style="margin-left:5px;">( {{ isPrevAda ? '✔' : ' ' }} )</span> Ada : {{ isPrevAda && dataItem.peringatanSebelumnyaMengenai ? dataItem.peringatanSebelumnyaMengenai : '............................................................' }}
                </div>
                <div style="margin-left: 25px;">
                    Jenis Peringatan :
                    <span class="p-box">( {{ isPrevPertama ? '✔' : ' ' }} )</span> Peringatan Pertama &nbsp;&nbsp;
                    <span class="p-box">( {{ isPrevKedua ? '✔' : ' ' }} )</span> Peringatan Kedua &nbsp;&nbsp;
                    <span class="p-box">( {{ isPrevTerakhir ? '✔' : ' ' }} )</span> Peringatan Terakhir
                </div>
            </div>

            <p style="font-size: 8.5pt; margin-top: 5px; margin-bottom: 5px;">
                Merujuk pada peraturan perundang-undangan dan peraturan perusahaan maka dengan ini memberikan sanksi :
            </p>

            <!-- Sanksi Section (Garis memanjang) -->
            <div class="sanksi-section" style="margin-top: 5px; margin-bottom: 5px;">
                <div class="sanksi-header">
                    <div class="col-1">PERINGATAN</div>
                    <div class="col-2">PERUBAHAN PERILAKU</div>
                    <div class="col-3">MASA BERLAKU</div>
                </div>
                <div class="sanksi-row">
                    <div class="col-1">
                        <span class="p-box">( {{ isSpPertama ? '✔' : ' ' }} )</span> Peringatan Pertama
                    </div>
                    <div class="col-2">Karyawan diminta untuk lebih hati-hati dan tidak mengulangi</div>
                    <div class="col-3">6 (enam) bulan</div>
                </div>
                <div class="sanksi-row">
                    <div class="col-1">
                        <span class="p-box">( {{ isSpKedua ? '✔' : ' ' }} )</span> Peringatan Kedua
                    </div>
                    <div class="col-2">Karyawan diingatkan untuk tidak mengulangi dan diminta Perhatian serius</div>
                    <div class="col-3">6 (enam) bulan</div>
                </div>
                <div class="sanksi-row">
                    <div class="col-1">
                        <span class="p-box">( {{ isSpTerakhir ? '✔' : ' ' }} )</span> Peringatan Terakhir
                    </div>
                    <div class="col-2">Karyawan diberitahu jika terjadi pelanggaran lagi dapat berakibat PHK</div>
                    <div class="col-3">6 (enam) bulan</div>
                </div>
            </div>

            <div class="footer-loc" style="margin-top: 5px; margin-bottom: 5px; text-align: right;">
                Dikeluarkan di : {{ dataItem.dikeluarkanDi || documentSettings?.lokasi || 'CSA Inggris' }}<br>
                <u>Pada tanggal : {{ formatDateIndonesia(dataItem.dikeluarkanTanggal) }}</u>
            </div>

            <!-- Area Tanda Tangan -->
            <div class="sig-container">
                <div class="sig-box">
                    <div class="sig-space">
                        <img
                            v-if="dataItem.ttdKaryawan"
                            :src="dataItem.ttdKaryawan"
                            alt="TTD Karyawan"
                            style="position: absolute; width: 120px; max-width: none; top: -30px; left: 50%; transform: translateX(-50%); z-index: 10;"
                        >
                    </div>
                    <div class="sig-line">{{ dataItem.nama }}</div>
                    <div class="sig-role">Karyawan Ybs,</div>
                </div>
                <div class="sig-box">
                    <div class="sig-space">
                        <img
                            v-if="dataItem.ttdAtasan"
                            :src="dataItem.ttdAtasan"
                            alt="TTD Atasan"
                            style="position: absolute; width: 120px; max-width: none; top: -30px; left: 50%; transform: translateX(-50%); z-index: 10;"
                        >
                    </div>
                    <div class="sig-line">{{ dataItem.pengawasName || '..........................' }}</div>
                    <div class="sig-role">Atasan langsung</div>
                </div>
                <div class="sig-box">
                    <div class="sig-space"></div>
                    <div class="sig-line">................................</div>
                    <div class="sig-role">Atasan dari atasan Langsung</div>
                </div>
            </div>

            <!-- Notes -->
            <div class="catatan-box">
                <span class="catatan-label">Catatan:</span> {{ dataItem.catatanKaryawan || 'Berkomitmen untuk mengikuti Prosedur yang berlaku' }}
            </div>

            <!-- Teks Kesadaran -->
            <div class="ack-text">
                Tanda tangan saudara diatas menandakan bahwa anda menyadari kejadian tersebut diatas dan telah diberitahukan sanksi perihal tersebut diatas dan akibatnya apabila terulang lagi.
            </div>

            <!-- Yellow Bar -->
            <div class="yellow-bar"></div>

            <!-- Company Address -->
            <div class="company-address">
                Gedung Office 8, Lantai 8, SCBD Lot 28. Jl Jend Sudirman Kav. 52-53 Senayan, Kebayoran Baru, Jakarta Selatan, 12190.<br>
                Telp. +62 21 5790 3456 | www.ppa.co.id
            </div>
        </div>

        <!-- Lampiran Dokumentasi Foto jika ada -->
        <div v-if="dataItem.foto" class="wrapper" style="page-break-before: always; margin-top: 20px; padding: 15px 0;">
            <div class="header">
                <img src="/assets/images/images.png" class="header-logo" alt="Logo" />
                <div class="company-name">PUTRA PERKASA ABADI</div>
            </div>
            <div class="title-sp" style="margin-bottom: 2px;">LAMPIRAN DOKUMENTASI FOTO</div>
            <div class="sp-nomor" style="margin-bottom: 15px;">Surat Peringatan: {{ dataItem.nomorSp || '-' }}</div>
            
            <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 10px;">
                <img :src="dataItem.foto" alt="Foto Dokumentasi SP" style="max-width: 95%; max-height: 600px; object-fit: contain; border: 1px solid #ccc; padding: 6px; background: #fff;" />
                <div style="font-size: 8pt; color: #666; margin-top: 8px;">
                    Dokumentasi Bukti: {{ dataItem.nrp }} - {{ dataItem.nama }} ({{ dataItem.tanggalKejadian || '-' }})
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
/* Setup Kertas A4 */
@page {
    size: A4 portrait;
    margin: 10mm 15mm;
}

* {
    box-sizing: border-box;
}

.print-root {
    font-family: Arial, Helvetica, sans-serif !important;
    font-size: 10pt !important;
    color: #000 !important;
    margin: 0 !important;
    padding: 20px 0 !important;
    line-height: 1.2 !important;
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
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
}

/* Header Logo */
.header {
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    margin-bottom: 5px !important;
    padding: 15px 0 !important;
    position: relative !important;
}

.header-logo {
    height: 50px !important;
    width: auto !important;
    max-width: none !important;
    margin-right: 15px !important;
    display: inline-block !important;
}

.company-name {
    font-size: 24pt !important;
    font-weight: 900 !important;
    letter-spacing: 0.5px !important;
    color: #000 !important;
    font-family: Calibri, Candara, Segoe, "Segoe UI", Optima, Arial, sans-serif !important;
}

.manual-num {
    position: absolute !important;
    right: 0 !important;
    top: 0 !important;
    font-size: 18pt !important;
    font-weight: normal !important;
    font-family: "Courier New", Courier, monospace !important;
}

/* Judul Dokumen */
.title-block {
    text-align: center !important;
    margin-top: 20px !important;
    margin-bottom: 20px !important;
    font-family: "Times New Roman", Times, serif !important;
}

.title-text {
    font-size: 16pt !important;
    font-weight: bold !important;
    letter-spacing: 1px !important;
}

.doc-number {
    font-size: 10pt !important;
    margin-top: 5px !important;
}

/* Tabel Info Atas */
.info-table {
    width: 100% !important;
    border-collapse: collapse !important;
    margin-bottom: 10px !important;
}

.info-table td {
    padding: 3px 0 !important;
    vertical-align: top !important;
    font-size: 9.5pt !important;
    color: #000 !important;
}

.label {
    width: 140px !important;
}

.sep {
    width: 15px !important;
    text-align: center !important;
}

.val-bold {
    font-weight: bold !important;
}

/* Garis Hitam Tebal */
.thick-line {
    border-bottom: 2.5px solid #000 !important;
    margin-top: 5px !important;
    margin-bottom: 10px !important;
    width: 100% !important;
}

/* Jenis Pelanggaran */
.section-header {
    font-weight: bold !important;
    font-size: 10pt !important;
    margin-top: 5px !important;
    margin-bottom: 10px !important;
    color: #000 !important;
}

.cb-container {
    display: grid !important;
    grid-template-columns: 1fr 1fr 1fr !important;
    gap: 5px !important;
    margin-bottom: 15px !important;
}

.cb-column {
    display: flex !important;
    flex-direction: column !important;
}

.cb-item {
    display: flex !important;
    align-items: center !important;
    margin-bottom: 8px !important;
}

.box {
    width: 16px !important;
    height: 16px !important;
    border: 1.5px solid #000 !important;
    margin-right: 10px !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    font-weight: bold !important;
    font-size: 11pt !important;
    flex-shrink: 0 !important;
    line-height: 1 !important;
}

.p-box {
    font-size: 11pt !important;
    margin-right: 5px !important;
    font-family: monospace !important;
    font-weight: bold !important;
}

.cb-label {
    font-size: 9.5pt !important;
    color: #000 !important;
}

/* Penjelasan Pelanggaran */
.explanation-block {
    margin-top: 10px !important;
}

.exp-label {
    font-weight: bold !important;
    font-size: 9.5pt !important;
    margin-bottom: 5px !important;
    text-transform: uppercase !important;
    color: #000 !important;
}

.dotted-line {
    border-bottom: 1px dotted #000 !important;
    min-height: 22px !important;
    padding-top: 5px !important;
    font-family: "Courier New", Courier, monospace !important;
    font-size: 11pt !important;
    font-style: italic !important;
    color: #000 !important;
}

/* Tabel Waktu Kejadian */
.occ-table {
    margin-top: 15px !important;
    width: 100% !important;
    border-collapse: collapse !important;
}

.occ-table td {
    padding: 2px 0 !important;
    font-size: 9.5pt !important;
    color: #000 !important;
}

/* Peringatan Sebelumnya */
.prev-sp-block {
    margin-top: 15px !important;
    font-size: 9.5pt !important;
    color: #000 !important;
}

/* Sanksi Section (Garis memanjang) */
.sanksi-section {
    width: 100% !important;
    margin-top: 10px !important;
    margin-bottom: 10px !important;
}

.sanksi-header {
    display: flex !important;
    font-weight: bold !important;
    border-bottom: 1px solid #000 !important;
    padding-bottom: 5px !important;
    font-size: 9pt !important;
    color: #000 !important;
}

.sanksi-row {
    display: flex !important;
    border-bottom: 1px solid #000 !important;
    padding: 8px 0 !important;
    font-size: 8.5pt !important;
    align-items: center !important;
    color: #000 !important;
}

.col-1 {
    width: 25% !important;
}

.col-2 {
    width: 55% !important;
    padding: 0 10px !important;
}

.col-3 {
    width: 20% !important;
    text-align: center !important;
}

/* Footer Lokasi */
.footer-loc {
    text-align: right !important;
    margin-top: 10px !important;
    margin-bottom: 10px !important;
    font-size: 10pt !important;
    padding-right: 40px !important;
    color: #000 !important;
}

/* Area Tanda Tangan */
.sig-container {
    display: flex !important;
    justify-content: space-between !important;
    margin-top: 5px !important;
    text-align: center !important;
}

.sig-box {
    width: 30% !important;
    position: relative !important;
}

.sig-space {
    height: 35px !important;
    position: relative !important;
}

.sig-line {
    border-top: 1px dotted #000 !important;
    margin: 0 10px !important;
    padding-top: 3px !important;
    font-weight: bold !important;
    text-transform: uppercase !important;
    font-size: 9pt !important;
    color: #000 !important;
}

.sig-role {
    font-size: 8.5pt !important;
    color: #000 !important;
}

/* Kotak Catatan */
.catatan-box {
    border: 1.5px solid #000 !important;
    padding: 5px 10px !important;
    margin-top: 10px !important;
    min-height: 30px !important;
    font-size: 9pt !important;
    color: #000 !important;
}

.catatan-label {
    font-weight: bold !important;
    font-style: italic !important;
    text-decoration: underline !important;
    margin-right: 5px !important;
}

/* Teks Kesadaran */
.ack-text {
    font-size: 7.5pt !important;
    margin-top: 5px !important;
    line-height: 1.1 !important;
    color: #000 !important;
}

/* Bottom Branding */
.yellow-bar {
    height: 6px !important;
    background-color: #ffcc00 !important;
    margin-top: 10px !important;
    width: 100% !important;
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
}

.company-address {
    text-align: center !important;
    font-size: 7pt !important;
    color: #444 !important;
    margin-top: 5px !important;
    line-height: 1.2 !important;
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
