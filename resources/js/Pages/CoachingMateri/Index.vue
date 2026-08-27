<script setup>
import { ref, computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { Plus, Edit2, Trash2, Eye, BookOpen } from '@lucide/vue';

const props = defineProps({
  materis: { type: Array, required: true },
});

const filterCategory = ref('');

const filteredMaterис = computed(() => {
  if (!filterCategory.value) return props.materis;
  return props.materis.filter(m => m.kategori === filterCategory.value);
});

const categoryLabel = (cat) => {
  const labels = { pribadi: 'Pribadi', keluarga: 'Keluarga', lingkungan: 'Lingkungan', pekerjaan: 'Pekerjaan' };
  return labels[cat] || cat;
};

const categoryColor = (cat) => {
  const colors = {
    pribadi: 'bg-purple-100 text-purple-700',
    keluarga: 'bg-pink-100 text-pink-700',
    lingkungan: 'bg-green-100 text-green-700',
    pekerjaan: 'bg-blue-100 text-blue-700',
  };
  return colors[cat] || 'bg-slate-100 text-slate-700';
};
</script>

<template>
  <Head title="Materi Coaching" />
  <AppLayout>
    <div class="space-y-6">
      <div>
        <h1 class="text-3xl font-bold text-slate-900">Materi Coaching</h1>
        <p class="mt-1 text-slate-600">Kelola materi dan konten untuk program coaching</p>
      </div>

      <div class="flex gap-4">
        <div class="flex gap-2">
          <button
            @click="filterCategory = ''"
            :class="[
              'px-4 py-2 rounded-lg text-sm font-medium transition-colors',
              filterCategory === '' ? 'bg-blue-600 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'
            ]"
          >
            Semua
          </button>
          <button
            v-for="cat in ['pribadi', 'keluarga', 'lingkungan', 'pekerjaan']"
            :key="cat"
            @click="filterCategory = cat"
            :class="[
              'px-4 py-2 rounded-lg text-sm font-medium transition-colors',
              filterCategory === cat ? 'bg-blue-600 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'
            ]"
          >
            {{ categoryLabel(cat) }}
          </button>
        </div>
        <Link :href="route('coaching-materi.create')" class="ml-auto flex items-center gap-2 rounded-lg bg-green-600 px-4 py-2 text-sm font-semibold text-white hover:bg-green-700">
          <Plus class="h-4 w-4" /> Tambah Materi
        </Link>
      </div>

      <div v-if="filteredMaterис.length === 0" class="rounded-lg border border-slate-200 bg-white p-8 text-center">
        <BookOpen class="mx-auto h-12 w-12 text-slate-300 mb-2" />
        <p class="text-slate-500">Belum ada materi coaching</p>
      </div>

      <div class="grid gap-5 md:grid-cols-2 lg:grid-cols-3">
        <div
          v-for="materi in filteredMateris"
          :key="materi.id"
          class="rounded-lg border border-slate-200 bg-white shadow-sm hover:shadow-md transition-all overflow-hidden"
        >
          <div v-if="materi.thumbnail" class="h-32 overflow-hidden bg-slate-100">
            <img :src="materi.thumbnail" :alt="materi.judul" class="h-full w-full object-cover" />
          </div>
          <div v-else class="h-32 flex items-center justify-center bg-gradient-to-br from-slate-100 to-slate-200">
            <BookOpen class="h-12 w-12 text-slate-300" />
          </div>

          <div class="p-4">
            <div class="flex items-start justify-between gap-2 mb-2">
              <h3 class="font-semibold text-slate-900 text-sm line-clamp-2">{{ materi.judul }}</h3>
              <span :class="['whitespace-nowrap rounded-full px-2 py-1 text-xs font-semibold', categoryColor(materi.kategori)]">
                {{ categoryLabel(materi.kategori) }}
              </span>
            </div>

            <p class="text-xs text-slate-600 mb-3 line-clamp-2">{{ materi.ringkasan }}</p>

            <div class="mb-4 space-y-1 text-xs text-slate-500">
              <div v-if="materi.versi">Versi {{ materi.versi }}</div>
              <div v-if="materi.file_pdf" class="text-green-600">✓ PDF tersedia</div>
              <div v-if="materi.video" class="text-green-600">✓ Video tersedia</div>
            </div>

            <div class="flex gap-2 border-t border-slate-100 pt-3">
              <Link :href="route('coaching-materi.show', materi.id)" class="flex flex-1 items-center justify-center gap-1 rounded-md bg-blue-50 px-2 py-1.5 text-xs font-medium text-blue-600 hover:bg-blue-100">
                <Eye class="h-3 w-3" /> Lihat
              </Link>
              <Link :href="route('coaching-materi.edit', materi.id)" class="flex flex-1 items-center justify-center gap-1 rounded-md bg-amber-50 px-2 py-1.5 text-xs font-medium text-amber-600 hover:bg-amber-100">
                <Edit2 class="h-3 w-3" /> Edit
              </Link>
              <Link :href="route('coaching-materi.destroy', materi.id)" method="delete" as="button" class="flex flex-1 items-center justify-center gap-1 rounded-md bg-red-50 px-2 py-1.5 text-xs font-medium text-red-600 hover:bg-red-100">
                <Trash2 class="h-3 w-3" /> Hapus
              </Link>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
