<script setup>
import { confirmDialog } from '@/utils/confirm';
import { ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { 
  Camera, 
  Calendar, 
  Upload, 
  Trash2, 
  Eye, 
  CheckCircle2, 
  AlertCircle,
  X,
  User,
  Clock
} from 'lucide-vue-next';

const props = defineProps({
  user: Object,
  notifCounts: Object,
  menu: Array,
  filters: Object,
  unitGrid: Array,
  metrics: Object,
});

const filterForm = ref({
  tanggal: props.filters?.tanggal || new Date().toISOString().split('T')[0],
  shift: props.filters?.shift || 'D',
});

const applyFilter = () => {
  router.get('/one-reporting/ojk', filterForm.value, { preserveState: true, replace: true });
};

// Upload Modal
const showUploadModal = ref(false);
const selectedUnitForUpload = ref('');
const uploadForm = ref({
  unit_code: '',
  tanggal: props.filters?.tanggal || new Date().toISOString().split('T')[0],
  shift: props.filters?.shift || 'D',
  nama_operator: '',
  foto: null,
});

const openUploadModal = (unitCode = '') => {
  uploadForm.value = {
    unit_code: unitCode,
    tanggal: filterForm.value.tanggal,
    shift: filterForm.value.shift,
    nama_operator: '',
    foto: null,
  };
  showUploadModal.value = true;
};

const handleFileSelect = (e) => {
  uploadForm.value.foto = e.target.files[0];
};

const submitUpload = () => {
  const formData = new FormData();
  formData.append('unit_code', uploadForm.value.unit_code);
  formData.append('tanggal', uploadForm.value.tanggal);
  formData.append('shift', uploadForm.value.shift);
  if (uploadForm.value.nama_operator) {
    formData.append('nama_operator', uploadForm.value.nama_operator);
  }
  if (uploadForm.value.foto) {
    formData.append('foto', uploadForm.value.foto);
  }

  router.post('/one-reporting/ojk/upload', formData, {
    onSuccess: () => {
      showUploadModal.value = false;
    }
  });
};

const deletePhoto = async (id, unitCode) => {
  if (await confirmDialog(`Hapus foto OJK unit ${unitCode}?`)) {
    router.delete(`/one-reporting/ojk/delete/${id}`, { preserveScroll: true });
  }
};

// Preview Modal
const previewImage = ref(null);
const openPreview = (url) => {
  previewImage.value = url;
};
</script>

<template>
  <Head title="OJK Monitoring - One-Reporting" />

  <AppLayout :user="user" :notif-counts="notifCounts" :menu="menu">
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm">
        <div>
          <div class="flex items-center gap-2 text-xs font-semibold text-sky-600 mb-1">
            <span>Operation Compliance Report</span>
            <span>/</span>
            <span class="text-slate-500">OJK Monitoring</span>
          </div>
          <h1 class="text-xl font-bold text-slate-800 tracking-tight flex items-center gap-2">
            <Camera class="w-6 h-6 text-sky-500" />
            Monitoring Upload Foto OJK
          </h1>
          <p class="text-xs text-slate-500 mt-0.5">Pantauan foto OJK per unit beserta nama operator &amp; jam upload</p>
        </div>

        <button @click="openUploadModal()" class="px-4 py-2 text-xs font-semibold rounded-xl bg-sky-600 hover:bg-sky-700 text-white shadow-sm transition flex items-center gap-1.5">
          <Upload class="w-4 h-4" /> Upload Foto OJK
        </button>
      </div>

      <!-- Filter Bar -->
      <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
        <form @submit.prevent="applyFilter" class="flex flex-wrap items-center gap-3">
          <div>
            <label class="block text-[10px] font-bold text-slate-600 uppercase mb-1">Tanggal</label>
            <input type="date" v-model="filterForm.tanggal" @change="applyFilter" class="px-3 py-1.5 text-xs font-medium border border-slate-300 rounded-xl bg-slate-50/50" />
          </div>

          <div>
            <label class="block text-[10px] font-bold text-slate-600 uppercase mb-1">Shift</label>
            <select v-model="filterForm.shift" @change="applyFilter" class="px-3 py-1.5 text-xs font-medium border border-slate-300 rounded-xl bg-slate-50/50">
              <option value="D">Day Shift (Siang)</option>
              <option value="N">Night Shift (Malam)</option>
            </select>
          </div>
        </form>

        <div class="flex items-center gap-2">
          <span class="px-3 py-1.5 text-xs font-bold rounded-xl bg-emerald-50 text-emerald-700 border border-emerald-200 flex items-center gap-1.5">
            <CheckCircle2 class="w-4 h-4" />
            Sudah Upload: {{ metrics?.totalUploaded || 0 }}
          </span>
          <span class="px-3 py-1.5 text-xs font-bold rounded-xl bg-slate-100 text-slate-600 border border-slate-200 flex items-center gap-1.5">
            <AlertCircle class="w-4 h-4" />
            Belum Upload: {{ metrics?.totalRemaining || 0 }}
          </span>
        </div>
      </div>

      <!-- Unit Cards Grid -->
      <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
        <div 
          v-for="u in unitGrid" 
          :key="u.unit_code"
          class="bg-white rounded-2xl border p-3 flex flex-col justify-between transition-all duration-200"
          :class="u.has_photo ? 'border-emerald-300 shadow-sm hover:shadow-md' : 'border-slate-200 opacity-80'"
        >
          <!-- Card Header -->
          <div class="flex items-center justify-between mb-2">
            <span class="font-extrabold text-xs text-sky-700 tracking-wide">{{ u.unit_code }}</span>
            <span v-if="u.has_photo" class="w-2 h-2 rounded-full bg-emerald-500"></span>
          </div>

          <!-- Photo Container -->
          <div 
            class="w-full aspect-4/3 rounded-xl overflow-hidden bg-slate-100 relative group flex items-center justify-center border border-slate-100"
          >
            <template v-if="u.has_photo">
              <img :src="u.photo.url" :alt="u.unit_code" class="w-full h-full object-cover" />
              <div class="absolute inset-0 bg-slate-900/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-2">
                <button @click="openPreview(u.photo.url)" class="p-1.5 bg-white text-slate-800 rounded-lg hover:bg-slate-100">
                  <Eye class="w-4 h-4" />
                </button>
                <button @click="deletePhoto(u.photo.id, u.unit_code)" class="p-1.5 bg-rose-600 text-white rounded-lg hover:bg-rose-700">
                  <Trash2 class="w-4 h-4" />
                </button>
              </div>
            </template>
            <template v-else>
              <button @click="openUploadModal(u.unit_code)" class="flex flex-col items-center gap-1 text-slate-400 hover:text-sky-600 transition">
                <Camera class="w-6 h-6" />
                <span class="text-[10px] font-bold uppercase tracking-wider">Upload Foto</span>
              </button>
            </template>
          </div>

          <!-- Card Footer -->
          <div class="mt-2 pt-2 border-t border-slate-100 text-[10px]">
            <template v-if="u.has_photo">
              <div class="font-semibold text-slate-800 flex items-center gap-1 truncate">
                <User class="w-3 h-3 text-slate-400 shrink-0" />
                {{ u.photo.operator_nama }}
              </div>
              <div class="text-slate-400 flex items-center gap-1 mt-0.5">
                <Clock class="w-3 h-3 text-slate-400 shrink-0" />
                {{ u.photo.created_at }} WIB
              </div>
            </template>
            <template v-else>
              <span class="text-slate-400 font-medium">Belum ada foto</span>
            </template>
          </div>
        </div>
      </div>
    </div>

    <!-- UPLOAD MODAL -->
    <div v-if="showUploadModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-xs">
      <div class="bg-white w-full max-w-md rounded-2xl shadow-xl border border-slate-100 p-5 space-y-4 text-xs">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
          <h3 class="font-bold text-slate-800 text-sm flex items-center gap-2">
            <Upload class="w-4 h-4 text-sky-500" /> Upload Foto OJK
          </h3>
          <button @click="showUploadModal = false" class="p-1 text-slate-400 hover:text-slate-600">
            <X class="w-5 h-5" />
          </button>
        </div>

        <form @submit.prevent="submitUpload" class="space-y-3">
          <div>
            <label class="block font-bold text-slate-700 mb-1">Kode Unit *</label>
            <input type="text" v-model="uploadForm.unit_code" placeholder="Contoh: HD785-012..." required class="w-full px-3 py-2 border border-slate-300 rounded-xl" />
          </div>

          <div>
            <label class="block font-bold text-slate-700 mb-1">Nama Operator</label>
            <input type="text" v-model="uploadForm.nama_operator" placeholder="Nama operator unit..." class="w-full px-3 py-2 border border-slate-300 rounded-xl" />
          </div>

          <div>
            <label class="block font-bold text-slate-700 mb-1">Pilih File Foto OJK *</label>
            <input type="file" @change="handleFileSelect" accept="image/*" required class="w-full text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-sky-50 file:text-sky-700 hover:file:bg-sky-100" />
          </div>

          <div class="flex justify-end gap-2 pt-3 border-t border-slate-100">
            <button type="button" @click="showUploadModal = false" class="px-4 py-2 font-semibold bg-slate-100 rounded-xl">Batal</button>
            <button type="submit" class="px-5 py-2 font-semibold text-white bg-sky-600 rounded-xl shadow-sm">Upload Foto</button>
          </div>
        </form>
      </div>
    </div>

    <!-- PREVIEW IMAGE MODAL -->
    <div v-if="previewImage" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm" @click="previewImage = null">
      <div class="max-w-3xl max-h-[85vh] relative rounded-2xl overflow-hidden shadow-2xl">
        <img :src="previewImage" alt="Preview Foto OJK" class="w-full h-full object-contain" />
        <button @click="previewImage = null" class="absolute top-4 right-4 p-2 bg-slate-900/60 text-white rounded-full hover:bg-slate-900">
          <X class="w-6 h-6" />
        </button>
      </div>
    </div>
  </AppLayout>
</template>
