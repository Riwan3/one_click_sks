<script setup>
import { Head } from '@inertiajs/vue3';
import { Truck } from 'lucide-vue-next';
import AppLayout from '@/Layouts/AppLayout.vue';

defineProps({
  user: Object,
  notifCounts: Object,
  menu: Array,
});
</script>

<template>
  <Head title="Setting Fleet - One-Setting" />

  <AppLayout :user="user" :notif-counts="notifCounts" :menu="menu">
    <div class="space-y-6">
      <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm">
        <h1 class="flex items-center gap-2 text-xl font-bold tracking-tight text-slate-800">
          <Truck class="h-6 w-6 text-sky-500" /> Setting Fleet
        </h1>
        <p class="mt-0.5 text-xs text-slate-500">Halaman ini sedang disiapkan.</p>
      </div>
    </div>
  </AppLayout>
</template>
