<script setup>
import { ShieldCheck, Cpu, BadgeCheck } from '@lucide/vue';

defineProps({
    user: { type: Object, required: true },
    greeting: { type: String, required: true },
});
</script>

<template>
    <div
        class="relative grid min-h-[160px] grid-cols-1 items-center gap-5 overflow-hidden rounded-2xl border border-blue-100 bg-cover bg-right bg-no-repeat p-7 md:grid-cols-[1fr_auto]"
        style="background-image: url('/images/bg-home-up.png')"
    >
        <div>
            <span class="mb-2 inline-block rounded-full bg-blue-100 px-2.5 py-1 text-[0.65rem] font-extrabold tracking-[2px] text-blue-500 lowercase">
                oneclick
            </span>
            <h3 class="mb-1.5 text-xl font-extrabold leading-tight text-[#1e3a5f] md:text-2xl">
                {{ greeting }},<br>
                <span class="text-blue-500">{{ user.displayName }} 👋</span>
            </h3>
            <p class="max-w-[400px] text-[0.82rem] leading-relaxed text-[#4a6fa5]">
                {{ user.jabatan.toUpperCase() }} &bull; PT Putra Perkasa Abadi
            </p>

            <div class="mt-4 flex flex-wrap gap-3">
                <div class="flex items-center gap-2 rounded-[10px] border border-blue-500/15 bg-white/85 px-3 py-2">
                    <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-blue-100 text-blue-500">
                        <ShieldCheck class="h-4 w-4" />
                    </div>
                    <div>
                        <div class="text-[0.78rem] font-bold text-[#1e3a5f]">Aman</div>
                        <div class="text-[0.65rem] text-slate-500">Utamakan Keselamatan</div>
                    </div>
                </div>
                <div class="flex items-center gap-2 rounded-[10px] border border-blue-500/15 bg-white/85 px-3 py-2">
                    <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-blue-100 text-blue-500">
                        <Cpu class="h-4 w-4" />
                    </div>
                    <div>
                        <div class="text-[0.78rem] font-bold text-[#1e3a5f]">Efisien</div>
                        <div class="text-[0.65rem] text-slate-500">Optimalkan Proses</div>
                    </div>
                </div>
                <div class="flex items-center gap-2 rounded-[10px] border border-blue-500/15 bg-white/85 px-3 py-2">
                    <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-blue-100 text-blue-500">
                        <BadgeCheck class="h-4 w-4" />
                    </div>
                    <div>
                        <div class="text-[0.78rem] font-bold text-[#1e3a5f]">Produktif</div>
                        <div class="text-[0.65rem] text-slate-500">Hasil Maksimal</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
