<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { UploadCloud, Plus, BookOpen } from 'lucide-vue-next';
import { confirmDialog } from '@/utils/confirm';
import MateriUploadForm from './MateriUploadForm.vue';
import MateriFilterBar from './MateriFilterBar.vue';
import MateriCard from './MateriCard.vue';
import MateriDetailModal from './MateriDetailModal.vue';
import MateriEditModal from './MateriEditModal.vue';

defineProps({
  materiList: { type: Array, default: () => [] },
  categories: { type: Array, default: () => [] },
  filters: { type: Object, required: true },
  canManage: { type: Boolean, default: false },
});

const isUploadOpen = ref(false);
const detailMateri = ref(null);
const editMateri = ref(null);

const handleFiltersChanged = (filters) => {
  const filled = Object.fromEntries(Object.entries(filters).filter(([, v]) => v !== ''));
  router.get('/one-academy/evaluasi-kompetensi', { tab: 'materi', ...filled }, { preserveState: true, preserveScroll: true, replace: true });
};

const handleDelete = async (materi) => {
  const isConfirmed = await confirmDialog({
    title: 'Hapus Materi?',
    text: `"${materi.title}" — assessment, soal & hasil evaluasi terkait juga akan terhapus.`,
    confirmButtonText: 'Hapus',
  });
  if (isConfirmed) router.delete(`/one-academy/evaluasi-kompetensi/materi/${materi.id}`, { preserveScroll: true });
};
</script>

<template>
  <div class="space-y-4">
    <div v-if="canManage" class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm">
      <div class="flex items-center justify-between p-4">
        <h3 class="flex items-center gap-2 text-sm font-bold text-slate-800">
          <UploadCloud class="h-4 w-4 text-rose-600" /> Upload Materi Training
        </h3>
        <button
          type="button"
          class="inline-flex items-center gap-1.5 rounded-full bg-rose-600 px-3.5 py-1.5 text-xs font-bold text-white hover:bg-rose-700"
          @click="isUploadOpen = !isUploadOpen"
        >
          <Plus class="h-3.5 w-3.5" /> Tambah Materi
        </button>
      </div>
      <MateriUploadForm v-if="isUploadOpen" :categories="categories" @closed="isUploadOpen = false" />
    </div>

    <MateriFilterBar :filters="filters" :categories="categories" :total="materiList.length" @filters-changed="handleFiltersChanged" />

    <div v-if="materiList.length === 0" class="rounded-2xl border border-slate-200/80 bg-white p-12 text-center shadow-sm">
      <BookOpen class="mx-auto mb-3 h-12 w-12 text-slate-300" />
      <p class="mb-1 text-sm font-semibold text-slate-500">Belum Ada Materi</p>
      <p class="text-xs text-slate-400">
        {{ canManage ? 'Klik "Tambah Materi" untuk menambahkan materi training pertama.' : 'Materi akan ditampilkan di sini setelah ditambahkan oleh admin.' }}
      </p>
    </div>

    <div v-else class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
      <MateriCard
        v-for="materi in materiList"
        :key="materi.id"
        :materi="materi"
        :can-manage="canManage"
        @detail-requested="detailMateri = $event"
        @edit-requested="editMateri = $event"
        @delete-requested="handleDelete"
      />
    </div>

    <MateriDetailModal v-if="detailMateri" :materi="detailMateri" @closed="detailMateri = null" />
    <MateriEditModal v-if="editMateri" :materi="editMateri" :categories="categories" @closed="editMateri = null" />
  </div>
</template>
