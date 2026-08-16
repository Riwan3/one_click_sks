<script setup>
import { ref } from 'vue';

const props = defineProps({
    tanggal: { type: String, required: true },
    shiftLabel: { type: String, required: true },
    petugasOptions: { type: Array, required: true },
});

const petugas1 = ref(props.petugasOptions[0]);
const petugas2 = ref(props.petugasOptions[1]);

const hariIndo = new Date(props.tanggal).toLocaleDateString('id-ID', { weekday: 'long' });
</script>

<template>
    <div class="mb-5 overflow-hidden rounded-lg border border-slate-300">
        <table class="w-full border-collapse text-sm">
            <tbody>
                <tr class="border-b border-slate-300">
                    <td class="w-20 border-r border-slate-300 p-3 text-center">
                        <img src="/images/title.png" alt="Logo" class="mx-auto h-10 w-10 object-contain">
                    </td>
                    <td class="border-r border-slate-300 p-3 text-center">
                        <div class="text-base font-extrabold uppercase text-slate-800">Formulir Driving Monitoring System</div>
                        <div class="text-xs text-slate-500">PT Putra Perkasa Abadi</div>
                    </td>
                    <td class="w-52 p-3 text-xs text-slate-500">
                        <div class="flex justify-between"><span>No Dokumen</span><span class="font-semibold text-slate-700">FR/DMS/001</span></div>
                        <div class="flex justify-between"><span>Revisi</span><span class="font-semibold text-slate-700">00</span></div>
                        <div class="flex justify-between"><span>Tgl Efektif</span><span class="font-semibold text-slate-700">01 Jan 2026</span></div>
                        <div class="flex justify-between"><span>Halaman</span><span class="font-semibold text-slate-700">1 / 1</span></div>
                    </td>
                </tr>
            </tbody>
        </table>

        <table class="w-full border-collapse text-sm">
            <tbody>
                <tr class="border-t border-slate-300">
                    <td class="border-r border-slate-300 p-3">
                        <span class="text-slate-500">Hari / Tanggal:</span>
                        <span class="ml-1 font-semibold text-slate-800">{{ hariIndo }}, {{ tanggal }}</span>
                    </td>
                    <td class="border-r border-slate-300 p-3">
                        <span class="text-slate-500">Shift:</span>
                        <span class="ml-1 font-semibold text-slate-800">{{ shiftLabel }}</span>
                    </td>
                    <td class="border-r border-slate-300 p-3">
                        <span class="mr-1 text-slate-500">Petugas 1:</span>
                        <select v-model="petugas1" class="rounded border border-slate-200 px-1.5 py-1 text-sm outline-none">
                            <option v-for="name in petugasOptions" :key="name" :value="name">{{ name }}</option>
                        </select>
                    </td>
                    <td class="p-3">
                        <span class="mr-1 text-slate-500">Petugas 2:</span>
                        <select v-model="petugas2" class="rounded border border-slate-200 px-1.5 py-1 text-sm outline-none">
                            <option v-for="name in petugasOptions" :key="name" :value="name">{{ name }}</option>
                        </select>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>
