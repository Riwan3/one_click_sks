<script setup>
import { computed } from 'vue';
import { Head } from '@inertiajs/vue3';

const props = defineProps({
    item: { type: Object, required: true },
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

const handlePrint = () => {
    window.print();
};

const handleClose = () => {
    window.close();
};
</script>

<template>
    <Head :title="`Print Interview Fatigue - ${dataItem.nama || dataItem.namaOperator || 'Operator'}`" />

    <div class="print-container">
        <!-- Action Buttons (Print / Close) -->
        <div class="no-print">
            <button class="print-btn" type="button" @click="handlePrint">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                    <path d="M5 1a2 2 0 0 0-2 2v2H2a2 2 0 0 0-2 2v3a2 2 0 0 0 2 2h1v1a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2v-1h1a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-1V3a2 2 0 0 0-2-2H5zM4 3a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2H4V3zm1 5a.5.5 0 0 0 0 1h6a.5.5 0 0 0 0-1H5zm0 2a.5.5 0 0 0 0 1h3a.5.5 0 0 0 0-1H5z"/>
                </svg>
                Cetak Sekarang
            </button>
            <button class="close-btn" type="button" @click="handleClose">TUTUP</button>
        </div>

        <div class="page">
            <!-- Header Table -->
            <table class="header-table">
                <tr>
                    <td class="logo-cell">
                        <img src="/images/images.png" alt="PT. PPA">
                        <div class="logo-text">PT. PPA</div>
                    </td>
                    <td class="title-cell">
                        FORMULIR INTERVIEW<br>OPERATOR IZIN FATIGUE
                    </td>
                    <td class="meta-cell">
                        <table style="width: 100%; border-collapse: collapse;">
                            <tr>
                                <td style="border-right: 1px solid #000; border-bottom: 1px solid #000; width: 40%; padding: 2px 5px;">No. Dokumen</td>
                                <td style="border-bottom: 1px solid #000; padding: 2px 5px;">: {{ documentSettings?.noDokumen || 'PPA-ADRO-F-PRO-06' }}</td>
                            </tr>
                            <tr>
                                <td style="border-right: 1px solid #000; border-bottom: 1px solid #000; padding: 2px 5px;">Revisi</td>
                                <td style="border-bottom: 1px solid #000; padding: 2px 5px;">: {{ documentSettings?.revisi || '00' }}</td>
                            </tr>
                            <tr>
                                <td style="border-right: 1px solid #000; border-bottom: 1px solid #000; padding: 2px 5px;">Tgl. Efektif</td>
                                <td style="border-bottom: 1px solid #000; padding: 2px 5px;">: {{ documentSettings?.tglEfektif ? formatDateIndonesia(documentSettings.tglEfektif) : '12 September 2022' }}</td>
                            </tr>
                            <tr>
                                <td style="border-right: 1px solid #000; padding: 2px 5px;">Halaman</td>
                                <td style="padding: 2px 5px;">: 1 dari 1</td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>

            <!-- Identity Info -->
            <div class="identity-info">
                <table style="width: 100%;">
                    <tr>
                        <td style="width: 50%; vertical-align: top;">
                            <table style="width: 100%;">
                                <tr><td class="label">NRP Operator</td><td class="colon">:</td><td>{{ dataItem.nrp || dataItem.nrpOperator || '-' }}</td></tr>
                                <tr><td class="label">Nama Operator</td><td class="colon">:</td><td>{{ dataItem.nama || dataItem.namaOperator || '-' }}</td></tr>
                                <tr><td class="label">Nomor Unit</td><td class="colon">:</td><td>{{ dataItem.nomorUnit || '-' }}</td></tr>
                                <tr><td class="label">Area</td><td class="colon">:</td><td>{{ dataItem.area || '-' }}</td></tr>
                            </table>
                        </td>
                        <td style="width: 50%; vertical-align: top;">
                            <table style="width: 100%;">
                                <tr><td class="label">NRP Pengawas</td><td class="colon">:</td><td>{{ dataItem.nrpPengawas || '-' }}</td></tr>
                                <tr><td class="label">Nama Pengawas</td><td class="colon">:</td><td>{{ dataItem.namaPengawas || '-' }}</td></tr>
                                <tr><td class="label">Tanggal</td><td class="colon">:</td><td>{{ formatDateIndonesia(dataItem.tanggal) }}</td></tr>
                                <tr><td class="label">Jam</td><td class="colon">:</td><td>{{ dataItem.jam ? dataItem.jam.substring(0, 5) : '-' }}</td></tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </div>

            <!-- Main Table -->
            <table class="main-table">
                <thead>
                    <tr>
                        <th class="col-no">NO</th>
                        <th class="col-q">PERTANYAAN</th>
                        <th class="col-a">JAWABAN</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="col-no">1</td>
                        <td><span class="question-text">Pada Hari berapakah anda saat ini bekerja pada shift ini?</span></td>
                        <td class="answer-text">{{ dataItem.q1ShiftDay || '-' }}</td>
                    </tr>
                    <tr>
                        <td class="col-no">2</td>
                        <td><span class="question-text">Bagaimana kondisi tubuh anda saat ini?</span></td>
                        <td class="answer-text">{{ dataItem.q2BodyCondition || '-' }}</td>
                    </tr>
                    <tr>
                        <td class="col-no" rowspan="2">3</td>
                        <td><span class="question-text">Apakah yang anda rasakan (Yang melatarbelakangi anda) Sehingga meminta izin Fatigue?</span></td>
                        <td class="answer-text">{{ dataItem.q3Reason || '-' }}</td>
                    </tr>
                    <tr>
                        <td><div class="sub-question">Menurut anda kenapa bisa merasakan hal tersebut?</div></td>
                        <td class="answer-text">{{ dataItem.q3Background || '-' }}</td>
                    </tr>
                    <tr>
                        <td class="col-no">4</td>
                        <td><span class="question-text">Berapa Jam Anda Istirahat sebelum masuk kerja?</span></td>
                        <td class="answer-text">{{ dataItem.q4RestHours || '-' }}</td>
                    </tr>
                    <tr>
                        <td class="col-no" rowspan="2">5</td>
                        <td><span class="question-text">Bagaimana kualitas istirahat anda?</span></td>
                        <td class="answer-text">{{ dataItem.q5RestQuality || '-' }}</td>
                    </tr>
                    <tr>
                        <td><div class="sub-question">Jika kurang berkualitas, hal apa yang menyebabkan hal tersebut?</div></td>
                        <td class="answer-text">{{ dataItem.q5BadQualityReason || '-' }}</td>
                    </tr>
                    <tr>
                        <td class="col-no" rowspan="2">6</td>
                        <td><span class="question-text">Apakah lingkungan tempat tinggal anda nyaman untuk istirahat (Terutama saat istirahat tidur siang) Menjelang kerja shift malam?</span></td>
                        <td class="answer-text">{{ dataItem.q6HomeComfort || '-' }}</td>
                    </tr>
                    <tr>
                        <td><div class="sub-question">Apabila lingkungan tempat tinggal anda tidak nyaman apa yang menyebabkan hal tersebut?</div></td>
                        <td class="answer-text">{{ dataItem.q6DiscomfortReason || '-' }}</td>
                    </tr>
                    <tr>
                        <td class="col-no" rowspan="2">7</td>
                        <td><span class="question-text">Setelah anda pulang kerja, sampai rumah / mess apakah ada aktivitas lain selain istirahat / tidur?</span></td>
                        <td class="answer-text">{{ dataItem.q7OtherActivities || '-' }}</td>
                    </tr>
                    <tr>
                        <td><div class="sub-question">Apabila Melakukan Aktivitas lain, apa aktivitas yang anda lakukan?</div></td>
                        <td class="answer-text">{{ dataItem.q7ActivityDetails || '-' }}</td>
                    </tr>
                    <tr>
                        <td class="col-no">8</td>
                        <td><span class="question-text">Apakah pernah mendapat edukasi karyawan terkait fatigue? Kapan?</span></td>
                        <td class="answer-text">{{ dataItem.q8Education || '-' }}</td>
                    </tr>
                    <tr>
                        <td class="col-no">9</td>
                        <td><span class="question-text">Hari Keberapa biasanya anda merasakan fatigue berat?</span></td>
                        <td class="answer-text">{{ dataItem.q9HeavyFatigueDay || '-' }}</td>
                    </tr>
                    <tr>
                        <td class="col-no">10</td>
                        <td><span class="question-text">Hal Apakah yang akan anda lakukan agar bisa senantiasa fit/bugar saat mengoperasikan unit?</span></td>
                        <td class="answer-text">{{ dataItem.q10FitPlan || '-' }}</td>
                    </tr>
                    <tr>
                        <td class="col-no">11</td>
                        <td><span class="question-text">Apa saran anda untuk mengatasi fatigue?</span></td>
                        <td class="answer-text">{{ dataItem.q11Suggestions || '-' }}</td>
                    </tr>
                </tbody>
            </table>

            <!-- Signature -->
            <div class="signature-section">
                <div style="font-weight: bold; margin-bottom: 5px;">PENGAWAS</div>
                <div class="sig-box">
                    <img v-if="dataItem.ttdPengawas" :src="dataItem.ttdPengawas" alt="Signature">
                </div>
                <div class="sig-name" style="white-space: nowrap;">( {{ dataItem.namaPengawas || '...................................' }} )</div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.print-container {
    font-family: Arial, sans-serif;
    font-size: 9.5pt;
    color: #000;
    margin: 0;
    padding: 20px 0;
    background: #e2e8f0;
    min-height: 100vh;
}
.page {
    width: 210mm;
    min-height: 297mm;
    padding: 10mm;
    margin: auto;
    background: white;
    box-sizing: border-box;
    position: relative;
    box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);
}

/* Header Table */
.header-table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 12px;
}
.header-table td {
    border: 1px solid #000;
    padding: 5px;
    vertical-align: middle;
}
.logo-cell {
    width: 15%;
    text-align: center;
    border-right: 1px solid #000;
}
.logo-cell img {
    width: 40px;
    margin: 0 auto 2px;
    display: block;
}
.logo-text {
    font-size: 9pt;
    font-weight: bold;
    margin-top: 2px;
}
.title-cell {
    width: 50%;
    text-align: center;
    font-weight: bold;
    font-size: 13pt;
    line-height: 1.2;
}
.meta-cell {
    width: 35%;
    font-size: 8.5pt;
    padding: 0 !important;
}
.meta-cell table {
    width: 100%;
    border-collapse: collapse;
}
.meta-cell td {
    border: none;
    padding: 2px;
}

/* Identity Info */
.identity-info {
    width: 100%;
    margin-bottom: 8px;
    font-size: 8.5pt;
}
.identity-info table {
    width: 100%;
    border-collapse: collapse;
}
.identity-info td {
    padding: 2px 5px;
}
.label {
    width: 100px;
    font-weight: normal;
}
.colon {
    width: 20px;
    text-align: left;
}

/* Main Table */
.main-table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 20px;
    table-layout: fixed;
}
.main-table th,
.main-table td {
    border: 1px solid #000;
    padding: 8px 10px;
    vertical-align: top;
}
.main-table th {
    background-color: #ffff00 !important;
    font-weight: bold;
    text-align: center;
    font-size: 10pt;
    color: #000;
}
.col-no {
    width: 45px;
    text-align: center;
}
.col-q {
    width: 55%;
}
.col-a {
    width: calc(45% - 45px);
}

.question-text {
    font-weight: bold;
    margin-bottom: 4px;
    display: block;
    font-size: 9.5pt;
}
.sub-question {
    font-weight: normal;
    font-style: italic;
    font-size: 9pt;
    color: #333;
}
.answer-text {
    min-height: 25px;
    word-wrap: break-word;
    font-size: 10pt;
    line-height: 1.4;
    white-space: pre-wrap;
}

/* Signature */
.signature-section {
    margin-top: 10px;
    float: right;
    width: 250px;
    text-align: center;
}
.sig-box {
    height: 50px;
    position: relative;
    margin-top: 2px;
    display: flex;
    align-items: center;
    justify-content: center;
}
.sig-box img {
    max-height: 50px;
    max-width: 200px;
}
.sig-name {
    font-weight: bold;
    margin-top: 5px;
}

.no-print {
    position: fixed;
    top: 20px;
    right: 20px;
    z-index: 9999;
    display: flex;
    gap: 10px;
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

* {
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
}

@media print {
    .print-container {
        background: none;
        margin: 0;
        padding: 0;
    }
    .page {
        width: 100%;
        margin: 0;
        box-shadow: none;
        padding: 8mm;
    }
    .header-table th,
    .main-table th {
        background-color: #ffff00 !important;
    }
    .no-print {
        display: none !important;
    }
    @page {
        size: portrait;
        margin: 0;
    }
}
</style>
