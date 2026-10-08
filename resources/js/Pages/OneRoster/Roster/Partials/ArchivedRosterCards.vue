<script setup>
import { Link } from '@inertiajs/vue3';
import { Archive, CalendarDays, Eye, Settings } from 'lucide-vue-next';

defineProps({
  rosters: { type: Array, required: true },
  canManage: { type: Boolean, default: false },
});

const emit = defineEmits(['duplicate']);
</script>

<template>
  <section>
    <div class="mb-2 flex flex-wrap items-center gap-2">
      <h2 class="flex items-center gap-1.5 text-base font-semibold text-slate-800"><Archive class="h-4 w-4 text-slate-400" />Arsip</h2>
      <span class="rounded-md bg-slate-800 px-2 py-0.5 text-[11px] font-semibold text-white">{{ rosters.length }}</span>
      <span class="text-xs text-slate-500">Roster yang sudah diarsipkan tidak bisa diubah, tetapi bisa dilihat dan diduplikasi.</span>
    </div>
    <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-3">
      <div v-for="roster in rosters" :key="roster.id" class="rounded-2xl border border-l-4 border-slate-200 border-l-slate-500 bg-slate-50/60 p-4 shadow-sm">
        <div class="flex items-start justify-between gap-2">
          <div class="min-w-0">
            <div class="truncate font-semibold text-slate-800" :title="roster.name">{{ roster.roster_group }}</div>
            <div class="flex items-center gap-1 text-xs text-slate-500"><CalendarDays class="h-3.5 w-3.5" />{{ roster.period_start }} s/d {{ roster.period_end }}</div>
          </div>
          <span class="rounded-md bg-slate-800 px-2 py-0.5 text-[11px] font-semibold text-white">Archived</span>
        </div>
        <div class="mt-2 text-xs text-slate-500">Dibuat {{ roster.created_at }}<br />Diarsipkan {{ roster.updated_at }}</div>
        <div class="mt-3 flex gap-2">
          <Link :href="`/one-roster/roster/${roster.id}`" class="inline-flex items-center gap-1 rounded-lg border border-blue-300 px-2 py-1 text-xs font-semibold text-blue-600 hover:bg-blue-50"><Eye class="h-3.5 w-3.5" />View</Link>
          <button v-if="canManage" type="button" class="inline-flex items-center gap-1 rounded-lg border border-slate-300 px-2 py-1 text-xs font-semibold text-slate-600 hover:bg-white" @click="emit('duplicate', roster)">
            <Settings class="h-3.5 w-3.5" />Duplikat
          </button>
        </div>
      </div>
    </div>
  </section>
</template>
