<script setup>
import { ref } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ShieldLock, Eye, EyeOff, TriangleAlert } from '@lucide/vue';

defineProps({
    error: { type: String, default: null },
});

const showPassword = ref(false);

const form = useForm({
    password: '',
});

const handleSubmit = () => {
    form.post('/one-core/unlock', {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <Head title="Otorisasi Keamanan - OneClick" />

    <div class="flex min-h-screen items-center justify-center p-5" style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);">
        <div class="w-full max-w-sm rounded-3xl border border-white/10 bg-slate-800/75 p-9 text-center shadow-2xl backdrop-blur-xl">
            <div
                class="mx-auto mb-5 flex h-16 w-16 items-center justify-center rounded-2xl text-white shadow-[0_0_0_8px_rgba(2,132,199,0.15)]"
                style="background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%);"
            >
                <ShieldLock class="h-7 w-7" />
            </div>

            <h1 class="mb-1.5 text-lg font-bold text-white">Otorisasi Keamanan</h1>
            <p class="mb-6 text-sm text-slate-400">Masukkan password otorisasi untuk mengakses data pengguna.</p>

            <div v-if="error" class="mb-4 flex items-center justify-center gap-2 rounded-xl bg-red-500/10 px-3 py-2.5 text-sm font-semibold text-red-400">
                <TriangleAlert class="h-4 w-4 shrink-0" />
                <span>{{ error }}</span>
            </div>

            <form @submit.prevent="handleSubmit">
                <div class="relative mb-4">
                    <input
                        v-model="form.password"
                        :type="showPassword ? 'text' : 'password'"
                        placeholder="Password otorisasi"
                        autofocus
                        required
                        class="w-full rounded-xl border border-white/10 bg-white/5 py-3 pl-4 pr-11 text-sm text-white outline-none placeholder:text-slate-500 focus:border-sky-500"
                    >
                    <button type="button" class="absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-200" @click="showPassword = !showPassword">
                        <EyeOff v-if="showPassword" class="h-4 w-4" />
                        <Eye v-else class="h-4 w-4" />
                    </button>
                </div>

                <button
                    type="submit"
                    :disabled="form.processing"
                    class="w-full rounded-xl py-3 text-sm font-bold text-white shadow-lg transition-all disabled:cursor-not-allowed disabled:opacity-70"
                    style="background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%);"
                >
                    Buka Akses
                </button>
            </form>
        </div>
    </div>
</template>
