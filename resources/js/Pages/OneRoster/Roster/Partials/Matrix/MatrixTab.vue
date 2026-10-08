<script setup>
import { computed, ref, watch } from 'vue';
import { LayoutGrid } from 'lucide-vue-next';
import { useRosterAction } from '@/composables/useRosterAction';
import { useRosterMatrixEdits } from '@/composables/useRosterMatrixEdits';
import MatrixToolbar from './MatrixToolbar.vue';
import MatrixLegend from './MatrixLegend.vue';
import MatrixSection from './MatrixSection.vue';
import MatrixLooseTable from './MatrixLooseTable.vue';
import ShiftPopover from './ShiftPopover.vue';
import MemberModal from './MemberModal.vue';
import MoveMemberModal from './MoveMemberModal.vue';
import RotationModal from './RotationModal.vue';
import TargetModal from './TargetModal.vue';
import FillRangeModal from './FillRangeModal.vue';

const props = defineProps({
  roster: { type: Object, required: true },
  matrix: { type: Object, required: true },
  ctxQuery: { type: String, default: '' },
  canEdit: { type: Boolean, default: false },
});

const { perform } = useRosterAction();
const edits = useRosterMatrixEdits(props.roster.id);
const base = computed(() => `/one-roster/roster/${props.roster.id}`);

const popover = ref(null); // { member, date, anchor }
const memberSection = ref(null);
const targetSection = ref(null);
const rotationMember = ref(null);
const fillMember = ref(null);
const move = ref(null);

const sectionOptions = computed(() => props.matrix.sections.map((s) => ({ id: s.id, name: s.name, total: s.total })));
const isEmpty = computed(() => !props.matrix.sections.length && !props.matrix.loose.length);

const handleCellClick = ({ member, date, event }) => {
  const isSame = popover.value?.member.nrp === member.nrp && popover.value?.date === date;
  popover.value = isSame ? null : { member, date, anchor: event.currentTarget.getBoundingClientRect() };
};

const handleChooseShift = (code) => {
  const { member, date } = popover.value;
  popover.value = null;
  edits.saveCell(member.nrp, date, code);
};

const handleSaveUnit = (member, unit) => edits.saveUnit(member, unit);
const handleMove = (member, section, position) => { move.value = { member, sectionId: section.id, position }; };

const handleDeleteSection = (section) => perform({
  method: 'delete',
  url: `${base.value}/sections/${section.id}`,
  confirm: `Hapus section ${section.name}?`,
});

const handleRemoveMember = (member) => perform({
  method: 'delete',
  url: `${base.value}/members/${member.id}`,
  confirm: `Keluarkan ${member.employee_name} dari roster? Seluruh jadwalnya di roster ini (semua bulan) ikut dihapus.`,
});

watch(() => props.matrix, () => edits.reset());
</script>

<template>
  <div class="space-y-4">
    <MatrixToolbar :roster-id="roster.id" :matrix="matrix" :ctx-query="ctxQuery" :can-edit="canEdit" />
    <MatrixLegend :shifts="matrix.shifts" :can-edit="canEdit" />

    <div v-if="isEmpty" class="rounded-2xl border border-slate-200 bg-white px-4 py-12 text-center text-slate-500 shadow-sm">
      <LayoutGrid class="mx-auto h-10 w-10 text-slate-300" />
      <p class="mt-2 text-sm">{{ canEdit ? 'Belum ada section. Tambahkan section pertama (mis. ADMIN PRODUKSI), lalu isi anggotanya.' : 'Belum ada section pada roster ini.' }}</p>
    </div>

    <MatrixSection
      v-for="section in matrix.sections" :key="section.id"
      :section="section" :days="matrix.days" :summary-codes="matrix.summary_codes" :can-edit="canEdit" :edits="edits"
      @add-member="memberSection = section" @delete-section="handleDeleteSection(section)" @edit-target="targetSection = section"
      @cell-click="handleCellClick" @save-unit="handleSaveUnit" @rotation="rotationMember = $event" @fill="fillMember = $event"
      @move="handleMove" @remove="handleRemoveMember"
    />

    <MatrixLooseTable v-if="matrix.loose.length" :members="matrix.loose" :days="matrix.days" :can-edit="canEdit" />

    <template v-if="canEdit">
      <ShiftPopover
        v-if="popover" :anchor="popover.anchor" :shifts="matrix.shifts" :current="edits.cellCode(popover.member, popover.date)"
        @choose="handleChooseShift" @close="popover = null"
      />
      <MemberModal :roster-id="roster.id" :section="memberSection" @close="memberSection = null" />
      <MoveMemberModal :roster-id="roster.id" :move="move" :sections="sectionOptions" @close="move = null" />
      <RotationModal :roster="roster" :member="rotationMember" @close="rotationMember = null" />
      <TargetModal :roster-id="roster.id" :section="targetSection" @close="targetSection = null" />
      <FillRangeModal :roster="roster" :member="fillMember" :shifts="matrix.shifts" :month-from="matrix.month_from" :month-to="matrix.month_to" @close="fillMember = null" />
    </template>
  </div>
</template>
