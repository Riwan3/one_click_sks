<script setup>
import { Link } from '@inertiajs/vue3';
import { Info } from '@lucide/vue';
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

        <div class="grid grid-cols-1 gap-[18px] sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
            <Link
                v-for="module in modules"
                :key="module.key"
                :href="`/${module.key}`"
                class="relative flex h-36 flex-col justify-center rounded-2xl border border-slate-900/[0.06] bg-white p-4 px-[18px] no-underline shadow-[0_1px_2px_rgba(15,23,42,0.04),0_8px_20px_rgba(15,23,42,0.03)] transition-all hover:-translate-y-0.5 hover:border-blue-400/35 hover:bg-[#FAFCFF] hover:shadow-[0_2px_6px_rgba(15,23,42,0.04),0_14px_28px_rgba(74,144,226,0.14)]"
            >
                <div class="group/tooltip absolute right-3 top-3 z-[5]">
                    <button
                        type="button"
                        class="flex h-8 w-8 items-center justify-center rounded-full bg-slate-500/[0.08] text-slate-500 transition-all hover:scale-105 hover:bg-blue-500/10 hover:text-blue-500"
                        :aria-label="`Informasi modul ${module.title}`"
                        @click.stop.prevent
                    >
                        <Info class="h-4 w-4" />
                    </button>
                    <div
                        class="pointer-events-none absolute right-0 top-full z-10 mt-2 w-max max-w-[240px] rounded-xl bg-slate-800 px-3.5 py-3 text-left text-[0.78rem] font-medium leading-relaxed text-white opacity-0 shadow-[0_10px_24px_rgba(0,0,0,0.18)] transition-opacity group-hover/tooltip:opacity-100"
                    >
                        {{ module.desc }}
                    </div>
                </div>

                <div class="flex flex-row items-center justify-center gap-4 text-center">
                    <div :class="['flex h-[66px] w-[66px] shrink-0 items-center justify-center rounded-[18px]', moduleThemeStyles[module.theme]]">
                        <component :is="moduleIcons[module.icon]" class="h-7 w-7" />
                    </div>
                    <h2 class="text-base font-bold leading-tight tracking-tight text-slate-800">{{ module.title }}</h2>
                </div>
            </Link>
        </div>
    </div>
</template>
