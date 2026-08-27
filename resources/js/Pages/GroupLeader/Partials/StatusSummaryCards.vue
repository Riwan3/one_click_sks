<script setup>
import { Link } from '@inertiajs/vue3';
import { CheckCircle2, ShieldCheck } from 'lucide-vue-next';

defineProps({
    atr: { type: Object, required: true },
    compliance: { type: Object, required: true },
});
</script>

<template>
    <div class="grid grid-cols-2 gap-3">
        <Link 
            href="/one-power/personal-data/attendance" 
            class="flex flex-col items-center gap-1.5 rounded-2xl bg-white p-5 text-center shadow-sm hover:shadow-md active:scale-95 transition no-underline cursor-pointer"
        >
            <div class="flex h-11 w-11 items-center justify-center rounded-full bg-emerald-500 text-white">
                <CheckCircle2 class="h-5 w-5" />
            </div>
            <p class="m-0 text-sm font-extrabold text-emerald-600">{{ atr.label }}</p>
            <p class="m-0 text-xs text-slate-400">{{ atr.period }}</p>
        </Link>

        <Link 
            href="/one-power/pembinaan/coaching-counseling" 
            class="flex flex-col items-center justify-center gap-1.5 rounded-2xl bg-white p-5 text-center shadow-sm hover:shadow-md active:scale-95 transition no-underline cursor-pointer"
        >
            <div class="flex h-11 w-11 items-center justify-center rounded-full bg-sky-500 text-white">
                <ShieldCheck class="h-5 w-5" />
            </div>
            <p class="m-0 text-sm font-extrabold text-emerald-600">{{ compliance.label }}</p>
            <p class="m-0 text-xs text-slate-400">{{ compliance.desc }}</p>
        </Link>
    </div>
</template>
