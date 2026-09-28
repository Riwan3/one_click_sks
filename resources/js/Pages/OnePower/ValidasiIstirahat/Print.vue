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
    <Head :title="`Print Validasi Istirahat - ${data.nrp || data.nama || ''}`" />

    <div class="print-root">
        <!-- Floating Actions -->
        <div class="no-print">
            <button class="btn btn-print" @click="handlePrint">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                    <path d="M5 1a2 2 0 0 0-2 2v2H2a2 2 0 0 0-2 2v3a2 2 0 0 0 2 2h1v1a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2v-1h1a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-1V3a2 2 0 0 0-2-2H5zM4 3a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2H4V3zm1 5a.5.5 0 0 0 0 1h6a.5.5 0 0 0 0-1H5zm0 2a.5.5 0 0 0 0 1h3a.5.5 0 0 0 0-1H5z"/>
                </svg>
                Cetak Sekarang
            </button>
            <button class="btn btn-close" @click="handleClose">TUTUP</button>
        </div>

        <div class="print-container">
            <!-- Header Table -->
            <table class="header-table">
                <tr>
                    <td class="logo-cell">
                        <img src="/images/images.png" alt="Logo PPA" class="logo-img">
                        <div class="logo-text">PPA</div>
                    </td>
                    <td class="title-cell">
                        FORMULIR VALIDASI ISTIRAHAT KARYAWAN
                        <div v-if="data.noSurat" class="no-surat-sub">No: {{ data.noSurat }}</div>
                    </td>
                    <td class="doc-info-cell">
                        <table class="doc-info-table">
                            <tr>
                                <td>No Dokumen</td>
                                <td>: {{ documentSettings.noDokumen || 'PPA-ADRO-F-SHE-29G' }}</td>
                            </tr>
                            <tr>
                                <td>Revisi</td>
                                <td>: {{ documentSettings.revisi || '0' }}</td>
                            </tr>
                            <tr>
                                <td>Tgl Efektif</td>
                                <td>: {{ formatDateIndonesia(documentSettings.tglEfektif || '2023-06-23') }}</td>
                            </tr>
                            <tr>
                                <td>Halaman</td>
                                <td>: 1 dari 1</td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>

            <!-- Identity Table -->
            <table class="identity-table">
                <tbody>
                    <tr>
                        <td class="label-col">Nama</td>
                        <td class="colon-col">:</td>
                        <td class="font-bold">{{ (data.nama || '-').toUpperCase() }}</td>
                        <td class="text-right">
                            <span class="text-muted">Tanggal: </span>
                            <strong>{{ formatDateIndonesia(data.tanggalValidasi) }}</strong>
                        </td>
                    </tr>
                    <tr>
                        <td class="label-col">Departemen</td>
                        <td class="colon-col">:</td>
                        <td>{{ data.departemen || '-' }}</td>
                        <td></td>
                    </tr>
                    <tr>
                        <td class="label-col">Jabatan</td>
                        <td class="colon-col">:</td>
                        <td>{{ data.jabatan || '-' }}</td>
                        <td></td>
                    </tr>
                    <tr>
                        <td class="label-col">NRP</td>
                        <td class="colon-col">:</td>
                        <td>{{ data.nrp || '-' }}</td>
                        <td></td>
                    </tr>
                </tbody>
            </table>

            <!-- Section Header -->
            <div class="section-header">PERTANYAAN &amp; JAWABAN</div>

            <!-- Content Table -->
            <table class="content-table">
                <tbody>
                    <tr>
                        <td>
                            <span class="question">1. Mengapa jam tidur anda &lt; 6 jam ?</span>
                            <div class="answer">{{ data.alasanKurangTidur || '-' }}</div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <span class="question">2. (Khusus tidur &lt; 6 Jam, berulang) Mengapa anda melakukan jam tidur &lt; 6 jam secara berulang ?</span>
                            <div class="answer">{{ data.alasanKurangTidurBerulang || '-' }}</div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <span class="question">3. Apakah anda mengalami kendala seperti gangguan tidur ? Jika iya, Jelaskan bagaimana kendala / gangguan tersebut !</span>
                            <div class="answer">{{ data.kendalaTidur || '-' }}</div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <span class="question">4. Apakah anda tinggal di mess atau non mess ?</span>
                            <div class="answer">
                                <span class="font-semibold">{{ data.tinggalDi || '-' }}</span>
                                <span v-if="data.tinggalDi === 'Mess' && data.namaMess"> &ndash; Nama Mess: {{ data.namaMess }}</span>
                                <span v-if="data.tinggalDi === 'Non Mess' && data.alamatNonMess"> &ndash; Alamat: {{ data.alamatNonMess }}</span>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <span class="question">5. Status Sekarang (Single / Menikah) :</span>
                            <div class="answer">{{ data.statusPerkawinan || '-' }}</div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <span class="question">6. Jumlah anak tinggal satu rumah ?</span>
                            <div class="answer">{{ data.jumlahAnakSerumah || '-' }}</div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <span class="question">7. Memiliki anak usia &lt; 2 tahun ? (Usia anak) :</span>
                            <div class="answer">{{ data.anakUsiaKurang2th || '-' }}</div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <span class="question">8. Apakah anda memiliki pekerjaan sampingan ? Sebutkan.</span>
                            <div class="answer">{{ data.pekerjaanSampingan || '-' }}</div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <span class="question">9. Keterangan :</span>
                            <div class="answer">{{ data.keterangan || '-' }}</div>
                        </td>
                    </tr>
                </tbody>
            </table>

            <!-- Signatures -->
            <div class="footer-section">
                <table class="footer-table">
                    <tr>
                        <td class="sig-block">
                            <div>Karyawan,</div>
                            <div class="sig-space">
                                <img v-if="data.ttdKaryawan" :src="data.ttdKaryawan" alt="TTD Karyawan">
                            </div>
                            <div class="sig-name">( {{ (data.nama || '........................................').toUpperCase() }} )</div>
                        </td>
                        <td class="sig-block">
                            <div>Pengawas,</div>
                            <div class="sig-space">
                                <img v-if="data.ttdPengawas" :src="data.ttdPengawas" alt="TTD Pengawas">
                            </div>
                            <div class="sig-name">( {{ (data.namaPengawas || '........................................').toUpperCase() }} )</div>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Lampiran Foto Dokumen jika ada -->
        <div v-if="data.foto" class="print-container" style="page-break-before: always; margin-top: 20px;">
            <div style="display: flex; align-items: center; justify-content: center; gap: 12px; margin-bottom: 20px;">
                <img src="/assets/images/images.png" alt="Logo PPA" style="height: 48px; width: auto;">
                <div style="font-size: 16pt; font-weight: bold; letter-spacing: 0.5px;">PUTRA PERKASA ABADI</div>
            </div>
            <div style="text-align: center; font-size: 13pt; font-weight: bold; margin-bottom: 4px;">LAMPIRAN DOKUMENTASI FOTO</div>
            <div style="text-align: center; font-size: 10pt; color: #444; margin-bottom: 20px;">Validasi Karyawan Istirahat: {{ data.noSurat || '-' }}</div>

            <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 10px;">
                <img :src="data.foto" alt="Foto Dokumentasi Validasi Istirahat" style="max-width: 95%; max-height: 620px; object-fit: contain; border: 1px solid #ccc; padding: 6px; background: #fff;" />
                <div style="font-size: 9pt; color: #666; margin-top: 10px;">
                    Dokumentasi Bukti: {{ data.nrp }} - {{ data.nama }} ({{ formatDateIndonesia(data.tanggalValidasi) }})
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
* {
    box-sizing: border-box;
}

@page {
    size: A4;
    margin: 15mm;
}

.print-root {
    font-family: Arial, sans-serif;
    font-size: 8.5pt;
    line-height: 1.25;
    color: #000;
    margin: 0;
    padding: 20px;
    background: #fff;
    min-height: 100vh;
}

.print-container {
    width: 175mm;
    margin: 0 auto;
}

.header-table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 5px;
    table-layout: fixed;
}

.header-table td {
    border: 1px solid #000;
    padding: 3px;
    vertical-align: middle;
    overflow: hidden;
}

.logo-cell {
    width: 50px;
    text-align: center;
}

.logo-img {
    width: 35px;
    height: auto;
    object-fit: contain;
}

.logo-text {
    font-size: 7pt;
    font-weight: bold;
}

.title-cell {
    text-align: center;
    font-weight: bold;
    font-size: 10.5pt;
}

.no-surat-sub {
    font-size: 8pt;
    font-weight: normal;
    margin-top: 2px;
}

.doc-info-cell {
    width: 210px;
    font-size: 7pt;
    padding: 0 !important;
}

.doc-info-table {
    width: 100%;
    border-collapse: collapse;
    height: 100%;
}

.doc-info-table td {
    border: 1px solid #000;
    padding: 2px 4px;
}

.doc-info-table tr:first-child td {
    border-top: none;
}

.doc-info-table tr:last-child td {
    border-bottom: none;
}

.doc-info-table td:first-child {
    border-left: none;
    width: 80px;
}

.doc-info-table td:last-child {
    border-right: none;
}

.identity-table {
    width: 100%;
    margin-bottom: 5px;
    font-size: 9pt;
}

.identity-table td {
    padding: 1.5px 0;
}

.label-col {
    width: 90px;
}

.colon-col {
    width: 12px;
}

.section-header {
    background-color: #BDD7EE;
    text-align: center;
    font-weight: bold;
    padding: 3px;
    border: 1px solid #000;
    text-transform: uppercase;
    font-size: 9pt;
}

.content-table {
    width: 100%;
    border-collapse: collapse;
    table-layout: fixed;
    margin-top: -1px;
}

.content-table td {
    border: 1px solid #000;
    padding: 4px 6px;
    vertical-align: top;
    word-wrap: break-word;
}

.question {
    font-weight: bold;
    margin-bottom: 2px;
    display: block;
    font-size: 8.5pt;
}

.answer {
    min-height: 18px;
    font-style: italic;
    white-space: pre-line;
}

.footer-section {
    margin-top: 10px;
    width: 100%;
}

.footer-table {
    width: 100%;
    text-align: center;
}

.sig-block {
    width: 50%;
    vertical-align: top;
}

.sig-space {
    height: 48px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.sig-space img {
    max-height: 44px;
    max-width: 160px;
    object-fit: contain;
}

.sig-name {
    font-weight: bold;
    font-size: 9pt;
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
        margin: 0;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }
    .print-root {
        padding: 0;
        background: transparent;
    }
    .print-container {
        width: 100%;
        margin: 0;
    }
    .no-print {
        display: none !important;
    }
}
</style>
