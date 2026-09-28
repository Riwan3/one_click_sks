<script setup>
import { computed, ref } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    tanggal: { type: String, required: true },
    shift: { type: String, required: true },
    shiftLabel: { type: String, required: true },
    petugas: { type: Object, required: true },
    petugasOptions: { type: Array, required: true },
    docSettings: { type: Object, required: true },
});

const petugas1 = ref(props.petugas.petugas1 || '');
const petugas2 = ref(props.petugas.petugas2 || '');

const docNo = ref(props.docSettings.docNo);
const docRevisi = ref(props.docSettings.docRevisi);
const docTglEfektif = ref(props.docSettings.docTglEfektif);
const docHalaman = ref(props.docSettings.docHalaman);

const hariIndo = computed(() => new Date(props.tanggal).toLocaleDateString('id-ID', { weekday: 'long' }));

const saveDocSetting = (field, value) => {
    router.post('/one-dms/formulir/settings', { [field]: value }, { preserveScroll: true, preserveState: true });
};

const savePetugas = () => {
    router.post('/one-dms/save-petugas', {
        tanggal: props.tanggal,
        shift: props.shift,
        petugas1: petugas1.value,
        petugas2: petugas2.value,
    }, { preserveScroll: true, preserveState: true });
};
</script>

<template>
    <div class="mb-5">
        <!-- Header Document Box -->
        <table class="mb-6 w-full border-collapse border border-black text-sm">
            <tbody>
                <tr>
                    <td class="w-32 border border-black p-2 text-center">
                        <img src="/images/images.png" alt="Logo PPA" class="mx-auto h-16 w-16 object-contain">
                        <div class="mt-1 text-[11px] font-extrabold text-black">PPA</div>
                    </td>
                    <td class="border border-black p-4 text-center align-middle">
                        <div class="text-sm uppercase tracking-wide text-black">
                            <span class="font-extrabold">FORMULIR</span> <span class="italic font-semibold">DRIVING MONITORING SYSTEM</span>
                        </div>
                    </td>
                    <td class="w-64 border border-black p-0 align-top text-xs text-black">
                        <table class="w-full border-collapse">
                            <tbody>
                                <tr class="border-b border-black">
                                    <td class="p-1 pl-2">No Dokumen</td>
                                    <td class="w-2 text-center">:</td>
                                    <td class="p-1 pr-2">
                                        <input type="text" v-model="docNo" @blur="saveDocSetting('docNo', docNo)" class="w-full bg-transparent p-0 text-xs text-black border-none focus:ring-0 outline-none" />
                                    </td>
                                </tr>
                                <tr class="border-b border-black">
                                    <td class="p-1 pl-2">Revisi</td>
                                    <td class="w-2 text-center">:</td>
                                    <td class="p-1 pr-2">
                                        <input type="text" v-model="docRevisi" @blur="saveDocSetting('docRevisi', docRevisi)" class="w-full bg-transparent p-0 text-xs text-black border-none focus:ring-0 outline-none" />
                                    </td>
                                </tr>
                                <tr class="border-b border-black">
                                    <td class="p-1 pl-2">Tgl Efektif</td>
                                    <td class="w-2 text-center">:</td>
                                    <td class="p-1 pr-2">
                                        <input type="text" v-model="docTglEfektif" @blur="saveDocSetting('docTglEfektif', docTglEfektif)" class="w-full bg-transparent p-0 text-xs text-black border-none focus:ring-0 outline-none" />
                                    </td>
                                </tr>
                                <tr>
                                    <td class="p-1 pl-2">Halaman</td>
                                    <td class="w-2 text-center">:</td>
                                    <td class="p-1 pr-2">
                                        <input type="text" v-model="docHalaman" @blur="saveDocSetting('docHalaman', docHalaman)" class="w-full bg-transparent p-0 text-xs text-black border-none focus:ring-0 outline-none" />
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </td>
                </tr>
            </tbody>
        </table>

        <!-- Inputs outside the border box -->
        <div class="flex flex-col gap-2 text-xs font-semibold text-slate-800">
            <div class="grid grid-cols-[180px_auto] items-center gap-2">
                <div>Hari, Tanggal</div>
                <div>: {{ hariIndo }}, {{ tanggal }}</div>
            </div>
            <div class="grid grid-cols-[180px_auto] items-center gap-2">
                <div>Shift Kerja</div>
                <div>: {{ shiftLabel }}</div>
            </div>
            <div class="grid grid-cols-[180px_auto] items-center gap-2 mt-2">
                <div>Petugas 1 (07:00 - 12:00)</div>
                <div class="flex items-center gap-2">
                    <span>:</span>
                    <select v-model="petugas1" @change="savePetugas" class="w-48 rounded-lg border border-slate-200 bg-slate-50/50 px-3 py-2 outline-none">
                        <option value="">-- Pilih Petugas 1 --</option>
                        <option v-for="name in petugasOptions" :key="name" :value="name">{{ name }}</option>
                    </select>
                </div>
            </div>
            <div class="grid grid-cols-[180px_auto] items-center gap-2">
                <div>Petugas 2 (13:00 - 18:30)</div>
                <div class="flex items-center gap-2">
                    <span>:</span>
                    <select v-model="petugas2" @change="savePetugas" class="w-48 rounded-lg border border-slate-200 bg-slate-50/50 px-3 py-2 outline-none">
                        <option value="">-- Pilih Petugas 2 --</option>
                        <option v-for="name in petugasOptions" :key="name" :value="name">{{ name }}</option>
                    </select>
                </div>
            </div>
        </div>
    </div>
</template>
