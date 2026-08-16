<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft, CheckCircle2, Circle, UserRound } from '@lucide/vue';
import TopNav from './Partials/TopNav.vue';
import BottomNav from './Partials/BottomNav.vue';

defineProps({
    user: { type: Object, required: true },
    notifCounts: { type: Object, required: true },
    profile: { type: Object, required: true },
    navItems: { type: Array, required: true },
    accentClass: { type: String, default: 'text-blue-500' },
});

const isEmptyValue = (value) => !value || value === '-' || value === 'Belum Ada';
</script>

<template>
    <Head title="Profil - OneClick" />

    <div class="min-h-screen bg-slate-100 pb-24 pt-[84px]">
        <TopNav :user="user" :notif-counts="notifCounts" />

        <div class="mx-auto max-w-md px-4">
            <Link href="/portal" class="mb-3 flex h-9 w-9 items-center justify-center rounded-full bg-white shadow-sm">
                <ArrowLeft class="h-4 w-4 text-slate-700" />
            </Link>

            <div class="overflow-hidden rounded-2xl bg-white shadow-sm">
                <div class="relative h-24 bg-gradient-to-r from-blue-500 to-blue-400">
                    <div class="absolute inset-x-0 -bottom-10 flex justify-center">
                        <div class="flex h-20 w-20 items-center justify-center rounded-3xl bg-white shadow-md">
                            <UserRound class="h-9 w-9 text-red-800" />
                        </div>
                    </div>
                </div>

                <div class="px-5 pb-5 pt-12 text-center">
                    <h1 class="m-0 text-lg font-extrabold uppercase text-slate-800">{{ user.displayName }}</h1>
                    <p class="m-0 mt-1 text-xs font-bold uppercase tracking-wide text-slate-400">{{ user.jabatan }} {{ user.unit }}</p>
                    <span class="mt-3 inline-flex items-center gap-1.5 rounded-full bg-emerald-500 px-3 py-1 text-xs font-bold uppercase text-white">
                        <CheckCircle2 class="h-3.5 w-3.5" /> {{ profile.statusSp }}
                    </span>
                </div>

                <div class="grid grid-cols-2 gap-x-4 gap-y-4 border-t border-slate-100 px-5 py-5 text-sm">
                    <div>
                        <p class="mb-1 text-[0.65rem] font-bold uppercase tracking-wide text-slate-400">NRP</p>
                        <p class="m-0 flex items-center gap-1.5 font-bold text-slate-800">
                            <Circle class="h-2 w-2 shrink-0 fill-emerald-500 text-emerald-500" /> {{ profile.nrp }}
                        </p>
                    </div>
                    <div>
                        <p class="mb-1 text-[0.65rem] font-bold uppercase tracking-wide text-slate-400">Unit</p>
                        <p class="m-0 font-bold text-slate-800">{{ profile.unit }}</p>
                    </div>

                    <div class="col-span-2">
                        <p class="mb-1 text-[0.65rem] font-bold uppercase tracking-wide text-slate-400">Lokasi</p>
                        <p class="m-0 font-bold text-slate-800">{{ profile.lokasi }}</p>
                    </div>

                    <div class="col-span-2">
                        <p class="mb-1 text-[0.65rem] font-bold uppercase tracking-wide text-slate-400">Atasan</p>
                        <p :class="['m-0 font-bold', isEmptyValue(profile.atasan) ? 'text-red-600' : 'text-slate-800']">{{ profile.atasan }}</p>
                    </div>

                    <div>
                        <p class="mb-1 text-[0.65rem] font-bold uppercase tracking-wide text-slate-400">Telepon</p>
                        <p class="m-0 font-bold text-slate-800">{{ profile.telepon }}</p>
                    </div>
                    <div>
                        <p class="mb-1 text-[0.65rem] font-bold uppercase tracking-wide text-slate-400">Jabatan</p>
                        <p class="m-0 font-bold text-slate-800">{{ profile.jabatanLengkap }}</p>
                    </div>

                    <div>
                        <p class="mb-1 text-[0.65rem] font-bold uppercase tracking-wide text-slate-400">Status</p>
                        <p class="m-0 font-bold text-emerald-600">{{ profile.status }}</p>
                    </div>
                    <div>
                        <p class="mb-1 text-[0.65rem] font-bold uppercase tracking-wide text-slate-400">Mulai Bekerja</p>
                        <p :class="['m-0 font-bold', isEmptyValue(profile.mulaiBekerja) ? 'text-red-600' : 'text-slate-800']">{{ profile.mulaiBekerja }}</p>
                    </div>

                    <div class="col-span-2">
                        <p class="mb-1 text-[0.65rem] font-bold uppercase tracking-wide text-slate-400">Masa Kerja</p>
                        <p :class="['m-0 font-bold', isEmptyValue(profile.masaKerja) ? 'text-red-600' : 'text-slate-800']">{{ profile.masaKerja }}</p>
                    </div>
                </div>

                <div class="border-t border-slate-100 px-5 py-5">
                    <p class="mb-2 text-xs font-extrabold uppercase text-red-600">ATR per Bulan {{ profile.atrYear }}</p>
                    <div class="flex gap-2">
                        <div
                            v-for="item in profile.atrPerBulan"
                            :key="item.label"
                            :class="[
                                'flex flex-col items-center gap-0.5 rounded-lg px-3 py-2 text-xs font-bold text-white',
                                item.value ? 'bg-emerald-500' : 'bg-slate-400',
                            ]"
                        >
                            <span>{{ item.label }}</span>
                            <span>{{ item.value ?? '-' }}</span>
                        </div>
                    </div>
                </div>

                <div class="border-t border-slate-100 px-5 py-5">
                    <p class="mb-2 text-xs font-extrabold uppercase text-red-600">Catatan</p>
                    <div class="rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-400">
                        {{ profile.catatan }}
                    </div>
                </div>
            </div>
        </div>

        <BottomNav active="profil" :items="navItems" :accent-class="accentClass" />
    </div>
</template>
