<script setup>
import { computed } from 'vue';
import { ChevronLeft, ChevronRight } from 'lucide-vue-next';

const props = defineProps({
  page: { type: Number, required: true },
  perPage: { type: Number, required: true },
  total: { type: Number, required: true },
});

const emit = defineEmits(['change-page', 'change-per-page']);

const totalPages = computed(() => Math.max(1, Math.ceil(props.total / props.perPage)));
const rangeStart = computed(() => (props.total === 0 ? 0 : (props.page - 1) * props.perPage + 1));
const rangeEnd = computed(() => Math.min(props.page * props.perPage, props.total));

const pages = computed(() => {
  const win = 2;
  const start = Math.max(1, props.page - win);
  const end = Math.min(totalPages.value, props.page + win);
  const list = [];
  for (let p = start; p <= end; p++) list.push(p);
  return list;
});
</script>

<template>
  <div class="flex flex-wrap items-center justify-between gap-3 border-t border-slate-100 px-4 py-3 dark:border-slate-800">
    <p class="text-xs text-slate-500 dark:text-slate-400">
      <template v-if="total > 0">Menampilkan <strong>{{ rangeStart }}</strong>&ndash;<strong>{{ rangeEnd }}</strong> dari <strong>{{ total }}</strong> data</template>
      <template v-else>Tidak ada data</template>
    </p>
    <div class="flex items-center gap-3">
      <label class="flex items-center gap-1.5 text-xs text-slate-500">
        Per halaman:
        <select :value="perPage" class="rounded-lg border border-slate-200 px-2 py-1 text-xs dark:border-slate-700 dark:bg-slate-800" @change="emit('change-per-page', Number($event.target.value))">
          <option v-for="opt in [25, 50, 100, 200]" :key="opt" :value="opt">{{ opt }}</option>
        </select>
      </label>
      <div v-if="totalPages > 1" class="flex items-center gap-1">
        <button type="button" :disabled="page <= 1" class="flex h-7 w-7 items-center justify-center rounded-lg border border-slate-200 text-slate-500 disabled:opacity-40 dark:border-slate-700" @click="emit('change-page', page - 1)">
          <ChevronLeft class="h-3.5 w-3.5" />
        </button>
        <button v-if="pages[0] > 1" type="button" class="h-7 min-w-7 rounded-lg border border-slate-200 px-1.5 text-xs dark:border-slate-700" @click="emit('change-page', 1)">1</button>
        <span v-if="pages[0] > 2" class="px-1 text-xs text-slate-400">&hellip;</span>
        <button
          v-for="p in pages"
          :key="p"
          type="button"
          class="h-7 min-w-7 rounded-lg border px-1.5 text-xs font-semibold"
          :class="p === page ? 'border-amber-500 bg-amber-500 text-slate-950' : 'border-slate-200 text-slate-500 dark:border-slate-700'"
          @click="emit('change-page', p)"
        >{{ p }}</button>
        <span v-if="pages[pages.length - 1] < totalPages - 1" class="px-1 text-xs text-slate-400">&hellip;</span>
        <button v-if="pages[pages.length - 1] < totalPages" type="button" class="h-7 min-w-7 rounded-lg border border-slate-200 px-1.5 text-xs dark:border-slate-700" @click="emit('change-page', totalPages)">{{ totalPages }}</button>
        <button type="button" :disabled="page >= totalPages" class="flex h-7 w-7 items-center justify-center rounded-lg border border-slate-200 text-slate-500 disabled:opacity-40 dark:border-slate-700" @click="emit('change-page', page + 1)">
          <ChevronRight class="h-3.5 w-3.5" />
        </button>
      </div>
    </div>
  </div>
</template>
