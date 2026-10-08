<script setup>
import { computed, ref } from 'vue';
import { useRosterAction } from '@/composables/useRosterAction';
import Pagination from '@/Components/ui/Pagination.vue';
import DetailForm from './DetailForm.vue';
import DetailTable from './DetailTable.vue';
import DetailEditModal from './DetailEditModal.vue';
import AllocationModal from './AllocationModal.vue';

const props = defineProps({
  roster: { type: Object, required: true },
  list: { type: Object, required: true },
  shifts: { type: Array, default: () => [] },
  canEdit: { type: Boolean, default: false },
});

const { perform } = useRosterAction();

const editingDetail = ref(null);
const allocatingDetail = ref(null);

const base = computed(() => `/one-roster/roster/${props.roster.id}`);
const startNumber = computed(() => (props.list.page - 1) * props.list.per_page);

const handleDelete = (detail) => perform({
  method: 'delete',
  url: `${base.value}/details/${detail.id}`,
  confirm: 'Hapus detail ini beserta alokasinya?',
});

const handleRemoveAllocation = (detail, allocation) => perform({
  method: 'delete',
  url: `${base.value}/details/${detail.id}/allocations`,
  data: { unit: allocation.unit, section: allocation.section },
  confirm: 'Hapus alokasi ini?',
});
</script>

<template>
  <div class="space-y-4">
    <DetailForm v-if="canEdit" :roster="roster" :shifts="shifts" />

    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
      <DetailTable
        :details="list.details" :start-number="startNumber" :can-edit="canEdit"
        @edit="editingDetail = $event" @allocate="allocatingDetail = $event" @delete="handleDelete" @remove-allocation="handleRemoveAllocation"
      />
      <div v-if="list.pages > 1" class="p-3">
        <Pagination :current-page="list.page" :last-page="list.pages" :base-url="base" :query="{ tab: 'list' }" />
      </div>
    </div>

    <template v-if="canEdit">
      <DetailEditModal :roster="roster" :detail="editingDetail" :shifts="shifts" @close="editingDetail = null" />
      <AllocationModal :roster-id="roster.id" :detail="allocatingDetail" @close="allocatingDetail = null" />
    </template>
  </div>
</template>
