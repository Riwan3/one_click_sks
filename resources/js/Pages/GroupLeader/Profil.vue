<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3';
import { 
    ArrowLeft, CheckCircle2, Circle, UserRound, Phone, MapPin, Calendar, 
    Award, Wrench, Table, FileText, UserCheck, ShieldAlert, Camera, 
    KeyRound, Printer, MessageCircle, Mail, Clock, Users, AlertTriangle, 
    Trash2, ZoomIn, X, ChevronRight, Check, History, AlertCircle, Pencil,
    CalendarCheck
} from 'lucide-vue-next';
import TopNav from './Partials/TopNav.vue';
import BottomNav from './Partials/BottomNav.vue';

const props = defineProps({
    user: { type: Object, required: true },
    notifCounts: { type: Object, required: true },
    profile: { type: Object, required: true },
    isOperator: { type: Boolean, default: false },
    navItems: { type: Array, required: true },
    accentClass: { type: String, default: 'text-blue-500' },
});

const page = usePage();
const flashSuccess = computed(() => page.props.flash?.success);
const flashError = computed(() => page.props.flash?.error);

// Operator Detection
const isOperatorUser = computed(() => {
    return props.isOperator 
        || (props.user?.role && props.user.role.toLowerCase() === 'operator')
        || (props.profile?.jabatanLengkap && props.profile.jabatanLengkap.toUpperCase().includes('OPERATOR'));
});

// Active Tab in History
const activeTab = ref('atr');
if (!isOperatorUser.value && activeTab.value === 'hm') {
    activeTab.value = 'atr';
}

// Modals
const showPhotoModal = ref(false);
const showPasswordModal = ref(false);
const showContactModal = ref(false);
const showEditProfileModal = ref(false);
const showLightbox = ref(false);

// Photo Preview
const photoPreview = ref(null);
const fileInput = ref(null);

const photoForm = useForm({
    foto: null,
});

const deletePhotoForm = useForm({});

const passwordForm = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const contactForm = useForm({
    no_telepon: props.profile.telepon && props.profile.telepon !== '-' ? props.profile.telepon : '',
    email: props.profile.email || '',
});

const profileForm = useForm({
    nama: props.profile.nama || props.user.displayName || '',
    unit: props.profile.unit && props.profile.unit !== '-' ? props.profile.unit : '',
    lokasi_kerja: props.profile.lokasi && props.profile.lokasi !== '-' ? props.profile.lokasi : '',
    no_telepon: props.profile.telepon && props.profile.telepon !== '-' ? props.profile.telepon : '',
    email: props.profile.email || '',
    efektif_bekerja: props.profile.efektif_bekerja || '',
    catatan: props.profile.rawCatatan || '',
});

const openEditProfileModal = () => {
    profileForm.nama = props.profile.nama || props.user.displayName || '';
    profileForm.unit = props.profile.unit && props.profile.unit !== '-' ? props.profile.unit : '';
    profileForm.lokasi_kerja = props.profile.lokasi && props.profile.lokasi !== '-' ? props.profile.lokasi : '';
    profileForm.no_telepon = props.profile.telepon && props.profile.telepon !== '-' ? props.profile.telepon : '';
    profileForm.email = props.profile.email || '';
    profileForm.efektif_bekerja = props.profile.efektif_bekerja || '';
    profileForm.catatan = props.profile.rawCatatan || '';
    showEditProfileModal.value = true;
};

const submitProfile = () => {
    profileForm.put('/profil', {
        preserveScroll: true,
        onSuccess: () => {
            showEditProfileModal.value = false;
        },
    });
};

const handleFileChange = (e) => {
    const file = e.target.files[0];
    if (file) {
        photoForm.foto = file;
        const reader = new FileReader();
        reader.onload = (ev) => {
            photoPreview.value = ev.target.result;
        };
        reader.readAsDataURL(file);
    }
};

const submitPhoto = () => {
    if (!photoForm.foto) return;
    photoForm.post('/profil/foto', {
        preserveScroll: true,
        onSuccess: () => {
            showPhotoModal.value = false;
            photoPreview.value = null;
            photoForm.reset();
        },
    });
};

const deletePhoto = () => {
    if (confirm('Yakin ingin menghapus foto profil?')) {
        deletePhotoForm.delete('/profil/foto', {
            preserveScroll: true,
            onSuccess: () => {
                showPhotoModal.value = false;
                photoPreview.value = null;
            },
        });
    }
};

const submitPassword = () => {
    passwordForm.post('/profil/password', {
        preserveScroll: true,
        onSuccess: () => {
            showPasswordModal.value = false;
            passwordForm.reset();
        },
    });
};

const submitContact = () => {
    contactForm.put('/profil/kontak', {
        preserveScroll: true,
        onSuccess: () => {
            showContactModal.value = false;
        },
    });
};

const changeYear = (year) => {
    router.get('/profil', { tahun: year }, {
        preserveState: true,
        preserveScroll: true,
    });
};

const isEmptyValue = (value) => !value || value === '-' || value === 'BELUM ADA';
</script>

<template>
    <Head title="Profil Karyawan - OneClick" />

    <div class="min-h-screen bg-slate-100 pb-28 pt-[84px] text-slate-800">
        <TopNav :user="user" :notif-counts="notifCounts" />

        <div class="mx-auto max-w-md md:max-w-4xl lg:max-w-5xl px-4 space-y-4">
            
            <!-- FLASH MESSAGES -->
            <div v-if="flashSuccess" class="flex items-center justify-between gap-2 p-4 rounded-xl bg-emerald-500 text-white shadow-md text-sm font-semibold transition-all">
                <div class="flex items-center gap-2">
                    <CheckCircle2 class="w-5 h-5 flex-shrink-0" />
                    <span>{{ flashSuccess }}</span>
                </div>
                <button @click="page.props.flash.success = null" class="p-1 hover:bg-emerald-600 rounded-lg">
                    <X class="w-4 h-4" />
                </button>
            </div>

            <div v-if="flashError" class="flex items-center justify-between gap-2 p-4 rounded-xl bg-rose-500 text-white shadow-md text-sm font-semibold transition-all">
                <div class="flex items-center gap-2">
                    <AlertCircle class="w-5 h-5 flex-shrink-0" />
                    <span>{{ flashError }}</span>
                </div>
                <button @click="page.props.flash.error = null" class="p-1 hover:bg-rose-600 rounded-lg">
                    <X class="w-4 h-4" />
                </button>
            </div>

            <!-- BACK BUTTON -->
            <div class="flex items-center justify-between">
                <Link 
                    href="/portal" 
                    :class="[
                        'inline-flex h-9 w-9 items-center justify-center rounded-full bg-white shadow-xs transition border border-slate-200/80',
                        isOperator ? 'hover:bg-rose-50 hover:text-[#7B112C]' : 'hover:bg-blue-50 hover:text-blue-600'
                    ]"
                >
                    <ArrowLeft class="h-4 w-4 text-slate-700" />
                </Link>

                <div class="text-xs font-bold text-slate-500 uppercase tracking-wider">
                    Profil Karyawan
                </div>
            </div>

            <!-- MAIN PROFILE CARD -->
            <div class="overflow-hidden rounded-2xl bg-white shadow-sm border border-slate-200/80 relative">
                
                <!-- LUBANG STACK INDICATOR (LEGACY PARITY) -->
                <div 
                    v-if="profile.activeLubang > 0" 
                    class="absolute top-4 right-4 z-10 flex flex-col items-end gap-1"
                    title="Lubang Aktif"
                >
                    <div class="flex items-center gap-1.5 px-3 py-1 bg-black/80 backdrop-blur-sm text-white rounded-full text-[11px] font-black shadow-lg">
                        <span class="w-2.5 h-2.5 rounded-full bg-rose-500 animate-pulse"></span>
                        {{ profile.activeLubang }} LUBANG
                    </div>
                </div>

                <!-- COVER BANNER -->
                <div 
                    class="relative h-28 md:h-36 transition-all"
                    :class="isOperator ? 'bg-gradient-to-r from-[#7B112C] via-[#941738] to-[#5A0B1E]' : 'bg-gradient-to-r from-blue-700 via-blue-600 to-indigo-700'"
                >
                    <!-- AVATAR CONTAINER -->
                    <div class="absolute inset-x-0 -bottom-12 md:-bottom-14 flex justify-center md:justify-start md:pl-8">
                        <div class="relative group">
                            <!-- Real Avatar Image or Icon -->
                            <div 
                                @click="profile.foto ? (showLightbox = true) : (showPhotoModal = true)"
                                class="h-24 w-24 md:h-28 md:w-28 rounded-3xl bg-white shadow-lg border-4 border-white overflow-hidden cursor-pointer flex items-center justify-center transition hover:opacity-95"
                            >
                                <img 
                                    v-if="profile.foto" 
                                    :src="profile.foto" 
                                    :alt="profile.nama" 
                                    class="w-full h-full object-cover"
                                />
                                <div v-else class="w-full h-full flex items-center justify-center bg-slate-100">
                                    <UserRound 
                                        class="h-12 w-12" 
                                        :class="isOperator ? 'text-[#7B112C]' : 'text-blue-600'" 
                                    />
                                </div>
                            </div>

                            <!-- Camera Button to Upload/Change Photo -->
                            <button 
                                @click.stop="showPhotoModal = true"
                                class="absolute -bottom-1 -right-1 h-8 w-8 rounded-full bg-slate-900 text-white shadow-md flex items-center justify-center hover:bg-black transition border-2 border-white"
                                title="Ubah Foto Profil"
                            >
                                <Camera class="w-4 h-4" />
                            </button>
                        </div>
                    </div>
                </div>

                <!-- PROFILE HEADER INFO & ACTIONS -->
                <div class="px-5 pb-5 pt-14 md:pt-16 md:px-8 border-b border-slate-100">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                        <div class="text-center md:text-left">
                            <h1 class="m-0 text-xl md:text-2xl font-black uppercase text-slate-800 tracking-tight">
                                {{ profile.nama || user.displayName }}
                            </h1>
                            <p class="m-0 mt-1 text-xs md:text-sm font-bold uppercase tracking-wide text-slate-500">
                                {{ profile.jabatanLengkap }}
                            </p>
                        </div>

                        <!-- SP STATUS BADGE -->
                        <div class="flex flex-col items-center md:items-end gap-1">
                            <span 
                                class="inline-flex items-center gap-1.5 rounded-full px-4 py-1.5 text-xs font-bold uppercase text-white shadow-xs"
                                :class="profile.statusSp === 'TIDAK ADA SP AKTIF' ? 'bg-emerald-500' : 'bg-rose-500'"
                            >
                                <CheckCircle2 v-if="profile.statusSp === 'TIDAK ADA SP AKTIF'" class="h-4 w-4" />
                                <ShieldAlert v-else class="h-4 w-4" />
                                {{ profile.statusSp }}
                            </span>
                            <div v-if="profile.activeSpDetail" class="text-[11px] font-bold text-rose-600 text-center md:text-right">
                                Berlaku s/d: {{ profile.activeSpDetail.berlaku_sampai }} ({{ profile.activeSpDetail.nomor }})
                            </div>
                        </div>
                    </div>

                    <!-- QUICK ACTION BUTTONS -->
                    <div class="mt-5 pt-4 border-t border-slate-100 flex flex-wrap gap-2 justify-center md:justify-start">
                        <!-- UPDATE DATA PROFIL (PRIMARY) -->
                        <button 
                            @click="openEditProfileModal" 
                            class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-black uppercase text-white shadow-sm transition"
                            :class="isOperator ? 'bg-[#7B112C] hover:bg-[#5A0B1E]' : 'bg-blue-600 hover:bg-blue-700'"
                        >
                            <Pencil class="w-3.5 h-3.5" />
                            <span>Update Data Profil</span>
                        </button>

                        <!-- Print / Cetak Data Diri -->
                        <a 
                            v-if="profile.printUrl" 
                            :href="profile.printUrl" 
                            target="_blank" 
                            class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-bold bg-slate-100 text-slate-700 hover:bg-slate-200 transition shadow-2xs border border-slate-200/60"
                        >
                            <Printer class="w-3.5 h-3.5 text-slate-600" />
                            <span>Cetak Data Diri</span>
                        </a>

                        <!-- WhatsApp Atasan Direct Link -->
                        <a 
                            v-if="profile.waSupervisorUrl" 
                            :href="profile.waSupervisorUrl" 
                            target="_blank" 
                            class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-bold bg-emerald-50 text-emerald-700 hover:bg-emerald-100 transition shadow-2xs border border-emerald-200/60"
                        >
                            <MessageCircle class="w-3.5 h-3.5 text-emerald-600" />
                            <span>WhatsApp Atasan</span>
                        </a>

                        <!-- Ganti Password -->
                        <button 
                            @click="showPasswordModal = true" 
                            class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-bold bg-slate-100 text-slate-700 hover:bg-slate-200 transition shadow-2xs border border-slate-200/60"
                        >
                            <KeyRound class="w-3.5 h-3.5 text-slate-600" />
                            <span>Ganti Password</span>
                        </button>
                    </div>
                </div>

                <!-- GRID DETAIL INFORMASI KARYAWAN -->
                <div class="p-5 md:p-8 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 text-sm">
                    
                    <!-- KELOMPOK 1: IDENTITAS & AREA -->
                    <div class="space-y-4 bg-slate-50/70 p-4 rounded-xl border border-slate-100">
                        <h3 
                            class="text-xs font-extrabold uppercase flex items-center gap-1.5"
                            :class="isOperator ? 'text-[#7B112C]' : 'text-blue-600'"
                        >
                            <UserCheck class="w-4 h-4" /> Identitas &amp; Unit Kerja
                        </h3>

                        <div class="space-y-3 text-xs">
                            <div>
                                <p class="text-[0.65rem] font-bold uppercase tracking-wide text-slate-400">NRP</p>
                                <p class="font-bold text-slate-800 flex items-center gap-1.5 mt-0.5 font-mono">
                                    <Circle class="h-2 w-2 fill-emerald-500 text-emerald-500" /> {{ profile.nrp }}
                                </p>
                            </div>
                            <div>
                                <p class="text-[0.65rem] font-bold uppercase tracking-wide text-slate-400">Unit Kerja</p>
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
                        <h3 
                            class="text-xs font-extrabold uppercase flex items-center gap-1.5"
                            :class="isOperator ? 'text-[#7B112C]' : 'text-blue-600'"
                        >
                            <Phone class="w-4 h-4" /> Atasan &amp; Kontak
                        </h3>

                        <div class="space-y-3 text-xs">
                            <div>
                                <p class="text-[0.65rem] font-bold uppercase tracking-wide text-slate-400">Atasan Langsung</p>
                                <p :class="['font-bold mt-0.5', isEmptyValue(profile.atasan) ? 'text-rose-600' : 'text-slate-800']">
                                    {{ profile.atasan }}
                                </p>
                            </div>
                            <div>
                                <p class="text-[0.65rem] font-bold uppercase tracking-wide text-slate-400">Jabatan Atasan</p>
                                <p class="font-bold text-slate-700 mt-0.5">{{ profile.atasanJabatan || 'GROUP LEADER PRODUCTION' }}</p>
                            </div>
                            <div>
                                <p class="text-[0.65rem] font-bold uppercase tracking-wide text-slate-400">No. HP / Email</p>
                                <p class="font-bold text-slate-800 mt-0.5 flex flex-wrap items-center gap-2">
                                    <span>{{ profile.telepon || '-' }}</span>
                                    <span v-if="profile.email" class="text-slate-400 font-normal">({{ profile.email }})</span>
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- KELOMPOK 3: STATUS & MASA KERJA -->
                    <div class="space-y-4 bg-slate-50/70 p-4 rounded-xl border border-slate-100 md:col-span-2 lg:col-span-1">
                        <h3 
                            class="text-xs font-extrabold uppercase flex items-center gap-1.5"
                            :class="isOperator ? 'text-[#7B112C]' : 'text-blue-600'"
                        >
                            <Calendar class="w-4 h-4" /> Status &amp; Masa Kerja
                        </h3>

                        <div class="space-y-3 text-xs">
                            <div>
                                <p class="text-[0.65rem] font-bold uppercase tracking-wide text-slate-400">Status Karyawan</p>
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

                <!-- CATATAN OPERASIONAL -->
                <div class="p-5 md:p-6 border-t border-slate-100 bg-slate-50/40">
                    <p 
                        class="mb-2.5 text-xs font-extrabold uppercase flex items-center gap-1.5 tracking-wider"
                        :class="isOperator ? 'text-[#7B112C]' : 'text-blue-600'"
                    >
                        <FileText class="w-4 h-4" /> Catatan Karyawan
                    </p>
                    <div class="rounded-xl border border-blue-100 bg-blue-50/50 p-4 text-xs text-slate-700 leading-relaxed flex items-start gap-3">
                        <div class="p-1.5 rounded-lg bg-blue-100 text-blue-600 flex-shrink-0 mt-0.5">
                            <FileText class="w-3.5 h-3.5" />
                        </div>
                        <div class="flex-1 italic font-medium">
                            {{ profile.catatan }}
                        </div>
                    </div>
                </div>

                <!-- DAFTAR MESIN YANG BISA DIOPERASIKAN (VERSATILITY) -->
                <div class="p-5 md:p-6 border-t border-slate-100">
                    <p 
                        class="mb-2.5 text-xs font-extrabold uppercase flex items-center gap-1.5 tracking-wider"
                        :class="isOperator ? 'text-[#7B112C]' : 'text-blue-600'"
                    >
                        <Wrench class="w-4 h-4" /> DAFTAR MESIN / ALAT YANG DIOPERASIKAN
                    </p>
                    
                    <div v-if="profile.daftarMesin && profile.daftarMesin.length > 0" class="flex flex-wrap gap-2">
                        <span 
                            v-for="m in profile.daftarMesin" 
                            :key="m" 
                            class="px-3.5 py-1.5 font-bold text-xs rounded-xl border shadow-2xs"
                            :class="isOperator ? 'bg-rose-50 text-[#7B112C] border-rose-200' : 'bg-blue-50 text-blue-700 border-blue-100'"
                        >
                            {{ m }}
                        </span>
                    </div>
                    <div v-else class="rounded-xl border border-dashed border-slate-200 bg-slate-50/70 p-4 text-xs text-slate-400 flex items-center justify-center gap-2">
                        <Wrench class="w-4 h-4 text-slate-300" />
                        <span>Belum ada data mesin atau alat berat yang terdaftar untuk dioperasikan.</span>
                    </div>
                </div>

                <!-- DAFTAR ANAK ASUH (IF GROUP LEADER) -->
                <div v-if="profile.anakAsuh && profile.anakAsuh.length > 0" class="p-5 md:p-8 border-t border-slate-100 bg-slate-50/50">
                    <div class="flex items-center justify-between mb-4">
                        <p 
                            class="text-xs font-extrabold uppercase flex items-center gap-1.5"
                            :class="isOperator ? 'text-[#7B112C]' : 'text-blue-600'"
                        >
                            <Users class="w-4 h-4" /> DAFTAR ANAK ASUH ({{ profile.anakAsuh.length }})
                        </p>
                    </div>

                    <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-slate-100 text-slate-700 uppercase font-black tracking-wider text-[10px] border-b border-slate-200">
                                <tr>
                                    <th class="px-4 py-3">Nama / NRP</th>
                                    <th class="px-4 py-3">Jabatan</th>
                                    <th class="px-4 py-3">Unit</th>
                                    <th class="px-4 py-3 text-center">Status</th>
                                    <th class="px-4 py-3 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 font-medium">
                                <tr v-for="asuh in profile.anakAsuh" :key="asuh.id" class="hover:bg-slate-50 transition">
                                    <td class="px-4 py-3">
                                        <div class="flex items-center gap-2.5">
                                            <div class="w-8 h-8 rounded-full overflow-hidden bg-slate-100 flex-shrink-0 flex items-center justify-center border">
                                                <img v-if="asuh.foto" :src="asuh.foto" class="w-full h-full object-cover" />
                                                <UserRound v-else class="w-4 h-4 text-slate-400" />
                                            </div>
                                            <div>
                                                <div class="font-bold text-slate-800 uppercase">{{ asuh.name }}</div>
                                                <div class="text-[11px] text-slate-400 font-mono">{{ asuh.nrp }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-slate-600 uppercase">{{ asuh.jabatan }}</td>
                                    <td class="px-4 py-3 text-slate-600">{{ asuh.unit }}</td>
                                    <td class="px-4 py-3 text-center">
                                        <span class="px-2 py-0.5 text-[10px] font-bold rounded-md bg-emerald-100 text-emerald-700">
                                            {{ asuh.status }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <a 
                                            :href="asuh.printUrl" 
                                            target="_blank" 
                                            class="inline-flex items-center gap-1 px-2.5 py-1 text-[11px] font-bold rounded-lg border border-slate-300 hover:bg-slate-100 text-slate-700"
                                        >
                                            <Printer class="w-3 h-3" /> Cetak
                                        </a>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>

            <!-- SECTION RIWAYAT / HISTORI BERDASARKAN TAHUN -->
            <div class="overflow-hidden rounded-2xl bg-white shadow-sm border border-slate-200/80">
                
                <!-- FILTER TAHUN BAR -->
                <div class="p-4 md:p-5 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-white">
                    <div class="flex items-center gap-2.5">
                        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-blue-50 text-blue-600">
                            <History class="w-4 h-4" />
                        </div>
                        <div>
                            <span class="text-xs font-black uppercase tracking-wider text-slate-800">Riwayat &amp; Rekam Jejak</span>
                            <p class="text-[11px] text-slate-400">Arsip data performa &amp; pembinaan tahun {{ profile.tahun }}</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        <span class="text-[11px] font-bold text-slate-400">TAHUN:</span>
                        <div class="inline-flex rounded-xl bg-slate-100 p-1 border border-slate-200/60">
                            <button 
                                v-for="y in profile.availableYears" 
                                :key="y"
                                @click="changeYear(y)"
                                type="button"
                                :class="[
                                    'px-3.5 py-1 rounded-lg text-xs font-black transition-all',
                                    profile.tahun === y 
                                        ? (isOperator ? 'bg-[#7B112C] text-white shadow-xs' : 'bg-blue-600 text-white shadow-xs') 
                                        : 'text-slate-600 hover:text-slate-900 hover:bg-slate-200/60'
                                ]"
                            >
                                {{ y }}
                            </button>
                        </div>
                    </div>
                </div>

                <!-- RESPONSIVE TAB CARDS (GRID - NEVER OVERFLOW OR TRUNCATE) -->
                <div 
                    class="grid grid-cols-2 sm:grid-cols-3 gap-2 p-3 bg-slate-50/70 border-b border-slate-100"
                    :class="isOperatorUser ? 'lg:grid-cols-6' : 'lg:grid-cols-5'"
                >
                    <!-- Tab ATR -->
                    <button 
                        @click="activeTab = 'atr'" 
                        type="button"
                        :class="[
                            'flex flex-col items-center justify-center p-3 rounded-xl border text-center transition-all cursor-pointer relative',
                            activeTab === 'atr' 
                                ? 'bg-white border-emerald-500 shadow-sm ring-2 ring-emerald-500/20 text-slate-900' 
                                : 'bg-white/70 border-slate-200/80 hover:bg-white hover:border-slate-300 text-slate-600'
                        ]"
                    >
                        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-100 text-emerald-700 mb-1.5">
                            <CalendarCheck class="w-4 h-4" />
                        </div>
                        <span class="text-xs font-extrabold truncate w-full">Kehadiran ATR</span>
                        <span 
                            class="mt-1 px-2 py-0.5 rounded-full text-[10px] font-bold"
                            :class="profile.atrPerBulan.length > 0 ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-500'"
                        >
                            {{ profile.atrPerBulan.length }} Bulan
                        </span>
                        <div v-if="activeTab === 'atr'" class="absolute -bottom-[13px] left-1/2 -translate-x-1/2 w-5 h-1 bg-emerald-500 rounded-full"></div>
                    </button>

                    <!-- Tab HM (Khusus Role Operator) -->
                    <button 
                        v-if="isOperatorUser"
                        @click="activeTab = 'hm'" 
                        type="button"
                        :class="[
                            'flex flex-col items-center justify-center p-3 rounded-xl border text-center transition-all cursor-pointer relative',
                            activeTab === 'hm' 
                                ? 'bg-white border-blue-500 shadow-sm ring-2 ring-blue-500/20 text-slate-900' 
                                : 'bg-white/70 border-slate-200/80 hover:bg-white hover:border-slate-300 text-slate-600'
                        ]"
                    >
                        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-blue-100 text-blue-700 mb-1.5">
                            <Clock class="w-4 h-4" />
                        </div>
                        <span class="text-xs font-extrabold truncate w-full">Jam Kerja (HM)</span>
                        <span 
                            class="mt-1 px-2 py-0.5 rounded-full text-[10px] font-bold"
                            :class="profile.hmList.length > 0 ? 'bg-blue-100 text-blue-800' : 'bg-slate-100 text-slate-500'"
                        >
                            {{ profile.hmList.length }} Data
                        </span>
                        <div v-if="activeTab === 'hm'" class="absolute -bottom-[13px] left-1/2 -translate-x-1/2 w-5 h-1 bg-blue-500 rounded-full"></div>
                    </button>

                    <!-- Tab Coaching -->
                    <button 
                        @click="activeTab = 'coaching'" 
                        type="button"
                        :class="[
                            'flex flex-col items-center justify-center p-3 rounded-xl border text-center transition-all cursor-pointer relative',
                            activeTab === 'coaching' 
                                ? 'bg-white border-amber-500 shadow-sm ring-2 ring-amber-500/20 text-slate-900' 
                                : 'bg-white/70 border-slate-200/80 hover:bg-white hover:border-slate-300 text-slate-600'
                        ]"
                    >
                        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-amber-100 text-amber-700 mb-1.5">
                            <Users class="w-4 h-4" />
                        </div>
                        <span class="text-xs font-extrabold truncate w-full">Coaching</span>
                        <span 
                            class="mt-1 px-2 py-0.5 rounded-full text-[10px] font-bold"
                            :class="profile.coachingList.length > 0 ? 'bg-amber-100 text-amber-800' : 'bg-slate-100 text-slate-500'"
                        >
                            {{ profile.coachingList.length }} Sesi
                        </span>
                        <div v-if="activeTab === 'coaching'" class="absolute -bottom-[13px] left-1/2 -translate-x-1/2 w-5 h-1 bg-amber-500 rounded-full"></div>
                    </button>

                    <!-- Tab SK Pembinaan -->
                    <button 
                        @click="activeTab = 'sk'" 
                        type="button"
                        :class="[
                            'flex flex-col items-center justify-center p-3 rounded-xl border text-center transition-all cursor-pointer relative',
                            activeTab === 'sk' 
                                ? 'bg-white border-indigo-500 shadow-sm ring-2 ring-indigo-500/20 text-slate-900' 
                                : 'bg-white/70 border-slate-200/80 hover:bg-white hover:border-slate-300 text-slate-600'
                        ]"
                    >
                        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-indigo-100 text-indigo-700 mb-1.5">
                            <FileText class="w-4 h-4" />
                        </div>
                        <span class="text-xs font-extrabold truncate w-full">SK Pembinaan</span>
                        <span 
                            class="mt-1 px-2 py-0.5 rounded-full text-[10px] font-bold"
                            :class="profile.skList.length > 0 ? 'bg-indigo-100 text-indigo-800' : 'bg-slate-100 text-slate-500'"
                        >
                            {{ profile.skList.length }} Surat
                        </span>
                        <div v-if="activeTab === 'sk'" class="absolute -bottom-[13px] left-1/2 -translate-x-1/2 w-5 h-1 bg-indigo-500 rounded-full"></div>
                    </button>

                    <!-- Tab SP & Teguran -->
                    <button 
                        @click="activeTab = 'sp'" 
                        type="button"
                        :class="[
                            'flex flex-col items-center justify-center p-3 rounded-xl border text-center transition-all cursor-pointer relative',
                            activeTab === 'sp' 
                                ? 'bg-white border-rose-500 shadow-sm ring-2 ring-rose-500/20 text-slate-900' 
                                : 'bg-white/70 border-slate-200/80 hover:bg-white hover:border-slate-300 text-slate-600'
                        ]"
                    >
                        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-rose-100 text-rose-700 mb-1.5">
                            <AlertTriangle class="w-4 h-4" />
                        </div>
                        <span class="text-xs font-extrabold truncate w-full">SP &amp; Teguran</span>
                        <span 
                            class="mt-1 px-2 py-0.5 rounded-full text-[10px] font-bold"
                            :class="profile.spList.length > 0 ? 'bg-rose-100 text-rose-800' : 'bg-slate-100 text-slate-500'"
                        >
                            {{ profile.spList.length }} Sanksi
                        </span>
                        <div v-if="activeTab === 'sp'" class="absolute -bottom-[13px] left-1/2 -translate-x-1/2 w-5 h-1 bg-rose-500 rounded-full"></div>
                    </button>

                    <!-- Tab Fatigue -->
                    <button 
                        @click="activeTab = 'fatigue'" 
                        type="button"
                        :class="[
                            'flex flex-col items-center justify-center p-3 rounded-xl border text-center transition-all cursor-pointer relative',
                            activeTab === 'fatigue' 
                                ? 'bg-white border-orange-500 shadow-sm ring-2 ring-orange-500/20 text-slate-900' 
                                : 'bg-white/70 border-slate-200/80 hover:bg-white hover:border-slate-300 text-slate-600'
                        ]"
                    >
                        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-orange-100 text-orange-700 mb-1.5">
                            <ShieldAlert class="w-4 h-4" />
                        </div>
                        <span class="text-xs font-extrabold truncate w-full">Deviasi Fatigue</span>
                        <span 
                            class="mt-1 px-2 py-0.5 rounded-full text-[10px] font-bold"
                            :class="profile.fatigueList.length > 0 ? 'bg-orange-100 text-orange-800' : 'bg-slate-100 text-slate-500'"
                        >
                            {{ profile.fatigueList.length }} Temuan
                        </span>
                        <div v-if="activeTab === 'fatigue'" class="absolute -bottom-[13px] left-1/2 -translate-x-1/2 w-5 h-1 bg-orange-500 rounded-full"></div>
                    </button>
                </div>

                <!-- TAB CONTENT 1: ATR -->
                <div v-if="activeTab === 'atr'" class="p-5 md:p-8 space-y-6">
                    <!-- ATR Badges -->
                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <p class="text-xs font-black uppercase text-slate-700">ATR Bulanan Tahun {{ profile.tahun }}</p>
                            <span class="text-xs font-bold text-emerald-600">Rata-rata: {{ profile.atrSummary.avgAtr }}</span>
                        </div>
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

                    <!-- ATR Detail Table -->
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
                                    <th class="px-4 py-3 text-center">A (Alpa)</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 font-medium">
                                <tr v-for="row in profile.atrDetailTable" :key="row.bulan" class="hover:bg-slate-50 transition">
                                    <td class="px-4 py-3 font-bold text-slate-800">{{ row.bulan }}</td>
                                    <td class="px-4 py-3 text-center">
                                        <span class="px-2 py-0.5 bg-emerald-100 text-emerald-700 font-bold rounded-md text-[11px]">
                                            {{ row.atr }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-center font-bold" :class="isOperator ? 'text-[#7B112C]' : 'text-blue-600'">{{ row.d }}</td>
                                    <td class="px-4 py-3 text-center font-bold text-indigo-600">{{ row.n }}</td>
                                    <td class="px-4 py-3 text-center text-slate-500">{{ row.s }}</td>
                                    <td class="px-4 py-3 text-center text-slate-500">{{ row.i }}</td>
                                    <td class="px-4 py-3 text-center" :class="row.a > 0 ? 'text-rose-600 font-bold' : 'text-slate-500'">{{ row.a }}</td>
                                </tr>
                            </tbody>
                            <tfoot class="bg-slate-50 text-slate-800 font-bold border-t border-slate-200 text-xs">
                                <tr>
                                    <td class="px-4 py-3 uppercase">Total Tahun {{ profile.tahun }}</td>
                                    <td class="px-4 py-3 text-center text-emerald-700 font-black">{{ profile.atrSummary.avgAtr }}</td>
                                    <td class="px-4 py-3 text-center">{{ profile.atrSummary.totalD }}</td>
                                    <td class="px-4 py-3 text-center">{{ profile.atrSummary.totalN }}</td>
                                    <td class="px-4 py-3 text-center">{{ profile.atrSummary.totalS }}</td>
                                    <td class="px-4 py-3 text-center">{{ profile.atrSummary.totalI }}</td>
                                    <td class="px-4 py-3 text-center" :class="profile.atrSummary.totalA > 0 ? 'text-rose-600 font-black' : ''">{{ profile.atrSummary.totalA }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                <!-- TAB CONTENT 2: HM (Khusus Role Operator) -->
                <div v-if="isOperatorUser && activeTab === 'hm'" class="p-5 md:p-8 space-y-4">
                    <div class="flex items-center justify-between">
                        <p class="text-xs font-black uppercase text-slate-700">Akumulasi Jam Kerja HM ({{ profile.tahun }})</p>
                        <div class="flex items-center gap-3 text-xs">
                            <span class="font-bold text-blue-600">Total HM: {{ profile.hmSummary.totalHm }} jam</span>
                            <span class="font-bold text-slate-600">Total Shift: {{ profile.hmSummary.totalShifts }}</span>
                        </div>
                    </div>

                    <div v-if="profile.hmList && profile.hmList.length > 0" class="overflow-x-auto rounded-xl border border-slate-200">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-slate-100 text-slate-700 uppercase font-black tracking-wider text-[10px] border-b border-slate-200">
                                <tr>
                                    <th class="px-4 py-3">Bulan</th>
                                    <th class="px-4 py-3 text-center">Total HM (Jam)</th>
                                    <th class="px-4 py-3 text-center">Jumlah Shift</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 font-medium">
                                <tr v-for="hm in profile.hmList" :key="hm.bulan" class="hover:bg-slate-50 transition">
                                    <td class="px-4 py-3 font-bold text-slate-800">{{ hm.bulan }}</td>
                                    <td class="px-4 py-3 text-center font-bold text-blue-600">{{ hm.total_hm }} jam</td>
                                    <td class="px-4 py-3 text-center text-slate-600">{{ hm.total_shifts }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div v-else class="rounded-xl border border-slate-200 bg-slate-50 p-6 text-center text-xs text-slate-500">
                        Tidak ada data jam kerja HM untuk tahun {{ profile.tahun }}.
                    </div>
                </div>

                <!-- TAB CONTENT 3: COACHING & COUNSELING -->
                <div v-if="activeTab === 'coaching'" class="p-5 md:p-8 space-y-4">
                    <p class="text-xs font-black uppercase text-slate-700">Riwayat Coaching &amp; Counseling ({{ profile.tahun }})</p>

                    <div v-if="profile.coachingList && profile.coachingList.length > 0" class="space-y-3">
                        <div 
                            v-for="cc in profile.coachingList" 
                            :key="cc.id" 
                            class="p-4 rounded-xl border border-slate-200 bg-white hover:border-slate-300 transition shadow-2xs space-y-2"
                        >
                            <div class="flex items-center justify-between flex-wrap gap-2">
                                <div class="flex items-center gap-2">
                                    <span class="px-2.5 py-0.5 rounded-md bg-blue-100 text-blue-700 text-[11px] font-bold">{{ cc.tanggal }}</span>
                                    <span class="text-xs font-bold text-slate-800">{{ cc.topik }}</span>
                                </div>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-700">
                                    {{ cc.status }}
                                </span>
                            </div>
                            <div class="text-xs text-slate-600 bg-slate-50 p-3 rounded-lg border border-slate-100 space-y-1">
                                <div v-if="cc.deskripsi && cc.deskripsi !== '-'"><span class="font-bold text-slate-700">Deskripsi:</span> {{ cc.deskripsi }}</div>
                                <div v-if="cc.rekomendasi && cc.rekomendasi !== '-'"><span class="font-bold text-slate-700">Rekomendasi:</span> {{ cc.rekomendasi }}</div>
                                <div v-if="cc.lokasi && cc.lokasi !== '-'"><span class="font-bold text-slate-700">Lokasi:</span> {{ cc.lokasi }}</div>
                            </div>
                            <div v-if="cc.file_bukti" class="text-right">
                                <a :href="cc.file_bukti" target="_blank" class="text-[11px] font-bold text-blue-600 hover:underline">
                                    Lihat Lampiran Bukti &rarr;
                                </a>
                            </div>
                        </div>
                    </div>
                    <div v-else class="rounded-xl border border-slate-200 bg-slate-50 p-6 text-center text-xs text-slate-500">
                        Tidak ada riwayat coaching &amp; counseling untuk tahun {{ profile.tahun }}.
                    </div>
                </div>

                <!-- TAB CONTENT 4: SK PEMBINAAN -->
                <div v-if="activeTab === 'sk'" class="p-5 md:p-8 space-y-4">
                    <p class="text-xs font-black uppercase text-slate-700">Riwayat SK Pembinaan &amp; Sanksi ({{ profile.tahun }})</p>

                    <div v-if="profile.skList && profile.skList.length > 0" class="overflow-x-auto rounded-xl border border-slate-200">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-slate-100 text-slate-700 uppercase font-black tracking-wider text-[10px] border-b border-slate-200">
                                <tr>
                                    <th class="px-4 py-3">Tanggal</th>
                                    <th class="px-4 py-3">No. Surat</th>
                                    <th class="px-4 py-3">Jenis Pelanggaran</th>
                                    <th class="px-4 py-3 text-center">Lubang</th>
                                    <th class="px-4 py-3">Berlaku Sampai</th>
                                    <th class="px-4 py-3 text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 font-medium">
                                <tr v-for="sk in profile.skList" :key="sk.id" class="hover:bg-slate-50 transition">
                                    <td class="px-4 py-3 text-slate-600">{{ sk.tanggal }}</td>
                                    <td class="px-4 py-3 font-mono font-bold text-slate-800">{{ sk.nomor_surat }}</td>
                                    <td class="px-4 py-3">
                                        <div class="text-slate-800">{{ sk.jenis_pelanggaran }}</div>
                                        <span v-if="sk.spdk_ref" class="text-[10px] text-slate-400">Ref: {{ sk.spdk_ref }}</span>
                                    </td>
                                    <td class="px-4 py-3 text-center font-black text-rose-600">{{ sk.lubang }}</td>
                                    <td class="px-4 py-3 text-slate-600">{{ sk.berlaku_sampai }}</td>
                                    <td class="px-4 py-3 text-center">
                                        <span 
                                            class="px-2 py-0.5 rounded-full text-[10px] font-bold"
                                            :class="sk.isActive ? 'bg-rose-100 text-rose-700 font-black' : 'bg-slate-100 text-slate-600'"
                                        >
                                            {{ sk.isActive ? 'AKTIF' : 'SELESAI' }}
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div v-else class="rounded-xl border border-slate-200 bg-slate-50 p-6 text-center text-xs text-slate-500">
                        Tidak ada riwayat SK Pembinaan untuk tahun {{ profile.tahun }}.
                    </div>
                </div>

                <!-- TAB CONTENT 5: SURAT PERINGATAN & TEGURAN -->
                <div v-if="activeTab === 'sp'" class="p-5 md:p-8 space-y-4">
                    <p class="text-xs font-black uppercase text-slate-700">Riwayat SP &amp; Teguran ({{ profile.tahun }})</p>

                    <div v-if="profile.spList && profile.spList.length > 0" class="overflow-x-auto rounded-xl border border-slate-200">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-slate-100 text-slate-700 uppercase font-black tracking-wider text-[10px] border-b border-slate-200">
                                <tr>
                                    <th class="px-4 py-3">Tanggal</th>
                                    <th class="px-4 py-3">Jenis</th>
                                    <th class="px-4 py-3">No. Surat</th>
                                    <th class="px-4 py-3">Pelanggaran</th>
                                    <th class="px-4 py-3">Masa Berlaku</th>
                                    <th class="px-4 py-3 text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 font-medium">
                                <tr v-for="sp in profile.spList" :key="sp.id" class="hover:bg-slate-50 transition">
                                    <td class="px-4 py-3 text-slate-600">{{ sp.tanggal }}</td>
                                    <td class="px-4 py-3 font-bold" :class="sp.isActive ? 'text-rose-600' : 'text-slate-700'">{{ sp.jenis }}</td>
                                    <td class="px-4 py-3 font-mono text-[11px] text-slate-800">{{ sp.nomor }}</td>
                                    <td class="px-4 py-3 text-slate-600">{{ sp.detail }}</td>
                                    <td class="px-4 py-3 text-slate-600">{{ sp.berlaku_sampai }}</td>
                                    <td class="px-4 py-3 text-center">
                                        <span 
                                            class="px-2 py-0.5 rounded-full text-[10px] font-bold"
                                            :class="sp.isActive ? 'bg-rose-100 text-rose-700 font-black' : 'bg-slate-100 text-slate-600'"
                                        >
                                            {{ sp.isActive ? 'AKTIF' : 'BERAKHIR' }}
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div v-else class="rounded-xl border border-slate-200 bg-slate-50 p-6 text-center text-xs text-slate-500">
                        Tidak ada riwayat Surat Peringatan atau Teguran untuk tahun {{ profile.tahun }}.
                    </div>
                </div>

                <!-- TAB CONTENT 6: FATIGUE & K3 -->
                <div v-if="activeTab === 'fatigue'" class="p-5 md:p-8 space-y-4">
                    <p class="text-xs font-black uppercase text-slate-700">Riwayat Deviasi Fatigue &amp; K3 ({{ profile.tahun }})</p>

                    <div v-if="profile.fatigueList && profile.fatigueList.length > 0" class="overflow-x-auto rounded-xl border border-slate-200">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-slate-100 text-slate-700 uppercase font-black tracking-wider text-[10px] border-b border-slate-200">
                                <tr>
                                    <th class="px-4 py-3">Tanggal / Jam</th>
                                    <th class="px-4 py-3">Unit / Area</th>
                                    <th class="px-4 py-3">Kondisi Tubuh</th>
                                    <th class="px-4 py-3">Keterangan / Alasan</th>
                                    <th class="px-4 py-3 text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 font-medium">
                                <tr v-for="f in profile.fatigueList" :key="f.id" class="hover:bg-slate-50 transition">
                                    <td class="px-4 py-3 text-slate-700">
                                        <div class="font-bold">{{ f.tanggal }}</div>
                                        <div class="text-[10px] text-slate-400">{{ f.jam }}</div>
                                    </td>
                                    <td class="px-4 py-3 text-slate-700">
                                        <div class="font-bold">{{ f.unit }}</div>
                                        <div class="text-[10px] text-slate-400">{{ f.area }}</div>
                                    </td>
                                    <td class="px-4 py-3 text-slate-700">{{ f.bodyCondition }}</td>
                                    <td class="px-4 py-3 text-slate-600">{{ f.reason }}</td>
                                    <td class="px-4 py-3 text-center">
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-700">
                                            {{ f.status }}
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div v-else class="rounded-xl border border-slate-200 bg-slate-50 p-6 text-center text-xs text-slate-500">
                        Tidak ada riwayat deviasi fatigue untuk tahun {{ profile.tahun }}.
                    </div>
                </div>

            </div>

        </div>

        <!-- MODAL 1: UPLOAD / HAPUS FOTO PROFIL -->
        <div v-if="showPhotoModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm animate-fade-in">
            <div class="bg-white rounded-2xl max-w-sm w-full p-6 shadow-2xl space-y-4">
                <div class="flex items-center justify-between border-b pb-3">
                    <h3 class="font-black text-sm uppercase text-slate-800 flex items-center gap-2">
                        <Camera class="w-4 h-4 text-slate-600" /> Foto Profil
                    </h3>
                    <button @click="showPhotoModal = false" class="text-slate-400 hover:text-slate-600">
                        <X class="w-5 h-5" />
                    </button>
                </div>

                <div class="space-y-4 text-xs">
                    <!-- Image Preview -->
                    <div class="flex flex-col items-center justify-center">
                        <div class="w-28 h-28 rounded-2xl overflow-hidden border-2 border-dashed border-slate-300 bg-slate-50 flex items-center justify-center mb-3">
                            <img v-if="photoPreview" :src="photoPreview" class="w-full h-full object-cover" />
                            <img v-else-if="profile.foto" :src="profile.foto" class="w-full h-full object-cover" />
                            <UserRound v-else class="w-12 h-12 text-slate-300" />
                        </div>
                        <p class="text-[11px] text-slate-400">Format: JPG, PNG, WEBP (Maksimal 3MB)</p>
                    </div>

                    <input 
                        ref="fileInput" 
                        type="file" 
                        accept="image/jpeg,image/png,image/webp" 
                        @change="handleFileChange" 
                        class="block w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200 cursor-pointer"
                    />

                    <div v-if="photoForm.errors.foto" class="text-rose-500 font-bold text-[11px]">
                        {{ photoForm.errors.foto }}
                    </div>

                    <div class="flex flex-col gap-2 pt-2">
                        <button 
                            @click="submitPhoto" 
                            :disabled="!photoForm.foto || photoForm.processing" 
                            :class="[
                                'w-full py-2.5 rounded-xl font-bold text-xs uppercase text-white shadow-md transition',
                                isOperator ? 'bg-[#7B112C] hover:bg-[#5A0B1E]' : 'bg-blue-600 hover:bg-blue-700',
                                (!photoForm.foto || photoForm.processing) ? 'opacity-50 cursor-not-allowed' : ''
                            ]"
                        >
                            {{ photoForm.processing ? 'Mengunggah...' : 'Simpan Foto Baru' }}
                        </button>

                        <button 
                            v-if="profile.foto" 
                            @click="deletePhoto" 
                            :disabled="deletePhotoForm.processing"
                            class="w-full py-2 rounded-xl font-bold text-xs text-rose-600 hover:bg-rose-50 transition flex items-center justify-center gap-1.5"
                        >
                            <Trash2 class="w-3.5 h-3.5" /> Hapus Foto Saat Ini
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- MODAL 2: GANTI PASSWORD -->
        <div v-if="showPasswordModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm animate-fade-in">
            <div class="bg-white rounded-2xl max-w-sm w-full p-6 shadow-2xl space-y-4">
                <div class="flex items-center justify-between border-b pb-3">
                    <h3 class="font-black text-sm uppercase text-slate-800 flex items-center gap-2">
                        <KeyRound class="w-4 h-4 text-slate-600" /> Ganti Password
                    </h3>
                    <button @click="showPasswordModal = false" class="text-slate-400 hover:text-slate-600">
                        <X class="w-5 h-5" />
                    </button>
                </div>

                <form @submit.prevent="submitPassword" class="space-y-3 text-xs">
                    <div>
                        <label class="block font-bold text-slate-600 mb-1">Password Lama</label>
                        <input 
                            v-model="passwordForm.current_password" 
                            type="password" 
                            required 
                            placeholder="Masukkan password saat ini"
                            class="w-full px-3 py-2 border rounded-xl focus:ring-2 focus:ring-blue-500 outline-none"
                        />
                        <div v-if="passwordForm.errors.current_password" class="text-rose-500 font-bold text-[11px] mt-1">
                            {{ passwordForm.errors.current_password }}
                        </div>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-600 mb-1">Password Baru</label>
                        <input 
                            v-model="passwordForm.password" 
                            type="password" 
                            required 
                            placeholder="Minimal 6 karakter"
                            class="w-full px-3 py-2 border rounded-xl focus:ring-2 focus:ring-blue-500 outline-none"
                        />
                        <div v-if="passwordForm.errors.password" class="text-rose-500 font-bold text-[11px] mt-1">
                            {{ passwordForm.errors.password }}
                        </div>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-600 mb-1">Konfirmasi Password Baru</label>
                        <input 
                            v-model="passwordForm.password_confirmation" 
                            type="password" 
                            required 
                            placeholder="Ketik ulang password baru"
                            class="w-full px-3 py-2 border rounded-xl focus:ring-2 focus:ring-blue-500 outline-none"
                        />
                    </div>

                    <div class="pt-3">
                        <button 
                            type="submit" 
                            :disabled="passwordForm.processing" 
                            :class="[
                                'w-full py-2.5 rounded-xl font-bold text-xs uppercase text-white shadow-md transition',
                                isOperator ? 'bg-[#7B112C] hover:bg-[#5A0B1E]' : 'bg-blue-600 hover:bg-blue-700',
                                passwordForm.processing ? 'opacity-50 cursor-not-allowed' : ''
                            ]"
                        >
                            {{ passwordForm.processing ? 'Memproses...' : 'Ubah Password' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- MODAL 3: EDIT KONTAK (TELEPON & EMAIL) -->
        <div v-if="showContactModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm animate-fade-in">
            <div class="bg-white rounded-2xl max-w-sm w-full p-6 shadow-2xl space-y-4">
                <div class="flex items-center justify-between border-b pb-3">
                    <h3 class="font-black text-sm uppercase text-slate-800 flex items-center gap-2">
                        <Phone class="w-4 h-4 text-slate-600" /> Perbarui Kontak
                    </h3>
                    <button @click="showContactModal = false" class="text-slate-400 hover:text-slate-600">
                        <X class="w-5 h-5" />
                    </button>
                </div>

                <form @submit.prevent="submitContact" class="space-y-3 text-xs">
                    <div>
                        <label class="block font-bold text-slate-600 mb-1">Nomor Telepon / WhatsApp</label>
                        <input 
                            v-model="contactForm.no_telepon" 
                            type="text" 
                            placeholder="Contoh: 081234567890"
                            class="w-full px-3 py-2 border rounded-xl focus:ring-2 focus:ring-blue-500 outline-none font-mono"
                        />
                        <div v-if="contactForm.errors.no_telepon" class="text-rose-500 font-bold text-[11px] mt-1">
                            {{ contactForm.errors.no_telepon }}
                        </div>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-600 mb-1">Email</label>
                        <input 
                            v-model="contactForm.email" 
                            type="email" 
                            placeholder="nama@ppa.co.id"
                            class="w-full px-3 py-2 border rounded-xl focus:ring-2 focus:ring-blue-500 outline-none"
                        />
                        <div v-if="contactForm.errors.email" class="text-rose-500 font-bold text-[11px] mt-1">
                            {{ contactForm.errors.email }}
                        </div>
                    </div>

                    <div class="pt-3">
                        <button 
                            type="submit" 
                            :disabled="contactForm.processing" 
                            :class="[
                                'w-full py-2.5 rounded-xl font-bold text-xs uppercase text-white shadow-md transition',
                                isOperator ? 'bg-[#7B112C] hover:bg-[#5A0B1E]' : 'bg-blue-600 hover:bg-blue-700',
                                contactForm.processing ? 'opacity-50 cursor-not-allowed' : ''
                            ]"
                        >
                            {{ contactForm.processing ? 'Menyimpan...' : 'Simpan Perubahan' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- MODAL 4: UPDATE DATA PROFIL LENGKAP -->
        <div v-if="showEditProfileModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm animate-fade-in">
            <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl space-y-4 max-h-[90vh] overflow-y-auto">
                <div class="flex items-center justify-between border-b pb-3">
                    <h3 class="font-black text-sm uppercase text-slate-800 flex items-center gap-2">
                        <Pencil class="w-4 h-4 text-slate-600" /> Update Data Profil
                    </h3>
                    <button @click="showEditProfileModal = false" class="text-slate-400 hover:text-slate-600">
                        <X class="w-5 h-5" />
                    </button>
                </div>

                <form @submit.prevent="submitProfile" class="space-y-3.5 text-xs">
                    <!-- Nama Lengkap -->
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Nama Lengkap</label>
                        <input 
                            v-model="profileForm.nama" 
                            type="text" 
                            required 
                            placeholder="Nama Lengkap Karyawan"
                            class="w-full px-3 py-2 border rounded-xl focus:ring-2 focus:ring-blue-500 outline-none uppercase font-bold"
                        />
                        <div v-if="profileForm.errors.nama" class="text-rose-500 font-bold text-[11px] mt-1">
                            {{ profileForm.errors.nama }}
                        </div>
                    </div>

                    <!-- Row 2: No Telepon & Email -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Nomor Telepon / WhatsApp</label>
                            <input 
                                v-model="profileForm.no_telepon" 
                                type="text" 
                                placeholder="Contoh: 081234567890"
                                class="w-full px-3 py-2 border rounded-xl focus:ring-2 focus:ring-blue-500 outline-none font-mono"
                            />
                            <div v-if="profileForm.errors.no_telepon" class="text-rose-500 font-bold text-[11px] mt-1">
                                {{ profileForm.errors.no_telepon }}
                            </div>
                        </div>

                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Email</label>
                            <input 
                                v-model="profileForm.email" 
                                type="email" 
                                placeholder="nama@ppa.co.id"
                                class="w-full px-3 py-2 border rounded-xl focus:ring-2 focus:ring-blue-500 outline-none"
                            />
                            <div v-if="profileForm.errors.email" class="text-rose-500 font-bold text-[11px] mt-1">
                                {{ profileForm.errors.email }}
                            </div>
                        </div>
                    </div>

                    <!-- Row 3: Unit Kerja & Lokasi Kerja -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Unit Kerja</label>
                            <input 
                                v-model="profileForm.unit" 
                                type="text" 
                                placeholder="Contoh: DT 785 / EX 2000"
                                class="w-full px-3 py-2 border rounded-xl focus:ring-2 focus:ring-blue-500 outline-none uppercase"
                            />
                        </div>

                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Lokasi Kerja</label>
                            <input 
                                v-model="profileForm.lokasi_kerja" 
                                type="text" 
                                placeholder="Contoh: MONTE BARU"
                                class="w-full px-3 py-2 border rounded-xl focus:ring-2 focus:ring-blue-500 outline-none uppercase"
                            />
                        </div>
                    </div>

                    <!-- Row 4: Tanggal Mulai Bekerja -->
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Tanggal Mulai Bekerja (Efektif)</label>
                        <input 
                            v-model="profileForm.efektif_bekerja" 
                            type="date" 
                            class="w-full px-3 py-2 border rounded-xl focus:ring-2 focus:ring-blue-500 outline-none"
                        />
                    </div>

                    <!-- Row 5: Catatan Pribadi / Karyawan -->
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Catatan Karyawan</label>
                        <textarea 
                            v-model="profileForm.catatan" 
                            rows="3"
                            placeholder="Tambahkan catatan khusus bila ada..."
                            class="w-full px-3 py-2 border rounded-xl focus:ring-2 focus:ring-blue-500 outline-none leading-relaxed"
                        ></textarea>
                    </div>

                    <!-- Link to Full Form (Admin Only) -->
                    <div v-if="user.isAdmin && profile.editUrl" class="pt-1 text-right">
                        <a 
                            :href="profile.editUrl" 
                            class="text-[11px] font-bold text-blue-600 hover:text-blue-800 hover:underline inline-flex items-center gap-1"
                        >
                            <span>Buka Formulir HR Lengkap (OnePower)</span>
                            <ChevronRight class="w-3.5 h-3.5" />
                        </a>
                    </div>

                    <div class="pt-2 flex items-center justify-end gap-2 border-t border-slate-100">
                        <button 
                            type="button" 
                            @click="showEditProfileModal = false"
                            class="px-4 py-2.5 rounded-xl font-bold text-xs text-slate-600 hover:bg-slate-100 transition"
                        >
                            Batal
                        </button>
                        <button 
                            type="submit" 
                            :disabled="profileForm.processing" 
                            :class="[
                                'px-5 py-2.5 rounded-xl font-bold text-xs uppercase text-white shadow-md transition',
                                isOperator ? 'bg-[#7B112C] hover:bg-[#5A0B1E]' : 'bg-blue-600 hover:bg-blue-700',
                                profileForm.processing ? 'opacity-50 cursor-not-allowed' : ''
                            ]"
                        >
                            {{ profileForm.processing ? 'Menyimpan...' : 'Simpan Perubahan' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- LIGHTBOX PREVIEW FOTO PROFIL -->
        <div v-if="showLightbox && profile.foto" @click="showLightbox = false" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-md cursor-pointer">
            <div class="relative max-w-lg w-full max-h-[85vh] flex items-center justify-center" @click.stop>
                <img :src="profile.foto" :alt="profile.nama" class="max-w-full max-h-[80vh] rounded-2xl shadow-2xl object-contain border-2 border-white/20" />
                <button @click="showLightbox = false" class="absolute top-3 right-3 p-2 bg-black/60 hover:bg-black/80 text-white rounded-full transition">
                    <X class="w-5 h-5" />
                </button>
            </div>
        </div>

        <!-- BOTTOM NAV (MOBILE & TABLET) -->
        <div class="lg:hidden">
            <BottomNav active="profil" :items="navItems" :accent-class="accentClass" />
        </div>
    </div>
</template>

<style scoped>
.no-scrollbar::-webkit-scrollbar {
    display: none;
}
.no-scrollbar {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
</style>
