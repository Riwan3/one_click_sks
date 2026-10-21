<script setup>
import { computed } from 'vue';
import ColumnFilterMenu from './ColumnFilterMenu.vue';

const props = defineProps({
  columns: { type: Array, required: true },
  groups: { type: Object, default: () => ({}) },
  rows: { type: Array, required: true },
  rowOffset: { type: Number, default: 0 },
  pageFilterValues: { type: Object, default: () => ({}) },
  activeFilters: { type: Object, default: () => ({}) },
  stickyCount: { type: Number, default: 3 },
  leftAlignKeys: { type: Array, default: () => [] },
  badgeKeys: { type: Object, default: () => ({}) },
});

const emit = defineEmits(['filter']);

// Literal di sini (bukan dari config PHP) supaya Tailwind JIT bisa memindai & menghasilkan kelasnya.
const GROUP_COLORS = {
  grp1: 'bg-amber-100 text-amber-900 dark:bg-amber-900/30 dark:text-amber-300',
  grp2: 'bg-emerald-100 text-emerald-900 dark:bg-emerald-900/30 dark:text-emerald-300',
  grp3: 'bg-blue-100 text-blue-900 dark:bg-blue-900/30 dark:text-blue-300',
  grp4: 'bg-pink-100 text-pink-900 dark:bg-pink-900/30 dark:text-pink-300',
  grp5: 'bg-orange-100 text-orange-900 dark:bg-orange-900/30 dark:text-orange-300',
  grp6: 'bg-purple-100 text-purple-900 dark:bg-purple-900/30 dark:text-purple-300',
  grp7: 'bg-rose-100 text-rose-900 dark:bg-rose-900/30 dark:text-rose-300',
};

const hasGroups = computed(() => props.columns.some((c) => c.group));

/** Lebar tetap kolom sticky (No, NRP, NAMA, JABATAN): offset left dihitung dari sini, jadi lebar sel harus persis sama. */
const STICKY_WIDTHS = [56, 100, 220, 220];
const HEADER_GROUP_ROW_HEIGHT = 36;

const stickyOffsets = computed(() => {
  let acc = 0;
  return Array.from({ length: props.stickyCount }, (_, i) => {
    const left = acc;
    acc += STICKY_WIDTHS[i] ?? 200;
    return left;
  });
});

function isSticky(idx) {
  return idx < props.stickyCount;
}

function stickyStyle(idx) {
  if (!isSticky(idx)) return {};
  const width = `${STICKY_WIDTHS[idx] ?? 200}px`;
  return { left: `${stickyOffsets.value[idx]}px`, width, minWidth: width, maxWidth: width };
}

/** Kelompokkan kolom berurutan yang punya `group` sama, untuk header baris pertama (colspan). */
const groupedHeader = computed(() => {
  const result = [];
  let i = 0;
  while (i < props.columns.length) {
    const col = props.columns[i];
    if (! col.group) {
      result.push({ type: 'col', col, colIndex: i });
      i++;
      continue;
    }
    let span = 1;
    while (i + span < props.columns.length && props.columns[i + span].group === col.group) span++;
    result.push({ type: 'group', key: col.group, span, startIndex: i });
    i += span;
  }
  return result;
});

function alignClass(key) {
  return props.leftAlignKeys.includes(key) ? 'text-left' : 'text-center';
}

function badgeClass(key) {
  return props.badgeKeys[key] ?? null;
}
</script>

<template>
  <div class="max-h-[72vh] overflow-auto rounded-2xl border border-slate-200 dark:border-slate-800">
    <table class="w-full border-separate border-spacing-0 text-left text-xs">
      <thead>
        <tr v-if="hasGroups" class="bg-slate-50 text-center font-bold uppercase tracking-wide text-slate-500 dark:bg-slate-800/60 dark:text-slate-400">
          <th rowspan="2" class="sticky left-0 top-0 z-30 border-b border-r border-slate-200 bg-slate-50 p-2 dark:border-slate-700 dark:bg-slate-800/60" :style="stickyStyle(0)">No</th>
          <template v-for="(item, gi) in groupedHeader" :key="gi">
            <th
              v-if="item.type === 'col'"
              :rowspan="2"
              class="sticky top-0 z-20 whitespace-nowrap border-b border-r border-slate-200 bg-slate-50 p-2 dark:border-slate-700 dark:bg-slate-800/60"
              :class="isSticky(item.colIndex + 1) ? 'sticky z-30' : ''"
              :style="stickyStyle(item.colIndex + 1)"
            >
              {{ item.col.label }}
            </th>
            <th v-else :colspan="item.span" class="sticky top-0 z-10 border-b border-r border-slate-200 p-2 dark:border-slate-700" :class="GROUP_COLORS[item.key]" :style="{ height: `${HEADER_GROUP_ROW_HEIGHT}px` }">
              {{ groups[item.key]?.label ?? item.key }}
            </th>
          </template>
        </tr>
        <tr class="bg-slate-50 text-center font-bold uppercase tracking-wide text-slate-500 dark:bg-slate-800/60 dark:text-slate-400">
          <th v-if="!hasGroups" class="sticky left-0 top-0 z-30 border-b border-r border-slate-200 bg-slate-50 p-2 dark:border-slate-700 dark:bg-slate-800/60" :style="stickyStyle(0)">No</th>
          <template v-for="(col, idx) in columns" :key="col.key">
            <th
              v-if="!hasGroups || col.group"
              class="sticky top-0 z-10 whitespace-nowrap border-b border-r border-slate-200 p-2 dark:border-slate-700"
              :class="[isSticky(idx + 1) && !hasGroups ? 'sticky z-30 bg-slate-50 dark:bg-slate-800/60' : (col.group ? GROUP_COLORS[col.group] : 'bg-slate-50 dark:bg-slate-800/60')]"
              :style="hasGroups ? { top: `${HEADER_GROUP_ROW_HEIGHT}px` } : stickyStyle(idx + 1)"
            >
              <span class="inline-flex items-center">
                {{ col.label }}
                <ColumnFilterMenu
                  v-if="pageFilterValues[col.key]?.length"
                  :column-key="col.key"
                  :label="col.label"
                  :options="pageFilterValues[col.key]"
                  :active-values="activeFilters[col.key] || []"
                  @apply="emit('filter', $event)"
                />
              </span>
            </th>
          </template>
        </tr>
      </thead>
      <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
        <tr v-if="!rows.length">
          <td :colspan="columns.length + 1" class="p-10 text-center text-slate-400">Belum ada data.</td>
        </tr>
        <tr v-for="(row, ri) in rows" :key="ri" class="hover:bg-slate-50 dark:hover:bg-slate-800/40">
          <td class="sticky left-0 z-10 border-r border-slate-100 bg-white p-2 text-center font-bold text-slate-400 dark:border-slate-800 dark:bg-slate-900" :style="stickyStyle(0)">{{ rowOffset + ri + 1 }}</td>
          <td
            v-for="(col, idx) in columns"
            :key="col.key"
            class="border-r border-slate-100 bg-white p-2 dark:border-slate-800 dark:bg-slate-900"
            :class="[alignClass(col.key), isSticky(idx + 1) ? 'sticky z-10' : '']"
            :style="stickyStyle(idx + 1)"
          >
            <span v-if="badgeClass(col.key) && row[col.key]" class="rounded px-2 py-0.5 text-[11px] font-bold" :class="badgeClass(col.key)">{{ row[col.key] }}</span>
            <template v-else>{{ row[col.key] ?? '-' }}</template>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>
