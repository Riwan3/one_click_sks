<script setup>
import { Link } from '@inertiajs/vue3';
import { BASE_URL } from '@/utils/manpowerV201';

const props = defineProps({
  pages: { type: Array, required: true }, // [[no, dari, sampai], ...]
  page: { type: Number, required: true },
  filters: { type: Object, required: true }, // { month, status }
});

const href = (no) => `${BASE_URL}/monitoring?${new URLSearchParams({ ...props.filters, page: no })}`;
const itemClass = (isActive) => ['rounded border px-2.5 py-1 text-xs font-semibold', isActive ? 'border-blue-600 bg-blue-600 text-white' : 'border-slate-300 text-slate-600 hover:bg-slate-50'];
</script>

<template>
  <nav aria-label="Halaman monitoring" class="flex flex-wrap gap-1">
    <Link v-if="page > 1" :href="href(page - 1)" :class="itemClass(false)">&laquo;</Link>
    <Link v-for="[no, from, to] in pages" :key="no" :href="href(no)" :class="itemClass(no === page)">{{ from }}&ndash;{{ to }}</Link>
    <Link v-if="page < pages.length" :href="href(page + 1)" :class="itemClass(false)">&raquo;</Link>
  </nav>
</template>
