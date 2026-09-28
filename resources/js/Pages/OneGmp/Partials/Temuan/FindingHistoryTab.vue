<script setup>
defineProps({
  timeline: { type: Array, default: () => [] },
  statusLabels: { type: Object, default: () => ({}) },
});

const dotClass = (action) => {
  if (action.includes('verification')) return 'bg-green-600';
  if (action === 'create') return 'bg-slate-400';
  return 'bg-blue-600';
};
</script>

<template>
  <div>
    <h6 class="mb-4 text-xs font-bold uppercase tracking-wide text-blue-700">Audit Trail &amp; Timeline</h6>
    <p v-if="timeline.length === 0" class="text-sm italic text-slate-400">Belum ada riwayat aktivitas.</p>
    <div v-else class="ml-3 border-l-2 border-blue-100 pb-2">
      <div v-for="t in timeline" :key="t.id" class="relative mb-5 pl-5">
        <span :class="['absolute -left-[8px] top-1 h-3.5 w-3.5 rounded-full', dotClass(t.action_type)]" />
        <div class="mb-0.5 text-xs font-bold text-slate-400">{{ t.created_at }} - {{ t.aktor_nama }}</div>
        <div class="font-bold text-slate-800">{{ t.description }}</div>
        <div class="mt-1 rounded-lg bg-slate-50 p-2 text-xs text-slate-500">Status menjadi: {{ statusLabels[t.new_status] ?? t.new_status }}</div>
      </div>
    </div>
  </div>
</template>
