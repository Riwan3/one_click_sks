<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';

const emit = defineEmits(['forgot-password']);

const showPassword = ref(false);

const form = useForm({
    username: '',
    password: '',
    remember: false,
});

const handleSubmit = () => {
    form.post('/login', { preserveScroll: true });
};
</script>

<template>
    <div class="mb-7 text-center">
        <div class="mb-3">
            <img src="/images/title.png" alt="OneClick Logo" class="mx-auto h-[54px] w-[54px] object-contain">
        </div>
        <div class="text-[1.6rem] font-extrabold leading-tight tracking-[-0.5px] text-slate-800">OneClick</div>
        <div class="mt-1.5 text-[0.7rem] font-semibold uppercase tracking-[0.5px] text-slate-500">
            Smart Manpower Management
        </div>
    </div>

    <div class="mb-6 text-center">
        <h3 class="mb-1 text-[1.18rem] font-bold text-[#1a3066]">Selamat Datang Kembali!</h3>
        <p class="m-0 text-[0.85rem] font-medium text-slate-500">Silakan masuk untuk melanjutkan</p>
    </div>

    <div
        v-if="form.errors.username"
        class="mb-4 flex items-center justify-center gap-2 rounded-xl bg-red-50 px-3 py-2.5 text-center text-[0.88rem] font-semibold text-red-800"
    >
        <svg class="h-4 w-4 shrink-0" viewBox="0 0 16 16" fill="currentColor"><path d="M7.938 2.016A.13.13 0 0 1 8.002 2a.13.13 0 0 1 .063.016.15.15 0 0 1 .054.057l6.857 11.667c.036.06.035.124.002.183a.2.2 0 0 1-.054.06.1.1 0 0 1-.066.017H1.146a.1.1 0 0 1-.066-.017.2.2 0 0 1-.054-.06.18.18 0 0 1 .002-.183L7.884 2.073a.15.15 0 0 1 .054-.057m1.044-.45a1.13 1.13 0 0 0-1.96 0L.163 13.233a1.13 1.13 0 0 0 .98 1.767h13.713a1.13 1.13 0 0 0 .98-1.767z"/><path d="M7.002 12a1 1 0 1 1 2 0 1 1 0 0 1-2 0M7.1 5.995a.905.905 0 1 1 1.8 0l-.35 3.507a.552.552 0 0 1-1.1 0z"/></svg>
        <span>{{ form.errors.username }}</span>
    </div>

    <form @submit.prevent="handleSubmit">
        <div class="relative mb-4">
            <svg class="pointer-events-none absolute left-4 top-1/2 h-[1.15rem] w-[1.15rem] -translate-y-1/2 text-slate-400" viewBox="0 0 16 16" fill="currentColor"><path d="M8 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6m2-3a2 2 0 1 1-4 0 2 2 0 0 1 4 0m4 8c0 1-1 1-1 1H3s-1 0-1-1 1-4 6-4 6 3 6 4m-1-.004c-.001-.246-.154-.986-.832-1.664C11.516 10.68 10.289 10 8 10s-3.516.68-4.168 1.332c-.678.678-.83 1.418-.832 1.664z"/></svg>
            <input
                v-model="form.username"
                type="text"
                placeholder="NRP / Username"
                required
                autofocus
                autocomplete="off"
                class="w-full rounded-xl border-[1.5px] border-slate-200 bg-white py-3.5 pl-[46px] pr-4 text-[0.95rem] font-medium text-slate-800 outline-none transition-all placeholder:text-slate-400 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10"
            >
        </div>

        <div class="relative mb-4">
            <svg class="pointer-events-none absolute left-4 top-1/2 h-[1.15rem] w-[1.15rem] -translate-y-1/2 text-slate-400" viewBox="0 0 16 16" fill="currentColor"><path d="M8 1a2 2 0 0 1 2 2v4H6V3a2 2 0 0 1 2-2m3 6V3a3 3 0 0 0-6 0v4a2 2 0 0 0-2 2v5a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2"/></svg>
            <input
                v-model="form.password"
                :type="showPassword ? 'text' : 'password'"
                placeholder="Password"
                required
                autocomplete="current-password"
                class="w-full rounded-xl border-[1.5px] border-slate-200 bg-white py-3.5 pl-[46px] pr-11 text-[0.95rem] font-medium text-slate-800 outline-none transition-all placeholder:text-slate-400 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10"
            >
            <button
                type="button"
                class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600"
                @click="showPassword = !showPassword"
            >
                <svg v-if="showPassword" class="h-[1.1rem] w-[1.1rem]" viewBox="0 0 16 16" fill="currentColor"><path d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8M1.173 8a13 13 0 0 1 1.66-2.043C4.12 4.668 5.88 3.5 8 3.5s3.879 1.168 5.168 2.457A13 13 0 0 1 14.828 8q-.086.13-.195.288c-.335.48-.83 1.12-1.465 1.755C11.879 11.332 10.119 12.5 8 12.5s-3.879-1.168-5.168-2.457A13 13 0 0 1 1.172 8z"/><path d="M8 5.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5M4.5 8a3.5 3.5 0 1 1 7 0 3.5 3.5 0 0 1-7 0"/></svg>
                <svg v-else class="h-[1.1rem] w-[1.1rem]" viewBox="0 0 16 16" fill="currentColor"><path d="M13.359 11.238C15.06 9.72 16 8 16 8s-3-5.5-8-5.5a7 7 0 0 0-2.79.588l.77.771A6 6 0 0 1 8 3.5c2.12 0 3.879 1.168 5.168 2.457A13 13 0 0 1 14.828 8q-.086.13-.195.288c-.335.48-.83 1.12-1.465 1.755q-.247.248-.517.486zm-2.943 1.299.772.772a7 7 0 0 1-2.188.361c-5 0-8-5.5-8-5.5S1.618 6.02 3.717 4.207L1.111 1.6a.5.5 0 1 1 .708-.708l13.6 13.6a.5.5 0 0 1-.708.708zM8 12.5c-2.12 0-3.879-1.168-5.168-2.457A13 13 0 0 1 1.172 8l.195-.288a13 13 0 0 1 1.465-1.755L4.5 7.62A3.5 3.5 0 0 0 8.5 11.5l.79.791a6.7 6.7 0 0 1-1.29.209"/><path d="M8 5.5c-.18 0-.356.014-.526.04l1.986 1.985A2.5 2.5 0 0 0 8 5.5m-3.75 1.75a3.5 3.5 0 0 0 3.75 3.4L4.5 7.15a3.5 3.5 0 0 0-.25.6z"/></svg>
            </button>
        </div>

        <div class="mb-6 mt-2 flex items-center justify-between">
            <label class="flex cursor-pointer select-none items-center gap-2">
                <input v-model="form.remember" type="checkbox" class="h-4 w-4 cursor-pointer rounded border-[1.5px] border-slate-300 accent-[#7B112C]">
                <span class="text-[0.85rem] font-medium text-slate-500">Ingat saya</span>
            </label>
            <button
                type="button"
                class="text-[0.85rem] font-semibold text-blue-500 hover:text-blue-600 hover:underline"
                @click="emit('forgot-password')"
            >
                Lupa Password?
            </button>
        </div>

        <button
            type="submit"
            :disabled="form.processing"
            class="w-full rounded-xl bg-[#7B112C] py-3.5 text-[0.98rem] font-bold text-white shadow-[0_10px_25px_rgba(123,17,44,0.25)] transition-all hover:bg-[#630e23] hover:shadow-[0_12px_30px_rgba(123,17,44,0.35)] active:scale-[0.98] disabled:cursor-not-allowed disabled:opacity-70"
        >
            Masuk
        </button>
    </form>

    <div class="mt-7 text-center text-[0.78rem] font-medium text-slate-400">
        &copy; 2026 OneClick System. All rights reserved.
    </div>
</template>
