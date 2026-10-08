<script setup>
defineProps({
  report: { type: Object, required: true }, // { stats: [[label, value]], warnings: [], lists: [{ title, tone, items }] }
});

const MAX_ITEMS = 300;

const TONE_CLASS = { danger: 'text-red-600', warning: 'text-amber-600', success: 'text-emerald-600' };
</script>

<template>
  <div class="space-y-3">
    <table class="w-full border border-slate-200 text-xs">
      <tbody>
        <tr v-for="[label, value] in report.stats ?? []" :key="label" class="border-b border-slate-100">
          <td class="px-3 py-1.5 text-slate-500">{{ label }}</td>
          <td class="px-3 py-1.5 text-right font-semibold">{{ value }}</td>
        </tr>
      </tbody>
    </table>
    <p v-for="warning in report.warnings ?? []" :key="warning" class="rounded-lg bg-amber-50 px-3 py-2 text-xs text-amber-800">{{ warning }}</p>
    <div v-for="list in report.lists ?? []" :key="list.title">
      <p :class="['text-xs font-semibold', TONE_CLASS[list.tone] ?? 'text-slate-600']">{{ list.title }}</p>
      <ul class="max-h-48 list-disc space-y-0.5 overflow-y-auto pl-5 text-xs text-slate-700">
        <li v-for="item in (list.items ?? []).slice(0, MAX_ITEMS)" :key="item">{{ item }}</li>
        <li v-if="(list.items ?? []).length > MAX_ITEMS">... dan {{ list.items.length - MAX_ITEMS }} lainnya</li>
      </ul>
    </div>
  </div>
</template>
