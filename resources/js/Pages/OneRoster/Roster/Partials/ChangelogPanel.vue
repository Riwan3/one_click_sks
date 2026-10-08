<script setup>
import { computed, ref } from 'vue';
import { Clock, Info } from 'lucide-vue-next';
import { shortDate } from '@/utils/rosterMatrix';

const props = defineProps({
  changelog: { type: Object, required: true }, // { ready, days, total, rows }
  canManage: { type: Boolean, default: false },
});

// action => [label, kelas badge]
const ACTION_META = {
  jadwal: ['Jadwal', 'bg-blue-600 text-white'],
  anggota: ['Anggota', 'bg-sky-500 text-white'],
  putaran: ['Putaran', 'bg-slate-500 text-white'],
  unit: ['Unit', 'bg-slate-500 text-white'],
  section: ['Section', 'bg-amber-400 text-slate-900'],
  target: ['Target', 'bg-amber-400 text-slate-900'],
  status: ['Status', 'bg-red-600 text-white'],
  roster: ['Roster', 'bg-slate-800 text-white'],
  import: ['Import', 'bg-emerald-600 text-white'],
  detail: ['Detail', 'bg-slate-200 text-slate-800'],
};

const actionFilter = ref('');
const search = ref('');

const metaOf = (action) => ACTION_META[action] ?? [action.charAt(0).toUpperCase() + action.slice(1), 'bg-slate-500 text-white'];
const actorOf = (row) => `${row.actor_name || '-'}${row.actor_nrp ? ` (${row.actor_nrp})` : ''}`.trim();
const targetOf = (row) => (row.target_nrp ? `${row.target_name || ''} (${row.target_nrp})`.trim() : '');
const timeOf = (row) => row.created_at.slice(11, 16);

const dayLabel = (day) => {
  const today = new Date();
  const toYmd = (date) => `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}-${String(date.getDate()).padStart(2, '0')}`;
  const yesterday = new Date(today.getTime() - 86400000);
  const prefix = { [toYmd(today)]: 'Hari ini · ', [toYmd(yesterday)]: 'Kemarin · ' }[day] ?? '';
  return `${prefix}${shortDate(day)}`;
};

const filteredRows = computed(() => {
  const term = search.value.trim().toLowerCase();
  return props.changelog.rows.filter((row) => {
    if (actionFilter.value && row.action !== actionFilter.value) return false;
    return !term || `${actorOf(row)} ${targetOf(row)} ${row.summary}`.toLowerCase().includes(term);
  });
});

const groups = computed(() => {
  const byDay = {};
  filteredRows.value.forEach((row) => { (byDay[row.created_at.slice(0, 10)] ??= []).push(row); });
  return Object.entries(byDay);
});

const hasRows = computed(() => props.changelog.rows.length > 0);
</script>

<template>
  <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
    <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-100 px-4 py-2">
      <span class="flex items-center gap-1.5 text-sm font-semibold text-slate-800">
        <Clock class="h-4 w-4" />Riwayat Perubahan <span class="text-xs font-normal text-slate-500">({{ changelog.days }} hari terakhir)</span>
      </span>
      <span v-if="changelog.ready && hasRows" class="flex gap-2">
        <select v-model="actionFilter" class="rounded-lg border border-slate-300 bg-white px-2 py-1 text-xs">
          <option value="">Semua jenis</option>
          <option v-for="(meta, action) in ACTION_META" :key="action" :value="action">{{ meta[0] }}</option>
        </select>
        <input v-model="search" type="search" placeholder="Cari nama / NRP / kata..." class="w-48 rounded-lg border border-slate-300 px-2 py-1 text-xs" />
      </span>
    </div>

    <p v-if="!changelog.ready" class="flex items-center gap-1.5 p-4 text-xs text-slate-500">
      <Info class="h-4 w-4" />Riwayat perubahan belum aktif.<template v-if="canManage"> Jalankan migration One-Roster di server.</template>
    </p>
    <div v-else-if="!hasRows" class="px-4 py-8 text-center text-slate-500">
      <Clock class="mx-auto h-8 w-8 text-slate-300" />
      <p class="mt-2 text-sm">Belum ada perubahan dalam {{ changelog.days }} hari terakhir.</p>
    </div>
    <template v-else>
      <p v-if="changelog.total > changelog.rows.length" class="px-4 pt-2 text-xs text-slate-500">Menampilkan {{ changelog.rows.length }} perubahan terbaru dari {{ changelog.total }}.</p>
      <div class="max-h-[420px] overflow-y-auto">
        <template v-for="[day, rows] in groups" :key="day">
          <div class="sticky top-0 border-y border-slate-100 bg-slate-50 px-4 py-1 text-xs font-semibold text-slate-600">
            {{ dayLabel(day) }} <span class="font-normal text-slate-500">· {{ rows.length }} perubahan</span>
          </div>
          <div v-for="row in rows" :key="row.id" class="flex flex-wrap items-baseline gap-2.5 border-b border-slate-50 px-4 py-1.5 text-sm hover:bg-slate-50 sm:flex-nowrap">
            <span class="w-10 shrink-0 text-xs tabular-nums text-slate-500">{{ timeOf(row) }}</span>
            <span :class="['w-20 shrink-0 rounded-md px-1.5 py-0.5 text-[11px] font-semibold', metaOf(row.action)[1]]">{{ metaOf(row.action)[0] }}</span>
            <span class="min-w-0 flex-1 break-words">
              <strong>{{ actorOf(row) }}</strong> {{ row.summary }}
              <span v-if="targetOf(row)" class="ml-1.5 inline-block whitespace-nowrap rounded-full bg-indigo-50 px-2 text-xs text-indigo-800">{{ targetOf(row) }}</span>
            </span>
          </div>
        </template>
        <p v-if="!groups.length" class="py-3 text-center text-xs text-slate-500">Tidak ada perubahan yang cocok dengan filter.</p>
      </div>
    </template>
  </div>
</template>
