<script setup>
import { computed } from 'vue';
import { UserPlus, X } from 'lucide-vue-next';
import { splitColumns } from '@/utils/manpowerV201';

const props = defineProps({
  csa: { type: Object, required: true },
  isEditable: { type: Boolean, default: false },
});

const emit = defineEmits(['plot', 'unassign', 'resolve-double']);

const columns = computed(() => Object.values(splitColumns(props.csa.categories)).filter((list) => list.length));
const isTwoColumns = computed(() => columns.value.length === 2);
</script>

<template>
  <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm">
    <div class="bg-slate-800 px-4 py-2 text-sm font-bold text-white">{{ csa.name }}</div>

    <div :class="['grid gap-3 p-3', isTwoColumns ? 'lg:grid-cols-2' : '']">
      <div v-for="(categories, index) in columns" :key="index" class="space-y-3">
        <div v-for="cat in categories" :key="cat.id" class="overflow-x-auto rounded-lg border border-slate-200">
          <table class="w-full min-w-[26rem] border-collapse text-xs">
            <thead>
              <tr><th colspan="5" class="bg-emerald-800 px-2 py-1 text-center text-[11px] font-bold text-white">{{ cat.name }}</th></tr>
              <tr class="bg-slate-50 text-left text-[11px] text-slate-600">
                <th class="w-24 px-2 py-1">No Unit</th>
                <th class="w-28 px-2 py-1">Telepon</th>
                <th class="w-20 px-2 py-1">NRP</th>
                <th class="px-2 py-1">Nama</th>
                <th class="w-14 px-2 py-1" />
              </tr>
            </thead>
            <tbody>
              <tr v-for="u in cat.units" :key="u.id" class="border-t border-slate-100">
                <td :class="['px-2 py-1 font-bold', u.is_high_risk ? 'bg-red-600 text-white' : '']">{{ u.unit }}</td>

                <template v-if="u.assignment">
                  <td class="px-2 py-1">{{ u.assignment.phone }}</td>
                  <td class="px-2 py-1">{{ u.assignment.nrp }}</td>
                  <td :class="['px-2 py-1', u.assignment.spare ? 'bg-lime-300 font-semibold' : '']">
                    {{ u.assignment.employee_name }}
                    <span v-if="u.assignment.absent" class="ml-1 rounded bg-red-600 px-1.5 py-0.5 text-[10px] font-bold text-white" title="Tidak hadir menurut Roster V2 saat ini">tidak hadir</span>
                    <span v-if="u.assignment.source === 'manual' && !u.is_spare_slot" class="ml-1 rounded border border-slate-300 bg-slate-50 px-1.5 py-0.5 text-[10px]">manual</span>
                  </td>
                  <td class="px-2 py-1 text-right">
                    <button v-if="isEditable" type="button" class="rounded border border-red-300 p-1 text-red-600 hover:bg-red-50" title="Lepas dari unit" @click="emit('unassign', u)"><X class="h-3 w-3" /></button>
                  </td>
                </template>

                <td v-else-if="u.double.length" colspan="3" class="bg-amber-50 px-2 py-1">
                  <span class="mr-1 rounded bg-amber-400 px-1.5 py-0.5 text-[10px] font-bold">DOUBLE</span> Pilih satu:
                  <button
                    v-for="c in u.double"
                    :key="c.nrp"
                    type="button"
                    :disabled="!isEditable"
                    class="ml-1 rounded border border-slate-700 px-1.5 text-[11px] hover:bg-slate-100 disabled:opacity-50"
                    @click="emit('resolve-double', u, c)"
                  >
                    {{ c.name }} ({{ c.nrp }})
                  </button>
                </td>

                <template v-else>
                  <td colspan="3" class="px-2 py-1 italic text-slate-400">kosong</td>
                  <td class="px-2 py-1 text-right">
                    <button v-if="isEditable" type="button" class="rounded bg-blue-600 p-1 text-white hover:bg-blue-700" title="Plot orang ke unit ini" @click="emit('plot', u, cat)"><UserPlus class="h-3 w-3" /></button>
                  </td>
                </template>

                <td v-if="u.double.length && !u.assignment" />
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</template>
