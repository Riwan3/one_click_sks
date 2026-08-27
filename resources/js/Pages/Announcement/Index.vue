<script setup>
import { ref } from 'vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ArrowLeft, Megaphone, PlusCircle, Send, Trash2, Image as ImageIcon, Eye, Calendar, User, Info, AlertTriangle, MessageSquare } from 'lucide-vue-next';
import TopNav from '@/Pages/GroupLeader/Partials/TopNav.vue';
import BottomNav from '@/Pages/GroupLeader/Partials/BottomNav.vue';

const props = defineProps({
    user: { type: Object, required: true },
    notifCounts: { type: Object, required: true },
    announcements: { type: Array, default: () => [] },
});

const form = useForm({
    title: '',
    type: '📢 Pengumuman',
    content: '',
    image: null,
});

const imagePreview = ref(null);

const handleFileChange = (e) => {
    const file = e.target.files[0];
    if (file) {
        form.image = file;
        imagePreview.value = URL.createObjectURL(file);
    } else {
        form.image = null;
        imagePreview.value = null;
    }
};

const submitAnnouncement = () => {
    form.post('/pengumuman', {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
            imagePreview.value = null;
        },
    });
};

const deleteAnnouncement = (id) => {
    if (confirm('Apakah Anda yakin ingin menghapus pengumuman ini?')) {
        router.delete(`/pengumuman/${id}`, {
            preserveScroll: true,
        });
    }
};

const typeBadges = {
    '📢 Pengumuman': 'bg-blue-100 text-blue-700 border-blue-200',
    '💡 Informasi': 'bg-emerald-100 text-emerald-700 border-emerald-200',
    '⚠️ Peringatan': 'bg-amber-100 text-amber-700 border-amber-200',
    '📌 Roster & Operasional': 'bg-purple-100 text-purple-700 border-purple-200',
};
</script>

<template>
    <Head title="Pengumuman - OneClick" />

    <div class="min-h-screen bg-slate-100 pb-24 pt-[84px]">
        <TopNav :user="user" :notif-counts="notifCounts" />

        <div class="mx-auto max-w-md md:max-w-4xl lg:max-w-5xl px-4 space-y-6">
            <!-- BACK BUTTON -->
            <div class="flex items-center justify-between">
                <Link href="/portal" class="inline-flex items-center gap-2 rounded-xl bg-white px-4 py-2.5 text-xs font-bold uppercase text-slate-700 shadow-2xs hover:bg-slate-50 border border-slate-200/80 transition">
                    <ArrowLeft class="h-4 w-4 text-slate-600" /> Kembali ke Portal
                </Link>
            </div>

            <!-- TOP BANNER -->
            <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-blue-600 via-blue-500 to-indigo-600 p-6 text-white shadow-lg">
                <div class="flex items-center gap-4">
                    <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-white/20 backdrop-blur-md border border-white/30 text-white shadow-inner">
                        <Megaphone class="h-8 w-8" />
                    </div>
                    <div>
                        <h1 class="m-0 text-2xl font-black tracking-tight text-white">Pengumuman</h1>
                        <p class="m-0 mt-1 text-xs md:text-sm font-medium text-blue-100">
                            Informasi dan pengumuman resmi untuk seluruh karyawan
                        </p>
                    </div>
                </div>
            </div>

            <!-- FORM BUAT PENGUMUMAN BARU -->
            <div class="overflow-hidden rounded-2xl bg-white p-6 shadow-sm border border-slate-200/80 space-y-4">
                <div class="flex items-center gap-2 border-b border-slate-100 pb-3 text-blue-600 font-extrabold text-sm uppercase">
                    <PlusCircle class="h-4 w-4" />
                    <h2>Buat Pengumuman Baru</h2>
                </div>

                <form @submit.prevent="submitAnnouncement" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <!-- JUDUL -->
                        <div class="md:col-span-2 space-y-1">
                            <label class="block text-xs font-bold uppercase text-slate-700">Judul <span class="text-rose-500">*</span></label>
                            <input
                                v-model="form.title"
                                type="text"
                                required
                                placeholder="Judul pengumuman..."
                                class="w-full rounded-xl border border-slate-200 bg-slate-50/70 p-3 text-sm outline-none focus:border-blue-500 focus:bg-white transition"
                            >
                        </div>

                        <!-- TIPE -->
                        <div class="space-y-1">
                            <label class="block text-xs font-bold uppercase text-slate-700">Tipe</label>
                            <select
                                v-model="form.type"
                                class="w-full rounded-xl border border-slate-200 bg-slate-50/70 p-3 text-sm outline-none focus:border-blue-500 focus:bg-white transition font-medium"
                            >
                                <option value="📢 Pengumuman">📢 Pengumuman</option>
                                <option value="💡 Informasi">💡 Informasi</option>
                                <option value="⚠️ Peringatan">⚠️ Peringatan</option>
                                <option value="📌 Roster & Operasional">📌 Roster &amp; Operasional</option>
                            </select>
                        </div>
                    </div>

                    <!-- ISI PENGUMUMAN -->
                    <div class="space-y-1">
                        <label class="block text-xs font-bold uppercase text-slate-700">Isi Pengumuman <span class="text-rose-500">*</span></label>
                        <textarea
                            v-model="form.content"
                            rows="4"
                            required
                            placeholder="Tuliskan isi pengumuman di sini..."
                            class="w-full rounded-xl border border-slate-200 bg-slate-50/70 p-3 text-sm outline-none focus:border-blue-500 focus:bg-white transition"
                        ></textarea>
                    </div>

                    <!-- LAMPIRAN GAMBAR & TOMBOL SUBMIT -->
                    <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 pt-2">
                        <div class="space-y-1 flex-1">
                            <label class="block text-xs font-bold uppercase text-slate-700">Lampiran Gambar (opsional)</label>
                            <div class="flex items-center gap-3">
                                <input
                                    type="file"
                                    accept="image/*"
                                    @change="handleFileChange"
                                    class="w-full rounded-xl border border-slate-200 bg-slate-50 p-2.5 text-xs text-slate-500 file:mr-3 file:rounded-lg file:border-0 file:bg-blue-50 file:px-3 file:py-1.5 file:text-xs file:font-bold file:text-blue-600 hover:file:bg-blue-100 cursor-pointer"
                                >
                            </div>
                            <div v-if="imagePreview" class="mt-2">
                                <img :src="imagePreview" class="h-20 w-auto rounded-lg border object-cover shadow-2xs">
                            </div>
                        </div>

                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="inline-flex items-center justify-center gap-2 rounded-xl bg-blue-600 px-6 py-3 text-xs font-black uppercase tracking-wider text-white shadow-md hover:bg-blue-700 disabled:opacity-50 transition shrink-0"
                        >
                            <Send class="h-4 w-4" /> KIRIM PENGUMUMAN
                        </button>
                    </div>
                </form>
            </div>

            <!-- DAFTAR PENGUMUMAN CARD -->
            <div class="space-y-4">
                <div v-if="announcements.length === 0" class="rounded-2xl border border-slate-200 bg-white p-8 text-center text-slate-400">
                    <Megaphone class="mx-auto h-10 w-10 text-slate-300 mb-2" />
                    <p class="text-sm font-medium">Belum ada pengumuman yang diterbitkan.</p>
                </div>

                <div
                    v-for="item in announcements"
                    :key="item.id"
                    class="overflow-hidden rounded-2xl bg-white p-6 shadow-xs border border-slate-200/80 space-y-4 hover:shadow-md transition"
                >
                    <!-- TYPE & TITLE -->
                    <div class="flex flex-wrap items-start justify-between gap-2 border-b border-slate-100 pb-3">
                        <div class="space-y-1">
                            <span :class="['inline-block rounded-full border px-3 py-1 text-[11px] font-extrabold uppercase', typeBadges[item.type] || 'bg-slate-100 text-slate-700 border-slate-200']">
                                {{ item.type }}
                            </span>
                            <h3 class="m-0 text-lg font-black uppercase text-slate-800 tracking-tight pt-1">
                                {{ item.title }}
                            </h3>
                        </div>
                    </div>

                    <!-- CONTENT TEXT -->
                    <p class="m-0 text-sm text-slate-700 leading-relaxed whitespace-pre-line font-medium">
                        {{ item.content }}
                    </p>

                    <!-- ATTACHED IMAGE PREVIEW -->
                    <div v-if="item.image_url" class="overflow-hidden rounded-xl border border-slate-200">
                        <img :src="item.image_url" alt="Lampiran Pengumuman" class="max-h-80 w-auto object-cover">
                    </div>

                    <!-- FOOTER METADATA & DELETE ACTION -->
                    <div class="flex flex-wrap items-center justify-between gap-3 border-t border-slate-100 pt-4 text-xs font-semibold text-slate-500">
                        <div class="flex flex-wrap items-center gap-4">
                            <span class="flex items-center gap-1.5">
                                <Calendar class="h-3.5 w-3.5 text-slate-400" /> {{ item.date_formatted }}
                            </span>
                            <span class="flex items-center gap-1.5">
                                <User class="h-3.5 w-3.5 text-slate-400" /> {{ item.author_name }}
                            </span>
                            <span class="flex items-center gap-1.5 rounded-full bg-slate-100 px-2.5 py-0.5 text-[11px]">
                                <Eye class="h-3 w-3 text-slate-400" /> {{ item.views_count }}x Dilihat
                            </span>
                        </div>

                        <!-- DELETE BUTTON (HAPUS) -->
                        <button
                            type="button"
                            @click="deleteAnnouncement(item.id)"
                            class="inline-flex items-center gap-1.5 rounded-xl border border-rose-200 bg-rose-50 px-3.5 py-1.5 text-xs font-bold text-rose-600 hover:bg-rose-600 hover:text-white transition shadow-2xs"
                        >
                            <Trash2 class="h-3.5 w-3.5" /> HAPUS
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="lg:hidden">
            <BottomNav active="beranda" :items="[]" accent-class="text-blue-500" />
        </div>
    </div>
</template>
