<script setup>
import { ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { 
  BookOpen, 
  Search, 
  Plus, 
  Pencil, 
  Trash2, 
  FileText, 
  Calendar, 
  ShieldAlert,
  Activity,
  X
} from 'lucide-vue-next';

const props = defineProps({
  user: Object,
  notifCounts: Object,
  menu: Array,
  filters: Object,
  procedures: Array,
  categories: Array,
});

const filterSearch = ref(props.filters?.search || '');
const filterCategory = ref(props.filters?.category || '');
const filterRisk = ref(props.filters?.risk || '');
const filterStatus = ref(props.filters?.status || '');

const applyFilter = () => {
  router.get('/one-procedure/prosedur', {
    search: filterSearch.value,
    category: filterCategory.value,
    risk: filterRisk.value,
    status: filterStatus.value,
  }, { preserveState: true });
};

// Modal Add / Edit
const showModal = ref(false);
const editingProcedure = ref(null);
const form = ref({
  id: null,
  title: '',
  category: 'Operasional',
  revision: 'Rev. 0',
  description: '',
  risk_level: 'medium',
  status: 'active',
  effective_date: new Date().toISOString().split('T')[0],
  expired_date: '',
});

const openAdd = () => {
  editingProcedure.value = null;
  form.value = {
    id: null,
    title: '',
    category: 'Operasional',
    revision: 'Rev. 0',
    description: '',
    risk_level: 'medium',
    status: 'active',
    effective_date: new Date().toISOString().split('T')[0],
    expired_date: '',
  };
  showModal.value = true;
};

const openEdit = (proc) => {
  editingProcedure.value = proc;
  form.value = {
    id: proc.id,
    title: proc.title,
    category: proc.category || 'Operasional',
    revision: proc.revision || 'Rev. 0',
    description: proc.description || '',
    risk_level: proc.risk_level || 'medium',
    status: proc.status || 'active',
    effective_date: proc.effective_date || '',
    expired_date: proc.expired_date || '',
  };
  showModal.value = true;
};

const submitForm = () => {
  if (form.value.id) {
    router.post(`/one-procedure/prosedur/update/${form.value.id}`, form.value, {
      preserveScroll: true,
      onSuccess: () => showModal.value = false,
    });
  } else {
    router.post('/one-procedure/prosedur/store', form.value, {
      preserveScroll: true,
      onSuccess: () => showModal.value = false,
    });
  }
};

const deleteProcedure = (id) => {
  if (confirm('Hapus dokumen prosedur ini?')) {
    router.delete(`/one-procedure/prosedur/delete/${id}`, { preserveScroll: true });
  }
};
</script>

<template>
  <Head title="Prosedur - One-Procedure" />

  <AppLayout :user="user" :notif-counts="notifCounts" :menu="menu">
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm">
        <div>
          <div class="flex items-center gap-2 text-xs font-semibold text-rose-600 mb-1">
            <span>SOP &amp; Knowledge Management</span>
            <span>/</span>
            <span class="text-slate-500">Prosedur</span>
          </div>
          <h1 class="text-xl font-bold text-slate-800 tracking-tight flex items-center gap-2">
            <BookOpen class="w-6 h-6 text-rose-500" />
            Dokumen Prosedur Operasional (SOP)
          </h1>
          <p class="text-xs text-slate-500 mt-0.5">Repositori standar operasional prosedur, tingkat risiko, dan status revisi dokumen</p>
        </div>

        <button @click="openAdd" class="px-4 py-2 text-xs font-semibold rounded-xl bg-rose-600 hover:bg-rose-700 text-white shadow-sm transition flex items-center gap-2">
          <Plus class="w-4 h-4" />
          Tambah Prosedur Baru
        </button>
      </div>

      <!-- Search & Filters Bar -->
      <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
        <form @submit.prevent="applyFilter" class="flex flex-wrap items-center gap-3 text-xs w-full">
          <div>
            <label class="block text-[10px] font-bold text-slate-600 uppercase mb-1">Cari Prosedur</label>
            <div class="relative">
              <Search class="w-3.5 h-3.5 absolute left-3 top-2.5 text-slate-400" />
              <input type="text" v-model="filterSearch" placeholder="Judul / Deskripsi..." class="pl-8 pr-3 py-1.5 border border-slate-300 rounded-xl bg-slate-50/50 w-52" />
            </div>
          </div>

          <div>
            <label class="block text-[10px] font-bold text-slate-600 uppercase mb-1">Kategori</label>
            <select v-model="filterCategory" @change="applyFilter" class="px-3 py-1.5 border border-slate-300 rounded-xl bg-slate-50/50">
              <option value="">Semua Kategori</option>
              <option v-for="c in categories" :key="c" :value="c">{{ c }}</option>
            </select>
          </div>

          <div>
            <label class="block text-[10px] font-bold text-slate-600 uppercase mb-1">Tingkat Risiko</label>
            <select v-model="filterRisk" @change="applyFilter" class="px-3 py-1.5 border border-slate-300 rounded-xl bg-slate-50/50">
              <option value="">Semua Risiko</option>
              <option value="low">Rendah</option>
              <option value="medium">Sedang</option>
              <option value="high">Tinggi</option>
              <option value="critical">Kritis</option>
            </select>
          </div>

          <div>
            <label class="block text-[10px] font-bold text-slate-600 uppercase mb-1">Status</label>
            <select v-model="filterStatus" @change="applyFilter" class="px-3 py-1.5 border border-slate-300 rounded-xl bg-slate-50/50">
              <option value="">Semua Status</option>
              <option value="active">Aktif</option>
              <option value="revision_needed">Perlu Revisi</option>
              <option value="expired">Kadaluarsa</option>
            </select>
          </div>

          <div class="self-end pb-0.5">
            <button type="submit" class="px-3.5 py-1.5 bg-slate-900 hover:bg-slate-800 text-white font-semibold rounded-xl transition">
              Filter
            </button>
          </div>
        </form>
      </div>

      <!-- Grid Cards of Procedures -->
      <div v-if="procedures && procedures.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <div 
          v-for="proc in procedures" 
          :key="proc.id"
          class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden flex flex-col hover:border-rose-300 hover:shadow-md transition"
        >
          <!-- Card Header -->
          <div class="p-4 border-b border-slate-100 flex items-start justify-between gap-2">
            <div class="flex items-start gap-3">
              <div class="w-10 h-10 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center shrink-0">
                <FileText class="w-5 h-5" />
              </div>
              <div>
                <h3 class="font-bold text-slate-800 text-sm line-clamp-1" :title="proc.title">{{ proc.title }}</h3>
                <span class="text-[11px] font-medium text-slate-500">{{ proc.category }} &bull; {{ proc.revision }}</span>
              </div>
            </div>
          </div>

          <!-- Card Body -->
          <div class="p-4 flex-1 space-y-3 text-xs">
            <p class="text-slate-600 line-clamp-2 text-[11px]">{{ proc.description || 'Tidak ada deskripsi' }}</p>

            <div class="flex items-center gap-2 pt-1">
              <span class="px-2.5 py-0.5 text-[10px] font-bold rounded-full uppercase" :class="{
                'bg-emerald-100 text-emerald-800': proc.risk_level === 'low',
                'bg-amber-100 text-amber-800': proc.risk_level === 'medium',
                'bg-rose-100 text-rose-800': proc.risk_level === 'high',
                'bg-purple-100 text-purple-800': proc.risk_level === 'critical',
              }">
                Risiko {{ proc.risk_level }}
              </span>

              <span class="px-2.5 py-0.5 text-[10px] font-bold rounded-full" :class="{
                'bg-emerald-100 text-emerald-800': proc.status === 'active',
                'bg-amber-100 text-amber-800': proc.status === 'revision_needed',
                'bg-rose-100 text-rose-800': proc.status === 'expired',
              }">
                {{ proc.status }}
              </span>
            </div>

            <div class="pt-2 border-t border-slate-100 flex items-center justify-between text-[10px] text-slate-400">
              <span class="flex items-center gap-1">
                <Calendar class="w-3 h-3 text-slate-400" />
                Berlaku: {{ proc.effective_date || '-' }}
              </span>
              <span class="font-semibold text-sky-600">
                {{ proc.assessments_count || 0 }} Activity
              </span>
            </div>
          </div>

          <!-- Card Footer -->
          <div class="p-3 bg-slate-50 border-t border-slate-100 flex items-center justify-between">
            <a :href="`/one-procedure/activity?procedure_id=${proc.id}`" class="text-[11px] font-bold text-rose-600 hover:text-rose-700 flex items-center gap-1">
              <Activity class="w-3.5 h-3.5" /> Kelola Activity
            </a>

            <div class="flex items-center gap-1">
              <button @click="openEdit(proc)" class="p-1.5 text-slate-400 hover:text-sky-600 rounded-lg hover:bg-white transition">
                <Pencil class="w-4 h-4" />
              </button>
              <button @click="deleteProcedure(proc.id)" class="p-1.5 text-slate-400 hover:text-rose-600 rounded-lg hover:bg-white transition">
                <Trash2 class="w-4 h-4" />
              </button>
            </div>
          </div>
        </div>
      </div>

      <div v-else class="bg-white p-12 rounded-2xl border border-slate-200/80 shadow-sm text-center text-slate-400 space-y-2">
        <BookOpen class="w-12 h-12 mx-auto text-slate-300" />
        <p class="text-sm font-semibold text-slate-600">Belum ada dokumen prosedur yang sesuai filter.</p>
      </div>
    </div>

    <!-- ADD / EDIT PROCEDURE MODAL -->
    <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-xs">
      <div class="bg-white w-full max-w-lg rounded-2xl shadow-xl border border-slate-100 p-5 space-y-4 text-xs">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
          <h3 class="font-bold text-slate-800 text-sm">{{ editingProcedure ? 'Edit Prosedur SOP' : 'Tambah Prosedur SOP Baru' }}</h3>
          <button @click="showModal = false" class="text-slate-400 hover:text-slate-600">
            <X class="w-4 h-4" />
          </button>
        </div>

        <form @submit.prevent="submitForm" class="space-y-3">
          <div>
            <label class="block font-bold text-slate-700 mb-1">Judul Prosedur *</label>
            <input type="text" v-model="form.title" placeholder="Contoh: Prosedur Pengisian Bahan Bakar..." required class="w-full px-3 py-2 border border-slate-300 rounded-xl bg-slate-50/50" />
          </div>

          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block font-bold text-slate-700 mb-1">Kategori</label>
              <input type="text" v-model="form.category" placeholder="Operasional, K3, Perawatan..." class="w-full px-3 py-2 border border-slate-300 rounded-xl bg-slate-50/50" />
            </div>

            <div>
              <label class="block font-bold text-slate-700 mb-1">Versi Revisi</label>
              <input type="text" v-model="form.revision" placeholder="Rev. 0..." class="w-full px-3 py-2 border border-slate-300 rounded-xl bg-slate-50/50" />
            </div>
          </div>

          <div>
            <label class="block font-bold text-slate-700 mb-1">Deskripsi Ringkas</label>
            <textarea v-model="form.description" rows="2" placeholder="Penjelasan singkat prosedur..." class="w-full px-3 py-2 border border-slate-300 rounded-xl bg-slate-50/50"></textarea>
          </div>

          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block font-bold text-slate-700 mb-1">Tingkat Risiko *</label>
              <select v-model="form.risk_level" required class="w-full px-3 py-2 border border-slate-300 rounded-xl bg-slate-50/50">
                <option value="low">Rendah (Low)</option>
                <option value="medium">Sedang (Medium)</option>
                <option value="high">Tinggi (High)</option>
                <option value="critical">Kritis (Critical)</option>
              </select>
            </div>

            <div>
              <label class="block font-bold text-slate-700 mb-1">Status Prosedur</label>
              <select v-model="form.status" required class="w-full px-3 py-2 border border-slate-300 rounded-xl bg-slate-50/50">
                <option value="active">Aktif</option>
                <option value="revision_needed">Perlu Revisi</option>
                <option value="expired">Kadaluarsa</option>
              </select>
            </div>
          </div>

          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block font-bold text-slate-700 mb-1">Tanggal Berlaku</label>
              <input type="date" v-model="form.effective_date" class="w-full px-3 py-2 border border-slate-300 rounded-xl bg-slate-50/50" />
            </div>

            <div>
              <label class="block font-bold text-slate-700 mb-1">Tanggal Kadaluarsa</label>
              <input type="date" v-model="form.expired_date" class="w-full px-3 py-2 border border-slate-300 rounded-xl bg-slate-50/50" />
            </div>
          </div>

          <div class="flex justify-end gap-2 pt-2 border-t border-slate-100">
            <button type="button" @click="showModal = false" class="px-4 py-2 font-semibold bg-slate-100 rounded-xl">Batal</button>
            <button type="submit" class="px-4 py-2 font-semibold text-white bg-rose-600 hover:bg-rose-700 rounded-xl">Simpan Prosedur</button>
          </div>
        </form>
      </div>
    </div>
  </AppLayout>
</template>
