<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import { CircleCheck, UserRoundX } from 'lucide-vue-next';

const props = defineProps({
  never: { type: Object, required: true }, // { rows, total, currentPage, lastPage, from, to }
  filters: { type: Object, required: true }, // { bulan, tahun }
});

const PAGE_WINDOW = 2;

const pageHref = (page) => `/login-trend?${new URLSearchParams({ bulan: props.filters.bulan, tahun: props.filters.tahun, page_never: page })}`;

const pages = computed(() => {
  const first = Math.max(1, props.never.currentPage - PAGE_WINDOW);
  const last = Math.min(props.never.lastPage, props.never.currentPage + PAGE_WINDOW);
  return Array.from({ length: last - first + 1 }, (_, index) => first + index);
});

const pageClass = (page) => ['rounded-lg border px-2.5 py-1 font-semibold', page === props.never.currentPage ? 'border-blue-600 bg-blue-600 text-white' : 'border-slate-200 text-slate-600 hover:bg-slate-50'];
</script>

<template>
  <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm">
    <div class="flex items-center justify-between px-5 pb-2 pt-4">
      <h2 class="flex items-center gap-2 text-sm font-bold text-slate-800"><UserRoundX class="h-4 w-4 text-red-500" /> Karyawan Belum Pernah Login</h2>
      <span class="rounded-full bg-red-500 px-2.5 py-0.5 text-xs font-bold text-white">{{ never.total }} karyawan</span>
    </div>

    <div v-if="!never.rows.length" class="py-12 text-center text-sm text-slate-400">
      <CircleCheck class="mx-auto mb-2 h-6 w-6 text-emerald-500" /> Semua karyawan aktif sudah pernah login.
    </div>

    <template v-else>
      <div class="overflow-x-auto">
        <table class="w-full whitespace-nowrap text-left text-xs">
          <thead class="bg-slate-50 text-[11px] uppercase text-slate-500">
            <tr>
              <th class="w-10 px-5 py-2.5">No</th>
              <th class="px-3 py-2.5">NRP</th>
              <th class="px-3 py-2.5">Nama</th>
              <th class="px-3 py-2.5">Jabatan</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            <tr v-for="(row, index) in never.rows" :key="row.nrp" class="hover:bg-slate-50/60">
              <td class="px-5 py-2 text-slate-400">{{ never.from + index }}</td>
              <td class="px-3 py-2 font-bold text-red-500">{{ row.nrp }}</td>
              <td class="px-3 py-2 font-semibold text-slate-800">{{ row.name || '-' }}</td>
              <td class="px-3 py-2 text-slate-500">{{ row.jabatan_ss6 || '-' }}</td>
            </tr>
          </tbody>
        </table>
      </div>

      <div class="flex flex-wrap items-center justify-between gap-2 border-t border-slate-100 px-5 py-3">
        <span class="text-xs text-slate-500">Menampilkan {{ never.from }}–{{ never.to }} dari {{ never.total }} karyawan</span>
        <nav v-if="never.lastPage > 1" class="flex items-center gap-1 text-xs">
          <Link v-if="never.currentPage > 1" :href="pageHref(never.currentPage - 1)" preserve-scroll class="rounded-lg border border-slate-200 px-2.5 py-1 text-slate-600 hover:bg-slate-50">&laquo;</Link>
          <Link v-for="page in pages" :key="page" :href="pageHref(page)" preserve-scroll :class="pageClass(page)">{{ page }}</Link>
          <Link v-if="never.currentPage < never.lastPage" :href="pageHref(never.currentPage + 1)" preserve-scroll class="rounded-lg border border-slate-200 px-2.5 py-1 text-slate-600 hover:bg-slate-50">&raquo;</Link>
        </nav>
      </div>
    </template>
  </div>
</template>
