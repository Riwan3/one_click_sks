<script setup>
import { ref } from 'vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ArrowLeft, Trophy, Star, Cake, Award, PlusCircle, Send, Trash2, Calendar, User } from 'lucide-vue-next';
import TopNav from '@/Pages/GroupLeader/Partials/TopNav.vue';
import BottomNav from '@/Pages/GroupLeader/Partials/BottomNav.vue';

const props = defineProps({
    user: { type: Object, required: true },
    notifCounts: { type: Object, required: true },
    rewards: { type: Array, default: () => [] },
});

const form = useForm({
    title: '',
    type: 'safety',
    category: 'Safety Award',
    reward_date: new Date().toISOString().slice(0, 10),
    description: '',
});

const typeOptions = [
    { value: 'safety', label: '⭐ Safety Award', category: 'Safety Award' },
    { value: 'birthday', label: '🎂 Birthday', category: 'Birthday' },
    { value: 'achievement', label: '🏆 Achievement', category: 'Achievement' },
    { value: 'performance', label: '🏅 Performance', category: 'Performance' },
];

const handleTypeChange = (e) => {
    const selected = typeOptions.find(o => o.value === e.target.value);
    if (selected) {
        form.category = selected.category;
    }
};

const submitReward = () => {
    form.post('/rewards', {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
        },
    });
};

const deleteReward = (id) => {
    if (confirm('Apakah Anda yakin ingin menghapus data penghargaan ini?')) {
        router.delete(`/rewards/${id}`, {
            preserveScroll: true,
        });
    }
};

const typeMeta = {
    safety: { icon: Star, color: 'text-amber-500 bg-amber-50 border-amber-200', badge: 'bg-amber-100 text-amber-800' },
    birthday: { icon: Cake, color: 'text-pink-500 bg-pink-50 border-pink-200', badge: 'bg-pink-100 text-pink-800' },
    achievement: { icon: Trophy, color: 'text-yellow-500 bg-yellow-50 border-yellow-200', badge: 'bg-yellow-100 text-yellow-800' },
    performance: { icon: Award, color: 'text-blue-500 bg-blue-50 border-blue-200', badge: 'bg-blue-100 text-blue-800' },
};
</script>

<template>
    <Head title="Reward Center - OneClick" />

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
            <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-amber-500 via-amber-600 to-yellow-600 p-6 text-white shadow-lg">
                <div class="flex items-center gap-4">
                    <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-white/20 backdrop-blur-md border border-white/30 text-white shadow-inner">
                        <Trophy class="h-8 w-8" />
                    </div>
                    <div>
                        <h1 class="m-0 text-2xl font-black tracking-tight text-white">Oneclick Reward Center</h1>
                        <p class="m-0 mt-1 text-xs md:text-sm font-medium text-amber-100">
                            Apresiasi dan pusat penghargaan prestasi seluruh karyawan
                        </p>
                    </div>
                </div>
            </div>

            <!-- FORM TAMBAH REWARD -->
            <div class="overflow-hidden rounded-2xl bg-white p-6 shadow-sm border border-slate-200/80 space-y-4">
                <div class="flex items-center gap-2 border-b border-slate-100 pb-3 text-amber-600 font-extrabold text-sm uppercase">
                    <PlusCircle class="h-4 w-4" />
                    <h2>Tambah Penghargaan / Apresiasi Baru</h2>
                </div>

                <form @submit.prevent="submitReward" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <!-- JUDUL -->
                        <div class="space-y-1">
                            <label class="block text-xs font-bold uppercase text-slate-700">Judul Penghargaan <span class="text-rose-500">*</span></label>
                            <input
                                v-model="form.title"
                                type="text"
                                required
                                placeholder="Contoh: Safety Award Q3 2026..."
                                class="w-full rounded-xl border border-slate-200 bg-slate-50/70 p-3 text-sm outline-none focus:border-amber-500 focus:bg-white transition"
                            >
                        </div>

                        <!-- TIPE -->
                        <div class="space-y-1">
                            <label class="block text-xs font-bold uppercase text-slate-700">Tipe &amp; Kategori</label>
                            <select
                                v-model="form.type"
                                @change="handleTypeChange"
                                class="w-full rounded-xl border border-slate-200 bg-slate-50/70 p-3 text-sm outline-none focus:border-amber-500 focus:bg-white transition font-medium"
                            >
                                <option v-for="opt in typeOptions" :key="opt.value" :value="opt.value">
                                    {{ opt.label }}
                                </option>
                            </select>
                        </div>

                        <!-- TANGGAL -->
                        <div class="space-y-1">
                            <label class="block text-xs font-bold uppercase text-slate-700">Tanggal <span class="text-rose-500">*</span></label>
                            <input
                                v-model="form.reward_date"
                                type="date"
                                required
                                class="w-full rounded-xl border border-slate-200 bg-slate-50/70 p-3 text-sm outline-none focus:border-amber-500 focus:bg-white transition"
                            >
                        </div>
                    </div>

                    <!-- DESKRIPSI -->
                    <div class="space-y-1">
                        <label class="block text-xs font-bold uppercase text-slate-700">Keterangan (opsional)</label>
                        <textarea
                            v-model="form.description"
                            rows="3"
                            placeholder="Catatan apresiasi atau rincian pencapaian..."
                            class="w-full rounded-xl border border-slate-200 bg-slate-50/70 p-3 text-sm outline-none focus:border-amber-500 focus:bg-white transition"
                        ></textarea>
                    </div>

                    <!-- SUBMIT BUTTON -->
                    <div class="flex justify-end pt-2">
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="inline-flex items-center justify-center gap-2 rounded-xl bg-amber-600 px-6 py-3 text-xs font-black uppercase tracking-wider text-white shadow-md hover:bg-amber-700 disabled:opacity-50 transition shrink-0"
                        >
                            <Send class="h-4 w-4" /> TAMBAH REWARD
                        </button>
                    </div>
                </form>
            </div>

            <!-- REWARD CARDS GRID -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <div v-if="rewards.length === 0" class="col-span-full rounded-2xl border border-slate-200 bg-white p-8 text-center text-slate-400">
                    <Trophy class="mx-auto h-10 w-10 text-slate-300 mb-2" />
                    <p class="text-sm font-medium">Belum ada data penghargaan yang ditambahkan.</p>
                </div>

                <div
                    v-for="item in rewards"
                    :key="item.id"
                    class="overflow-hidden rounded-2xl bg-white p-5 shadow-xs border border-slate-200/80 space-y-3 hover:shadow-md transition flex flex-col justify-between"
                >
                    <div class="space-y-3">
                        <div class="flex items-center justify-between gap-2">
                            <div :class="['flex h-11 w-11 items-center justify-center rounded-xl border', (typeMeta[item.type] || typeMeta.safety).color]">
                                <component :is="(typeMeta[item.type] || typeMeta.safety).icon" class="h-6 w-6" />
                            </div>
                            <span :class="['rounded-full px-3 py-0.5 text-[11px] font-extrabold uppercase', (typeMeta[item.type] || typeMeta.safety).badge]">
                                {{ item.category }}
                            </span>
                        </div>

                        <div>
                            <h3 class="m-0 text-base font-black text-slate-800 leading-snug">
                                {{ item.title }}
                            </h3>
                            <p v-if="item.description" class="m-0 mt-1.5 text-xs text-slate-600 leading-relaxed line-clamp-3">
                                {{ item.description }}
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center justify-between border-t border-slate-100 pt-3 text-xs font-semibold text-slate-500">
                        <span class="flex items-center gap-1.5 text-[11px]">
                            <Calendar class="h-3.5 w-3.5 text-slate-400" /> {{ item.date_formatted }}
                        </span>

                        <button
                            type="button"
                            @click="deleteReward(item.id)"
                            class="inline-flex items-center gap-1 rounded-lg border border-rose-200 bg-rose-50 px-2.5 py-1 text-[11px] font-bold text-rose-600 hover:bg-rose-600 hover:text-white transition"
                        >
                            <Trash2 class="h-3 w-3" /> HAPUS
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="lg:hidden">
            <BottomNav active="beranda" :items="[]" accent-class="text-amber-500" />
        </div>
    </div>
</template>
