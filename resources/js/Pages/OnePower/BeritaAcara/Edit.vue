<script setup>
import { computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft } from '@lucide/vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import BeritaAcaraForm from './Partials/BeritaAcaraForm.vue';

const props = defineProps({
    user: { type: Object, required: true },
    notifCounts: { type: Object, required: true },
    menu: { type: Array, required: true },
    targetItem: { type: Object, required: true },
});

const itemData = computed(() => props.targetItem?.data ?? props.targetItem ?? {});

const form = useForm({
    nomor_surat: itemData.value.nomorSurat ?? '',
    employee_id: itemData.value.employeeId ?? null,
    tanggal: itemData.value.tanggal ?? '',
    kronologi: itemData.value.kronologi ?? '',
    diketahui_oleh_id: itemData.value.diketahuiOlehId ?? null,
    ttd_karyawan: itemData.value.ttdKaryawan ?? '',
    ttd_atasan: itemData.value.ttdAtasan ?? '',
});

const submit = () => form.put(`/one-power/pembinaan/berita-acara/${itemData.value.id}`);
</script>

<template>
    <Head title="Edit Berita Acara - OneClick" />

    <AppLayout :user="user" :notif-counts="notifCounts" :menu="menu">
        <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <Link
                    href="/one-power/pembinaan/berita-acara"
                    class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-bold text-slate-700 shadow-2xs hover:bg-slate-50 transition"
                >
                    <ArrowLeft class="h-4 w-4" />
                    Kembali
                </Link>
                <h1 class="text-xl font-extrabold text-slate-800">Edit Berita Acara</h1>
            </div>
        </div>
        <BeritaAcaraForm :form="form" :on-submit="submit" :target-item="targetItem" />
    </AppLayout>
</template>
