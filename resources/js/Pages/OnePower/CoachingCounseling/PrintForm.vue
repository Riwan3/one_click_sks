<script setup>
import { computed, onMounted } from 'vue';
import { Head } from '@inertiajs/vue3';

const props = defineProps({
    item: { type: Object, default: null },
    documentSettings: { type: Object, default: () => ({}) },
});

const printItem = computed(() => props.item?.data ?? props.item ?? {});

const BULAN_INDO = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

const formatDateIndonesia = (value) => {
    if (! value) return '';
    const date = new Date(value);
    if (Number.isNaN(date.getTime())) return value;
    return `${date.getDate()} ${BULAN_INDO[date.getMonth() + 1]} ${date.getFullYear()}`;
};

const isMateriChecked = (val) => val == 1 || val === '1' || val === true || val === 'true';

const effectiveNoDokumen = computed(() => props.documentSettings?.noDokumen || 'PPA-ADRO-F-HCGA-17');
const effectiveRevisi = computed(() => props.documentSettings?.revisi || '1');
const effectiveTglEfektif = computed(() => formatDateIndonesia(props.documentSettings?.tglEfektif || '2022-06-07') || '07 Juni 2022');
const effectiveLokasi = computed(() => props.documentSettings?.lokasi || 'Tabalong');

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
    <Head :title="printItem.nama ? `${printItem.nama} - Form CC` : 'Formulir Coaching & Counseling Kosong'" />

    <div class="print-container">
        <div class="no-print" style="position: fixed; top: 20px; right: 20px; z-index: 9999; display: flex; gap: 10px;">
            <button class="print-btn" type="button" @click="handlePrint">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                    <path d="M5 1a2 2 0 0 0-2 2v2H2a2 2 0 0 0-2 2v3a2 2 0 0 0 2 2h1v1a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2v-1h1a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-1V3a2 2 0 0 0-2-2H5zM4 3a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2H4V3zm1 5a.5.5 0 0 0 0 1h6a.5.5 0 0 0 0-1H5zm0 2a.5.5 0 0 0 0 1h3a.5.5 0 0 0 0-1H5z"/>
                </svg>
                Cetak Sekarang
            </button>
            <button class="btn-tutup" type="button" @click="handleClose">TUTUP</button>
        </div>

        <div class="container">
            <!-- HEADER -->
            <table class="main-table">
                <tbody>
                    <tr>
                        <td class="header-logo" style="width: 100px;">
                            <img src="/images/images.png" width="50" height="50" alt="Logo" class="mx-auto block">
                            <div style="font-weight: bold; font-size: 11pt; margin-top: -5px;">PPA</div>
                        </td>
                        <td class="header-title" style="font-size: 16pt;">COACHING &amp; COUNSELING</td>
                        <td class="header-info" style="width: 250px;">
                            <table style="width: 100%; border: none; font-size: 8pt;">
                                <tbody>
                                    <tr>
                                        <td style="width: 90px; border: none; border-left: 1px solid #000; border-bottom: 1px solid #000; padding: 2px 5px;">No Dokumen</td>
                                        <td style="width: 10px; border: none; border-bottom: 1px solid #000; padding: 2px 5px;">:</td>
                                        <td style="border: none; border-bottom: 1px solid #000; padding: 2px 5px;">{{ effectiveNoDokumen }}</td>
                                    </tr>
                                    <tr>
                                        <td style="border: none; border-left: 1px solid #000; border-bottom: 1px solid #000; padding: 2px 5px;">Revisi</td>
                                        <td style="width: 10px; border: none; border-bottom: 1px solid #000; padding: 2px 5px;">:</td>
                                        <td style="border: none; border-bottom: 1px solid #000; padding: 2px 5px;">{{ effectiveRevisi }}</td>
                                    </tr>
                                    <tr>
                                        <td style="border: none; border-left: 1px solid #000; border-bottom: 1px solid #000; padding: 2px 5px;">Tgl Efektif</td>
                                        <td style="width: 10px; border: none; border-bottom: 1px solid #000; padding: 2px 5px;">:</td>
                                        <td style="border: none; border-bottom: 1px solid #000; padding: 2px 5px;">{{ effectiveTglEfektif }}</td>
                                    </tr>
                                    <tr>
                                        <td style="border: none; border-left: 1px solid #000; padding: 2px 5px;">Halaman</td>
                                        <td style="border: none; padding: 2px 5px;">:</td>
                                        <td style="border: none; padding: 2px 5px;">1 dari 1</td>
                                    </tr>
                                </tbody>
                            </table>
                        </td>
                    </tr>
                </tbody>
            </table>

            <!-- INFO KARYAWAN -->
            <table class="main-table">
                <tbody>
                    <tr><td><span class="label-text">Nama Karyawan</span> <span class="colon">:</span> {{ printItem.nama || '' }}</td></tr>
                    <tr><td><span class="label-text">Jabatan</span> <span class="colon">:</span> {{ printItem.jabatan || '' }}</td></tr>
                    <tr><td><span class="label-text">Lokasi</span> <span class="colon">:</span> {{ printItem.lokasi || effectiveLokasi }}</td></tr>
                    <tr><td><span class="label-text">Tanggal</span> <span class="colon">:</span> {{ formatDateIndonesia(printItem.tanggal) }}</td></tr>
                    <tr><td><span class="label-text">Waktu Pertemuan</span> <span class="colon">:</span> {{ printItem.waktuPertemuan || '' }}</td></tr>
                </tbody>
            </table>

            <!-- POKOK MATERI & SIGNATURES -->
            <table class="main-table">
                <tbody>
                    <tr>
                        <th style="width: 30%;">Pokok Materi</th>
                        <th style="width: 35%;">Karyawan</th>
                        <th style="width: 35%;">Counselor / Coach</th>
                    </tr>
                    <tr>
                        <td style="padding: 0;">
                            <table style="border: none; width: 100%; margin: 0;">
                                <tbody>
                                    <tr><td style="border:none; border-bottom:1px solid #000;"><span style="display:inline-block; width:80px;">Pribadi</span> <span class="colon">:</span> <div class="checkbox-box">{{ isMateriChecked(printItem.materiPribadi) ? '✓' : '' }}</div></td></tr>
                                    <tr><td style="border:none; border-bottom:1px solid #000;"><span style="display:inline-block; width:80px;">Keluarga</span> <span class="colon">:</span> <div class="checkbox-box">{{ isMateriChecked(printItem.materiKeluarga) ? '✓' : '' }}</div></td></tr>
                                    <tr><td style="border:none; border-bottom:1px solid #000;"><span style="display:inline-block; width:80px;">Lingkungan</span> <span class="colon">:</span> <div class="checkbox-box">{{ isMateriChecked(printItem.materiLingkungan) ? '✓' : '' }}</div></td></tr>
                                    <tr><td style="border:none;"><span style="display:inline-block; width:80px;">Pekerjaan</span> <span class="colon">:</span> <div class="checkbox-box">{{ isMateriChecked(printItem.materiPekerjaan) ? '✓' : '' }}</div></td></tr>
                                </tbody>
                            </table>
                        </td>
                        <td class="signature-cell">
                            <div v-if="printItem.ttdAnakAsuh" style="text-align:center; margin-bottom:5px;">
                                <img :src="printItem.ttdAnakAsuh" style="max-width:180px; max-height:70px; display:inline-block;" alt="TTD Anak Asuh">
                            </div>
                            <div class="name-wrapper">
                                <span class="name-underlined">{{ printItem.nama || '' }}</span>
                            </div>
                            <div style="text-align: center;">
                                {{ effectiveLokasi }}, {{ printItem.tanggal ? formatDateIndonesia(printItem.tanggal) : '.............................' }}
                            </div>
                        </td>
                        <td class="signature-cell">
                            <div v-if="printItem.ttdBapaAsuh" style="text-align:center; margin-bottom:5px;">
                                <img :src="printItem.ttdBapaAsuh" style="max-width:180px; max-height:70px; display:inline-block;" alt="TTD Bapa Asuh">
                            </div>
                            <div class="name-wrapper">
                                <span class="name-underlined">{{ printItem.coachNama || '' }}</span>
                            </div>
                            <div style="text-align: center;">
                                {{ effectiveLokasi }}, {{ printItem.tanggal ? formatDateIndonesia(printItem.tanggal) : '.............................' }}
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>

            <!-- DESKRIPSI -->
            <table class="main-table">
                <tbody>
                    <tr><td style="font-weight:bold; background:#f2f2f2;">Deskripsi :</td></tr>
                    <tr><td class="content-cell" style="white-space: pre-line;">{{ printItem.deskripsi || '' }}</td></tr>
                </tbody>
            </table>

            <!-- REKOMENDASI -->
            <table class="main-table">
                <tbody>
                    <tr><td style="font-weight:bold; background:#f2f2f2;">Rekomendasi :</td></tr>
                    <tr><td class="rekomendasi-cell" style="white-space: pre-line;">{{ printItem.rekomendasi || '' }}</td></tr>
                </tbody>
            </table>

            <!-- NOTE -->
            <table class="main-table">
                <tbody>
                    <tr>
                        <td class="note-cell">
                            <strong>Note :</strong><br>
                            1. <strong>Coaching</strong> fokus pada keterampilan dan pengetahuan mengenai tugas yang kurang maksimal<br>
                            2. <strong>Counseling</strong> fokus terhadap masalah pribadi and berpengaruh terhadap kinerjanya.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>

<style scoped>
@page {
    size: A4;
    margin: 0.5cm;
}

.print-container {
    font-family: Arial, sans-serif;
    font-size: 10pt;
    margin: 0;
    padding: 10px;
    color: #000;
    background: #fff;
    min-height: 100vh;
}

.container {
    width: 100%;
    max-width: 100%;
}

.main-table {
    width: 100%;
    border-collapse: collapse;
    table-layout: fixed;
    margin-bottom: -1px;
}

.main-table td,
.main-table th {
    border: 1px solid #000;
    padding: 6px 8px;
    word-wrap: break-word;
}

.header-logo {
    width: 80px;
    text-align: center;
}

.header-title {
    text-align: center;
    font-size: 14pt;
    font-weight: bold;
}

.header-info {
    width: 220px;
    font-size: 8pt;
    padding: 0 !important;
}

.label-text {
    display: inline-block;
    width: 140px;
    font-weight: bold;
}

.colon {
    margin-right: 5px;
}

.checkbox-box {
    width: 20px;
    height: 20px;
    border: 1px solid #000;
    display: inline-block;
    vertical-align: middle;
    text-align: center;
    line-height: 20px;
    margin-left: 10px;
    font-weight: bold;
    font-size: 12pt;
}

.signature-cell {
    vertical-align: bottom;
    height: 100px;
    padding: 5px;
    text-align: left;
}

.name-wrapper {
    text-align: center;
    width: 100%;
    margin-bottom: 5px;
}

.name-underlined {
    font-weight: bold;
    text-decoration: underline;
    text-transform: uppercase;
    display: inline-block;
}

.content-cell {
    height: 120px;
    vertical-align: top;
}

.rekomendasi-cell {
    height: 60px;
    vertical-align: top;
}

.note-cell {
    font-size: 8pt;
    line-height: 1.2;
    padding: 5px;
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

.btn-tutup {
    padding: 10px 20px;
    background: #6c757d;
    color: white;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-weight: bold;
    box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);
}
.btn-tutup:hover {
    background: #5a6268;
}

@media print {
    .no-print {
        display: none !important;
    }
    .print-container {
        padding: 0;
        min-height: 0;
    }
}
</style>
