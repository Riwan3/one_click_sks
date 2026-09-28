<script setup>
import { confirmDialog } from '@/utils/confirm';
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { 
    ArrowLeft, Trophy, Star, Cake, Award, PlusCircle, Send, Trash2, 
    Calendar, User, Users, Search, Image as ImageIcon, X, Check, Filter
} from 'lucide-vue-next';
import TopNav from '@/Pages/GroupLeader/Partials/TopNav.vue';
import BottomNav from '@/Pages/GroupLeader/Partials/BottomNav.vue';

const props = defineProps({
    user: { type: Object, required: true },
    notifCounts: { type: Object, required: true },
    rewards: { type: Array, default: () => [] },
    employees: { type: Array, default: () => [] },
    canManage: { type: Boolean, default: false },
});

// Search & Filter State
const selectedCategory = ref('ALL');
const searchQuery = ref('');

const typeOptions = [
    { value: 'safety', label: '⭐ Safety Award', category: 'Safety Award' },
    { value: 'birthday', label: '🎂 Birthday', category: 'Birthday' },
    { value: 'achievement', label: '🏆 Achievement', category: 'Achievement' },
    { value: 'performance', label: '🏅 Performance', category: 'Performance' },
];

const typeMeta = {
    safety: { icon: Star, color: 'text-emerald-600 bg-emerald-50 border-emerald-200', badge: 'bg-emerald-100 text-emerald-800' },
    birthday: { icon: Cake, color: 'text-pink-600 bg-pink-50 border-pink-200', badge: 'bg-pink-100 text-pink-800' },
    achievement: { icon: Trophy, color: 'text-amber-600 bg-amber-50 border-amber-200', badge: 'bg-amber-100 text-amber-800' },
    performance: { icon: Award, color: 'text-blue-600 bg-blue-50 border-blue-200', badge: 'bg-blue-100 text-blue-800' },
};

// Form State
const recipientMode = ref('global'); // 'global' or 'specific'
const employeeSearch = ref('');
const showEmployeeDropdown = ref(false);
const dropdownRef = ref(null);
const imagePreview = ref(null);
const fileInput = ref(null);

const handleClickOutside = (e) => {
    if (dropdownRef.value && !dropdownRef.value.contains(e.target)) {
        showEmployeeDropdown.value = false;
    }
};

onMounted(() => {
    document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside);
});

const form = useForm({
    title: '',
    type: 'safety',
    category: 'Safety Award',
    reward_date: new Date().toISOString().slice(0, 10),
    description: '',
    employee_ids: [],
    image: null,
});

const handleTypeChange = (e) => {
    const selected = typeOptions.find(o => o.value === e.target.value);
    if (selected) {
        form.category = selected.category;
    }
};

const filteredEmployees = computed(() => {
    if (!employeeSearch.value) return props.employees.slice(0, 15);
    const q = employeeSearch.value.toLowerCase();
    return props.employees
        .filter(e => e.name.toLowerCase().includes(q) || e.nrp.toLowerCase().includes(q))
        .slice(0, 15);
});

const selectedEmployees = computed(() => {
    if (!form.employee_ids || form.employee_ids.length === 0) return [];
    return props.employees.filter(e => form.employee_ids.includes(e.id));
});

const toggleEmployee = (emp) => {
    const idx = form.employee_ids.indexOf(emp.id);
    if (idx === -1) {
        form.employee_ids.push(emp.id);
    } else {
        form.employee_ids.splice(idx, 1);
    }
};

const removeEmployee = (empId) => {
    const idx = form.employee_ids.indexOf(empId);
    if (idx !== -1) {
        form.employee_ids.splice(idx, 1);
    }
};

const clearAllEmployees = () => {
    form.employee_ids = [];
    employeeSearch.value = '';
};

const handleImageChange = (e) => {
    const file = e.target.files[0];
    if (file) {
        form.image = file;
        const reader = new FileReader();
        reader.onload = (ev) => {
            imagePreview.value = ev.target.result;
        };
        reader.readAsDataURL(file);
    }
};

const removeImage = () => {
    form.image = null;
    imagePreview.value = null;
    if (fileInput.value) fileInput.value.value = '';
};

const submitReward = () => {
    form.post('/rewards', {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
            form.employee_ids = [];
            form.reward_date = new Date().toISOString().slice(0, 10);
            recipientMode.value = 'global';
            imagePreview.value = null;
            if (fileInput.value) fileInput.value.value = '';
        },
    });
};

const deleteReward = async (id) => {
    const confirmed = await confirmDialog({
        title: 'Hapus Data Penghargaan?',
        text: 'Data penghargaan apresiasi ini akan dihapus permanen dari sistem.',
        confirmButtonText: 'Ya, Hapus!',
        danger: true,
    });
    if (confirmed) {
        router.delete(`/rewards/${id}`, { preserveScroll: true });
    }
};

// Filtered Rewards List
const filteredRewards = computed(() => {
    return props.rewards.filter(r => {
        const matchCategory = selectedCategory.value === 'ALL' || r.type === selectedCategory.value;
        const q = searchQuery.value.trim().toLowerCase();
        if (!q) return matchCategory;

        const matchSearch = (r.title && r.title.toLowerCase().includes(q)) ||
            (r.recipient && r.recipient.toLowerCase().includes(q)) ||
            (r.recipient_nrp && r.recipient_nrp.toLowerCase().includes(q)) ||
            (r.description && r.description.toLowerCase().includes(q));

        return matchCategory && matchSearch;
    });
});
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
                <div v-if="canManage" class="px-3 py-1 rounded-lg bg-amber-100 text-amber-800 text-xs font-bold flex items-center gap-1.5">
                    <Trophy class="w-3.5 h-3.5" /> Mode Kelola (Admin/GL)
                </div>
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
                            Apresiasi dan pusat penghargaan prestasi serta rekam jejak insan perusahaan
                        </p>
                    </div>
                </div>
            </div>

            <!-- FORM TAMBAH REWARD (HANYA UNTUK ADMIN / GL) -->
            <div v-if="canManage" class="overflow-hidden rounded-2xl bg-white p-6 shadow-sm border border-slate-200/80 space-y-5">
                <div class="flex items-center gap-2 border-b border-slate-100 pb-3 text-amber-600 font-extrabold text-sm uppercase">
                    <PlusCircle class="h-4 w-4" />
                    <h2>Tambah Penghargaan / Apresiasi Baru</h2>
                </div>

                <form @submit.prevent="submitReward" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <!-- JUDUL -->
                        <div class="space-y-1 md:col-span-2">
                            <label class="block text-xs font-bold uppercase text-slate-700">Judul Penghargaan <span class="text-rose-500">*</span></label>
                            <input
                                v-model="form.title"
                                type="text"
                                required
                                placeholder="Contoh: Best Operator of the Month / Safety Champion 2026..."
                                class="w-full rounded-xl border border-slate-200 bg-slate-50/70 p-3 text-sm outline-none focus:border-amber-500 focus:bg-white transition"
                            >
                        </div>

                        <!-- TIPE & KATEGORI -->
                        <div class="space-y-1">
                            <label class="block text-xs font-bold uppercase text-slate-700">Kategori</label>
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
                    </div>

                    <!-- PENERIMA PENGHARGAAN SELECTION -->
                    <div class="rounded-xl border border-slate-200 bg-slate-50/50 p-4 space-y-3">
                        <label class="block text-xs font-black uppercase text-slate-700 tracking-wider">
                            Penerima Penghargaan <span class="text-rose-500">*</span>
                        </label>
                        
                        <div class="flex flex-wrap items-center gap-4 text-xs font-bold">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input 
                                    type="radio" 
                                    v-model="recipientMode" 
                                    value="global" 
                                    @change="clearAllEmployees"
                                    class="text-amber-600 focus:ring-amber-500" 
                                />
                                <span>🌐 Semua Karyawan (Umum / Tim)</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input 
                                    type="radio" 
                                    v-model="recipientMode" 
                                    value="specific" 
                                    class="text-amber-600 focus:ring-amber-500" 
                                />
                                <span>👥 Pilih Karyawan / Operator (Bisa Pilih Beberapa)</span>
                            </label>
                        </div>

                        <!-- MULTI EMPLOYEE SELECTOR -->
                        <div v-if="recipientMode === 'specific'" class="space-y-3 pt-1">
                            <!-- SEARCH INPUT -->
                            <div ref="dropdownRef" class="relative">
                                <Search class="absolute left-3.5 top-3.5 h-4 w-4 text-slate-400" />
                                <input
                                    v-model="employeeSearch"
                                    @focus="showEmployeeDropdown = true"
                                    @keydown.esc="showEmployeeDropdown = false"
                                    type="text"
                                    placeholder="Ketik NRP atau nama karyawan untuk menambah ke daftar..."
                                    class="w-full rounded-xl border border-slate-200 bg-white pl-10 pr-10 py-2.5 text-xs outline-none focus:border-amber-500 transition shadow-2xs"
                                />
                                <button 
                                    v-if="showEmployeeDropdown || employeeSearch"
                                    type="button" 
                                    @click="showEmployeeDropdown = false; employeeSearch = ''"
                                    class="absolute right-3 top-2.5 text-slate-400 hover:text-slate-600 p-0.5 rounded transition"
                                    title="Tutup / Bersihkan"
                                >
                                    <X class="w-4 h-4" />
                                </button>

                                <div 
                                    v-if="showEmployeeDropdown" 
                                    class="absolute left-0 right-0 top-full mt-1.5 z-30 max-h-60 overflow-y-auto rounded-xl border border-slate-200 bg-white shadow-xl divide-y divide-slate-100"
                                >
                                    <div class="p-2.5 bg-slate-50 border-b border-slate-100 flex items-center justify-between text-[11px] font-bold text-slate-500 sticky top-0 bg-white z-10">
                                        <span>Daftar Karyawan:</span>
                                        <button 
                                            type="button" 
                                            @click="showEmployeeDropdown = false" 
                                            class="text-amber-600 hover:text-amber-800 text-[11px] font-extrabold flex items-center gap-1 px-2 py-0.5 rounded-md hover:bg-amber-50 transition"
                                        >
                                            <X class="w-3 h-3" /> Selesai / Tutup
                                        </button>
                                    </div>
                                    <div v-if="filteredEmployees.length === 0" class="p-4 text-center text-xs text-slate-400">
                                        Tidak ditemukan karyawan yang cocok.
                                    </div>
                                    <button
                                        v-else
                                        v-for="emp in filteredEmployees"
                                        :key="emp.id"
                                        type="button"
                                        @click="toggleEmployee(emp)"
                                        class="w-full flex items-center justify-between p-3 text-left hover:bg-amber-50/60 transition text-xs"
                                    >
                                        <div>
                                            <span class="font-bold text-slate-800">{{ emp.name }}</span>
                                            <span class="text-slate-400 text-[11px] ml-1.5 font-mono">({{ emp.nrp }})</span>
                                            <div class="text-[11px] text-slate-500">{{ emp.jabatan || '-' }} - Unit: {{ emp.unit || '-' }}</div>
                                        </div>
                                        <div>
                                            <span 
                                                v-if="form.employee_ids.includes(emp.id)" 
                                                class="px-2 py-0.5 rounded-md bg-emerald-100 text-emerald-700 font-bold text-[10px] flex items-center gap-1"
                                            >
                                                <Check class="w-3 h-3" /> Terpilih
                                            </span>
                                            <span v-else class="text-[11px] font-bold text-amber-600">+ Tambah</span>
                                        </div>
                                    </button>
                                </div>
                            </div>

                            <!-- SELECTED EMPLOYEES CHIPS -->
                            <div v-if="selectedEmployees.length > 0" class="space-y-2">
                                <div class="flex items-center justify-between">
                                    <span class="text-xs font-bold text-slate-600">
                                        Karyawan Terpilih (<strong class="text-amber-600">{{ selectedEmployees.length }}</strong>):
                                    </span>
                                    <button 
                                        type="button" 
                                        @click="clearAllEmployees" 
                                        class="text-[11px] font-bold text-rose-500 hover:underline"
                                    >
                                        Hapus Semua
                                    </button>
                                </div>
                                
                                <div class="flex flex-wrap gap-2 max-h-36 overflow-y-auto p-2 bg-white rounded-xl border border-slate-200/80">
                                    <div 
                                        v-for="emp in selectedEmployees" 
                                        :key="emp.id"
                                        class="inline-flex items-center gap-2 pl-2.5 pr-1.5 py-1 rounded-lg bg-amber-50 border border-amber-200 text-slate-800 text-xs font-bold shadow-2xs"
                                    >
                                        <div class="w-5 h-5 rounded-full bg-amber-500 text-white flex items-center justify-center text-[10px] font-black">
                                            {{ emp.name.charAt(0) }}
                                        </div>
                                        <span class="truncate max-w-[150px]">{{ emp.name }}</span>
                                        <span class="text-slate-400 text-[10px] font-mono">({{ emp.nrp }})</span>
                                        <button 
                                            type="button" 
                                            @click="removeEmployee(emp.id)"
                                            class="p-0.5 rounded hover:bg-rose-100 text-slate-400 hover:text-rose-600 transition"
                                        >
                                            <X class="w-3.5 h-3.5" />
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <p v-else class="text-[11px] text-amber-700 italic">
                                * Silakan cari dan pilih satu atau beberapa karyawan di atas.
                            </p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- TANGGAL -->
                        <div class="space-y-1">
                            <label class="block text-xs font-bold uppercase text-slate-700">Tanggal Penghargaan <span class="text-rose-500">*</span></label>
                            <input
                                v-model="form.reward_date"
                                type="date"
                                required
                                class="w-full rounded-xl border border-slate-200 bg-slate-50/70 p-3 text-sm outline-none focus:border-amber-500 focus:bg-white transition"
                            >
                        </div>

                        <!-- FOTO / DOKUMENTASI (OPSIONAL) -->
                        <div class="space-y-1">
                            <label class="block text-xs font-bold uppercase text-slate-700">Foto / Piagam (Opsional)</label>
                            <input
                                ref="fileInput"
                                type="file"
                                accept="image/*"
                                @change="handleImageChange"
                                class="w-full rounded-xl border border-slate-200 bg-slate-50/70 p-2.5 text-xs outline-none focus:border-amber-500 focus:bg-white transition"
                            />
                        </div>
                    </div>

                    <!-- IMAGE PREVIEW -->
                    <div v-if="imagePreview" class="relative inline-block mt-2">
                        <img :src="imagePreview" alt="Preview" class="h-28 w-auto rounded-xl border border-slate-200 object-cover shadow-sm" />
                        <button 
                            type="button" 
                            @click="removeImage"
                            class="absolute -top-2 -right-2 p-1 bg-rose-600 text-white rounded-full shadow hover:bg-rose-700 transition"
                        >
                            <X class="w-3.5 h-3.5" />
                        </button>
                    </div>

                    <!-- DESKRIPSI -->
                    <div class="space-y-1">
                        <label class="block text-xs font-bold uppercase text-slate-700">Keterangan / Rincian Prestasi (opsional)</label>
                        <textarea
                            v-model="form.description"
                            rows="2"
                            placeholder="Catatan apresiasi, ucapan selamat, atau alasan pemberian reward..."
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

            <!-- SEARCH & FILTER TOOLBAR -->
            <div class="flex flex-col sm:flex-row items-center justify-between gap-3 bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs">
                <!-- CATEGORY TABS -->
                <div class="flex items-center gap-1.5 overflow-x-auto w-full sm:w-auto pb-1 sm:pb-0">
                    <button
                        @click="selectedCategory = 'ALL'"
                        type="button"
                        :class="[
                            'px-3.5 py-1.5 rounded-xl text-xs font-extrabold transition-all whitespace-nowrap',
                            selectedCategory === 'ALL' ? 'bg-amber-600 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'
                        ]"
                    >
                        Semua Kategori ({{ rewards.length }})
                    </button>
                    <button
                        v-for="opt in typeOptions"
                        :key="opt.value"
                        @click="selectedCategory = opt.value"
                        type="button"
                        :class="[
                            'px-3 py-1.5 rounded-xl text-xs font-extrabold transition-all whitespace-nowrap',
                            selectedCategory === opt.value ? 'bg-amber-600 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'
                        ]"
                    >
                        {{ opt.label }}
                    </button>
                </div>

                <!-- SEARCH INPUT -->
                <div class="relative w-full sm:w-64">
                    <Search class="absolute left-3 top-2.5 h-4 w-4 text-slate-400" />
                    <input
                        v-model="searchQuery"
                        type="text"
                        placeholder="Cari penerima / reward..."
                        class="w-full rounded-xl border border-slate-200 bg-slate-50/70 pl-9 pr-3 py-2 text-xs outline-none focus:border-amber-500 focus:bg-white transition"
                    />
                </div>
            </div>

            <!-- REWARD CARDS GRID -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <div v-if="filteredRewards.length === 0" class="col-span-full rounded-2xl border border-slate-200 bg-white p-10 text-center text-slate-400">
                    <Trophy class="mx-auto h-12 w-12 text-slate-300 mb-2" />
                    <p class="text-sm font-bold text-slate-600">Belum ada penghargaan pada kategori ini.</p>
                    <p class="text-xs text-slate-400 mt-1">Data apresiasi dan prestasi yang ditambahkan akan muncul di sini.</p>
                </div>

                <div
                    v-for="item in filteredRewards"
                    :key="item.id"
                    class="overflow-hidden rounded-2xl bg-white shadow-xs border border-slate-200/80 hover:shadow-md transition flex flex-col justify-between group"
                >
                    <div>
                        <!-- REWARD IMAGE (IF PRESENT) -->
                        <div v-if="item.image_url" class="h-40 w-full overflow-hidden bg-slate-100 border-b border-slate-100 relative">
                            <img :src="item.image_url" :alt="item.title" class="h-full w-full object-cover group-hover:scale-105 transition duration-300" />
                        </div>

                        <div class="p-5 space-y-3.5">
                            <div class="flex items-center justify-between gap-2">
                                <div :class="['flex h-10 w-10 items-center justify-center rounded-xl border', (typeMeta[item.type] || typeMeta.safety).color]">
                                    <component :is="(typeMeta[item.type] || typeMeta.safety).icon" class="h-5 w-5" />
                                </div>
                                <span :class="['rounded-full px-3 py-0.5 text-[10px] font-extrabold uppercase', (typeMeta[item.type] || typeMeta.safety).badge]">
                                    {{ item.category }}
                                </span>
                            </div>

                            <div>
                                <h3 class="m-0 text-base font-black text-slate-900 leading-snug">
                                    {{ item.title }}
                                </h3>
                                <p v-if="item.description" class="m-0 mt-2 text-xs text-slate-600 leading-relaxed line-clamp-3">
                                    {{ item.description }}
                                </p>
                            </div>

                            <!-- PENERIMA BADGE -->
                            <div class="rounded-xl border border-amber-200/70 bg-amber-50/50 p-3 flex items-center gap-3">
                                <div class="w-9 h-9 rounded-full overflow-hidden bg-white border border-amber-200 flex-shrink-0 flex items-center justify-center text-amber-700 font-black text-xs shadow-2xs">
                                    <img v-if="item.recipient_foto" :src="item.recipient_foto" :alt="item.recipient" class="w-full h-full object-cover" />
                                    <span v-else>{{ item.recipient ? item.recipient.charAt(0).toUpperCase() : 'U' }}</span>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <div class="text-[10px] uppercase font-bold text-amber-700 tracking-wider">Dianugerahkan Kepada:</div>
                                    <div class="text-xs font-black text-slate-900 truncate">{{ item.recipient }}</div>
                                    <div v-if="item.recipient_nrp" class="text-[10px] text-slate-500 truncate">
                                        NRP: {{ item.recipient_nrp }} &bull; {{ item.recipient_jabatan || '-' }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- FOOTER -->
                    <div class="flex items-center justify-between border-t border-slate-100 px-5 py-3 text-xs font-semibold text-slate-500 bg-slate-50/50">
                        <span class="flex items-center gap-1.5 text-[11px]">
                            <Calendar class="h-3.5 w-3.5 text-slate-400" /> {{ item.date_formatted }}
                        </span>

                        <button
                            v-if="canManage"
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
