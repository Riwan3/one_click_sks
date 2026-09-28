<script setup>
import { onMounted, onUnmounted, ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import { Pen, Bell, MessageCircle, UserRound, IdCard, ShieldCheck, Phone, LogOut, Megaphone } from 'lucide-vue-next';
import SignatureModal from '@/Components/modals/SignatureModal.vue';
import NotificationModal from '@/Components/modals/NotificationModal.vue';
import PesanModal from '@/Components/modals/PesanModal.vue';
import NomorPentingModal from '@/Components/modals/NomorPentingModal.vue';
import PengumumanModal from '@/Components/modals/PengumumanModal.vue';

const props = defineProps({
    user: { type: Object, required: true },
    notifCounts: { 
        type: Object, 
        default: () => ({ signature: 0, notification: 0, message: 0 }) 
    },
});

const showDropdown = ref(false);
const dropdownRef = ref(null);

const showSignatureModal = ref(false);
const showNotifModal = ref(false);
const showPesanModal = ref(false);
const showNomorPentingModal = ref(false);
const showPengumumanModal = ref(false);

const unreadMessageCount = ref(props.notifCounts?.message || 0);

watch(() => props.notifCounts?.message, (newVal) => {
    if (typeof newVal === 'number') unreadMessageCount.value = newVal;
});

const checkUnreadMessages = async () => {
    try {
        const res = await fetch('/internal-messages/unread-count');
        const data = await res.json();
        if (data && typeof data.unread_count === 'number') {
            unreadMessageCount.value = data.unread_count;
        }
    } catch (e) {
        // silent
    }
};

let unreadInterval = null;

const closeIfOutside = (event) => {
    if (dropdownRef.value && !dropdownRef.value.contains(event.target)) {
        showDropdown.value = false;
    }
};

onMounted(() => {
    document.addEventListener('click', closeIfOutside);
    checkUnreadMessages();
    unreadInterval = setInterval(checkUnreadMessages, 10000);
});

onUnmounted(() => {
    document.removeEventListener('click', closeIfOutside);
    if (unreadInterval) clearInterval(unreadInterval);
});

const handleLogout = () => {
    router.post('/logout');
};
</script>

<template>
    <div class="flex items-center gap-3">
        <!-- 1. SIGNATURE PEN BUTTON -->
        <button 
            @click="showSignatureModal = true" 
            class="relative flex h-[38px] w-[38px] items-center justify-center rounded-lg border-2 border-blue-500 text-blue-500 shadow-xs hover:bg-blue-50 transition"
            title="Notifikasi Penandatanganan"
        >
            <Pen class="h-5 w-5" />
            <span v-if="notifCounts && notifCounts.signature > 0" class="absolute -right-1.5 -top-1.5 flex h-4 w-4 items-center justify-center rounded-full border-2 border-white bg-blue-500 text-[0.6rem] font-black text-white">
                {{ notifCounts.signature }}
            </span>
        </button>

        <!-- 2. BELL NOTIFICATION BUTTON -->
        <button 
            @click="showNotifModal = true" 
            class="relative flex h-[38px] w-[38px] items-center justify-center rounded-lg border-2 border-blue-500 text-blue-500 shadow-xs hover:bg-blue-50 transition"
            title="Notifikasi Sistem"
        >
            <Bell class="h-5 w-5" />
            <span v-if="notifCounts && notifCounts.notification > 0" class="absolute -right-1.5 -top-1.5 flex h-4 w-4 items-center justify-center rounded-full border-2 border-white bg-blue-500 text-[0.6rem] font-black text-white">
                {{ notifCounts.notification }}
            </span>
        </button>

        <!-- 3. PESAN MESSAGE BUTTON -->
        <button 
            @click="showPesanModal = true" 
            class="relative flex h-[38px] w-[38px] items-center justify-center rounded-lg border-2 border-blue-500 text-blue-500 shadow-xs hover:bg-blue-50 transition"
            title="Pesan Internal"
        >
            <MessageCircle class="h-5 w-5" />
            <span v-if="unreadMessageCount > 0" class="absolute -right-1.5 -top-1.5 flex h-4 w-4 items-center justify-center rounded-full border-2 border-white bg-blue-500 text-[0.6rem] font-black text-white">
                {{ unreadMessageCount }}
            </span>
        </button>

        <!-- 4. PROFILE DROPDOWN BUTTON -->
        <div ref="dropdownRef" class="relative">
            <button class="flex items-center gap-2.5 p-1 rounded-xl hover:bg-slate-100 transition" @click="showDropdown = !showDropdown">
                <span class="hidden text-xs font-bold uppercase tracking-tight text-slate-800 md:inline">{{ user.displayName }}</span>
                <img v-if="user.foto" :src="user.foto" class="h-[38px] w-[38px] rounded-full border object-cover">
                <div v-else class="flex h-[35px] w-[35px] items-center justify-center rounded-full bg-blue-50/80 text-blue-600 border border-blue-100">
                    <UserRound class="h-[18px] w-[18px]" />
                </div>
            </button>

            <!-- DROPDOWN CONTENT -->
            <div
                v-if="showDropdown"
                class="absolute right-0 mt-3 w-[280px] overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-2xl z-50 animate-in fade-in zoom-in-95 duration-150"
            >
                <div class="p-4 text-center text-white" style="background: linear-gradient(135deg, #1e3a5f 0%, #2563eb 100%);">
                    <div v-if="user.foto" class="mx-auto mb-3 h-[75px] w-[75px] overflow-hidden rounded-full border-2 border-white">
                        <img :src="user.foto" class="h-full w-full object-cover">
                    </div>
                    <div v-else class="mx-auto mb-3 flex h-[75px] w-[75px] items-center justify-center rounded-full bg-white text-blue-600">
                        <UserRound class="h-8 w-8" />
                    </div>
                    <h6 class="mb-0 text-sm font-bold uppercase tracking-tight">{{ user.displayName }}</h6>
                    <small class="text-[0.7rem] uppercase tracking-wider opacity-75">{{ user.jabatan || 'OPERATOR' }}</small>
                </div>

                <div class="grid grid-cols-2 gap-2 bg-slate-50 px-4 py-3 text-[0.7rem] uppercase border-b border-slate-100">
                    <div>
                        <span class="block text-slate-400 opacity-75">NRP</span>
                        <span class="font-bold text-slate-800">{{ user.username || '-' }}</span>
                    </div>
                    <div class="text-right">
                        <span class="block text-slate-400 opacity-75">Area Kerja</span>
                        <span class="font-bold text-slate-800">{{ user.unit || 'MASTER' }}</span>
                    </div>
                </div>

                <div class="p-2 space-y-0.5 text-xs">
                    <a href="/profil" class="flex items-center gap-2 rounded-xl px-3 py-2 font-bold text-slate-800 hover:bg-blue-50 hover:text-blue-600 transition">
                        <IdCard class="h-4 w-4 text-blue-600" /> PROFIL
                    </a>
                    <a href="/pengumuman" class="flex items-center gap-2 rounded-xl px-3 py-2 font-bold text-slate-800 hover:bg-amber-50 hover:text-amber-600 transition">
                        <Megaphone class="h-4 w-4 text-amber-500" /> Pengumuman
                    </a>
                    <a href="/peraturan" class="flex items-center gap-2 rounded-xl px-3 py-2 font-bold text-slate-800 hover:bg-blue-50 hover:text-blue-600 transition">
                        <ShieldCheck class="h-4 w-4 text-emerald-600" /> Peraturan
                    </a>
                    <button 
                        @click="showDropdown = false; showNomorPentingModal = true" 
                        class="flex w-full items-center gap-2 rounded-xl px-3 py-2 text-left font-bold text-slate-800 hover:bg-blue-50 hover:text-blue-600 transition"
                    >
                        <Phone class="h-4 w-4 text-sky-600" /> Nomor Penting
                    </button>
                    <hr class="my-1.5 border-slate-100">
                    <button type="button" class="flex w-full items-center gap-2 rounded-xl px-3 py-2 text-left font-bold text-rose-600 hover:bg-rose-50 transition" @click="handleLogout">
                        <LogOut class="h-4 w-4" /> Logout
                    </button>
                </div>
            </div>
        </div>

        <!-- MODAL COMPONENTS -->
        <SignatureModal :show="showSignatureModal" @close="showSignatureModal = false" />
        <NotificationModal :show="showNotifModal" @close="showNotifModal = false" />
        <PesanModal :show="showPesanModal" @close="showPesanModal = false" />
        <NomorPentingModal :show="showNomorPentingModal" :user="user" @close="showNomorPentingModal = false" />
        <PengumumanModal :show="showPengumumanModal" @close="showPengumumanModal = false" />
    </div>
</template>
