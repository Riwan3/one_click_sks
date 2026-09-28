<script setup>
defineProps({
    user: { type: Object, required: true },
});
</script>

<template>
    <div
        class="relative flex items-center gap-4 overflow-hidden rounded-2xl border border-blue-100 bg-white bg-cover bg-right bg-no-repeat p-5 shadow-xs"
        style="background-image: url('/images/bg-home-up.png')"
    >
        <div class="relative z-10 flex h-16 w-16 shrink-0 items-center justify-center overflow-hidden rounded-full border-2 border-white bg-white text-2xl font-black text-blue-500 shadow-sm">
            <img
                v-if="user.foto || user.avatar"
                :src="user.foto || user.avatar"
                :alt="user.displayName"
                class="h-full w-full object-cover"
            />
            <span v-else>{{ user.displayName ? user.displayName.charAt(0) : 'U' }}</span>
        </div>
        <div class="relative z-10 min-w-0">
            <p class="mb-0.5 text-[0.65rem] font-bold uppercase tracking-[2px] text-slate-500">Selamat Datang</p>
            <h1 class="mb-0.5 truncate text-lg font-extrabold text-blue-500">{{ user.displayName }}</h1>
            <p class="mb-0.5 truncate text-xs font-bold uppercase tracking-wide text-slate-700">{{ user.jabatan }}</p>
            <p class="m-0 text-xs text-slate-500">NRP: {{ user.username }}</p>
        </div>
    </div>
</template>
