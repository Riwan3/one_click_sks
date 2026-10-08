<script setup>
import { computed } from 'vue';
import { CheckCircle2, Lock } from 'lucide-vue-next';
import RosterStatusBadge from './RosterStatusBadge.vue';

const props = defineProps({
  roster: { type: Object, required: true },
  totalDetail: { type: Number, required: true },
});

const isArchived = computed(() => props.roster.status === 'archived');
const isPublished = computed(() => props.roster.status === 'published');
</script>

<template>
  <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
    <div class="grid grid-cols-2 gap-4 md:grid-cols-4">
      <div><div class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">Roster Group</div><div class="font-semibold text-slate-800">{{ roster.roster_group }}</div></div>
      <div><div class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">Period</div><div class="font-semibold text-slate-800">{{ roster.period_start }} s/d {{ roster.period_end }}</div></div>
      <div><div class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">Status</div><RosterStatusBadge :status="roster.status" :label="roster.status_label" /></div>
      <div><div class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">Total Detail</div><div class="font-semibold text-slate-800">{{ totalDetail }}</div></div>
      <div>
        <div class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">PIC Roster</div>
        <div class="font-semibold text-slate-800">
          {{ roster.pic_name || '-' }}<span v-if="roster.pic_jabatan" class="ml-1 text-xs font-normal text-slate-500">({{ roster.pic_jabatan }})</span>
        </div>
      </div>
      <div v-if="roster.notes" class="col-span-2 md:col-span-4"><div class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">Catatan</div><div>{{ roster.notes }}</div></div>
    </div>
    <div v-if="isArchived" class="mt-3 flex items-center gap-1.5 rounded-lg border border-slate-200 bg-slate-100 px-3 py-2 text-xs text-slate-700">
      <Lock class="h-4 w-4" />Roster berstatus <strong>{{ roster.status_label }}</strong> — isi roster terkunci dan tidak dapat diubah.
    </div>
    <div v-else-if="isPublished" class="mt-3 flex items-center gap-1.5 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs text-emerald-800">
      <CheckCircle2 class="h-4 w-4" />Roster sudah <strong>dipublish</strong> (final). Penyesuaian tetap bisa dilakukan; perubahan langsung berlaku.
    </div>
  </div>
</template>
