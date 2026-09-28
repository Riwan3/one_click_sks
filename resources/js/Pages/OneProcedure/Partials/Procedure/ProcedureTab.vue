<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { UploadCloud, Plus, BookOpen } from 'lucide-vue-next';
import { confirmDialog } from '@/utils/confirm';
import ProcedureUploadForm from './ProcedureUploadForm.vue';
import ProcedureFilterBar from './ProcedureFilterBar.vue';
import ProcedureCard from './ProcedureCard.vue';
import ProcedureDetailModal from './ProcedureDetailModal.vue';
import ProcedureEditModal from './ProcedureEditModal.vue';

defineProps({
  procedures: { type: Array, default: () => [] },
  categories: { type: Array, default: () => [] },
  filters: { type: Object, required: true },
  canManage: { type: Boolean, default: false },
});

const isUploadOpen = ref(false);
const detailProcedure = ref(null);
const editProcedure = ref(null);

const handleFiltersChanged = (filters) => {
  const filled = Object.fromEntries(Object.entries(filters).filter(([, v]) => v !== ''));
  router.get('/one-procedure/evaluasi', { tab: 'procedure', ...filled }, { preserveState: true, preserveScroll: true, replace: true });
};

const handleDelete = async (procedure) => {
  const isConfirmed = await confirmDialog({
    title: 'Hapus Prosedur?',
    text: `"${procedure.title}" — assessment, soal & hasil evaluasi terkait juga akan terhapus.`,
    confirmButtonText: 'Hapus',
  });
  if (isConfirmed) router.delete(`/one-procedure/evaluasi/procedure/delete/${procedure.id}`, { preserveScroll: true });
};
</script>

<template>
  <div class="space-y-4">
    <div v-if="canManage" class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm">
      <div class="flex items-center justify-between p-4">
        <h3 class="flex items-center gap-2 text-sm font-bold text-slate-800">
          <UploadCloud class="h-4 w-4 text-rose-600" /> Upload Materi Prosedur
        </h3>
        <button
          type="button"
          class="inline-flex items-center gap-1.5 rounded-full bg-rose-600 px-3.5 py-1.5 text-xs font-bold text-white hover:bg-rose-700"
          @click="isUploadOpen = !isUploadOpen"
        >
          <Plus class="h-3.5 w-3.5" /> Tambah Prosedur
        </button>
      </div>
      <ProcedureUploadForm v-if="isUploadOpen" :categories="categories" @closed="isUploadOpen = false" />
    </div>

    <ProcedureFilterBar :filters="filters" :categories="categories" :total="procedures.length" @filters-changed="handleFiltersChanged" />

    <div v-if="procedures.length === 0" class="rounded-2xl border border-slate-200/80 bg-white p-12 text-center shadow-sm">
      <BookOpen class="mx-auto mb-3 h-12 w-12 text-slate-300" />
      <p class="mb-1 text-sm font-semibold text-slate-500">Belum Ada Prosedur</p>
      <p class="text-xs text-slate-400">
        {{ canManage ? 'Klik "Tambah Prosedur" untuk menambahkan prosedur pertama.' : 'Prosedur akan ditampilkan di sini setelah ditambahkan oleh admin.' }}
      </p>
    </div>

    <div v-else class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
      <ProcedureCard
        v-for="procedure in procedures"
        :key="procedure.id"
        :procedure="procedure"
        :can-manage="canManage"
        @detail-requested="detailProcedure = $event"
        @edit-requested="editProcedure = $event"
        @delete-requested="handleDelete"
      />
    </div>

    <ProcedureDetailModal v-if="detailProcedure" :procedure="detailProcedure" @closed="detailProcedure = null" />
    <ProcedureEditModal v-if="editProcedure" :procedure="editProcedure" :categories="categories" @closed="editProcedure = null" />
  </div>
</template>
