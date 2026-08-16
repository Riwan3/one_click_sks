<script setup>
import { ref } from 'vue';
import { Head } from '@inertiajs/vue3';
import LoginForm from '@/Components/shared/LoginForm.vue';
import ForgotPasswordModal from '@/Components/shared/ForgotPasswordModal.vue';

const showForgotPassword = ref(false);
</script>

<template>
    <Head title="Login - OneClick" />

    <div class="relative flex min-h-screen items-center justify-center p-5 font-['Plus_Jakarta_Sans',sans-serif]">
        <div
            class="fixed -inset-2.5 -z-10 bg-cover bg-bottom bg-no-repeat"
            style="background-image: url('/images/bg-login.png')"
        />

        <!-- MOBILE -->
        <div class="w-full max-w-[420px] rounded-[28px] border border-white/40 bg-white/95 p-10 px-7 shadow-[0_20px_50px_rgba(0,0,0,0.15)] backdrop-blur-md lg:hidden">
            <LoginForm @forgot-password="showForgotPassword = true" />
        </div>

        <!-- DESKTOP -->
        <div class="hidden w-full max-w-[1100px] items-center justify-between gap-20 p-5 lg:flex">
            <div class="flex flex-[1.2] flex-col items-start justify-center text-left">
                <h1 class="mb-6 text-[3.5rem] font-extrabold leading-[1.15] tracking-[-1.5px]">
                    <span class="text-[#1a3066]">Satu Data,</span><br>
                    <span class="text-[#1a3066]">Satu Sistem,</span><br>
                    <span class="text-[#7B112C]">Produktivitas<br>Maksimal.</span>
                </h1>
                <p class="max-w-[460px] text-[1.05rem] leading-[1.65] text-gray-600">
                    Kelola tenaga kerja, pantau kinerja, dan tingkatkan produktivitas secara real-time dalam satu platform terintegrasi.
                </p>
            </div>
            <div class="flex flex-none basis-[450px] justify-end">
                <div class="w-full rounded-[28px] border border-black/5 bg-white p-11 shadow-[0_30px_70px_rgba(0,0,0,0.12)]">
                    <LoginForm @forgot-password="showForgotPassword = true" />
                </div>
            </div>
        </div>

        <ForgotPasswordModal :open="showForgotPassword" @close="showForgotPassword = false" />
    </div>
</template>
