<script setup>
import { computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import { OctagonX, Truck, TriangleAlert } from 'lucide-vue-next';
import AppLayout from '@/Layouts/AppLayout.vue';
import { FLEET_URL } from '@/utils/fleet';
import ManpowerNotice from '@/Pages/OneSetting/ManpowerV201/Partials/ManpowerNotice.vue';

const props = defineProps({
  user: Object,
  notifCounts: Object,
  menu: Array,
  subtitle: { type: String, required: true },
  tab: { type: String, required: true }, // harian | preview | master
  filters: { type: Object, default: null }, // { date, shift } ikut di tautan tab Setting Fleet
  ready: { type: Boolean, default: true },
  loadError: { type: String, default: null },
});

const TABS = [
  { key: 'harian', label: 'Setting Fleet', path: '', keepsFilters: true },
  { key: 'preview', label: 'Preview & Gambar', path: '/preview', keepsFilters: true },
  { key: 'master', label: 'Master Unit', path: '/master', keepsFilters: false },
];

const query = computed(() => (props.filters ? `?${new URLSearchParams(props.filters)}` : ''));
const tabHref = (tab) => `${FLEET_URL}${tab.path}${tab.keepsFilters ? query.value : ''}`;
</script>

<template>
  <Head title="Setting Fleet - One-Setting" />

  <AppLayout :user="user" :notif-counts="notifCounts" :menu="menu">
    <div class="space-y-4">
      <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h1 class="flex items-center gap-2 text-xl font-bold tracking-tight text-slate-800"><Truck class="h-5 w-5 text-sky-500" /> Setting Fleet</h1>
          <p class="text-xs text-slate-500">{{ subtitle }}</p>
        </div>
        <nav class="flex flex-wrap gap-1">
          <Link
            v-for="item in TABS"
            :key="item.key"
            :href="tabHref(item)"
            :class="['rounded-lg px-3 py-1.5 text-sm font-semibold', item.key === tab ? 'bg-blue-600 text-white' : 'text-blue-600 hover:bg-blue-50']"
          >
            {{ item.label }}
          </Link>
        </nav>
      </div>

      <div v-if="loadError" class="flex items-start gap-2 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
        <OctagonX class="mt-0.5 h-4 w-4 shrink-0" />
        <div>
          <strong>Gagal membaca data.</strong>
          <code class="mt-1 block break-all text-xs">{{ loadError }}</code>
        </div>
      </div>
      <div v-else-if="!ready" class="flex items-start gap-2 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
        <TriangleAlert class="mt-0.5 h-4 w-4 shrink-0" />
        <span>Tabel modul belum dibuat. Jalankan <code>php artisan migrate</code> terlebih dahulu.</span>
      </div>

      <slot v-else />
    </div>

    <ManpowerNotice />
  </AppLayout>
</template>
