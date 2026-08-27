<script setup>
import { ref, onMounted } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import { Printer, ArrowLeft, Calendar, User, Phone, Mail, Award, CheckCircle, Clock, FileText } from 'lucide-vue-next';

const props = defineProps({
    user: { type: Object, required: true },
    employee: { type: Object, required: true },
    tahun: { type: Number, required: true },
    monthlyAtr: { type: Array, required: true },
    monthlyHm: { type: Array, required: true },
    spHistory: { type: Array, required: true },
    teguranHistory: { type: Array, required: true },
    coachingHistory: { type: Array, required: true },
});

const selectedYear = ref(props.tahun);

const changeYear = () => {
    router.get(`/one-power/employees/${props.employee.id}/print`, { tahun: selectedYear.value }, { preserveScroll: true });
};

const triggerPrint = () => {
    window.print();
};

onMounted(() => {
    // Optionally auto print if query param auto_print=1
    const params = new URLSearchParams(window.location.search);
    if (params.get('auto_print') === '1') {
        setTimeout(() => window.print(), 500);
    }
});
</script>

<template>
    <Head :title="`Print Profile - ${employee.name} (${employee.nrp})`" />

    <div class="min-h-screen bg-slate-100 p-4 print:bg-white print:p-0">
        <!-- Top Toolbar (Hidden when printing) -->
        <div class="mx-auto mb-6 flex max-w-4xl items-center justify-between rounded-xl border border-slate-200 bg-white p-4 shadow-sm print:hidden">
            <div class="flex items-center gap-3">
                <button type="button" class="rounded-lg border border-slate-200 p-2 text-slate-600 hover:bg-slate-50 transition" @click="window.history.back()">
                    <ArrowLeft class="h-5 w-5" />
                </button>
                <div>
                    <h1 class="text-base font-bold text-slate-800">Cetak Data Diri Karyawan</h1>
                    <p class="text-xs text-slate-500">Pratinjau cetak A4 profil {{ employee.name }} ({{ employee.nrp }})</p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <div class="flex items-center gap-1.5">
                    <span class="text-xs font-semibold text-slate-500">Tahun</span>
                    <select v-model="selectedYear" class="rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-bold outline-none focus:border-indigo-500" @change="changeYear">
                        <option v-for="y in [2026, 2025, 2024, 2023]" :key="y" :value="y">{{ y }}</option>
                    </select>
                </div>
                <button type="button" class="flex items-center gap-1.5 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-bold text-white shadow-sm hover:bg-indigo-700 transition" @click="triggerPrint">
                    <Printer class="h-4 w-4" /> Cetak Halaman
                </button>
            </div>
        </div>

        <!-- Printable Document Canvas -->
        <div class="mx-auto max-w-4xl rounded-2xl bg-white p-8 shadow-md print:max-w-none print:rounded-none print:p-0 print:shadow-none font-sans text-slate-800">
            <!-- Header Banner -->
            <div class="border-b-2 border-slate-800 pb-4 mb-6 flex justify-between items-start">
                <div>
                    <h2 class="text-2xl font-black tracking-tight text-slate-900 uppercase">PT PUTRA PERKASA ABADI</h2>
                    <h3 class="text-sm font-bold text-slate-600 uppercase tracking-widest">ONECLICK OPERATIONAL SYSTEM — DATA DIRI KARYAWAN</h3>
                </div>
                <div class="text-right">
                    <div class="text-xs font-bold text-slate-500">TANGGAL CETAK</div>
                    <div class="text-sm font-mono font-bold text-slate-800">{{ new Date().toLocaleDateString('id-ID', { day: '2-digit', month: 'long', year: 'numeric' }) }}</div>
                </div>
            </div>

            <!-- Profile Summary Section -->
            <div class="grid grid-cols-4 gap-6 mb-6 pb-6 border-b border-slate-200">
                <div class="col-span-1">
                    <div class="aspect-[3/4] rounded-xl border-2 border-slate-200 bg-slate-100 flex items-center justify-center overflow-hidden">
                        <User class="h-20 w-20 text-slate-300" />
                    </div>
                </div>
                <div class="col-span-3 grid grid-cols-2 gap-y-3 text-sm">
                    <div>
                        <div class="text-[11px] font-bold uppercase text-slate-400">Nama Lengkap</div>
                        <div class="font-extrabold text-base text-slate-900">{{ employee.name }}</div>
                    </div>
                    <div>
                        <div class="text-[11px] font-bold uppercase text-slate-400">NRP Karyawan</div>
                        <div class="font-mono font-extrabold text-base text-indigo-600">{{ employee.nrp }}</div>
                    </div>

                    <div>
                        <div class="text-[11px] font-bold uppercase text-slate-400">Jabatan SS6</div>
                        <div class="font-semibold text-slate-800">{{ employee.jabatanSs6 || '-' }}</div>
                    </div>
                    <div>
                        <div class="text-[11px] font-bold uppercase text-slate-400">Jabatan Roster</div>
                        <div class="font-semibold text-slate-800">{{ employee.jabatanRoster || '-' }}</div>
                    </div>

                    <div>
                        <div class="text-[11px] font-bold uppercase text-slate-400">Status Karyawan</div>
                        <div class="font-bold text-emerald-700">{{ employee.statusKaryawan }}</div>
                    </div>
                    <div>
                        <div class="text-[11px] font-bold uppercase text-slate-400">Masa Kerja / Tenure</div>
                        <div class="font-semibold text-slate-800">{{ employee.tenure || '-' }}</div>
                    </div>

                    <div>
                        <div class="text-[11px] font-bold uppercase text-slate-400">Lokasi Kerja / Unit</div>
                        <div class="font-semibold text-slate-800">{{ employee.lokasiKerja || '-' }} ({{ employee.unit || '-' }})</div>
                    </div>
                    <div>
                        <div class="text-[11px] font-bold uppercase text-slate-400">Group Leader (Bapak Asuh)</div>
                        <div class="font-semibold text-slate-800">{{ employee.groupLeaderName || '-' }}</div>
                    </div>

                    <div>
                        <div class="text-[11px] font-bold uppercase text-slate-400">No. Telepon / HP</div>
                        <div class="font-mono text-slate-800">{{ employee.noTelepon || '-' }}</div>
                    </div>
                    <div>
                        <div class="text-[11px] font-bold uppercase text-slate-400">Email</div>
                        <div class="font-mono text-slate-800">{{ employee.email || '-' }}</div>
                    </div>
                </div>
            </div>

            <!-- Attendance ATR Monthly Breakdown Table -->
            <div class="mb-6">
                <h4 class="text-xs font-black uppercase tracking-wider text-slate-800 mb-2 flex items-center gap-1.5">
                    <CheckCircle class="h-4 w-4 text-emerald-600" />
                    Rangkuman Attendance ATR Tahun {{ tahun }}
                </h4>
                <table class="w-full text-xs border-collapse border border-slate-300 text-center">
                    <thead class="bg-slate-100 font-bold text-slate-700 uppercase">
                        <tr>
                            <th class="border border-slate-300 p-1.5">Bulan</th>
                            <th class="border border-slate-300 p-1.5 bg-amber-50 text-amber-800">D (Siang)</th>
                            <th class="border border-slate-300 p-1.5 bg-indigo-50 text-indigo-800">N (Malam)</th>
                            <th class="border border-slate-300 p-1.5 bg-blue-50 text-blue-800">S (Sakit)</th>
                            <th class="border border-slate-300 p-1.5 bg-purple-50 text-purple-800">I (Izin)</th>
                            <th class="border border-slate-300 p-1.5 bg-rose-50 text-rose-800">A (Alpa)</th>
                            <th class="border border-slate-300 p-1.5">Total Hari</th>
                            <th class="border border-slate-300 p-1.5 bg-emerald-50 text-emerald-800">ATR %</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="m in monthlyAtr" :key="m.bulan" class="hover:bg-slate-50">
                            <td class="border border-slate-300 p-1.5 font-bold text-left px-3">{{ m.namaBulan }}</td>
                            <td class="border border-slate-300 p-1.5 font-semibold">{{ m.d }}</td>
                            <td class="border border-slate-300 p-1.5 font-semibold">{{ m.n }}</td>
                            <td class="border border-slate-300 p-1.5 font-semibold">{{ m.s }}</td>
                            <td class="border border-slate-300 p-1.5 font-semibold">{{ m.i }}</td>
                            <td class="border border-slate-300 p-1.5 font-semibold text-rose-600">{{ m.a }}</td>
                            <td class="border border-slate-300 p-1.5 font-bold">{{ m.total }}</td>
                            <td class="border border-slate-300 p-1.5 font-extrabold text-emerald-700">{{ m.atr }}%</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Hour Meter (HM) Breakdown Table -->
            <div class="mb-6">
                <h4 class="text-xs font-black uppercase tracking-wider text-slate-800 mb-2 flex items-center gap-1.5">
                    <Clock class="h-4 w-4 text-blue-600" />
                    Rangkuman Hour Meter (HM) Alat Tahun {{ tahun }}
                </h4>
                <div class="grid grid-cols-6 gap-2 text-center text-xs">
                    <div v-for="h in monthlyHm" :key="h.bulan" class="rounded-lg border border-slate-200 bg-slate-50 p-2">
                        <div class="text-[10px] font-bold text-slate-500 uppercase">{{ h.namaBulan }}</div>
                        <div class="text-sm font-extrabold text-blue-600 mt-0.5">{{ h.totalHm }} HM</div>
                        <div class="text-[10px] text-slate-400">{{ h.totalShift }} Shift</div>
                    </div>
                </div>
            </div>

            <!-- Warning & Coaching History -->
            <div class="grid grid-cols-2 gap-6">
                <div>
                    <h4 class="text-xs font-black uppercase tracking-wider text-slate-800 mb-2 flex items-center gap-1.5">
                        <FileText class="h-4 w-4 text-rose-600" />
                        Riwayat Surat Peringatan / Teguran
                    </h4>
                    <div class="rounded-lg border border-slate-200 p-3 text-xs min-h-[80px]">
                        <div v-if="spHistory.length === 0 && teguranHistory.length === 0" class="text-slate-400 italic">
                            Tidak ada data Surat Peringatan atau Teguran.
                        </div>
                        <div v-else class="space-y-1.5">
                            <div v-for="sp in spHistory" :key="sp.id" class="flex justify-between font-medium text-rose-700">
                                <span>SP: {{ sp.alasan || sp.jenis_sp }}</span>
                                <span class="font-mono text-[11px] text-slate-500">{{ sp.tanggal }}</span>
                            </div>
                            <div v-for="t in teguranHistory" :key="t.id" class="flex justify-between font-medium text-amber-700">
                                <span>Teguran: {{ t.alasan }}</span>
                                <span class="font-mono text-[11px] text-slate-500">{{ t.tanggal }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div>
                    <h4 class="text-xs font-black uppercase tracking-wider text-slate-800 mb-2 flex items-center gap-1.5">
                        <Award class="h-4 w-4 text-purple-600" />
                        Riwayat Coaching Clinic
                    </h4>
                    <div class="rounded-lg border border-slate-200 p-3 text-xs min-h-[80px]">
                        <div v-if="coachingHistory.length === 0" class="text-slate-400 italic">
                            Tidak ada riwayat Coaching Clinic.
                        </div>
                        <div v-else class="space-y-1.5">
                            <div v-for="c in coachingHistory" :key="c.id" class="flex justify-between font-medium text-purple-700">
                                <span>{{ c.materi || 'Coaching' }}</span>
                                <span class="font-mono text-[11px] text-slate-500">{{ c.tanggal }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Signatures Footer -->
            <div class="mt-12 pt-6 border-t border-slate-300 grid grid-cols-2 text-center text-xs text-slate-600">
                <div>
                    <div class="mb-12">Mengetahui,</div>
                    <div class="font-bold text-slate-900 text-sm uppercase">{{ employee.groupLeaderName || '( Group Leader )' }}</div>
                    <div class="text-[11px] text-slate-400">Group Leader / Atasan</div>
                </div>
                <div>
                    <div class="mb-12">Dicetak oleh,</div>
                    <div class="font-bold text-slate-900 text-sm uppercase">{{ user.name || user.username }}</div>
                    <div class="text-[11px] text-slate-400">Departemen Manpower</div>
                </div>
            </div>
        </div>
    </div>
</template>

<style>
@media print {
    @page {
        size: A4;
        margin: 10mm;
    }
    body {
        background: white !important;
    }
}
</style>
