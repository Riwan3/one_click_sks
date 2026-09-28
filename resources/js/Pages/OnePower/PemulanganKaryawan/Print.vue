<script setup>
import { computed, onMounted } from 'vue';
import { Head } from '@inertiajs/vue3';

const props = defineProps({
    item: { type: Object, required: true },
    documentSettings: { type: Object, default: () => ({}) },
});

const data = computed(() => props.item?.data ?? props.item ?? {});

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

const formatDateStrip = (value) => {
    if (! value) return '-';
    const clean = String(value).split('T')[0].split(' ')[0];
    const parts = clean.split('-');
    if (parts.length === 3) {
        return `${parts[2]} - ${parts[1]} - ${parts[0]}`;
    }
    return value;
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
    <Head :title="`Print Pemulangan Karyawan - ${data.nama || ''}`" />

    <div class="print-container">
        <!-- Floating Actions -->
        <div class="no-print">
            <button class="btn btn-print" @click="handlePrint">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                    <path d="M5 1a2 2 0 0 0-2 2v2H2a2 2 0 0 0-2 2v3a2 2 0 0 0 2 2h1v1a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2v-1h1a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-1V3a2 2 0 0 0-2-2H5zM4 3a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2H4V3zm1 5a.5.5 0 0 0 0 1h6a.5.5 0 0 0 0-1H5zm0 2a.5.5 0 0 0 0 1h3a.5.5 0 0 0 0-1H5z"/>
                </svg>
                Cetak Sekarang
            </button>
            <button class="btn btn-close" @click="handleClose">Tutup</button>
        </div>

        <div class="page">
            <!-- Header Table -->
            <table class="main-table">
                <tr>
                    <td class="logo-cell">
                        <img src="/images/images.png" alt="Logo PPA">
                        <span class="logo-text">PPA</span>
                    </td>
                    <td class="title-cell">
                        FORMULIR PEMULANGAN KARYAWAN<br>FATIGUE
                    </td>
                    <td class="info-cell">
                        <table>
                            <tr>
                                <td style="width: 38%; border-left: none;">No Dokumen</td>
                                <td>: {{ documentSettings.noDokumen || 'PPA-ADRO-F-SHE-29F' }}</td>
                            </tr>
                            <tr>
                                <td style="border-left: none;">Revisi</td>
                                <td>: {{ documentSettings.revisi || '0' }}</td>
                            </tr>
                            <tr>
                                <td style="border-left: none;">Tgl Efektif</td>
                                <td>: {{ formatDateIndonesia(documentSettings.tglEfektif || '2023-05-18') }}</td>
                            </tr>
                            <tr>
                                <td style="border-left: none; border-bottom: none;">Halaman</td>
                                <td style="border-bottom: none;">: 1 dari 1</td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>

            <!-- Detail Table -->
            <table class="main-table" style="border-top: none;">
                <tr>
                    <td style="width: 25%; font-weight: bold; border-right: none;">Nama Karyawan</td>
                    <td style="width: 3%; text-align: center; font-weight: bold; border-left: none; border-right: none;">:</td>
                    <td style="width: 72%; border-left: none;">{{ data.nama || '-' }}</td>
                </tr>
                <tr>
                    <td style="font-weight: bold; border-right: none;">Jabatan</td>
                    <td style="text-align: center; font-weight: bold; border-left: none; border-right: none;">:</td>
                    <td style="border-left: none;">{{ data.jabatan || '-' }}</td>
                </tr>
                <tr>
                    <td style="font-weight: bold; border-right: none;">Departemen</td>
                    <td style="text-align: center; font-weight: bold; border-left: none; border-right: none;">:</td>
                    <td style="border-left: none;">{{ data.departemen || '-' }}</td>
                </tr>
                <tr>
                    <td style="font-weight: bold; border-right: none;">Lokasi</td>
                    <td style="text-align: center; font-weight: bold; border-left: none; border-right: none;">:</td>
                    <td style="border-left: none;">{{ data.lokasi || '-' }}</td>
                </tr>
                <tr>
                    <td style="font-weight: bold; border-right: none;">Tanggal</td>
                    <td style="text-align: center; font-weight: bold; border-left: none; border-right: none;">:</td>
                    <td style="border-left: none;">{{ formatDateStrip(data.tanggal) }}</td>
                </tr>
                <tr>
                    <td style="font-weight: bold; border-right: none;">Indikasi Fatigue</td>
                    <td style="text-align: center; font-weight: bold; border-left: none; border-right: none;">:</td>
                    <td style="border-left: none;">{{ data.indikasiFatigue || '-' }}</td>
                </tr>
                <tr>
                    <td style="font-weight: bold; border-right: none;">Jam Tidur &le; 4 Jam</td>
                    <td style="text-align: center; font-weight: bold; border-left: none; border-right: none;">:</td>
                    <td style="border-left: none;">
                        <div class="checkbox">{{ data.jamTidurKurang4jam ? '✓' : '' }}</div>
                    </td>
                </tr>
                <tr>
                    <td style="font-weight: bold; border-right: none; border-bottom: 2px solid black;">Fatigue berat</td>
                    <td style="text-align: center; font-weight: bold; border-left: none; border-right: none; border-bottom: 2px solid black;">:</td>
                    <td style="border-left: none; border-bottom: 2px solid black;">
                        <div class="checkbox">{{ data.fatigueBerat ? '✓' : '' }}</div>
                    </td>
                </tr>
                <tr>
                    <td colspan="3" class="row-full">
                        <strong>Deskripsi (Penyebab Fatigue) :</strong><br>
                        <div class="content-box">{{ data.deskripsiPenyebab || '-' }}</div>
                    </td>
                </tr>
                <tr>
                    <td colspan="3" class="row-full">
                        <strong>Alamat Tempat Tinggal (Pengantaran):</strong><br>
                        <div class="content-box" style="min-height: 40px;">{{ data.alamatPengantaran || '-' }}</div>
                    </td>
                </tr>
            </table>

            <!-- Penanggung Jawab Table -->
            <table class="main-table" style="border-top: none;">
                <tr>
                    <td style="width: 78%; vertical-align: top;">
                        <strong>Penanggungjawab Pengantaran:</strong><br>
                        <div style="margin-top: 10px; font-size: 15px;">{{ data.penanggungjawabPengantaran || '-' }}</div>
                    </td>
                    <td style="width: 22%; vertical-align: top; border-left: 2px solid black; height: 80px;">
                        <strong>Paraf:</strong>
                    </td>
                </tr>
            </table>

            <!-- Signature Section -->
            <div class="signature-section">
                <div class="tabalong-row">{{ documentSettings.lokasi || 'Tabalong' }}, {{ formatDateIndonesia(data.tanggal) }}</div>

                <div class="sig-container">
                    <div class="sig-item">
                        <div class="sig-label">Karyawan,</div>
                        <img v-if="data.ttdKaryawan" :src="data.ttdKaryawan" alt="TTD Karyawan" class="sig-img">
                        <div class="sig-name">( {{ data.nama || '........................................' }} )</div>
                    </div>
                    <div class="sig-item">
                        <div class="sig-label">Pengawas,</div>
                        <img v-if="data.ttdPengawas" :src="data.ttdPengawas" alt="TTD Pengawas" class="sig-img">
                        <div class="sig-name">( ........................................ )</div>
                    </div>
                </div>
            </div>

            <!-- Notes Section -->
            <div class="note-section">
                <strong>Note:</strong><br>
                1. <strong>Fatigue berat</strong> berdasarkan penilaian fatigue (<i>fatigue check</i>) dari Paramedic.<br>
                2. Bagi pemulangan akibat <strong>fatigue berat</strong>, wajib terdapat paraf Paramedic pada bagian sebelah kiri kolom tandatangan <strong>Pengawas</strong>.<br>
                3. Penanggungjawab pengantaran merupakan petugas pengantaran/ driver yang ditunjuk oleh Departemen HCGA.
            </div>
        </div>
    </div>
</template>

<style scoped>
* {
    box-sizing: border-box;
}

.print-container {
    font-family: Arial, sans-serif;
    font-size: 13px;
    margin: 0;
    padding: 20px;
    color: #000;
    background: #fff;
    min-height: 100vh;
}

.page {
    width: 100%;
    max-width: 210mm;
    margin: 0 auto;
    padding: 0;
    background: white;
}

.main-table {
    width: 100%;
    border-collapse: collapse;
    border: 2px solid black;
    margin-top: -1px;
}

.main-table td {
    border: 1px solid black;
    padding: 6px 10px;
    vertical-align: middle;
}

.logo-cell {
    width: 15%;
    text-align: center;
    border-right: 2px solid black;
    padding: 8px;
}

.logo-cell img {
    width: 65px;
    height: auto;
    object-fit: contain;
}

.logo-text {
    font-weight: bold;
    font-size: 18px;
    display: block;
    margin-top: 3px;
}

.title-cell {
    width: 50%;
    text-align: center;
    font-weight: bold;
    font-size: 15px;
    line-height: 1.4;
}

.info-cell {
    width: 35%;
    padding: 0 !important;
}

.info-cell table {
    width: 100%;
    border-collapse: collapse;
    border: none;
}

.info-cell td {
    border: none;
    border-bottom: 1px solid black;
    border-left: 1px solid black;
    padding: 4px 8px;
    font-size: 11px;
    height: 26px;
}

.info-cell tr:last-child td {
    border-bottom: none;
}

.checkbox {
    width: 22px;
    height: 22px;
    border: 1px solid black;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 16px;
}

.row-full {
    padding-top: 8px !important;
}

.row-full strong {
    font-size: 13px;
}

.content-box {
    min-height: 70px;
    padding: 8px 0;
    font-size: 14px;
    white-space: pre-line;
}

.signature-section {
    border: 2px solid black;
    border-top: none;
    position: relative;
    padding-bottom: 25px;
    margin-top: -1px;
}

.tabalong-row {
    text-align: right;
    padding-right: 80px;
    font-weight: bold;
    padding-top: 12px;
    margin-bottom: 25px;
    font-size: 13px;
}

.sig-container {
    display: flex;
    justify-content: space-around;
    text-align: center;
}

.sig-item {
    width: 40%;
    position: relative;
}

.sig-label {
    font-weight: bold;
    margin-bottom: 60px;
    font-size: 13px;
}

.sig-img {
    position: absolute;
    top: 15px;
    left: 50%;
    transform: translateX(-50%);
    height: 75px;
    max-width: 180px;
    object-fit: contain;
    z-index: 1;
}

.sig-name {
    font-weight: bold;
    font-size: 14px;
    display: inline-block;
    min-width: 180px;
    border-bottom: 1px dotted black;
    padding-bottom: 2px;
}

.note-section {
    border: 2px solid black;
    padding: 12px;
    font-size: 12px;
    line-height: 1.5;
    margin-top: -1px;
}

.note-section strong {
    font-size: 13px;
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
    border-radius: 6px;
    cursor: pointer;
    font-weight: bold;
    font-size: 12px;
    display: flex;
    align-items: center;
    gap: 6px;
    color: white;
}

.btn-print {
    background: #0284c7;
}

.btn-print:hover {
    background: #0369a1;
}

.btn-close {
    background: #64748b;
}

.btn-close:hover {
    background: #475569;
}

@media print {
    body {
        background: none;
    }
    .print-container {
        padding: 0;
        background: transparent;
    }
    .page {
        width: 100%;
        max-width: none;
        margin: 0;
        padding: 0;
    }
    .no-print {
        display: none !important;
    }
    @page {
        size: portrait;
        margin: 10mm;
    }
}
</style>
