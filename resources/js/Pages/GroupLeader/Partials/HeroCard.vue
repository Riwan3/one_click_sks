<script setup>
defineProps({
    user: { type: Object, required: true },
});
</script>

<template>
    <div class="flex items-center gap-4 rounded-2xl border border-blue-100 bg-gradient-to-br from-blue-50 to-white p-5">
        <div class="flex h-16 w-16 shrink-0 items-center justify-center rounded-full bg-white text-2xl font-black text-blue-500 shadow-sm">
            {{ user.displayName.charAt(0) }}
        </div>
        <div class="min-w-0">
            <p class="mb-0.5 text-[0.65rem] font-bold uppercase tracking-[2px] text-slate-500">Selamat Datang</p>
            <h1 class="mb-0.5 truncate text-lg font-extrabold text-blue-500">{{ user.displayName }}</h1>
            <p class="mb-0.5 truncate text-xs font-bold uppercase tracking-wide text-slate-700">{{ user.jabatan }} {{ user.unit }}</p>
            <p class="m-0 text-xs text-slate-500">NRP: {{ user.username }}</p>
        </div>
    </div>
</template>
