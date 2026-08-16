<script setup>
import { Link } from '@inertiajs/vue3';
import { moduleIcons, moduleThemeStyles } from '@/utils/moduleTheme';

defineProps({
    modules: { type: Array, required: true },
});
</script>

<template>
    <div>
        <div class="mb-4 flex items-center gap-2.5 text-[0.7rem] font-extrabold uppercase tracking-[1.5px] text-slate-500">
            <span class="inline-block h-3.5 w-1 rounded-sm bg-gradient-to-r from-blue-500 to-blue-400"></span>
            Pilih Modul
        </div>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3">
            <Link
                v-for="module in modules"
                :key="module.key"
                :href="`/${module.key}`"
                class="flex h-[155px] flex-col rounded-xl border border-white/45 bg-white/70 p-4 no-underline shadow-[0_4px_20px_rgba(74,144,226,0.06)] backdrop-blur-md transition-all hover:-translate-y-1 hover:bg-white/80 hover:shadow-[0_12px_32px_rgba(74,144,226,0.12)]"
            >
                <div :class="['mb-2 flex h-9 w-9 items-center justify-center rounded-[9px]', moduleThemeStyles[module.theme]]">
                    <component :is="moduleIcons[module.icon]" class="h-[1.05rem] w-[1.05rem]" />
                </div>
                <h2 class="mb-1 text-sm font-bold text-slate-800">{{ module.title }}</h2>
                <p class="line-clamp-3 text-xs leading-snug text-slate-500">{{ module.desc }}</p>
            </Link>
        </div>
    </div>
</template>
