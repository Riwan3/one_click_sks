<script setup>
import { computed, onMounted } from 'vue';
import { Head } from '@inertiajs/vue3';

const props = defineProps({
    item: { type: Object, required: true },
});

const printItem = computed(() => props.item?.data ?? props.item ?? {});

const materiLabel = (value) => (value == 1 || value === '1' || value === true || value === 'true' ? 't' : 'n');

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
    <Head :title="`${printItem.nomorSurat || 'Coaching Counseling'} - OneClick`" />

    <div class="print-result-body">
        <div class="no-print" style="position: fixed; top: 20px; right: 20px; z-index: 9999; display: flex; gap: 10px;">
            <button class="print-btn" type="button" @click="handlePrint">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                    <path d="M5 1a2 2 0 0 0-2 2v2H2a2 2 0 0 0-2 2v3a2 2 0 0 0 2 2h1v1a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2v-1h1a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-1V3a2 2 0 0 0-2-2H5zM4 3a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2H4V3zm1 5a.5.5 0 0 0 0 1h6a.5.5 0 0 0 0-1H5zm0 2a.5.5 0 0 0 0 1h3a.5.5 0 0 0 0-1H5z"/>
                </svg>
                Cetak Sekarang
            </button>
            <button class="btn-tutup" type="button" @click="handleClose">TUTUP</button>
        </div>

        <div class="header">
            Coaching conseling
        </div>

        <div class="content">
            <div class="details">
                <div class="doc-number">
                    {{ printItem.nomorSurat }}
                </div>

                <table class="info-table">
                    <tbody>
                        <tr><td>Created By</td><td>:</td><td>{{ printItem.coachNama }}</td></tr>
                        <tr><td>Tanggal</td><td>:</td><td>{{ printItem.tanggal }}</td></tr>
                        <tr><td>NRP</td><td>:</td><td>{{ printItem.nrp }}</td></tr>
                        <tr><td>Nama</td><td>:</td><td>{{ printItem.nama }}</td></tr>
                        <tr><td>Jabatan</td><td>:</td><td>{{ printItem.jabatan || '-' }}</td></tr>
                        <tr><td>Dept</td><td>:</td><td>PRO</td></tr>
                        <tr><td>Lokasi</td><td>:</td><td>{{ printItem.lokasi || '-' }}</td></tr>
                        <tr><td>Waktu pertemuan</td><td>:</td><td>{{ printItem.waktuPertemuan || '-' }}</td></tr>
                        <tr><td>Materi Pribadi</td><td>:</td><td>{{ materiLabel(printItem.materiPribadi) }}</td></tr>
                        <tr><td>Materi Keluarga</td><td>:</td><td>{{ materiLabel(printItem.materiKeluarga) }}</td></tr>
                        <tr><td>Materi Pekerjaan</td><td>:</td><td>{{ materiLabel(printItem.materiPekerjaan) }}</td></tr>
                        <tr><td>Keterangan</td><td>:</td><td style="white-space: pre-line;">{{ printItem.deskripsi }}</td></tr>
                    </tbody>
                </table>
            </div>

            <div class="photos">
                <template v-if="printItem.fileSlip || printItem.fileBukti">
                    <div v-if="printItem.fileSlip" class="photo-box">
                        <img :src="printItem.fileSlip" alt="Slip Coaching">
                    </div>
                    <div v-if="printItem.fileBukti" class="photo-box">
                        <img :src="printItem.fileBukti" alt="Bukti Coaching">
                    </div>
                </template>
                <div v-else class="photo-box" style="height: 300px; display: flex; align-items: center; justify-content: center;">
                    <p style="color: #94a3b8;">Tidak ada foto yang diupload.</p>
                </div>
            </div>
        </div>

        <div class="footer">
            <span>www.ppa.co.id</span>
            <span>pt.ppa.official</span>
            <img src="/images/images.png" class="logo-white" alt="PPA Logo">
        </div>
    </div>
</template>

<style scoped>
@page {
    size: landscape;
    margin: 0;
}

.print-result-body {
    font-family: 'Arial', sans-serif;
    margin: 0;
    padding: 0;
    background-color: #fff;
    color: #333;
    min-height: 100vh;
    position: relative;
    padding-bottom: 70px;
}

.header {
    background-color: #a0a0a0;
    color: white;
    padding: 15px 40px;
    font-size: 24pt;
    font-weight: bold;
}

.content {
    padding: 40px;
    display: flex;
    gap: 40px;
}

.details {
    flex: 1;
}

.doc-number {
    font-weight: bold;
    margin-bottom: 30px;
    font-size: 14pt;
}

.info-table {
    width: 100%;
    border-collapse: collapse;
}

.info-table td {
    padding: 8px 0;
    vertical-align: top;
    font-size: 11pt;
}

.info-table td:first-child {
    width: 180px;
    font-weight: bold;
}

.info-table td:nth-child(2) {
    width: 20px;
}

.photos {
    flex: 2;
    display: flex;
    flex-direction: row;
    gap: 15px;
    align-items: flex-start;
}

.photo-box {
    flex: 1;
    border: 1px solid #ddd;
    padding: 5px;
    background: #f9f9f9;
    text-align: center;
}

.photo-box img {
    max-width: 100%;
    max-height: 450px;
    display: block;
    margin: 0 auto;
    object-fit: contain;
}

.footer {
    position: fixed;
    bottom: 0;
    width: 100%;
    background-color: #e31e24;
    color: white;
    padding: 10px 0;
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 30px;
    font-weight: bold;
    height: 50px;
    z-index: 10;
}

.footer img.logo-white {
    position: absolute;
    right: 40px;
    bottom: 10px;
    height: 70px;
    width: 70px;
    object-fit: contain;
    background: white;
    border-radius: 50%;
    padding: 5px;
    border: 4px solid #e31e24;
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
    .header {
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
        color-adjust: exact;
    }
    .footer {
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
        color-adjust: exact;
        background-color: #e31e24 !important;
    }
}
</style>
