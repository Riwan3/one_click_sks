<script setup>
import { computed } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import { Users, Settings } from 'lucide-vue-next';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    user: { type: Object, required: true },
    isAdmin: { type: Boolean, default: true },
    notifCounts: { type: Object, default: () => ({}) },
    menu: { type: Array, required: true },
    staff: { type: Array, default: () => [] },
    nonStaff: { type: Array, default: () => [] },
    settings: { type: Object, default: () => ({ mpp_target_1: '2.7', mpp_target_2: '2.8' }) },
});

const targetForm = useForm({
    target_1: props.settings?.mpp_target_1 ?? '2.7',
    target_2: props.settings?.mpp_target_2 ?? '2.8',
});

const submitTarget = () => {
    targetForm.post(route('one-power.mpp-departemen.update-target'), {
        preserveScroll: true,
    });
};

const t1 = computed(() => Number(targetForm.target_1) || 2.7);
const t2 = computed(() => Number(targetForm.target_2) || 2.8);

// Staff Totals
const totalStaffIndeks = computed(() => {
    return props.staff.reduce((acc, row) => acc + (Number(row.index_pengawas) || 0), 0);
});

const totalStaffActual = computed(() => {
    return props.staff.reduce((acc, row) => acc + (Number(row.actual_mp) || 0), 0);
});

// Non-Staff Calculations
const getMp1 = (row) => Math.round((Number(row.jumlah_unit) || 0) * t1.value);
const getMp2 = (row) => Math.round((Number(row.jumlah_unit) || 0) * t2.value);
const getDeviasi = (row) => (Number(row.actual_mp) || 0) - getMp2(row);
const getAch = (row) => {
    const mp2 = getMp2(row);
    const actual = Number(row.actual_mp) || 0;
    return mp2 > 0 ? Math.round((actual / mp2) * 100) : 0;
};

// Non-Staff Totals
const totalNonStaffUnits = computed(() => {
    return props.nonStaff.reduce((acc, row) => acc + (Number(row.jumlah_unit) || 0), 0);
});

const totalNonStaffMp1 = computed(() => {
    return props.nonStaff.reduce((acc, row) => acc + getMp1(row), 0);
});

const totalNonStaffActual = computed(() => {
    return props.nonStaff.reduce((acc, row) => acc + (Number(row.actual_mp) || 0), 0);
});

const totalNonStaffMp2 = computed(() => {
    return props.nonStaff.reduce((acc, row) => acc + getMp2(row), 0);
});

const totalNonStaffDeviasi = computed(() => {
    return totalNonStaffActual.value - totalNonStaffMp2.value;
});

const getDeviasiClass = (deviasi) => {
    if (deviasi > 0) return 'bg-[#00a0e9] text-white'; // Cyan/Blue
    if (deviasi < 0) return 'bg-[#ff0000] text-white'; // Red
    return 'bg-[#00c04b] text-white'; // Green
};
</script>

<template>
    <Head title="Analisis MPP - OneClick" />

    <AppLayout :user="user" :notif-counts="notifCounts" :menu="menu" active-module="one-power">
        <div class="space-y-6 pb-12">
            <!-- PAGE TITLE -->
            <div class="flex items-center gap-2">
                <Users class="h-5 w-5 text-blue-600" />
                <h1 class="text-sm sm:text-base font-extrabold uppercase tracking-wide text-blue-600">
                    ANALISIS MANPOWER PLANNING (MPP)
                </h1>
            </div>

            <!-- SETTINGS FORM (GLOBAL) -->
            <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                <div class="mb-3 flex items-center gap-1.5 text-[11px] font-bold uppercase tracking-wider text-blue-600">
                    <Settings class="h-3.5 w-3.5 text-blue-500" />
                    <span>PENGATURAN TARGET % MPP (GLOBAL)</span>
                </div>

                <form @submit.prevent="submitTarget" class="grid grid-cols-1 gap-4 sm:grid-cols-2 md:grid-cols-[1fr_1fr_auto] items-end">
                    <div>
                        <label class="mb-1 block text-xs font-semibold text-slate-700">Target 1 (%)</label>
                        <div class="relative">
                            <input
                                v-model="targetForm.target_1"
                                type="text"
                                placeholder="2.7"
                                class="w-full rounded-md border border-slate-300 py-1.5 pl-3 pr-8 text-sm text-slate-800 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
                            />
                            <span class="pointer-events-none absolute right-2.5 top-1/2 -translate-y-1/2 text-xs font-semibold text-slate-400">%</span>
                        </div>
                        <p v-if="targetForm.errors.target_1" class="mt-1 text-xs text-red-600">{{ targetForm.errors.target_1 }}</p>
                    </div>

                    <div>
                        <label class="mb-1 block text-xs font-semibold text-slate-700">Target 2 (%)</label>
                        <div class="relative">
                            <input
                                v-model="targetForm.target_2"
                                type="text"
                                placeholder="2.8"
                                class="w-full rounded-md border border-slate-300 py-1.5 pl-3 pr-8 text-sm text-slate-800 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
                            />
                            <span class="pointer-events-none absolute right-2.5 top-1/2 -translate-y-1/2 text-xs font-semibold text-slate-400">%</span>
                        </div>
                        <p v-if="targetForm.errors.target_2" class="mt-1 text-xs text-red-600">{{ targetForm.errors.target_2 }}</p>
                    </div>

                    <div>
                        <button
                            type="submit"
                            :disabled="targetForm.processing"
                            class="w-full sm:w-auto min-w-[130px] rounded-md bg-[#800000] px-6 py-2 text-xs font-extrabold uppercase tracking-wider text-white shadow hover:bg-[#660000] transition disabled:opacity-50"
                        >
                            SIMPAN
                        </button>
                    </div>
                </form>
            </div>

            <!-- TABEL 1: ANALISIS MPP STAFF (PENGAWAS) -->
            <div>
                <div class="mb-2">
                    <h2 class="text-xs sm:text-sm font-bold uppercase tracking-wider text-blue-600">
                        ANALISIS MPP STAFF (PENGAWAS)
                    </h2>
                </div>

                <div class="overflow-x-auto rounded-lg border border-slate-300 bg-white shadow-sm">
                    <table class="w-full border-collapse text-left text-xs sm:text-sm">
                        <thead class="bg-[#2f6cb3] text-white text-[11px] font-bold uppercase">
                            <tr>
                                <th class="border border-slate-300 px-3 py-2.5 text-center w-12">NO</th>
                                <th class="border border-slate-300 px-4 py-2.5 text-left">KATEGORI JABATAN</th>
                                <th class="border border-slate-300 px-4 py-2.5 text-center w-40">INDEKS PENGAWAS</th>
                                <th class="border border-slate-300 px-4 py-2.5 text-center w-40">MP AKTUAL</th>
                                <th class="border border-slate-300 px-4 py-2.5 text-center w-36">ACH (%)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="staff.length === 0">
                                <td colspan="5" class="border border-slate-300 px-4 py-6 text-center text-slate-400">
                                    Belum ada data Staff.
                                </td>
                            </tr>
                            <tr v-for="(row, index) in staff" :key="row.id" class="hover:bg-slate-50">
                                <td class="border border-slate-300 px-3 py-2 text-center text-slate-600">{{ index + 1 }}</td>
                                <td class="border border-slate-300 px-4 py-2 text-left font-bold text-slate-800">{{ row.name }}</td>
                                <td class="border border-slate-300 px-4 py-2 text-center text-slate-700">{{ row.index_pengawas }}</td>
                                <td class="border border-slate-300 px-4 py-2 text-center font-bold text-slate-800 bg-[#e8f0fe]">{{ row.actual_mp }}</td>
                                <td
                                    class="border border-slate-300 px-4 py-2 text-center font-semibold"
                                    :class="row.ach >= 100 ? 'bg-[#dcfce7] text-[#155724]' : 'bg-[#fee2e2] text-[#991b1b]'"
                                >
                                    {{ row.ach }}%
                                </td>
                            </tr>
                        </tbody>
                        <tfoot v-if="staff.length > 0" class="bg-slate-100 font-bold text-slate-800 text-xs">
                            <tr>
                                <td colspan="2" class="border border-slate-300 px-4 py-2 text-center">TOTAL STAFF PRODUKSI</td>
                                <td class="border border-slate-300 px-4 py-2 text-center">{{ totalStaffIndeks }}</td>
                                <td class="border border-slate-300 px-4 py-2 text-center bg-[#e8f0fe]">{{ totalStaffActual }}</td>
                                <td class="border border-slate-300 px-4 py-2 text-center"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <!-- TABEL 2: ANALISIS MPP NON-STAFF (OPERATOR/UNIT) -->
            <div>
                <div class="mb-2 flex items-center justify-between">
                    <h2 class="text-xs sm:text-sm font-bold uppercase tracking-wider text-blue-600">
                        ANALISIS MPP NON-STAFF (OPERATOR/UNIT)
                    </h2>
                    <span class="rounded-full bg-[#10b981] px-3 py-0.5 text-[11px] font-bold text-white shadow-sm">
                        Target Aktif: {{ t2 }}%
                    </span>
                </div>

                <div class="overflow-x-auto rounded-lg border border-slate-300 bg-white shadow-sm">
                    <table class="w-full border-collapse text-left text-xs sm:text-sm">
                        <thead class="bg-[#2f6cb3] text-white text-[11px] font-bold uppercase">
                            <tr>
                                <th class="border border-slate-300 px-2 py-2.5 text-center w-12">NO</th>
                                <th class="border border-slate-300 px-3 py-2.5 text-left">TYPE UNIT / KATEGORI</th>
                                <th class="border border-slate-300 px-2 py-2.5 text-center w-24">JML UNIT</th>
                                <th class="border border-slate-300 px-2 py-2.5 text-center w-24">MP {{ t1 }} %</th>
                                <th class="border border-slate-300 px-2 py-2.5 text-center w-28">MP AKTUAL</th>
                                <th class="border border-slate-300 px-2 py-2.5 text-center w-24">MP {{ t2 }} %</th>
                                <th class="border border-slate-300 px-2 py-2.5 text-center w-24">DEVIASI</th>
                                <th class="border border-slate-300 px-2 py-2.5 text-center w-24">ACH (%)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="nonStaff.length === 0">
                                <td colspan="8" class="border border-slate-300 px-4 py-6 text-center text-slate-400">
                                    Belum ada data Unit Non-Staff.
                                </td>
                            </tr>
                            <tr v-for="(row, index) in nonStaff" :key="row.id" class="hover:bg-slate-50">
                                <td class="border border-slate-300 px-2 py-2 text-center text-slate-600">{{ index + 1 }}</td>
                                <td class="border border-slate-300 px-3 py-2 text-left font-bold text-slate-800">{{ row.name }}</td>
                                <td class="border border-slate-300 px-2 py-2 text-center text-slate-700">{{ row.jumlah_unit }}</td>
                                <td class="border border-slate-300 px-2 py-2 text-center text-slate-700">{{ getMp1(row) }}</td>
                                <td class="border border-slate-300 px-2 py-2 text-center font-bold text-slate-800 bg-[#e8f0fe]">{{ row.actual_mp }}</td>
                                <td class="border border-slate-300 px-2 py-2 text-center text-slate-700">{{ getMp2(row) }}</td>
                                <td
                                    class="border border-slate-300 px-2 py-2 text-center font-bold text-xs"
                                    :class="getDeviasiClass(getDeviasi(row))"
                                >
                                    {{ getDeviasi(row) }}
                                </td>
                                <td
                                    class="border border-slate-300 px-2 py-2 text-center font-semibold"
                                    :class="getAch(row) >= 100 ? 'bg-[#dcfce7] text-[#155724]' : 'bg-[#fee2e2] text-[#991b1b]'"
                                >
                                    {{ getAch(row) }}%
                                </td>
                            </tr>
                        </tbody>
                        <tfoot v-if="nonStaff.length > 0" class="bg-slate-100 font-bold text-slate-800 text-xs">
                            <tr>
                                <td colspan="2" class="border border-slate-300 px-3 py-2 text-center">TOTAL NON-STAFF</td>
                                <td class="border border-slate-300 px-2 py-2 text-center">{{ totalNonStaffUnits }}</td>
                                <td class="border border-slate-300 px-2 py-2 text-center">{{ totalNonStaffMp1 }}</td>
                                <td class="border border-slate-300 px-2 py-2 text-center bg-[#e8f0fe]">{{ totalNonStaffActual }}</td>
                                <td class="border border-slate-300 px-2 py-2 text-center">{{ totalNonStaffMp2 }}</td>
                                <td
                                    class="border border-slate-300 px-2 py-2 text-center font-bold text-xs"
                                    :class="getDeviasiClass(totalNonStaffDeviasi)"
                                >
                                    {{ totalNonStaffDeviasi }}
                                </td>
                                <td class="border border-slate-300 px-2 py-2 text-center"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
