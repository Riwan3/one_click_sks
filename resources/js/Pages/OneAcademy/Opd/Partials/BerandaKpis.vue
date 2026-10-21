<script setup>
defineProps({
  counts: { type: Object, required: true },
  active: { type: String, required: true },
});

const emit = defineEmits(['select']);

const CARDS = [
  { key: 'all', field: 'total', label: 'Total Karyawan', color: '#475569' },
  { key: 'under', field: 'under', label: '1 · Under Grade', color: '#dc2626' },
  { key: 'bad', field: 'bad', label: '2 · Grade Tidak Sesuai', color: '#d97706' },
  { key: 'tiga', field: 'tiga', label: '3 · ≥ 3 Tahun di Grade', color: '#2563eb' },
  { key: 'na', field: 'na', label: 'Belum Ada Data', color: '#94a3b8' },
];

const percentOf = (count, total) => (total > 0 ? Math.round((count * 100) / total) : 0);
</script>

<template>
  <div class="grid grid-cols-2 gap-3 lg:grid-cols-5">
    <button
      v-for="card in CARDS"
      :key="card.key"
      type="button"
      class="rounded-2xl border-2 border-t-4 bg-white p-4 text-left shadow-sm transition hover:-translate-y-0.5 hover:shadow-md"
      :style="{ borderColor: active === card.key ? card.color : 'transparent', borderTopColor: card.color }"
      @click="emit('select', card.key)"
    >
      <div class="text-[11px] font-bold uppercase tracking-wide text-slate-500">{{ card.label }}</div>
      <div class="mt-1 text-3xl font-extrabold" :style="{ color: card.color }">
        {{ counts[card.field] }}
        <small v-if="card.key !== 'all'" class="ml-1 text-xs font-semibold text-slate-400">{{ percentOf(counts[card.field], counts.total) }}%</small>
      </div>
    </button>
  </div>
</template>
