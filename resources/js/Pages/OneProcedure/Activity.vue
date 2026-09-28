<script setup>
import { ref, computed } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { confirmDialog } from '@/utils/confirm';
import {
  Activity,
  FileText,
  Search,
  Plus,
  Pencil,
  Trash2,
  Eye,
  CheckCircle2,
  ChevronRight,
  ArrowLeft,
  X,
  FileCheck,
  FolderPlus,
  Tag
} from 'lucide-vue-next';

const props = defineProps({
  user: Object,
  notifCounts: Object,
  menu: Array,
  docs: Array,
  jenis: String,
  search: String,
  searchActive: Boolean,
  currentLabel: String,
  currentCategory: String,
  activeCategory: Object,
  attachedDocIds: Array,
  isAktivityContext: Boolean,
  showTable: Boolean,
  counts: Object,
  allSopCategories: Array,
  allSopTypes: Array,
  allDocsForSelect: Array,
});

const searchQuery = ref(props.search || '');

const handleSearch = () => {
  router.get('/one-procedure/activity', {
    type: props.jenis,
    search: searchQuery.value,
  }, { preserveState: true });
};

const resetSearch = () => {
  searchQuery.value = '';
  router.get('/one-procedure/activity', {
    type: props.jenis,
  }, { preserveState: true });
};

const navigateType = (typeSlug) => {
  router.get('/one-procedure/activity', {
    type: typeSlug,
    search: searchQuery.value,
  }, { preserveState: true });
};

// Modal Tambah Dokumen
const showAddModal = ref(false);
const addForm = ref({
  category_slug: props.currentCategory || '',
  type_slug: props.allSopTypes?.[0]?.slug || 'sop',
  judul: '',
  kode_dokumen: '',
  status: 'aktif',
  file_dokumen: null,
});

const openAddModal = () => {
  const activeTypeSlug = props.currentCategory && props.jenis.startsWith(props.currentCategory + '_')
    ? props.jenis.substring(props.currentCategory.length + 1)
    : (props.allSopTypes?.[0]?.slug || 'sop');

  addForm.value = {
    category_slug: props.currentCategory || '',
    type_slug: activeTypeSlug,
    judul: '',
    kode_dokumen: '',
    status: 'aktif',
    file_dokumen: null,
  };
  showAddModal.value = true;
};

const handleAddFile = (e) => {
  addForm.value.file_dokumen = e.target.files[0] || null;
};

const submitAddDocument = () => {
  const formData = new FormData();
  formData.append('category_slug', addForm.value.category_slug);
  formData.append('type_slug', addForm.value.type_slug);
  formData.append('judul', addForm.value.judul);
  formData.append('kode_dokumen', addForm.value.kode_dokumen);
  formData.append('status', addForm.value.status);
  if (addForm.value.file_dokumen) {
    formData.append('file_dokumen', addForm.value.file_dokumen);
  }

  router.post('/one-procedure/prosedur/store', formData, {
    preserveScroll: true,
    onSuccess: () => {
      showAddModal.value = false;
    },
  });
};

// Modal Edit Dokumen
const showEditModal = ref(false);
const editForm = ref({
  id: null,
  category_slug: 'global',
  type_slug: 'sop',
  judul: '',
  kode_dokumen: '',
  status: 'aktif',
  current_file: '',
  file_dokumen: null,
});

const openEditModal = (doc) => {
  let foundCat = 'global';
  let foundType = doc.jenis;

  for (const cat of (props.allSopCategories || [])) {
    if (doc.jenis.startsWith(cat.slug + '_')) {
      foundCat = cat.slug;
      foundType = doc.jenis.substring(cat.slug.length + 1);
      break;
    }
  }

  editForm.value = {
    id: doc.id,
    category_slug: foundCat,
    type_slug: foundType,
    judul: doc.judul,
    kode_dokumen: doc.kode_dokumen,
    status: doc.status || 'aktif',
    current_file: doc.file_dokumen || '',
    file_dokumen: null,
  };
  showEditModal.value = true;
};

const handleEditFile = (e) => {
  editForm.value.file_dokumen = e.target.files[0] || null;
};

const submitEditDocument = () => {
  const formData = new FormData();
  formData.append('category_slug', editForm.value.category_slug);
  formData.append('type_slug', editForm.value.type_slug);
  formData.append('judul', editForm.value.judul);
  formData.append('kode_dokumen', editForm.value.kode_dokumen);
  formData.append('status', editForm.value.status);
  if (editForm.value.file_dokumen) {
    formData.append('file_dokumen', editForm.value.file_dokumen);
  }

  router.post(`/one-procedure/prosedur/update/${editForm.value.id}`, formData, {
    preserveScroll: true,
    onSuccess: () => {
      showEditModal.value = false;
    },
  });
};

const deleteDocument = async (doc) => {
  const ok = await confirmDialog({
    title: 'Hapus Dokumen?',
    text: `Apakah Anda yakin ingin menghapus dokumen "${doc.judul}"?`,
    icon: 'warning',
    confirmButtonText: 'Ya, Hapus',
    cancelButtonText: 'Batal',
  });
  if (ok) {
    router.delete(`/one-procedure/prosedur/delete/${doc.id}`, {
      preserveScroll: true,
    });
  }
};

// Modal Tambah Jenis
const showTypeModal = ref(false);
const typeName = ref('');

const openTypeModal = () => {
  typeName.value = '';
  showTypeModal.value = true;
};

const submitAddType = () => {
  if (!typeName.value.trim()) return;
  router.post('/one-procedure/prosedur/type/store', {
    name: typeName.value.trim(),
  }, {
    preserveScroll: true,
    onSuccess: () => {
      showTypeModal.value = false;
      typeName.value = '';
    },
  });
};

const deleteType = async (typeItem) => {
  const ok = await confirmDialog({
    title: 'Hapus Jenis Dokumen?',
    text: `Hapus jenis "${typeItem.name}" secara permanen?`,
    icon: 'warning',
    confirmButtonText: 'Ya, Hapus',
    cancelButtonText: 'Batal',
  });
  if (ok) {
    router.delete(`/one-procedure/prosedur/type/delete/${typeItem.id}`, {
      preserveScroll: true,
    });
  }
};

// Modal Tambah Kategori
const showCatModal = ref(false);
const catName = ref('');
const catSelectedDocs = ref([]);
const catDocSearchQuery = ref('');

const openCategoryModal = () => {
  catName.value = '';
  catSelectedDocs.value = [];
  catDocSearchQuery.value = '';
  showCatModal.value = true;
};

const filteredSelectDocs = computed(() => {
  const q = catDocSearchQuery.value.toLowerCase().trim();
  if (!q) return props.allDocsForSelect || [];
  return (props.allDocsForSelect || []).filter((d) => {
    const text = `${d.judul} ${d.kode_dokumen} ${d.jenis}`.toLowerCase();
    return text.includes(q);
  });
});

const selectAllDocs = () => {
  catSelectedDocs.value = filteredSelectDocs.value.map((d) => d.id);
};

const clearSelectDocs = () => {
  catSelectedDocs.value = [];
};

const submitAddCategory = () => {
  if (!catName.value.trim()) return;
  router.post('/one-procedure/prosedur/category/store', {
    name: catName.value.trim(),
    doc_ids: catSelectedDocs.value,
  }, {
    preserveScroll: true,
    onSuccess: () => {
      showCatModal.value = false;
      catName.value = '';
      catSelectedDocs.value = [];
    },
  });
};

const deleteCategory = async (cat) => {
  const ok = await confirmDialog({
    title: 'Hapus Kategori?',
    text: `Hapus kategori "${cat.name}"? Dokumen di dalamnya akan tetap ada namun kategorinya dihapus.`,
    icon: 'warning',
    confirmButtonText: 'Ya, Hapus',
    cancelButtonText: 'Batal',
  });
  if (ok) {
    router.delete(`/one-procedure/prosedur/category/delete/${cat.id}`, {
      preserveScroll: true,
    });
  }
};

const openViewer = (doc) => {
  window.open(`/one-procedure/prosedur/view/${doc.id}`, '_blank');
};

// Modal Tambahkan Dokumen ke Aktifitas Terpilih
const showAttachModal = ref(false);
const attachSelectedDocs = ref([]);
const attachSearchQuery = ref('');
const attachTypeFilter = ref('');
const isSubmittingAttach = ref(false);

const openAttachToActivityModal = () => {
  attachSelectedDocs.value = [...(props.attachedDocIds || [])];
  attachSearchQuery.value = '';
  attachTypeFilter.value = '';
  showAttachModal.value = true;
};

const filteredAttachDocs = computed(() => {
  let list = props.allDocsForSelect || [];
  if (attachTypeFilter.value) {
    list = list.filter((d) => {
      const j = (d.jenis || '').toLowerCase();
      return j === attachTypeFilter.value || j.endsWith('_' + attachTypeFilter.value);
    });
  }
  const q = attachSearchQuery.value.toLowerCase().trim();
  if (q) {
    list = list.filter((d) => {
      const text = `${d.judul} ${d.kode_dokumen} ${d.jenis}`.toLowerCase();
      return text.includes(q);
    });
  }
  return list;
});

const isDocAttached = (docId) => {
  return attachSelectedDocs.value.includes(docId);
};

const toggleAttachDoc = (docId) => {
  const idx = attachSelectedDocs.value.indexOf(docId);
  if (idx > -1) {
    attachSelectedDocs.value.splice(idx, 1);
  } else {
    attachSelectedDocs.value.push(docId);
  }
};

const selectAllAttachDocs = () => {
  const visibleIds = filteredAttachDocs.value.map((d) => d.id);
  const combined = new Set([...attachSelectedDocs.value, ...visibleIds]);
  attachSelectedDocs.value = Array.from(combined);
};

const clearAttachDocs = () => {
  if (attachTypeFilter.value || attachSearchQuery.value) {
    const visibleIds = new Set(filteredAttachDocs.value.map((d) => d.id));
    attachSelectedDocs.value = attachSelectedDocs.value.filter((id) => !visibleIds.has(id));
  } else {
    attachSelectedDocs.value = [];
  }
};

const submitSyncCategoryDocs = () => {
  if (!props.activeCategory?.id) return;
  isSubmittingAttach.value = true;
  router.post('/one-procedure/activity/category/sync-docs', {
    category_id: props.activeCategory.id,
    doc_ids: attachSelectedDocs.value,
  }, {
    preserveScroll: true,
    onSuccess: () => {
      showAttachModal.value = false;
      isSubmittingAttach.value = false;
    },
    onError: () => {
      isSubmittingAttach.value = false;
    },
  });
};
</script>

<template>
  <AppLayout :user="user" :notifCounts="notifCounts" :menu="menu">
    <Head title="Aktivity - One-Procedure" />

    <div class="space-y-6">
      <!-- HEADER -->
      <div class="bg-white border border-slate-200/80 rounded-2xl p-6 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
          <div class="text-xs font-semibold text-sky-600 tracking-wider uppercase flex items-center gap-1.5 mb-1.5">
            <span>SOP & Knowledge Management</span>
            <span class="text-slate-300">/</span>
            <span>Aktivity</span>
          </div>
          <div class="flex items-center gap-3 flex-wrap">
            <div class="p-2.5 bg-sky-50 text-sky-600 rounded-xl">
              <Activity class="w-6 h-6" />
            </div>
            <div>
              <div class="flex items-center gap-2">
                <h1 class="text-xl font-black text-slate-800 tracking-tight">Aktivity</h1>
                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-sky-100 text-sky-700 uppercase">
                  {{ currentCategory ? currentLabel : 'Kategori Aktivitas' }}
                </span>
              </div>
              <p class="text-xs text-slate-500 mt-0.5">
                Daftar dokumen aktivity {{ currentLabel.toLowerCase() }} yang tersedia.
              </p>
            </div>
          </div>
        </div>

        <div class="flex items-center gap-2 flex-wrap">
          <button
            v-if="currentCategory"
            @click="openAttachToActivityModal"
            class="px-4 py-2.5 text-xs font-bold rounded-xl bg-sky-600 hover:bg-sky-700 text-white shadow-sm shadow-sky-200 transition-all flex items-center gap-2"
          >
            <Plus class="w-4 h-4" />
            <span>Tambahkan Dokumen ke Aktifitas Terpilih</span>
          </button>
          <button
            v-else
            @click="openAddModal"
            class="px-4 py-2.5 text-xs font-bold rounded-xl bg-sky-600 hover:bg-sky-700 text-white shadow-sm shadow-sky-200 transition-all flex items-center gap-2"
          >
            <Plus class="w-4 h-4" />
            <span>Tambah Dokumen</span>
          </button>
        </div>
      </div>

      <!-- FILTER / SEARCH BAR -->
      <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm">
        <form @submit.prevent="handleSearch" class="flex flex-col sm:flex-row items-center gap-3">
          <div class="relative flex-1 w-full">
            <Search class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" />
            <input
              type="text"
              v-model="searchQuery"
              placeholder="Cari judul atau kode dokumen..."
              class="w-full pl-10 pr-4 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 transition"
            />
          </div>

          <div class="flex items-center gap-2 w-full sm:w-auto">
            <button
              type="submit"
              class="px-5 py-2 text-xs font-bold rounded-xl bg-sky-600 hover:bg-sky-700 text-white shadow-sm transition flex-1 sm:flex-initial"
            >
              Filter
            </button>
            <button
              v-if="searchActive"
              type="button"
              @click="resetSearch"
              class="p-2 text-slate-400 hover:text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl transition"
              title="Reset Filter"
            >
              <X class="w-4 h-4" />
            </button>
          </div>
        </form>
      </div>

      <!-- SUB-MENU / MODE SELECTION -->
      <!-- 1. Category Context: Sub-menu types for selected category -->
      <div v-if="currentCategory" class="space-y-3">
        <div class="flex items-center justify-between p-4 bg-slate-50 border border-slate-200 rounded-2xl">
          <div>
            <span class="text-[10px] font-bold tracking-wider text-slate-400 uppercase block">Kategori Terpilih:</span>
            <h3 class="text-sm font-black text-sky-700 uppercase">
              {{ allSopCategories?.find(c => c.slug === currentCategory)?.name || currentCategory }}
            </h3>
          </div>
          <div class="flex items-center gap-2">
            <button
              @click="navigateType('aktivity_select')"
              class="px-3 py-1.5 text-xs font-semibold text-slate-600 bg-white border border-slate-200 hover:bg-slate-100 rounded-xl transition flex items-center gap-1.5"
            >
              <ArrowLeft class="w-3.5 h-3.5" />
              <span>Ganti Kategori</span>
            </button>
          </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3">
          <div
            v-for="dt in allSopTypes"
            :key="dt.id"
            class="relative group"
          >
            <button
              @click="navigateType(`${currentCategory}_${dt.slug}`)"
              :class="[
                'w-full p-4 rounded-xl font-bold text-xs uppercase flex items-center justify-between text-left transition border shadow-sm',
                jenis === `${currentCategory}_${dt.slug}`
                  ? 'bg-sky-600 text-white border-sky-600 shadow-sky-200'
                  : 'bg-white text-slate-700 border-slate-200/90 hover:border-sky-300 hover:bg-sky-50/30'
              ]"
            >
              <span class="truncate pr-8">{{ dt.name }}</span>
              <CheckCircle2 v-if="jenis === `${currentCategory}_${dt.slug}`" class="w-4 h-4 text-white flex-shrink-0" />
              <ChevronRight v-else class="w-4 h-4 text-slate-400 group-hover:text-sky-500 flex-shrink-0" />
            </button>

            <button
              @click.stop="deleteType(dt)"
              class="absolute right-3 top-1/2 -translate-y-1/2 p-1.5 text-slate-400 hover:text-rose-600 opacity-0 group-hover:opacity-100 transition rounded-lg hover:bg-white/80"
              title="Hapus jenis dokumen ini dari SEMUA kategori"
            >
              <Trash2 class="w-3.5 h-3.5" />
            </button>
          </div>
        </div>
      </div>

      <!-- 2. Category Selection Mode (when jenis === 'aktivity_select' or no category selected) -->
      <div v-else class="space-y-3">
        <div class="flex items-center justify-between pb-1">
          <div class="flex items-center gap-2 text-xs font-bold text-slate-600 uppercase tracking-wider">
            <Tag class="w-3.5 h-3.5 text-sky-500" />
            <span>Pilih Kategori Aktivitas:</span>
          </div>
          <button
            @click="openCategoryModal"
            class="px-3 py-1.5 text-xs font-bold text-white bg-sky-600 hover:bg-sky-700 rounded-xl transition flex items-center gap-1.5 shadow-sm shadow-sky-200"
          >
            <Plus class="w-3.5 h-3.5" />
            <span>Tambah Kategori</span>
          </button>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3">
          <div
            v-for="cat in allSopCategories"
            :key="cat.id"
            class="relative group"
          >
            <button
              @click="navigateType(`${cat.slug}_${allSopTypes?.[0]?.slug || 'sop'}`)"
              class="w-full p-4 rounded-xl font-bold text-xs uppercase text-slate-700 bg-white border border-slate-200/90 hover:border-sky-300 hover:bg-sky-50/30 flex items-center justify-between text-left transition shadow-sm"
            >
              <span class="truncate pr-8">{{ cat.name }}</span>
              <ChevronRight class="w-4 h-4 text-slate-400 group-hover:text-sky-500 flex-shrink-0" />
            </button>

            <button
              @click.stop="deleteCategory(cat)"
              class="absolute right-3 top-1/2 -translate-y-1/2 p-1.5 text-slate-400 hover:text-rose-600 opacity-0 group-hover:opacity-100 transition rounded-lg hover:bg-white/80"
              title="Hapus kategori ini"
            >
              <Trash2 class="w-3.5 h-3.5" />
            </button>
          </div>
        </div>
      </div>

      <!-- SEARCH RESULT INDICATOR -->
      <div v-if="searchActive && showTable" class="px-2">
        <h3 class="text-xs font-black text-sky-600 flex items-center gap-1.5 uppercase">
          <Search class="w-3.5 h-3.5" />
          <span>Hasil Pencarian: "{{ search }}"</span>
        </h3>
      </div>

      <!-- TABLE OF DOCUMENTS -->
      <div v-if="showTable" class="bg-white border border-slate-200/80 rounded-2xl shadow-sm overflow-hidden">
        <div v-if="!docs || docs.length === 0" class="p-12 text-center text-slate-400">
          <FileText class="w-12 h-12 mx-auto mb-3 opacity-40 text-slate-400" />
          <p class="text-sm font-bold text-slate-600 mb-1">Belum Ada Dokumen {{ currentLabel }}</p>
          <p class="text-xs text-slate-400">Silakan klik tombol "Tambah Dokumen" di atas untuk menambahkan dokumen baru.</p>
        </div>

        <div v-else class="overflow-x-auto">
          <table class="w-full text-left text-xs border-collapse">
            <thead class="bg-slate-50 text-slate-600 border-b border-slate-200/80 font-bold uppercase tracking-wider text-[11px]">
              <tr>
                <th class="py-3 px-4 text-center w-12">No</th>
                <th class="py-3 px-4">Nama Dokumen</th>
                <th class="py-3 px-4 w-32">Tanggal</th>
                <th class="py-3 px-4 w-44">Kode Dokumen</th>
                <th class="py-3 px-4 w-44">Kategori</th>
                <th class="py-3 px-4 w-36">Jenis Dokumen</th>
                <th class="py-3 px-4 w-28 text-center">Aksi</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-slate-700">
              <tr
                v-for="(doc, idx) in docs"
                :key="doc.id"
                class="hover:bg-slate-50/70 transition-colors group"
              >
                <td class="py-3.5 px-4 text-center text-slate-400 font-medium">
                  {{ idx + 1 }}
                </td>
                <td class="py-3.5 px-4 font-bold text-slate-800">
                  <div class="max-w-md">
                    {{ doc.judul }}
                  </div>
                </td>
                <td class="py-3.5 px-4 text-slate-500 whitespace-nowrap">
                  {{ doc.tanggal || doc.created_at || '-' }}
                </td>
                <td class="py-3.5 px-4 whitespace-nowrap">
                  <code class="px-2 py-0.5 rounded bg-slate-100 text-slate-800 font-mono text-[11px] border border-slate-200">
                    {{ doc.kode_dokumen }}
                  </code>
                </td>
                <td class="py-3.5 px-4 whitespace-nowrap">
                  <span class="px-2.5 py-0.5 rounded-full text-[11px] font-medium bg-slate-100 text-slate-700 border border-slate-200">
                    {{ doc.category_label }}
                  </span>
                </td>
                <td class="py-3.5 px-4 whitespace-nowrap">
                  <span class="px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-sky-50 text-sky-700 border border-sky-200">
                    {{ doc.type_label }}
                  </span>
                </td>
                <td class="py-3.5 px-4 text-center whitespace-nowrap">
                  <div class="flex items-center justify-center gap-1">
                    <button
                      v-if="doc.file_dokumen"
                      @click="openViewer(doc)"
                      class="p-1.5 text-slate-600 hover:text-sky-600 hover:bg-sky-50 rounded-lg transition"
                      title="Lihat Dokumen PDF"
                    >
                      <Eye class="w-4 h-4" />
                    </button>
                    <button
                      @click="openEditModal(doc)"
                      class="p-1.5 text-slate-600 hover:text-amber-600 hover:bg-amber-50 rounded-lg transition"
                      title="Edit Dokumen"
                    >
                      <Pencil class="w-4 h-4" />
                    </button>
                    <button
                      @click="deleteDocument(doc)"
                      class="p-1.5 text-slate-600 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition"
                      title="Hapus Dokumen"
                    >
                      <Trash2 class="w-4 h-4" />
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- ===== MODAL TAMBAH DOKUMEN ===== -->
    <div
      v-if="showAddModal"
      class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4 overflow-y-auto"
    >
      <div class="bg-white rounded-3xl max-w-lg w-full p-6 shadow-2xl border border-slate-100 space-y-5 animate-in fade-in zoom-in-95 duration-200 my-8">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
          <div class="flex items-center gap-2">
            <div class="p-2 bg-sky-50 text-sky-600 rounded-xl">
              <FileText class="w-5 h-5" />
            </div>
            <h3 class="font-black text-slate-800 text-sm">Tambah Dokumen Baru</h3>
          </div>
          <button @click="showAddModal = false" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg">
            <X class="w-5 h-5" />
          </button>
        </div>

        <form @submit.prevent="submitAddDocument" class="space-y-4">
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div>
              <label class="block text-xs font-bold text-slate-700 mb-1">Grup / Kategori</label>
              <select
                v-model="addForm.category_slug"
                class="w-full text-xs p-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500"
              >
                <option value="">UMUM / GLOBAL</option>
                <option
                  v-for="cat in allSopCategories"
                  :key="cat.id"
                  :value="cat.slug"
                >
                  {{ cat.name }}
                </option>
              </select>
            </div>

            <div>
              <label class="block text-xs font-bold text-slate-700 mb-1">Jenis Dokumen <span class="text-rose-500">*</span></label>
              <select
                v-model="addForm.type_slug"
                required
                class="w-full text-xs p-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500"
              >
                <option
                  v-for="dt in allSopTypes"
                  :key="dt.id"
                  :value="dt.slug"
                >
                  {{ dt.name }}
                </option>
              </select>
            </div>
          </div>

          <div>
            <label class="block text-xs font-bold text-slate-700 mb-1">Judul Dokumen <span class="text-rose-500">*</span></label>
            <input
              type="text"
              v-model="addForm.judul"
              required
              placeholder="Contoh: STANDAR OPERASIONAL PROSEDUR PENGOPERASIAN DOZER"
              class="w-full text-xs p-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500"
            />
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div>
              <label class="block text-xs font-bold text-slate-700 mb-1">Kode Dokumen <span class="text-rose-500">*</span></label>
              <input
                type="text"
                v-model="addForm.kode_dokumen"
                required
                placeholder="Contoh: PPA-ADRO-SOP-PRO-01"
                class="w-full text-xs p-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500"
              />
            </div>

            <div>
              <label class="block text-xs font-bold text-slate-700 mb-1">Status</label>
              <select
                v-model="addForm.status"
                class="w-full text-xs p-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500"
              >
                <option value="aktif">Aktif</option>
                <option value="draft">Draft</option>
                <option value="arsip">Arsip</option>
              </select>
            </div>
          </div>

          <div>
            <label class="block text-xs font-bold text-slate-700 mb-1">File Dokumen (PDF) <span class="text-rose-500">*</span></label>
            <input
              type="file"
              required
              accept=".pdf,.jpg,.jpeg,.png"
              @change="handleAddFile"
              class="w-full text-xs p-2 bg-slate-50 border border-slate-200 rounded-xl file:mr-3 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-sky-50 file:text-sky-700 hover:file:bg-sky-100"
            />
            <p class="text-[11px] text-slate-400 mt-1">Mendukung file PDF, JPG, PNG (Maks 20MB)</p>
          </div>

          <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
            <button
              type="button"
              @click="showAddModal = false"
              class="px-4 py-2 text-xs font-bold text-slate-600 hover:bg-slate-100 rounded-xl transition"
            >
              Batal
            </button>
            <button
              type="submit"
              class="px-5 py-2 text-xs font-bold bg-sky-600 hover:bg-sky-700 text-white rounded-xl shadow-sm transition"
            >
              Simpan Dokumen
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- ===== MODAL EDIT DOKUMEN ===== -->
    <div
      v-if="showEditModal"
      class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4 overflow-y-auto"
    >
      <div class="bg-white rounded-3xl max-w-lg w-full p-6 shadow-2xl border border-slate-100 space-y-5 animate-in fade-in zoom-in-95 duration-200 my-8">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
          <div class="flex items-center gap-2">
            <div class="p-2 bg-amber-50 text-amber-600 rounded-xl">
              <Pencil class="w-5 h-5" />
            </div>
            <h3 class="font-black text-slate-800 text-sm">Edit Dokumen Prosedur</h3>
          </div>
          <button @click="showEditModal = false" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg">
            <X class="w-5 h-5" />
          </button>
        </div>

        <form @submit.prevent="submitEditDocument" class="space-y-4">
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div>
              <label class="block text-xs font-bold text-slate-700 mb-1">Grup / Kategori</label>
              <select
                v-model="editForm.category_slug"
                class="w-full text-xs p-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500"
              >
                <option value="global">UMUM / GLOBAL</option>
                <option
                  v-for="cat in allSopCategories"
                  :key="cat.id"
                  :value="cat.slug"
                >
                  {{ cat.name }}
                </option>
              </select>
            </div>

            <div>
              <label class="block text-xs font-bold text-slate-700 mb-1">Jenis Dokumen <span class="text-rose-500">*</span></label>
              <select
                v-model="editForm.type_slug"
                required
                class="w-full text-xs p-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500"
              >
                <option
                  v-for="dt in allSopTypes"
                  :key="dt.id"
                  :value="dt.slug"
                >
                  {{ dt.name }}
                </option>
              </select>
            </div>
          </div>

          <div>
            <label class="block text-xs font-bold text-slate-700 mb-1">Judul Dokumen <span class="text-rose-500">*</span></label>
            <input
              type="text"
              v-model="editForm.judul"
              required
              class="w-full text-xs p-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500"
            />
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div>
              <label class="block text-xs font-bold text-slate-700 mb-1">Kode Dokumen <span class="text-rose-500">*</span></label>
              <input
                type="text"
                v-model="editForm.kode_dokumen"
                required
                class="w-full text-xs p-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500"
              />
            </div>

            <div>
              <label class="block text-xs font-bold text-slate-700 mb-1">Status</label>
              <select
                v-model="editForm.status"
                class="w-full text-xs p-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500"
              >
                <option value="aktif">Aktif</option>
                <option value="draft">Draft</option>
                <option value="arsip">Arsip</option>
              </select>
            </div>
          </div>

          <div>
            <label class="block text-xs font-bold text-slate-700 mb-1">File Dokumen PDF (Biarkan kosong jika tidak diganti)</label>
            <input
              type="file"
              accept=".pdf,.jpg,.jpeg,.png"
              @change="handleEditFile"
              class="w-full text-xs p-2 bg-slate-50 border border-slate-200 rounded-xl file:mr-3 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-amber-50 file:text-amber-700 hover:file:bg-amber-100"
            />
            <div v-if="editForm.current_file" class="mt-1 text-[11px] text-slate-500 font-mono">
              File saat ini: {{ editForm.current_file }}
            </div>
          </div>

          <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
            <button
              type="button"
              @click="showEditModal = false"
              class="px-4 py-2 text-xs font-bold text-slate-600 hover:bg-slate-100 rounded-xl transition"
            >
              Batal
            </button>
            <button
              type="submit"
              class="px-5 py-2 text-xs font-bold bg-amber-600 hover:bg-amber-700 text-white rounded-xl shadow-sm transition"
            >
              Simpan Perubahan
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- ===== MODAL TAMBAH JENIS ===== -->
    <div
      v-if="showTypeModal"
      class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4 overflow-y-auto"
    >
      <div class="bg-white rounded-3xl max-w-sm w-full p-6 shadow-2xl border border-slate-100 space-y-4 animate-in fade-in zoom-in-95 duration-200">
        <div class="flex items-center justify-between pb-2 border-b border-slate-100">
          <h3 class="font-black text-slate-800 text-sm flex items-center gap-2">
            <Tag class="w-4 h-4 text-sky-500" />
            <span>Tambah Jenis Dokumen</span>
          </h3>
          <button @click="showTypeModal = false" class="text-slate-400 hover:text-slate-600 p-1">
            <X class="w-4 h-4" />
          </button>
        </div>

        <form @submit.prevent="submitAddType" class="space-y-4">
          <div>
            <label class="block text-xs font-bold text-slate-700 mb-1">Nama Jenis Dokumen <span class="text-rose-500">*</span></label>
            <input
              type="text"
              v-model="typeName"
              required
              placeholder="Contoh: TIR / HIRADC, JSA, IK"
              class="w-full text-xs p-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500"
            />
          </div>

          <div class="flex items-center justify-end gap-2 pt-2">
            <button
              type="button"
              @click="showTypeModal = false"
              class="px-4 py-2 text-xs font-bold text-slate-600 hover:bg-slate-100 rounded-xl transition"
            >
              Batal
            </button>
            <button
              type="submit"
              class="px-4 py-2 text-xs font-bold bg-sky-600 hover:bg-sky-700 text-white rounded-xl shadow-sm transition"
            >
              Simpan Jenis
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- ===== MODAL TAMBAH KATEGORI ===== -->
    <div
      v-if="showCatModal"
      class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4 overflow-y-auto"
    >
      <div class="bg-white rounded-3xl max-w-lg w-full p-6 shadow-2xl border border-slate-100 space-y-4 animate-in fade-in zoom-in-95 duration-200 my-8">
        <div class="flex items-center justify-between pb-2 border-b border-slate-100">
          <h3 class="font-black text-slate-800 text-sm flex items-center gap-2">
            <FolderPlus class="w-4 h-4 text-sky-500" />
            <span>Tambah Kategori Aktivitas</span>
          </h3>
          <button @click="showCatModal = false" class="text-slate-400 hover:text-slate-600 p-1">
            <X class="w-4 h-4" />
          </button>
        </div>

        <form @submit.prevent="submitAddCategory" class="space-y-4">
          <div>
            <label class="block text-xs font-bold text-slate-700 mb-1">Nama Kategori <span class="text-rose-500">*</span></label>
            <input
              type="text"
              v-model="catName"
              required
              placeholder="Contoh: Hauling Coal, Land Clearing"
              class="w-full text-xs p-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500"
            />
          </div>

          <div>
            <div class="flex items-center justify-between mb-1.5">
              <label class="text-xs font-bold text-slate-700">Pilih Dokumen Aktivitas</label>
              <div class="text-[11px] text-sky-600 space-x-2">
                <button type="button" @click="selectAllDocs" class="hover:underline font-semibold">Pilih Semua</button>
                <span>&bull;</span>
                <button type="button" @click="clearSelectDocs" class="hover:underline font-semibold">Kosongkan</button>
              </div>
            </div>

            <input
              type="text"
              v-model="catDocSearchQuery"
              placeholder="Cari judul / kode dokumen..."
              class="w-full text-xs p-2 mb-2 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500"
            />

            <div class="border border-slate-200 rounded-xl p-2 bg-slate-50 max-h-56 overflow-y-auto space-y-1">
              <div v-if="filteredSelectDocs.length === 0" class="text-center py-4 text-xs text-slate-400">
                Dokumen tidak ditemukan
              </div>
              <label
                v-for="d in filteredSelectDocs"
                :key="d.id"
                class="flex items-start gap-2.5 p-2 bg-white rounded-lg border border-slate-100 hover:border-slate-300 cursor-pointer text-xs transition"
              >
                <input
                  type="checkbox"
                  :value="d.id"
                  v-model="catSelectedDocs"
                  class="rounded text-sky-600 focus:ring-sky-500 mt-0.5"
                />
                <div class="flex-1 min-w-0">
                  <div class="flex items-center gap-1.5 flex-wrap">
                    <span class="px-1.5 py-0.2 rounded text-[10px] font-bold bg-slate-100 text-slate-600 uppercase">
                      {{ d.jenis }}
                    </span>
                    <strong class="text-slate-800 text-[11px]">{{ d.kode_dokumen }}</strong>
                  </div>
                  <div class="text-slate-600 text-[11px] truncate mt-0.5">
                    {{ d.judul }}
                  </div>
                </div>
              </label>
            </div>
            <p class="text-[11px] text-slate-400 mt-1">Dokumen yang dicentang akan otomatis muncul di bawah kategori ini.</p>
          </div>

          <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
            <button
              type="button"
              @click="showCatModal = false"
              class="px-4 py-2 text-xs font-bold text-slate-600 hover:bg-slate-100 rounded-xl transition"
            >
              Batal
            </button>
            <button
              type="submit"
              class="px-5 py-2 text-xs font-bold bg-sky-600 hover:bg-sky-700 text-white rounded-xl shadow-sm transition"
            >
              Simpan Kategori
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- ===== MODAL TAMBAHKAN DOKUMEN KE AKTIFITAS TERPILIH ===== -->
    <div
      v-if="showAttachModal"
      class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4 overflow-y-auto"
    >
      <div class="bg-white rounded-3xl max-w-2xl w-full p-6 shadow-2xl border border-slate-100 space-y-4 animate-in fade-in zoom-in-95 duration-200 my-8">
        <!-- Header -->
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
          <div class="flex items-center gap-3">
            <div class="p-2.5 bg-sky-50 text-sky-600 rounded-xl">
              <FileCheck class="w-6 h-6" />
            </div>
            <div>
              <h3 class="font-black text-slate-800 text-sm">Tambahkan Dokumen ke Aktifitas Terpilih</h3>
              <p class="text-xs text-slate-500">
                Pilih checklist dokumen dari prosedur untuk aktifitas <strong class="text-sky-600">{{ activeCategory?.name || currentCategory }}</strong>
              </p>
            </div>
          </div>
          <button @click="showAttachModal = false" class="text-slate-400 hover:text-slate-600 p-1">
            <X class="w-5 h-5" />
          </button>
        </div>

        <!-- Filter & Search Controls -->
        <div class="space-y-3">
          <div class="flex items-center gap-2 flex-wrap sm:flex-nowrap">
            <div class="relative flex-1">
              <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" />
              <input
                type="text"
                v-model="attachSearchQuery"
                placeholder="Cari judul, kode, atau jenis prosedur..."
                class="w-full pl-9 pr-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500"
              />
            </div>
            <select
              v-model="attachTypeFilter"
              class="text-xs py-2 px-3 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none"
            >
              <option value="">Semua Jenis Dokumen</option>
              <option v-for="t in allSopTypes" :key="t.id" :value="t.slug">
                {{ t.name }}
              </option>
            </select>
          </div>

          <!-- Quick Action Buttons -->
          <div class="flex items-center justify-between text-xs pt-1">
            <div class="flex items-center gap-2">
              <button
                type="button"
                @click="selectAllAttachDocs"
                class="text-xs font-semibold text-sky-600 hover:text-sky-700 bg-sky-50 hover:bg-sky-100 px-2.5 py-1 rounded-lg transition"
              >
                Pilih Semua Yang Tampil ({{ filteredAttachDocs.length }})
              </button>
              <button
                type="button"
                @click="clearAttachDocs"
                class="text-xs font-semibold text-slate-500 hover:text-slate-700 bg-slate-100 hover:bg-slate-200 px-2.5 py-1 rounded-lg transition"
              >
                Kosongkan
              </button>
            </div>
            <span class="text-slate-600 font-bold text-[11px] bg-slate-100 px-3 py-1 rounded-full">
              {{ attachSelectedDocs.length }} dokumen terpilih
            </span>
          </div>
        </div>

        <!-- Checklist List -->
        <div class="border border-slate-200 rounded-2xl p-2 bg-slate-50/50 max-h-[380px] overflow-y-auto space-y-1.5">
          <div
            v-if="!filteredAttachDocs || filteredAttachDocs.length === 0"
            class="text-center py-10 text-slate-400 text-xs"
          >
            Tidak ada dokumen prosedur yang cocok dengan pencarian.
          </div>

          <div
            v-for="d in filteredAttachDocs"
            :key="d.id"
            @click="toggleAttachDoc(d.id)"
            :class="[
              'p-3 rounded-xl border transition-all cursor-pointer flex items-center gap-3',
              isDocAttached(d.id)
                ? 'bg-sky-50/80 border-sky-300 shadow-sm'
                : 'bg-white border-slate-200/80 hover:bg-slate-50 hover:border-slate-300'
            ]"
          >
            <input
              type="checkbox"
              :checked="isDocAttached(d.id)"
              @click.stop="toggleAttachDoc(d.id)"
              class="w-4 h-4 text-sky-600 rounded border-slate-300 focus:ring-sky-500 cursor-pointer"
            />
            <div class="flex-1 min-w-0">
              <div class="flex items-center gap-2 mb-0.5 flex-wrap">
                <span class="px-2 py-0.5 rounded text-[10px] font-bold font-mono uppercase bg-slate-100 text-slate-700 border border-slate-200">
                  {{ d.jenis }}
                </span>
                <span class="font-mono text-xs font-bold text-slate-800">
                  {{ d.kode_dokumen }}
                </span>
              </div>
              <p class="text-xs font-bold text-slate-800 line-clamp-1">
                {{ d.judul }}
              </p>
            </div>
            <CheckCircle2
              v-if="isDocAttached(d.id)"
              class="w-5 h-5 text-sky-600 flex-shrink-0"
            />
          </div>
        </div>

        <div class="flex items-center justify-between pt-3 border-t border-slate-100">
          <button
            type="button"
            @click="openAddModal"
            class="text-xs text-sky-600 hover:text-sky-700 font-semibold flex items-center gap-1"
          >
            <Plus class="w-3.5 h-3.5" />
            <span>Upload Berkas Baru</span>
          </button>

          <div class="flex items-center gap-2">
            <button
              type="button"
              @click="showAttachModal = false"
              class="px-4 py-2 text-xs font-bold text-slate-600 hover:bg-slate-100 rounded-xl transition"
            >
              Batal
            </button>
            <button
              type="button"
              :disabled="isSubmittingAttach"
              @click="submitSyncCategoryDocs"
              class="px-5 py-2 text-xs font-bold bg-sky-600 hover:bg-sky-700 text-white rounded-xl shadow-sm shadow-sky-200 transition flex items-center gap-1.5 disabled:opacity-50"
            >
              <CheckCircle2 class="w-4 h-4" />
              <span>{{ isSubmittingAttach ? 'Menyimpan...' : 'Simpan ke Aktifitas' }}</span>
            </button>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
