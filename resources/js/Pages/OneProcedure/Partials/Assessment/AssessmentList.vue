<script setup>
import { ref, computed } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import { Plus, SquarePen } from 'lucide-vue-next';
import { confirmDialog } from '@/utils/confirm';
import { ASSESSMENT_BASE_URL } from '@/utils/procedureAssessment';
import AssessmentCard from './AssessmentCard.vue';
import SignCertificateModal from './SignCertificateModal.vue';

const props = defineProps({
  assessments: { type: Array, default: () => [] },
  canManage: { type: Boolean, default: false },
  currentEmployeeId: { type: Number, default: null },
});

const signingAssessment = ref(null);

const countByStatus = (status) => props.assessments.filter((a) => a.status === status).length;
const publishedCount = computed(() => countByStatus('published'));
const draftCount = computed(() => countByStatus('draft'));

const handleDelete = async (assessment) => {
  const isConfirmed = await confirmDialog({
    title: 'Hapus Assessment?',
    text: 'Peringatan: Menghapus assessment akan menghapus seluruh data nilai, notifikasi, dan riwayat peserta terkait assessment ini. Lanjutkan hapus?',
  });
  if (isConfirmed) router.delete(`${ASSESSMENT_BASE_URL}/${assessment.id}`);
};
</script>

<template>
  <div class="space-y-4">
    <div class="flex flex-wrap items-center justify-between gap-2">
      <div class="flex gap-2 text-xs font-semibold">
        <span class="rounded-md border border-slate-200 bg-slate-50 px-2 py-1 text-slate-800">{{ assessments.length }} Total</span>
        <span class="rounded-md bg-green-100 px-2 py-1 text-green-700">{{ publishedCount }} Published</span>
        <span class="rounded-md bg-slate-100 px-2 py-1 text-slate-500">{{ draftCount }} Draft</span>
      </div>
      <Link
        v-if="canManage"
        href="/one-procedure/evaluasi?tab=assessment&create=1"
        class="inline-flex items-center gap-1 rounded-full bg-rose-600 px-4 py-2 text-xs font-bold text-white hover:bg-rose-700"
      >
        <Plus class="h-3.5 w-3.5" /> Buat Assessment
      </Link>
    </div>

    <div v-if="assessments.length === 0" class="rounded-2xl border border-slate-200/80 bg-white p-12 text-center shadow-sm">
      <SquarePen class="mx-auto mb-3 h-10 w-10 text-slate-300" />
      <p class="mb-1 text-sm font-semibold text-slate-500">Belum Ada Assessment</p>
      <p class="text-xs text-slate-400">
        {{ canManage ? 'Klik "Buat Assessment" untuk membuat evaluasi pertama.' : 'Belum ada assessment yang tersedia.' }}
      </p>
    </div>

    <div v-else class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
      <AssessmentCard
        v-for="assessment in assessments"
        :key="assessment.id"
        :assessment="assessment"
        :can-manage="canManage"
        :current-employee-id="currentEmployeeId"
        @delete-requested="handleDelete"
        @sign-requested="signingAssessment = $event"
      />
    </div>

    <SignCertificateModal v-if="signingAssessment" :assessment="signingAssessment" @closed="signingAssessment = null" />
  </div>
</template>
