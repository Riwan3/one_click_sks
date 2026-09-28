<script setup>
import { Link } from '@inertiajs/vue3';
import { CheckCircle2, ShieldCheck, AlertTriangle } from 'lucide-vue-next';

defineProps({
    atr: { type: Object, required: true },
    compliance: { type: Object, required: true },
});
</script>

<template>
    <div class="grid grid-cols-2 gap-3">
        <Link 
            href="/one-power/personal-data/attendance" 
            class="flex flex-col items-center justify-center rounded-2xl bg-white p-5 text-center shadow-xs border border-slate-100/80 hover:shadow-md active:scale-[0.98] transition no-underline cursor-pointer min-h-[108px]"
        >
            <p v-if="atr.value" class="m-0 text-3xl font-black tracking-tight" :class="atr.isLow ? 'text-rose-600' : 'text-emerald-600'">{{ atr.value }}</p>
            <div v-else-if="atr.showIcon" class="flex h-11 w-11 items-center justify-center rounded-full bg-emerald-50 text-emerald-600 mb-1">
                <CheckCircle2 class="h-6 w-6" />
            </div>
            <p class="m-0 mt-0.5 text-xs font-black" :class="atr.isLow ? 'text-rose-600' : 'text-emerald-600'">{{ atr.label }}</p>
            <p class="m-0 mt-0.5 text-[0.68rem] font-medium text-slate-400">{{ atr.period }}</p>
        </Link>

        <Link 
            href="/one-power/pembinaan/coaching-counseling" 
            class="flex flex-col items-center justify-center rounded-2xl bg-white p-5 text-center shadow-xs border border-slate-100/80 hover:shadow-md active:scale-[0.98] transition no-underline cursor-pointer min-h-[108px]"
        >
            <div v-if="compliance.hasViolation" class="flex h-11 w-11 items-center justify-center rounded-full bg-rose-50 text-rose-600 mb-1">
                <AlertTriangle class="h-6 w-6" />
            </div>
            <div v-else-if="compliance.showIcon" class="flex h-11 w-11 items-center justify-center rounded-full bg-emerald-50 text-emerald-600 mb-1">
                <ShieldCheck class="h-6 w-6" />
            </div>
            <p class="m-0 text-sm font-black" :class="compliance.hasViolation ? 'text-rose-600' : 'text-emerald-600'">{{ compliance.label }}</p>
            <p class="m-0 mt-1 text-xs font-medium text-slate-400">{{ compliance.desc }}</p>
        </Link>
    </div>
</template>

