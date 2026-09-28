<script setup>
import { ref, computed } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import { Calendar, Inbox, Plus, Eye } from 'lucide-vue-next';
import { complianceStatusOf, rowPercentColor, averagePercentColor, formatLongDate, formatSlashDate } from '@/utils/odrCompliance';

const props = defineProps({
  rows: { type: Array, default: () => [] },
  tanggal: { type: String, required: true },
  assignmentUrl: { type: String, required: true },
  canManage: { type: Boolean, default: false },
});

const emit = defineEmits(['detail-requested']);

const savingKey = ref(null);

const taskColumnCount = computed(() => Math.max(1, ...props.rows.map((r) => r.tasks.length)));

const averagePct = computed(() => {
  if (props.rows.length === 0) return 0;
  const sum = props.rows.reduce((acc, r) => acc + r.compliance.pct, 0);
  return Math.round((sum / props.rows.length) * 10) / 10;
});

const truncate = (text) => (text.length > 20 ? `${text.slice(0, 20)}…` : text);

const toggleTask = (row, taskIndex) => {
  const key = `${row.id}-${taskIndex}`;
  savingKey.value = key;
  router.post(`/one-reporting/daily/${row.id}/tasks`, { task_index: taskIndex, value: !row.done[taskIndex] }, {
    preserveScroll: true,
    preserveState: true,
    onFinish: () => { if (savingKey.value === key) savingKey.value = null; },
  });
};
</script>

<template>
  <div class="overflow-hidden rounded-2xl bg-white shadow-md">
    <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-100 bg-slate-50 px-4 py-2 text-[13px] font-bold text-slate-600">
      <span class="inline-flex items-center gap-1">
        <Calendar class="h-3.5 w-3.5" />{{ formatLongDate(tanggal) }} · <span class="text-red-600">{{ rows.length }}</span> data
      </span>
      <span v-if="rows.length > 0">
        Rata-rata Compliance: <span class="text-base" :style="{ color: averagePercentColor(averagePct) }">{{ averagePct }}%</span>
      </span>
    </div>

    <div class="overflow-x-auto">
      <table class="w-full text-left text-[13px] text-slate-700">
        <thead class="whitespace-nowrap border-b-2 border-slate-100 text-xs font-bold uppercase tracking-wide text-slate-500">
          <tr>
            <th class="py-2 pl-4 pr-2">#</th>
            <th class="px-2 py-2">Tanggal</th>
            <th class="px-2 py-2">Shift</th>
            <th class="px-2 py-2">Area</th>
            <th class="px-2 py-2">Unit</th>
            <th class="px-2 py-2">Pengawas</th>
            <th v-for="ti in taskColumnCount" :key="ti" class="min-w-[100px] px-2 py-2 text-center">Task {{ ti }}</th>
            <th class="min-w-[110px] px-2 py-2 text-center">Compliance %</th>
            <th class="px-2 py-2 text-center">Status</th>
            <th class="py-2 pl-2 pr-4 text-center">Aksi</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
          <tr v-if="rows.length === 0">
            <td :colspan="9 + taskColumnCount" class="py-10 text-center">
              <Inbox class="mx-auto h-8 w-8 text-slate-400" />
              <div class="mt-2 text-sm text-slate-500">Belum ada data untuk tanggal ini</div>
              <Link v-if="canManage" :href="assignmentUrl" class="mt-2 inline-flex items-center gap-1 rounded-lg bg-red-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-red-700">
                <Plus class="h-3.5 w-3.5" />Tambah Assignment
              </Link>
            </td>
          </tr>
          <tr v-for="(row, idx) in rows" :key="row.id" class="hover:bg-slate-50">
            <td class="py-2.5 pl-4 pr-2 text-slate-400">{{ idx + 1 }}</td>
            <td class="whitespace-nowrap px-2 py-2.5">{{ formatSlashDate(row.tanggal) }}</td>
            <td class="px-2 py-2.5"><span class="rounded bg-amber-100 px-2 py-0.5 text-xs font-semibold text-amber-800">{{ row.shift }}</span></td>
            <td class="px-2 py-2.5"><span class="rounded bg-blue-50 px-2 py-0.5 text-xs font-semibold text-blue-700">{{ row.area }}</span></td>
            <td class="px-2 py-2.5 text-slate-500">{{ row.unit }}</td>
            <td class="px-2 py-2.5 font-semibold">{{ row.pengawas }}</td>
            <td v-for="ti in taskColumnCount" :key="ti" class="px-2 py-2.5 text-center">
              <div v-if="row.tasks[ti - 1] !== undefined" class="flex flex-col items-center gap-1">
                <button
                  type="button"
                  :title="row.tasks[ti - 1]"
                  :disabled="!canManage || savingKey === `${row.id}-${ti - 1}`"
                  :class="[
                    'flex h-[30px] w-[30px] items-center justify-center rounded-lg border-2 text-sm font-extrabold transition disabled:cursor-not-allowed disabled:opacity-50',
                    row.done[ti - 1] ? 'border-emerald-500 bg-emerald-500 text-white' : 'border-slate-200 bg-white text-slate-300',
                  ]"
                  @click="toggleTask(row, ti - 1)"
                >
                  {{ row.done[ti - 1] ? '✓' : '–' }}
                </button>
                <span v-if="savingKey === `${row.id}-${ti - 1}`" class="text-[10px] text-slate-400">saving…</span>
                <span class="block max-w-[110px] text-[10px] leading-tight text-slate-400" :title="row.tasks[ti - 1]">{{ truncate(row.tasks[ti - 1]) }}</span>
              </div>
              <span v-else class="text-xs text-slate-400">—</span>
            </td>
            <td class="px-2 py-2.5 text-center">
              <div class="text-sm font-bold" :style="{ color: rowPercentColor(row.compliance.pct) }">{{ row.compliance.pct }}%</div>
              <div class="mx-auto mt-1 h-[7px] w-20 overflow-hidden rounded bg-slate-100">
                <div class="h-full rounded transition-all" :style="{ width: `${row.compliance.pct}%`, background: rowPercentColor(row.compliance.pct) }" />
              </div>
              <div class="mt-0.5 text-[11px] text-slate-400">{{ row.compliance.done }}/{{ row.compliance.total }} task</div>
            </td>
            <td class="px-2 py-2.5 text-center">
              <span :class="['inline-flex items-center gap-1 whitespace-nowrap rounded-full px-3 py-1 text-xs font-bold', complianceStatusOf(row.compliance.status).badge]">
                <span :class="['h-2.5 w-2.5 rounded-full', complianceStatusOf(row.compliance.status).dot]" />
                {{ complianceStatusOf(row.compliance.status).label }}
              </span>
            </td>
            <td class="py-2.5 pl-2 pr-4 text-center">
              <button type="button" title="Detail" class="rounded-lg bg-slate-100 p-1.5 text-blue-600 hover:bg-slate-200" @click="emit('detail-requested', row)">
                <Eye class="h-4 w-4" />
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>
