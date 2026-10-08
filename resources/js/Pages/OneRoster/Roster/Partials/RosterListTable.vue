<script setup>
import { Link } from '@inertiajs/vue3';
import { Eye, Settings, Pencil, Archive } from 'lucide-vue-next';
import RosterStatusBadge from './RosterStatusBadge.vue';

defineProps({
  rosters: { type: Array, required: true },
  canManage: { type: Boolean, default: false },
});

const emit = defineEmits(['duplicate', 'edit', 'archive']);

const BUTTON = 'inline-flex items-center gap-1 rounded-lg border px-2 py-1 text-xs font-semibold';
</script>

<template>
  <div class="overflow-x-auto">
    <table class="w-full min-w-[760px] text-sm">
      <thead class="bg-slate-50 text-left text-xs font-semibold text-slate-600">
        <tr>
          <th class="px-3 py-2.5">No</th><th class="px-3 py-2.5">Roster Group</th><th class="px-3 py-2.5">Period Start</th><th class="px-3 py-2.5">Period End</th>
          <th class="px-3 py-2.5">Status</th><th class="px-3 py-2.5">Created At</th><th class="px-3 py-2.5">Updated At</th><th class="px-3 py-2.5 text-right">Action</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-slate-100">
        <tr v-for="(roster, index) in rosters" :key="roster.id" class="hover:bg-slate-50">
          <td class="px-3 py-2">{{ index + 1 }}</td>
          <td class="px-3 py-2 font-semibold text-slate-800">{{ roster.roster_group }}</td>
          <td class="whitespace-nowrap px-3 py-2">{{ roster.period_start }}</td>
          <td class="whitespace-nowrap px-3 py-2">{{ roster.period_end }}</td>
          <td class="px-3 py-2"><RosterStatusBadge :status="roster.status" :label="roster.status_label" /></td>
          <td class="whitespace-nowrap px-3 py-2 text-xs">{{ roster.created_at }}</td>
          <td class="whitespace-nowrap px-3 py-2 text-xs">{{ roster.updated_at }}</td>
          <td class="whitespace-nowrap px-3 py-2 text-right">
            <div class="inline-flex gap-1.5">
              <Link :href="`/one-roster/roster/${roster.id}`" :class="[BUTTON, 'border-blue-300 text-blue-600 hover:bg-blue-50']"><Eye class="h-3.5 w-3.5" />View</Link>
              <template v-if="canManage">
                <button type="button" title="Duplikat roster (lanjut ke tahun berikutnya)" :class="[BUTTON, 'border-slate-300 text-slate-600 hover:bg-slate-50']" @click="emit('duplicate', roster)"><Settings class="h-3.5 w-3.5" /></button>
                <button type="button" :class="[BUTTON, 'border-slate-300 text-slate-600 hover:bg-slate-50']" @click="emit('edit', roster)"><Pencil class="h-3.5 w-3.5" />Edit</button>
                <button type="button" :class="[BUTTON, 'border-red-300 text-red-600 hover:bg-red-50']" @click="emit('archive', roster)"><Archive class="h-3.5 w-3.5" />Archive</button>
              </template>
            </div>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>
