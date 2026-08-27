<script setup>
import { reactive, ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import { ClipboardCheck, Save } from 'lucide-vue-next';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    user: { type: Object, required: true },
    notifCounts: { type: Object, required: true },
    menu: { type: Array, required: true },
    type: { type: String, required: true },
    questions: { type: Object, required: true },
    masterLocations: { type: Array, default: () => [] },
});

const form = reactive({
    tipe_gmp: props.type,
    lokasi: props.masterLocations[0] || '',
    penilai: props.user?.name || '',
    nrp: props.user?.username || '',
    tanggal_gmp: new Date().toISOString().split('T')[0],
    keterangan: '',
    foto_area: '',
    answers: {},
});

// Initialize default score = 5 for all questions
Object.keys(props.questions).forEach((qNo) => {
    form.answers[qNo] = 5;
});

const handleSubmit = () => {
    router.post('/one-gmp/penilaian/store', form);
};
</script>

<template>
    <Head :title="`Form Penilaian GMP - ${type}`" />

    <AppLayout :user="user" :notif-counts="notifCounts" :menu="menu">
        <!-- Banner Header -->
        <div class="mb-6 rounded-3xl p-6 text-white shadow-md flex justify-between items-center flex-wrap gap-4" style="background: linear-gradient(135deg, #059669 0%, #047857 100%);">
            <div>
                <h1 class="mb-1 text-2xl font-extrabold flex items-center gap-3">
                    <ClipboardCheck class="w-8 h-8 text-emerald-200" />
                    Form Penilaian GMP: {{ type }}
                </h1>
                <p class="m-0 text-sm text-white/90">
                    Isi lembar kriteria evaluasi inspeksi Good Mining Practice untuk kategori {{ type }}.
                </p>
            </div>
        </div>

        <form @submit.prevent="handleSubmit" class="space-y-6">
            <!-- Metadata Header Card -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
                <h3 class="text-sm font-extrabold text-slate-800 mb-4 uppercase tracking-wider">Identitas Penilaian</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Lokasi / Equipment</label>
                        <select v-if="masterLocations.length > 0" v-model="form.lokasi" required class="w-full border border-slate-300 rounded-xl px-3 py-2 text-xs bg-slate-50 text-slate-800 outline-none focus:border-emerald-500">
                            <option v-for="loc in masterLocations" :key="loc" :value="loc">{{ loc }}</option>
                        </select>
                        <input v-else v-model="form.lokasi" type="text" placeholder="Ketikkan nama lokasi..." required class="w-full border border-slate-300 rounded-xl px-3 py-2 text-xs bg-slate-50 text-slate-800 outline-none focus:border-emerald-500" />
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Nama Penilai</label>
                        <input v-model="form.penilai" type="text" required class="w-full border border-slate-300 rounded-xl px-3 py-2 text-xs bg-slate-50 text-slate-800 outline-none focus:border-emerald-500" />
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase mb-1">NRP Penilai</label>
                        <input v-model="form.nrp" type="text" required class="w-full border border-slate-300 rounded-xl px-3 py-2 text-xs bg-slate-50 text-slate-800 outline-none focus:border-emerald-500" />
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Tanggal GMP</label>
                        <input v-model="form.tanggal_gmp" type="date" required class="w-full border border-slate-300 rounded-xl px-3 py-2 text-xs bg-slate-50 text-slate-800 outline-none focus:border-emerald-500" />
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Catatan / Keterangan</label>
                        <input v-model="form.keterangan" type="text" placeholder="Opsional..." class="w-full border border-slate-300 rounded-xl px-3 py-2 text-xs bg-slate-50 text-slate-800 outline-none focus:border-emerald-500" />
                    </div>
                </div>
            </div>

            <!-- Questions Card Grid -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
                <h3 class="text-sm font-extrabold text-slate-800 mb-4 uppercase tracking-wider">Kriteria Penilaian Kualitas (0 - 5)</h3>

                <div class="space-y-4">
                    <div v-for="(q, qNo) in questions" :key="qNo" class="p-4 rounded-xl border border-slate-200 bg-slate-50 flex flex-col md:flex-row md:items-center justify-between gap-4">
                        <div class="max-w-xl">
                            <span class="text-[10px] font-black uppercase px-2 py-0.5 rounded bg-emerald-100 text-emerald-800">
                                {{ q.section_key }}
                            </span>
                            <h4 class="text-xs font-bold text-slate-900 mt-1">{{ qNo }}. {{ q.name }}</h4>
                            <p class="text-[11px] text-slate-500 m-0">{{ q.title }}</p>
                        </div>

                        <!-- Option Score Selector -->
                        <div class="flex items-center gap-1.5 flex-wrap">
                            <button
                                v-for="score in q.allowed_scores"
                                :key="score"
                                type="button"
                                @click="form.answers[qNo] = score"
                                :class="[
                                    'px-3 py-1.5 rounded-lg text-xs font-black transition border',
                                    form.answers[qNo] === score
                                        ? 'bg-emerald-600 text-white border-emerald-600 shadow'
                                        : 'bg-white text-slate-700 border-slate-300 hover:bg-slate-100'
                                ]"
                            >
                                Nilai {{ score }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Submit Action Footer -->
            <div class="flex justify-end gap-3">
                <button type="submit" class="px-6 py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold rounded-2xl text-xs shadow-lg flex items-center gap-2">
                    <Save class="w-4 h-4" /> Kirim Penilaian GMP
                </button>
            </div>
        </form>
    </AppLayout>
</template>
