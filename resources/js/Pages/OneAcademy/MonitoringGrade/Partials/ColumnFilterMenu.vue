<script setup>
import { computed, nextTick, ref, watch } from 'vue';
import { Filter } from 'lucide-vue-next';

const props = defineProps({
  columnKey: { type: String, required: true },
  label: { type: String, required: true },
  options: { type: Array, default: () => [] },
  activeValues: { type: Array, default: () => [] },
});

const emit = defineEmits(['apply']);

const open = ref(false);
const triggerEl = ref(null);
const menuStyle = ref({});
const search = ref('');
const pending = ref(new Set());

const isActive = computed(() => props.activeValues.length > 0);
const filteredOptions = computed(() => {
  const q = search.value.trim().toLowerCase();
  return q ? props.options.filter((v) => String(v).toLowerCase().includes(q)) : props.options;
});
const allChecked = computed(() => filteredOptions.value.length > 0 && filteredOptions.value.every((v) => pending.value.has(v)));

function toggleOpen() {
  if (open.value) {
    close();
    return;
  }
  pending.value = new Set(props.activeValues);
  search.value = '';
  open.value = true;
  nextTick(positionMenu);
}

function positionMenu() {
  if (! triggerEl.value) return;
  const rect = triggerEl.value.getBoundingClientRect();
  const width = 280;
  let left = rect.left + window.scrollX;
  const maxLeft = window.scrollX + window.innerWidth - width - 10;
  left = Math.min(left, maxLeft);
  left = Math.max(window.scrollX + 10, left);
  menuStyle.value = { top: `${rect.bottom + window.scrollY + 4}px`, left: `${left}px` };
}

function close() {
  open.value = false;
}

function toggleValue(v) {
  if (pending.value.has(v)) pending.value.delete(v);
  else pending.value.add(v);
  pending.value = new Set(pending.value);
}

function toggleAll() {
  if (allChecked.value) {
    filteredOptions.value.forEach((v) => pending.value.delete(v));
  } else {
    filteredOptions.value.forEach((v) => pending.value.add(v));
  }
  pending.value = new Set(pending.value);
}

function apply() {
  emit('apply', { key: props.columnKey, values: Array.from(pending.value) });
  close();
}

function clearAndApply() {
  emit('apply', { key: props.columnKey, values: [] });
  close();
}

watch(open, (v) => {
  if (v) document.addEventListener('click', onClickOutside, true);
  else document.removeEventListener('click', onClickOutside, true);
});

function onClickOutside(e) {
  if (triggerEl.value?.contains(e.target)) return;
  const menu = document.getElementById(`mg-filter-menu-${props.columnKey}`);
  if (menu?.contains(e.target)) return;
  close();
}
</script>

<template>
  <button
    ref="triggerEl"
    type="button"
    class="ml-1 inline-flex h-4 w-4 shrink-0 items-center justify-center rounded"
    :class="isActive ? 'text-amber-500' : 'text-slate-400 hover:text-slate-600'"
    @click.stop="toggleOpen"
  >
    <Filter class="h-3 w-3" :fill="isActive ? 'currentColor' : 'none'" />
  </button>

  <Teleport to="body">
    <div
      v-if="open"
      :id="`mg-filter-menu-${columnKey}`"
      class="fixed z-[100] flex max-h-[420px] w-[280px] flex-col rounded-xl border border-slate-200 bg-white shadow-2xl dark:border-slate-700 dark:bg-slate-900"
      :style="menuStyle"
      @click.stop
    >
      <div class="border-b border-slate-100 px-3 py-2 text-xs font-bold text-slate-700 dark:border-slate-800 dark:text-slate-200">Filter "{{ label }}"</div>
      <div class="border-b border-slate-100 p-2 dark:border-slate-800">
        <input v-model="search" type="text" placeholder="Cari..." class="w-full rounded-lg border border-slate-200 px-2 py-1 text-xs dark:border-slate-700 dark:bg-slate-800">
      </div>
      <div class="flex-1 overflow-y-auto py-1">
        <label class="flex cursor-pointer items-center gap-2 px-3 py-1.5 text-xs font-bold hover:bg-slate-50 dark:hover:bg-slate-800">
          <input type="checkbox" :checked="allChecked" @change="toggleAll"> (Pilih Semua)
        </label>
        <label v-for="v in filteredOptions" :key="v" class="flex cursor-pointer items-center gap-2 truncate px-3 py-1.5 text-xs hover:bg-slate-50 dark:hover:bg-slate-800">
          <input type="checkbox" :checked="pending.has(v)" @change="toggleValue(v)">
          <span class="truncate">{{ v }}</span>
        </label>
        <p v-if="!filteredOptions.length" class="px-3 py-2 text-xs text-slate-400">Tidak ada nilai.</p>
      </div>
      <div class="flex justify-end gap-2 border-t border-slate-100 px-3 py-2 dark:border-slate-800">
        <button type="button" class="rounded-lg bg-slate-100 px-3 py-1 text-xs font-semibold dark:bg-slate-800" @click="clearAndApply">Reset</button>
        <button type="button" class="rounded-lg bg-amber-500 px-3 py-1 text-xs font-bold text-slate-950" @click="apply">OK</button>
      </div>
    </div>
  </Teleport>
</template>
