<script setup>
import { computed } from 'vue';
import { Trash2, UserPlus } from 'lucide-vue-next';
import MatrixDayHeaders from './MatrixDayHeaders.vue';
import MatrixMemberRow from './MatrixMemberRow.vue';
import MatrixSummaryRows from './MatrixSummaryRows.vue';

const props = defineProps({
  section: { type: Object, required: true },
  days: { type: Array, required: true },
  summaryCodes: { type: Object, required: true },
  canEdit: { type: Boolean, default: false },
  edits: { type: Object, required: true },
});

const emit = defineEmits(['add-member', 'delete-section', 'edit-target', 'cell-click', 'save-unit', 'rotation', 'fill', 'move', 'remove']);

const HEAD = 'sticky top-0 border border-slate-300 bg-slate-800 px-1.5 py-1 text-center text-[11px] font-semibold text-white';

const columnCount = computed(() => 5 + props.days.length + (props.canEdit ? 1 : 0));
</script>

<template>
  <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
    <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-100 px-4 py-2">
      <div class="text-sm font-semibold text-slate-800">
        {{ section.name }} <span class="ml-1 rounded-md border border-slate-200 bg-slate-50 px-1.5 py-0.5 text-[11px] font-medium">{{ section.total }} orang</span>
      </div>
      <div v-if="canEdit" class="flex gap-2">
        <button type="button" class="flex items-center gap-1 rounded-lg border border-blue-300 px-2 py-1 text-xs font-semibold text-blue-600 hover:bg-blue-50" @click="emit('add-member')"><UserPlus class="h-3.5 w-3.5" />Tambah Anggota</button>
        <button v-if="!section.total" type="button" title="Hapus section kosong" class="rounded-lg border border-red-300 px-2 py-1 text-red-600 hover:bg-red-50" @click="emit('delete-section')"><Trash2 class="h-3.5 w-3.5" /></button>
      </div>
    </div>
    <div class="max-h-[max(320px,calc(100vh-260px))] overflow-auto">
      <table class="w-max min-w-full border-separate border-spacing-0 text-[12px]">
        <thead>
          <tr>
            <th :class="[HEAD, 'left-0 z-[5] w-[38px] min-w-[38px]']">NO</th>
            <th :class="[HEAD, 'left-[38px] z-[5] w-[76px] min-w-[76px]']">NRP</th>
            <th :class="[HEAD, 'left-[114px] z-[5] w-[200px] min-w-[200px] text-left']">NAMA</th>
            <th :class="[HEAD, 'left-[314px] z-[5] w-[130px] min-w-[130px] shadow-[2px_0_0_#C9CFDA]']">UNIT</th>
            <th :class="[HEAD, 'z-[3]']">KODE OFF</th>
            <MatrixDayHeaders :days="days" />
            <th v-if="canEdit" :class="[HEAD, 'z-[3]']"></th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="!section.members.length"><td :colspan="columnCount" class="py-3 text-center text-slate-500">Belum ada anggota di section ini.</td></tr>
          <MatrixMemberRow
            v-for="(member, index) in section.members" :key="member.id"
            :member="member" :index="index" :days="days" :can-edit="canEdit" :edits="edits"
            @cell-click="emit('cell-click', $event)" @save-unit="emit('save-unit', member, $event)"
            @rotation="emit('rotation', member)" @fill="emit('fill', member)" @move="emit('move', member, section, index + 1)" @remove="emit('remove', member)"
          />
        </tbody>
        <MatrixSummaryRows :section="section" :days="days" :summary-codes="summaryCodes" :can-edit="canEdit" :edits="edits" @edit-target="emit('edit-target')" />
      </table>
    </div>
  </div>
</template>
