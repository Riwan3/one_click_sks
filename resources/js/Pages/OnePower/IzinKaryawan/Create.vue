<script setup>
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ArrowLeft } from '@lucide/vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import IzinKaryawanForm from './Partials/IzinKaryawanForm.vue';

const props = defineProps({
    user: { type: Object, required: true },
    notifCounts: { type: Object, required: true },
    menu: { type: Array, required: true },
    isAdmin: { type: Boolean, default: false },
    isMedic: { type: Boolean, default: false },
    isAdminOrManpower: { type: Boolean, default: false },
    currentUserEmployee: { type: Object, default: () => ({}) },
});

const form = useForm({
    jenis_izin: props.isMedic ? 'sakit' : 'izin',
    no_surat: '',
    nama: props.currentUserEmployee?.nama || props.user?.name || '',
    nrp: props.currentUserEmployee?.nrp || props.user?.username || '',
    jabatan: props.currentUserEmployee?.jabatan || props.user?.jabatan || '',
    departemen: props.currentUserEmployee?.departemen || '',
    alasan: '',
    tujuan: '',
    alamat: '',
    tgl_berangkat: '',
    tgl_kembali: '',
    no_telepon: '',
    keluhan: '',
    hasil_td: '',
    hasil_rr: '',
    hasil_t: '',
    hasil_p: '',
    hasil_spo: '',
    diagnosa: '',
    terapi: '',
    keterangan_status: 1,
    rujuk_ke: '',
    rumah_sakit_rujukan: '',
    dokter_rujukan: '',
    dipulangkan_tidur: false,
    file_bukti: null,
});

const submit = () => form.post('/one-power/deviasi-operator/izin-karyawan');
</script>

<template>
    <Head title="Ajukan Izin Karyawan - OneClick" />

    <AppLayout :user="user" :notif-counts="notifCounts" :menu="menu">
        <div class="mb-5 flex items-center gap-3">
            <Link
                href="/one-power/deviasi-operator/izin-karyawan"
                class="inline-flex h-9 w-9 items-center justify-center rounded-full border border-slate-200 bg-white text-slate-600 hover:bg-slate-100 transition shadow-xs"
            >
                <ArrowLeft class="h-4 w-4" />
            </Link>
            <div>
                <h1 class="text-xl font-extrabold text-slate-800">Tambah Pengajuan</h1>
                <p class="text-xs text-slate-500">Ajukan izin, sakit, atau emergency karyawan</p>
            </div>
        </div>

        <div class="max-w-3xl">
            <IzinKaryawanForm
                :form="form"
                :is-edit="false"
                :is-medic="isMedic"
                :is-admin-or-manpower="isAdminOrManpower"
                :current-user-employee="currentUserEmployee"
                :on-submit="submit"
            />
        </div>
    </AppLayout>
</template>
