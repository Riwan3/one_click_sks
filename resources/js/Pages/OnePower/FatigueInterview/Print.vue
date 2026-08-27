<script setup>
import { Head } from '@inertiajs/vue3';

defineProps({
    item: { type: Object, required: true },
});

const questions = [
    ['Q1. Shift Hari Ini', 'q1ShiftDay'],
    ['Q2. Kondisi Badan', 'q2BodyCondition'],
    ['Q3. Alasan', 'q3Reason'],
    ['Q3. Latar Belakang', 'q3Background'],
    ['Q4. Jam Istirahat', 'q4RestHours'],
    ['Q5. Kualitas Istirahat', 'q5RestQuality'],
    ['Q5. Alasan Kualitas Buruk', 'q5BadQualityReason'],
    ['Q6. Kenyamanan Rumah', 'q6HomeComfort'],
    ['Q6. Alasan Ketidaknyamanan', 'q6DiscomfortReason'],
    ['Q7. Aktivitas Lain', 'q7OtherActivities'],
    ['Q7. Detail Aktivitas', 'q7ActivityDetails'],
    ['Q8. Edukasi', 'q8Education'],
    ['Q9. Hari Fatigue Berat', 'q9HeavyFatigueDay'],
    ['Q10. Rencana Fit', 'q10FitPlan'],
    ['Q11. Saran', 'q11Suggestions'],
];
</script>

<template>
    <Head :title="`Cetak ${item.noSurat || 'Interview Fatigue'} - OneClick`" />

    <div class="mx-auto max-w-2xl p-8 print:p-0">
        <div class="mb-6 flex justify-end print:hidden">
            <button type="button" class="rounded-lg bg-blue-500 px-4 py-2 text-sm font-bold text-white hover:bg-blue-600" @click="window.print()">Cetak</button>
        </div>

        <h1 class="mb-1 text-center text-lg font-extrabold uppercase text-slate-800">Interview Fatigue</h1>
        <p class="mb-6 text-center text-sm text-slate-500">No. {{ item.noSurat }}</p>

        <table class="mb-6 w-full text-sm">
            <tbody>
                <tr><td class="w-40 py-1 text-slate-500">Nama</td><td class="py-1 font-semibold text-slate-800">: {{ item.nama }}</td></tr>
                <tr><td class="py-1 text-slate-500">NRP</td><td class="py-1 font-semibold text-slate-800">: {{ item.nrp }}</td></tr>
                <tr><td class="py-1 text-slate-500">Nomor Unit</td><td class="py-1 font-semibold text-slate-800">: {{ item.nomorUnit || '-' }}</td></tr>
                <tr><td class="py-1 text-slate-500">Area</td><td class="py-1 font-semibold text-slate-800">: {{ item.area || '-' }}</td></tr>
                <tr><td class="py-1 text-slate-500">Tanggal / Jam</td><td class="py-1 font-semibold text-slate-800">: {{ item.tanggal }} {{ item.jam || '' }}</td></tr>
                <tr><td class="py-1 text-slate-500">Pengawas</td><td class="py-1 font-semibold text-slate-800">: {{ item.namaPengawas || '-' }}</td></tr>
            </tbody>
        </table>

        <div class="mb-6 space-y-3">
            <div v-for="[label, key] in questions" :key="key">
                <p class="mb-0.5 text-sm font-semibold text-slate-700">{{ label }}</p>
                <p class="text-sm text-slate-700">{{ item[key] || '-' }}</p>
            </div>
        </div>

        <div class="text-center text-sm">
            <p class="mb-2 text-slate-500">Tanda Tangan Pengawas</p>
            <img v-if="item.ttdPengawas" :src="item.ttdPengawas" alt="TTD Pengawas" class="mx-auto h-16">
            <p class="mt-2 inline-block border-t border-slate-300 pt-1 font-semibold text-slate-700">{{ item.namaPengawas || ' ' }}</p>
        </div>
    </div>
</template>
