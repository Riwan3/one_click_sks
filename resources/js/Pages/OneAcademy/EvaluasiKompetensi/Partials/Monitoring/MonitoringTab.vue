<script setup>
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import Pagination from '@/Components/ui/Pagination.vue';
import { confirmDialog } from '@/utils/confirm';
import MonitoringSummaryCards from './MonitoringSummaryCards.vue';
import MonitoringCharts from './MonitoringCharts.vue';
import MonitoringFilterBar from './MonitoringFilterBar.vue';
import MonitoringTable from './MonitoringTable.vue';
import MonitoringDetailModal from './MonitoringDetailModal.vue';
import RecordResultModal from './RecordResultModal.vue';

const props = defineProps({
  monitoring: { type: Object, required: true },
  canManage: { type: Boolean, default: false },
});

const BASE_URL = '/one-academy/evaluasi-kompetensi';

const detailResultId = ref(null);
const isRecordModalOpen = ref(false);

const activeQuery = computed(() => {
  const filled = Object.entries(props.monitoring.filters).filter(([, v]) => v !== '' && v !== 0);
  return { tab: 'monitoring', ...Object.fromEntries(filled) };
});

const exportQuery = computed(() => new URLSearchParams(activeQuery.value).toString());

const rowOffset = computed(() => (props.monitoring.pagination.currentPage - 1) * props.monitoring.pagination.perPage);

const visitWith = (query) => router.get(BASE_URL, query, { preserveState: true, preserveScroll: true, replace: true });

const handleFiltersChanged = (filters) => {
  const filled = Object.entries(filters).filter(([, v]) => v !== '' && v !== null);
  visitWith({ tab: 'monitoring', ...Object.fromEntries(filled) });
};

const handleRemove = async (row) => {
  const isConfirmed = await confirmDialog({
    title: 'Hapus dari Assessment?',
    text: `Hapus ${row.name} dari assessment ini? Data hasil terkait juga akan dihapus. Lakukan ini jika karyawan tidak valid.`,
  });
  if (!isConfirmed) return;

  router.delete(`${BASE_URL}/monitoring/${row.assessment_id}/${encodeURIComponent(row.nrp)}`, { preserveScroll: true });
};
</script>

<template>
  <div class="space-y-4">
    <MonitoringSummaryCards :summary="monitoring.summary" />

    <MonitoringCharts :summary="monitoring.summary" :daily-completions="monitoring.dailyCompletions" />

    <MonitoringFilterBar
      :filters="monitoring.filters"
      :assessments="monitoring.assessments"
      :jabatans="monitoring.jabatans"
      :total="monitoring.pagination.total"
      :export-query="exportQuery"
      :can-manage="canManage"
      @filters-changed="handleFiltersChanged"
      @record-result-requested="isRecordModalOpen = true"
    />

    <MonitoringTable
      :rows="monitoring.rows"
      :row-offset="rowOffset"
      :can-manage="canManage"
      @detail-requested="detailResultId = $event"
      @remove-requested="handleRemove"
    >
      <template #footer>
        <div v-if="monitoring.pagination.lastPage > 1" class="flex items-center justify-between border-t border-slate-100 p-3">
          <small class="text-xs text-slate-500">Hal {{ monitoring.pagination.currentPage }} dari {{ monitoring.pagination.lastPage }}</small>
          <Pagination
            :current-page="monitoring.pagination.currentPage"
            :last-page="monitoring.pagination.lastPage"
            :base-url="BASE_URL"
            :query="activeQuery"
          />
        </div>
      </template>
    </MonitoringTable>

    <MonitoringDetailModal v-if="detailResultId" :result-id="detailResultId" @closed="detailResultId = null" />
    <RecordResultModal
      v-if="isRecordModalOpen"
      :assessments="monitoring.assessments"
      :default-assessment-id="monitoring.filters.assessment_id"
      @closed="isRecordModalOpen = false"
    />
  </div>
</template>
