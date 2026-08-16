<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft } from '@lucide/vue';
import { moduleIcons, moduleThemeStyles } from '@/utils/moduleTheme';

const props = defineProps({
    module: { type: Object, required: true },
});
</script>

<template>
    <Head :title="`${module.title} - OneClick`" />

    <div class="min-h-screen bg-gradient-to-br from-slate-50 to-blue-50/60 px-5 py-10 lg:px-10">
        <div class="mx-auto max-w-3xl">
            <Link href="/portal" class="mb-6 inline-flex items-center gap-2 text-sm font-semibold text-blue-500 no-underline hover:text-blue-600">
                <ArrowLeft class="h-4 w-4" />
                Kembali ke Portal
            </Link>

            <div class="rounded-2xl border border-white/45 bg-white/80 p-8 shadow-[0_4px_20px_rgba(74,144,226,0.06)] backdrop-blur-md">
                <div :class="['mb-4 flex h-14 w-14 items-center justify-center rounded-2xl', moduleThemeStyles[module.theme]]">
                    <component :is="moduleIcons[module.icon]" class="h-7 w-7" />
                </div>
                <h1 class="mb-2 text-2xl font-extrabold text-slate-800">{{ module.title }}</h1>
                <p class="mb-6 leading-relaxed text-slate-500">{{ module.desc }}</p>

                <div class="rounded-xl bg-amber-50 px-4 py-3 text-sm font-semibold text-amber-800">
                    Modul ini belum tersedia — masih dalam tahap pengembangan.
                </div>
            </div>
        </div>
    </div>
</template>
