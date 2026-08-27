<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft, CheckCircle2, Circle, UserRound, Phone, MapPin, Calendar, Award, Wrench, Table, FileText, UserCheck } from 'lucide-vue-next';
import TopNav from './Partials/TopNav.vue';
import BottomNav from './Partials/BottomNav.vue';

defineProps({
    user: { type: Object, required: true },
    notifCounts: { type: Object, required: true },
    profile: { type: Object, required: true },
    navItems: { type: Array, required: true },
    accentClass: { type: String, default: 'text-blue-500' },
});

const isEmptyValue = (value) => !value || value === '-' || value === 'BELUM ADA';
</script>

<template>
    <Head title="Profil - OneClick" />

    <div class="min-h-screen bg-slate-100 pb-24 pt-[84px]">
        <TopNav :user="user" :notif-counts="notifCounts" />

        <div class="mx-auto max-w-md md:max-w-4xl lg:max-w-5xl px-4 space-y-4">
            <Link href="/portal" class="inline-flex h-9 w-9 items-center justify-center rounded-full bg-white shadow-xs hover:bg-slate-50 transition">
                <ArrowLeft class="h-4 w-4 text-slate-700" />
            </Link>

            <div class="overflow-hidden rounded-2xl bg-white shadow-sm border border-slate-200/80">
                <!-- COVER BANNER -->
                <div class="relative h-28 md:h-36 bg-gradient-to-r from-blue-600 via-blue-500 to-indigo-600">
                    <div class="absolute inset-x-0 -bottom-10 md:-bottom-12 flex justify-center md:justify-start md:pl-8">
                        <div class="flex h-20 w-20 md:h-24 md:w-24 items-center justify-center rounded-3xl bg-white shadow-md border-4 border-white">
                            <UserRound class="h-10 w-10 text-blue-600" />
                        </div>
                    </div>
                </div>

                <!-- PROFILE HEADER INFO -->
                <div class="px-5 pb-5 pt-12 md:pt-14 md:px-8 flex flex-col md:flex-row md:items-center md:justify-between border-b border-slate-100 gap-4">
                    <div class="text-center md:text-left">
                        <h1 class="m-0 text-xl md:text-2xl font-black uppercase text-slate-800 tracking-tight">{{ user.displayName }}</h1>
                        <p class="m-0 mt-1 text-xs md:text-sm font-bold uppercase tracking-wide text-slate-500">
                            {{ profile.jabatanLengkap }}
                        </p>
                    </div>

                    <div class="flex justify-center md:justify-end">
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-500 px-4 py-1.5 text-xs font-bold uppercase text-white shadow-xs">
                            <CheckCircle2 class="h-4 w-4" /> {{ profile.statusSp }}
                        </span>
                    </div>
                </div>

                <!-- GRID DETAIL INFORMASI KARYAWAN -->
                <div class="p-5 md:p-8 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 text-sm">
                    
                    <!-- KELOMPOK 1: IDENTITAS & AREA -->
                    <div class="space-y-4 bg-slate-50/70 p-4 rounded-xl border border-slate-100">
                        <h3 class="text-xs font-extrabold uppercase text-blue-600 flex items-center gap-1.5">
                            <UserCheck class="w-4 h-4" /> Identitas &amp; Area Kerja
                        </h3>

                        <div class="space-y-3 text-xs">
                            <div>
                                <p class="text-[0.65rem] font-bold uppercase tracking-wide text-slate-400">NRP</p>
                                <p class="font-bold text-slate-800 flex items-center gap-1.5 mt-0.5">
                                    <Circle class="h-2 w-2 fill-emerald-500 text-emerald-500" /> {{ profile.nrp }}
                                </p>
                            </div>
                            <div>
                                <p class="text-[0.65rem] font-bold uppercase tracking-wide text-slate-400">Area Kerja</p>
                                <p class="font-bold text-slate-800 mt-0.5">{{ profile.unit }}</p>
                            </div>
                            <div>
                                <p class="text-[0.65rem] font-bold uppercase tracking-wide text-slate-400">Lokasi Kerja</p>
                                <p class="font-bold text-slate-800 flex items-center gap-1 mt-0.5">
                                    <MapPin class="w-3.5 h-3.5 text-slate-400" /> {{ profile.lokasi }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- KELOMPOK 2: ATASAN & KONTAK -->
                    <div class="space-y-4 bg-slate-50/70 p-4 rounded-xl border border-slate-100">
                        <h3 class="text-xs font-extrabold uppercase text-blue-600 flex items-center gap-1.5">
                            <Phone class="w-4 h-4" /> Atasan &amp; Jabatan
                        </h3>

                        <div class="space-y-3 text-xs">
                            <div>
                                <p class="text-[0.65rem] font-bold uppercase tracking-wide text-slate-400">Atasan</p>
                                <p :class="['font-bold mt-0.5', isEmptyValue(profile.atasan) ? 'text-rose-600' : 'text-slate-800']">
                                    {{ profile.atasan }}
                                </p>
                            </div>
                            <div>
                                <p class="text-[0.65rem] font-bold uppercase tracking-wide text-slate-400">Telepon</p>
                                <p class="font-bold text-slate-800 mt-0.5">{{ profile.telepon || '-' }}</p>
                            </div>
                            <div>
                                <p class="text-[0.65rem] font-bold uppercase tracking-wide text-slate-400">Jabatan</p>
                                <p class="font-bold text-slate-800 mt-0.5">{{ profile.jabatanLengkap }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- KELOMPOK 3: STATUS & MASA KERJA -->
                    <div class="space-y-4 bg-slate-50/70 p-4 rounded-xl border border-slate-100 md:col-span-2 lg:col-span-1">
                        <h3 class="text-xs font-extrabold uppercase text-blue-600 flex items-center gap-1.5">
                            <Calendar class="w-4 h-4" /> Status &amp; Masa Kerja
                        </h3>

                        <div class="space-y-3 text-xs">
                            <div>
                                <p class="text-[0.65rem] font-bold uppercase tracking-wide text-slate-400">Status</p>
                                <p class="font-bold text-emerald-600 mt-0.5">{{ profile.status }}</p>
                            </div>
                            <div>
                                <p class="text-[0.65rem] font-bold uppercase tracking-wide text-slate-400">Mulai Bekerja</p>
                                <p class="font-bold text-slate-800 mt-0.5">{{ profile.mulaiBekerja }}</p>
                            </div>
                            <div>
                                <p class="text-[0.65rem] font-bold uppercase tracking-wide text-slate-400">Masa Kerja</p>
                                <p class="font-bold text-slate-800 mt-0.5">{{ profile.masaKerja }}</p>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- ATR BADGES & CATATAN -->
                <div class="p-5 md:p-8 border-t border-slate-100 grid grid-cols-1 md:grid-cols-2 gap-6 bg-slate-50/30">
                    <div>
                        <p class="mb-3 text-xs font-extrabold uppercase text-blue-600 flex items-center gap-1.5">
                            <Award class="w-4 h-4" /> ATR PER BULAN {{ profile.atrYear }}
                        </p>
                        <div class="flex flex-wrap gap-2">
                            <div
                                v-for="item in profile.atrPerBulan"
                                :key="item.label"
                                class="flex flex-col items-center justify-center rounded-xl bg-emerald-500 text-white px-3.5 py-2 min-w-[54px] shadow-2xs"
                            >
                                <span class="text-[10px] font-bold uppercase opacity-90">{{ item.label }}</span>
                                <span class="text-xs font-black">{{ item.value }}</span>
                            </div>
                        </div>
                    </div>

                    <div>
                        <p class="mb-3 text-xs font-extrabold uppercase text-blue-600 flex items-center gap-1.5">
                            <FileText class="w-4 h-4" /> Catatan
                        </p>
                        <div class="rounded-xl border border-slate-200 bg-white p-3 text-xs text-slate-600 leading-relaxed shadow-2xs">
                            {{ profile.catatan }}
                        </div>
                    </div>
                </div>

                <!-- DAFTAR MESIN YANG BISA DIOPERASIKAN -->
                <div class="p-5 md:p-8 border-t border-slate-100">
                    <p class="mb-3 text-xs font-extrabold uppercase text-blue-600 flex items-center gap-1.5">
                        <Wrench class="w-4 h-4" /> DAFTAR MESIN YANG BISA DIOPERASIKAN
                    </p>
                    
                    <div v-if="profile.daftarMesin && profile.daftarMesin.length > 0" class="flex flex-wrap gap-2">
                        <span v-for="m in profile.daftarMesin" :key="m" class="px-3 py-1.5 bg-blue-50 text-blue-700 font-bold text-xs rounded-lg border border-blue-100">
                            {{ m }}
                        </span>
                    </div>
                    <div v-else class="rounded-xl border border-slate-200 bg-slate-50 p-3.5 text-xs text-slate-500">
                        Belum ada data mesin yang bisa dioperasikan.
                    </div>
                </div>

                <!-- RINCIAN TABLE KEHADIRAN ATR -->
                <div class="p-5 md:p-8 border-t border-slate-100 bg-white">
                    <div class="flex items-center justify-between mb-4">
                        <p class="text-xs font-extrabold uppercase text-blue-600 flex items-center gap-1.5">
                            <Table class="w-4 h-4" /> TABEL KEHADIRAN ATR (TAHUN {{ profile.atrYear }})
                        </p>
                    </div>

                    <div class="overflow-x-auto rounded-xl border border-slate-200">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-slate-100 text-slate-700 uppercase font-black tracking-wider text-[10px] border-b border-slate-200">
                                <tr>
                                    <th class="px-4 py-3">Bulan</th>
                                    <th class="px-4 py-3 text-center">ATR %</th>
                                    <th class="px-4 py-3 text-center">D (Day)</th>
                                    <th class="px-4 py-3 text-center">N (Night)</th>
                                    <th class="px-4 py-3 text-center">S (Sakit)</th>
                                    <th class="px-4 py-3 text-center">I (Izin)</th>
                                    <th class="px-4 py-3 text-center">A (Alpha)</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 font-medium">
                                <tr v-for="row in profile.atrDetailTable" :key="row.bulan" class="hover:bg-blue-50/40 transition">
                                    <td class="px-4 py-3 font-bold text-slate-800">{{ row.bulan }}</td>
                                    <td class="px-4 py-3 text-center">
                                        <span class="px-2 py-0.5 bg-emerald-100 text-emerald-700 font-bold rounded-md text-[11px]">
                                            {{ row.atr }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-center font-bold text-blue-600">{{ row.d }}</td>
                                    <td class="px-4 py-3 text-center font-bold text-indigo-600">{{ row.n }}</td>
                                    <td class="px-4 py-3 text-center text-slate-500">{{ row.s }}</td>
                                    <td class="px-4 py-3 text-center text-slate-500">{{ row.i }}</td>
                                    <td class="px-4 py-3 text-center text-slate-500">{{ row.a }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>

        <!-- BOTTOM NAV (VISIBLE ON MOBILE & TABLET) -->
        <div class="lg:hidden">
            <BottomNav active="profil" :items="navItems" :accent-class="accentClass" />
        </div>
    </div>
</template>
