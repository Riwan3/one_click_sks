<script setup>
import ComplianceBar from './ComplianceBar.vue';

defineProps({
  title: { type: String, required: true },
  icon: { type: [Object, Function], required: true },
  iconClass: { type: String, default: '' },
  labelHeader: { type: String, required: true },
  rows: { type: Array, default: () => [] },
  showAssignment: { type: Boolean, default: false },
});
</script>

<template>
  <div class="h-full overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
    <div class="flex items-center gap-2 border-b border-slate-100 px-5 py-3 text-sm font-bold text-slate-800">
      <component :is="icon" :class="['h-4 w-4', iconClass]" /> {{ title }}
    </div>
    <div class="overflow-x-auto">
      <table class="w-full text-left text-[13px] text-slate-700">
        <thead class="border-b-2 border-slate-100 text-xs font-bold uppercase tracking-wide text-slate-500">
          <tr>
            <th class="py-2 pl-4 pr-2">#</th>
            <th class="px-2 py-2">{{ labelHeader }}</th>
            <th v-if="showAssignment" class="px-2 py-2 text-center">Assignment</th>
            <th class="w-40 px-4 py-2 text-center">Compliance %</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
          <tr v-if="rows.length === 0">
            <td :colspan="showAssignment ? 4 : 3" class="py-6 text-center text-slate-400">Belum ada data</td>
          </tr>
          <tr v-for="(row, idx) in rows" :key="row.label" class="hover:bg-slate-50">
            <td class="py-2.5 pl-4 pr-2 text-slate-400">{{ idx + 1 }}</td>
            <td class="px-2 py-2.5 font-semibold">{{ row.label }}</td>
            <td v-if="showAssignment" class="px-2 py-2.5 text-center">{{ row.total }}</td>
            <td class="px-4 py-2.5 text-center"><ComplianceBar :percent="row.persen" /></td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>
