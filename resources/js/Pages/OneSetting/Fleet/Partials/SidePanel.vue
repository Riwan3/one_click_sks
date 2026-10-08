<script setup>
import { computed, ref } from 'vue';

const props = defineProps({
  title: { type: String, required: true },
  items: { type: Array, default: () => [] }, // setiap item punya `code`
  tone: { type: String, default: 'slate' }, // slate | sky | rose
  emptyText: { type: String, default: 'Tidak ada unit.' },
});

const SEARCH_THRESHOLD = 10;
const TONE_CLASS = { slate: 'bg-slate-700', sky: 'bg-sky-700', rose: 'bg-rose-700' };

const search = ref('');

const visible = computed(() => {
  const query = search.value.toLowerCase().trim();

  return props.items.filter((item) => !query || `${item.code} ${item.category ?? ''}`.toLowerCase().includes(query));
});
</script>

<template>
  <section class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm">
    <h3 :class="['px-3 py-1.5 text-xs font-extrabold tracking-wide text-white', TONE_CLASS[tone]]">{{ title }} <span class="font-semibold opacity-80">({{ items.length }})</span></h3>
    <div v-if="items.length > SEARCH_THRESHOLD" class="border-b border-slate-100 px-2 py-1.5">
      <input v-model="search" type="search" placeholder="Cari..." class="w-full rounded border border-slate-300 px-2 py-1 text-xs focus:border-blue-500 focus:outline-none" />
    </div>
    <p v-if="!visible.length" class="px-3 py-3 text-center text-[11px] italic text-slate-400">{{ emptyText }}</p>
    <ul v-else class="max-h-56 divide-y divide-slate-100 overflow-y-auto px-2">
      <slot :items="visible" />
    </ul>
  </section>
</template>
