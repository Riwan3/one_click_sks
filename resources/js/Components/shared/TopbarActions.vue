<script setup>
import { onMounted, onUnmounted, ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { Pen, Bell, MessageCircle, UserRound, IdCard, ShieldCheck, Phone, LogOut } from '@lucide/vue';

defineProps({
    user: { type: Object, required: true },
    notifCounts: { type: Object, required: true },
});

const showDropdown = ref(false);
const dropdownRef = ref(null);

const closeIfOutside = (event) => {
    if (dropdownRef.value && !dropdownRef.value.contains(event.target)) {
        showDropdown.value = false;
    }
};

onMounted(() => document.addEventListener('click', closeIfOutside));
onUnmounted(() => document.removeEventListener('click', closeIfOutside));

const handleLogout = () => {
    router.post('/logout');
};
</script>

<template>
    <div class="flex items-center gap-3">
        <button class="relative flex h-[38px] w-[38px] items-center justify-center rounded-lg border-2 border-blue-500 text-blue-500 shadow-sm">
            <Pen class="h-5 w-5" />
            <span v-if="notifCounts.signature > 0" class="absolute -right-1.5 -top-1.5 flex h-4 w-4 items-center justify-center rounded-full border-2 border-white bg-blue-500 text-[0.6rem] font-black text-white">
                {{ notifCounts.signature }}
            </span>
        </button>

        <button class="relative flex h-[38px] w-[38px] items-center justify-center rounded-lg border-2 border-blue-500 text-blue-500 shadow-sm">
            <Bell class="h-5 w-5" />
            <span v-if="notifCounts.notification > 0" class="absolute -right-1.5 -top-1.5 flex h-4 w-4 items-center justify-center rounded-full border-2 border-white bg-blue-500 text-[0.6rem] font-black text-white">
                {{ notifCounts.notification }}
            </span>
        </button>

        <button class="relative flex h-[38px] w-[38px] items-center justify-center rounded-lg border-2 border-blue-500 text-blue-500 shadow-sm">
            <MessageCircle class="h-5 w-5" />
            <span v-if="notifCounts.message > 0" class="absolute -right-1.5 -top-1.5 flex h-4 w-4 items-center justify-center rounded-full border-2 border-white bg-blue-500 text-[0.6rem] font-black text-white">
                {{ notifCounts.message }}
            </span>
        </button>

        <div ref="dropdownRef" class="relative">
            <button class="flex items-center gap-2" @click="showDropdown = !showDropdown">
                <span class="hidden text-xs font-bold uppercase text-slate-800 md:inline">{{ user.displayName }}</span>
                <img v-if="user.foto" :src="user.foto" class="h-[38px] w-[38px] rounded-full border object-cover">
                <div v-else class="flex h-[35px] w-[35px] items-center justify-center rounded-full bg-blue-500/10 text-blue-500">
                    <UserRound class="h-[18px] w-[18px]" />
                </div>
            </button>

            <div
                v-if="showDropdown"
                class="absolute right-0 mt-3 w-[280px] overflow-hidden rounded-xl border-0 bg-white shadow-xl"
            >
                <div class="p-4 text-center text-white" style="background: linear-gradient(135deg, #1e3a5f 0%, #2563eb 100%);">
                    <div v-if="user.foto" class="mx-auto mb-3 h-[75px] w-[75px] overflow-hidden rounded-full border-2 border-white">
                        <img :src="user.foto" class="h-full w-full object-cover">
                    </div>
                    <div v-else class="mx-auto mb-3 flex h-[75px] w-[75px] items-center justify-center rounded-full bg-white text-blue-600">
                        <UserRound class="h-8 w-8" />
                    </div>
                    <h6 class="mb-0 text-sm font-bold uppercase">{{ user.displayName }}</h6>
                    <small class="text-[0.7rem] uppercase tracking-wider opacity-75">{{ user.jabatan }}</small>
                </div>

                <div class="grid grid-cols-2 gap-2 bg-slate-50 px-4 py-3 text-[0.7rem] uppercase">
                    <div>
                        <span class="block text-slate-400 opacity-75">NRP</span>
                        <span class="font-bold text-slate-800">{{ user.username }}</span>
                    </div>
                    <div class="text-right">
                        <span class="block text-slate-400 opacity-75">Area Kerja</span>
                        <span class="font-bold text-slate-800">{{ user.unit || 'MASTER' }}</span>
                    </div>
                </div>

                <div class="p-2">
                    <a href="#" class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-bold text-slate-800 hover:bg-slate-50">
                        <IdCard class="h-4 w-4 text-blue-600" /> Profil
                    </a>
                    <a href="#" class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-bold text-slate-800 hover:bg-slate-50">
                        <ShieldCheck class="h-4 w-4 text-blue-600" /> Peraturan
                    </a>
                    <a href="#" class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-bold text-slate-800 hover:bg-slate-50">
                        <Phone class="h-4 w-4 text-blue-600" /> Nomor Penting
                    </a>
                    <hr class="my-2 border-slate-100">
                    <button type="button" class="flex w-full items-center gap-2 rounded-lg px-3 py-2 text-left text-sm font-bold text-red-600 hover:bg-red-50" @click="handleLogout">
                        <LogOut class="h-4 w-4" /> Logout
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
