<script setup>
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import { Award, BadgeCheck, AlertTriangle, CalendarX, Trophy } from 'lucide-vue-next';
import Pagination from '@/Components/ui/Pagination.vue';
import { confirmDialog } from '@/utils/confirm';
import CertificateFilterBar from './CertificateFilterBar.vue';
import CertificateTable from './CertificateTable.vue';
import CertificatePreviewModal from './CertificatePreviewModal.vue';

const props = defineProps({
  certification: { type: Object, required: true },
  canManage: { type: Boolean, default: false },
});

const BASE_URL = '/one-procedure/evaluasi';

const SUMMARY_CARDS = [
  { key: 'total', label: 'Total Sertifikat', icon: Award, tone: { border: 'border-t-[#0f3460]', text: 'text-[#0f3460]', bg: 'bg-blue-100' } },
  { key: 'active', label: 'Aktif', icon: BadgeCheck, tone: { border: 'border-t-green-700', text: 'text-green-700', bg: 'bg-green-100' } },
  { key: 'expiring_soon', label: 'Expiring Soon', icon: AlertTriangle, tone: { border: 'border-t-yellow-800', text: 'text-yellow-800', bg: 'bg-yellow-100' } },
  { key: 'expired', label: 'Kadaluarsa', icon: CalendarX, tone: { border: 'border-t-red-700', text: 'text-red-700', bg: 'bg-red-100' } },
];

const previewCertificate = ref(null);

const activeQuery = computed(() => {
  const filled = Object.entries(props.certification.filters).filter(([, v]) => v !== '' && v !== 0);
  return { tab: 'certification', ...Object.fromEntries(filled) };
});

const rowOffset = computed(() => (props.certification.pagination.currentPage - 1) * props.certification.pagination.perPage);

const handleFiltersChanged = (filters) => {
  const filled = Object.fromEntries(Object.entries(filters).filter(([, v]) => v !== '' && v !== null));
  router.get(BASE_URL, { tab: 'certification', ...filled }, { preserveState: true, preserveScroll: true, replace: true });
};

const handleIssueAll = async () => {
  const count = props.certification.passedWithoutCertCount;
  if (!(await confirmDialog(`Terbitkan sertifikat untuk ${count} karyawan yang lulus?`))) return;
  router.post(`${BASE_URL}/certificates/issue-all`, { assessment_id: props.certification.filters.assessment_id || null }, { preserveScroll: true });
};

const handleRevoke = async (certificate) => {
  if (!(await confirmDialog('Cabut sertifikat ini? Tindakan tidak bisa dibatalkan.'))) return;
  router.post(`${BASE_URL}/certificates/${certificate.id}/revoke`, {}, { preserveScroll: true });
};
</script>

<template>
  <div class="space-y-4">
    <div class="grid grid-cols-2 gap-3 md:grid-cols-4">
      <div
        v-for="card in SUMMARY_CARDS"
        :key="card.key"
        :class="['rounded-2xl border border-t-[3px] border-slate-200/80 bg-white p-3 text-center shadow-sm', card.tone.border]"
      >
        <div :class="['mx-auto mb-2 flex h-11 w-11 items-center justify-center rounded-full', card.tone.bg]">
          <component :is="card.icon" :class="['h-5 w-5', card.tone.text]" />
        </div>
        <div :class="['text-3xl font-black leading-none', card.tone.text]">{{ certification.summary[card.key] ?? 0 }}</div>
        <div class="mt-1 text-[11px] font-semibold text-slate-400">{{ card.label }}</div>
      </div>
    </div>

    <div
      v-if="certification.passedWithoutCertCount > 0 && canManage"
      class="flex flex-col gap-3 rounded-xl border-l-4 border-orange-500 bg-orange-50 p-4 shadow-sm sm:flex-row sm:items-center"
    >
      <Trophy class="h-6 w-6 flex-shrink-0 text-orange-600" />
      <div class="flex-1">
        <div class="text-sm font-bold text-orange-900">{{ certification.passedWithoutCertCount }} karyawan lulus assessment belum memiliki sertifikat</div>
        <div class="text-xs text-orange-700">Terbitkan sertifikat untuk karyawan yang sudah lulus.</div>
      </div>
      <button type="button" class="inline-flex items-center gap-1 rounded-full bg-amber-400 px-4 py-1.5 text-xs font-bold text-slate-900 hover:bg-amber-500" @click="handleIssueAll">
        <Award class="h-3.5 w-3.5" />Terbitkan Semua
      </button>
    </div>

    <CertificateFilterBar
      :filters="certification.filters"
      :assessments="certification.assessments"
      :total="certification.pagination.total"
      :can-manage="canManage"
      @filters-changed="handleFiltersChanged"
    />

    <CertificateTable
      :rows="certification.rows"
      :row-offset="rowOffset"
      :can-manage="canManage"
      @preview-requested="previewCertificate = $event"
      @revoke-requested="handleRevoke"
    >
      <template #footer>
        <div v-if="certification.pagination.lastPage > 1" class="flex items-center justify-between border-t border-slate-100 p-3">
          <small class="text-xs text-slate-500">Hal {{ certification.pagination.currentPage }} dari {{ certification.pagination.lastPage }}</small>
          <Pagination
            :current-page="certification.pagination.currentPage"
            :last-page="certification.pagination.lastPage"
            :base-url="BASE_URL"
            :query="activeQuery"
          />
        </div>
      </template>
    </CertificateTable>

    <CertificatePreviewModal v-if="previewCertificate" :certificate="previewCertificate" @closed="previewCertificate = null" />
  </div>
</template>
