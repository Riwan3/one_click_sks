<script setup>
import { ref, computed } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import axios from 'axios';
import AppLayout from '@/Layouts/AppLayout.vue';
import {
  OctagonAlert,
  Plus,
  Download,
  ListTodo,
  FolderOpen,
  Wrench,
  ShieldCheck,
  AlertTriangle,
  CheckCircle2,
  TrendingUp,
  X,
  Camera,
  Info,
  Send,
  Calendar,
  Layers,
  MapPin,
  Clock,
  RotateCcw
} from 'lucide-vue-next';
import FindingFilterPanel from './Partials/Temuan/FindingFilterPanel.vue';
import FindingTable from './Partials/Temuan/FindingTable.vue';
import FindingInfoTab from './Partials/Temuan/FindingInfoTab.vue';
import FindingPicTab from './Partials/Temuan/FindingPicTab.vue';
import FindingActionTab from './Partials/Temuan/FindingActionTab.vue';
import FindingVerifyTab from './Partials/Temuan/FindingVerifyTab.vue';
import FindingHistoryTab from './Partials/Temuan/FindingHistoryTab.vue';
import PicCombobox from './Partials/Temuan/PicCombobox.vue';
import {
  FINDING_BASE_URL,
  FINDING_ASPECTS,
  findingStatusClass,
  priorityClass,
  addDays
} from '@/utils/gmpFinding';

const props = defineProps({
  user: { type: Object, required: true },
  notifCounts: { type: Object, default: () => ({}) },
  menu: { type: Array, required: true },
  filters: { type: Object, required: true },
  stats: { type: Object, required: true },
  findings: { type: Array, default: () => [] },
  yearOptions: { type: Array, default: () => [] },
  areaOptions: { type: Array, default: () => [] },
  priorityOptions: { type: Array, default: () => [] },
  statusOptions: { type: Object, default: () => ({}) },
  slaDays: { type: Object, default: () => ({}) },
  employees: { type: Array, default: () => [] },
});

// ==================== FILTER HANDLING ====================
const applyFilters = (newFilters) => {
  router.get(FINDING_BASE_URL, newFilters, {
    preserveState: true,
    preserveScroll: true,
  });
};

// ==================== DRAWER STATE & LOGIC ====================
const isDrawerOpen = ref(false);
const activeDrawerTab = ref('info');
const drawerFinding = ref(null);
const isLoadingDetail = ref(false);

const drawerTabs = [
  { key: 'info', label: 'Informasi', icon: Info },
  { key: 'pic', label: 'Penanggung Jawab', icon: ListTodo },
  { key: 'action', label: 'Corrective Action', icon: Wrench },
  { key: 'verif', label: 'Verifikasi', icon: ShieldCheck },
  { key: 'history', label: 'Riwayat', icon: Clock },
];

const handleOpenDrawer = async ({ id, tab }) => {
  activeDrawerTab.value = tab || 'info';
  const initial = props.findings.find((f) => f.id === id);
  drawerFinding.value = initial || null;
  isDrawerOpen.value = true;

  // Load detail lengkap (termasuk evidences & timeline) via API show
  isLoadingDetail.value = true;
  try {
    const res = await axios.get(`${FINDING_BASE_URL}/${id}`);
    if (res.data) {
      drawerFinding.value = res.data;
    }
  } catch (err) {
    console.error('Gagal memuat detail temuan:', err);
  } finally {
    isLoadingDetail.value = false;
  }
};

const closeDrawer = () => {
  isDrawerOpen.value = false;
  drawerFinding.value = null;
};

const refreshActiveFinding = async () => {
  if (!drawerFinding.value?.id) return;
  router.reload({
    only: ['findings', 'stats'],
    onSuccess: async () => {
      try {
        const res = await axios.get(`${FINDING_BASE_URL}/${drawerFinding.value.id}`);
        if (res.data) {
          drawerFinding.value = res.data;
        }
      } catch (err) {
        console.error('Gagal memperbarui detail temuan:', err);
      }
    }
  });
};

// ==================== MODAL TAMBAH TEMUAN ====================
const isModalCreateOpen = ref(false);
const newPicJabatan = ref('');

const createForm = useForm({
  tahun: new Date().getFullYear(),
  week: '',
  area_id: props.areaOptions?.[0] || 'MHR',
  location_id: '',
  aspect_id: FINDING_ASPECTS[0] || 'TEKNIS',
  checklist_id: '',
  standard_description: '',
  finding_description: '',
  foto_sebelum: null,
  pic_user_id: '',
  priority: 'Medium',
  due_date: addDays(props.slaDays['Medium'] ?? 7),
  recommendation: '',
});

const openCreateModal = () => {
  createForm.reset();
  createForm.tahun = new Date().getFullYear();
  createForm.area_id = props.areaOptions?.[0] || 'MHR';
  createForm.aspect_id = FINDING_ASPECTS[0] || 'TEKNIS';
  createForm.priority = 'Medium';
  createForm.due_date = addDays(props.slaDays['Medium'] ?? 7);
  newPicJabatan.value = '';
  isModalCreateOpen.value = true;
};

const handlePriorityChangeCreate = () => {
  const p = createForm.priority;
  if (props.slaDays[p] !== undefined) {
    createForm.due_date = addDays(props.slaDays[p]);
  }
};

const handleFileBefore = (e) => {
  createForm.foto_sebelum = e.target.files[0] || null;
};

const submitCreateFinding = () => {
  createForm.post(FINDING_BASE_URL, {
    forceFormData: true,
    preserveScroll: true,
    onSuccess: () => {
      isModalCreateOpen.value = false;
      createForm.reset();
    },
  });
};

// ==================== EXPORT DATA ====================
const exportCsv = () => {
  if (!props.findings || props.findings.length === 0) return;
  const headers = ['Kode', 'Tanggal', 'Tahun', 'Week', 'Area', 'Lokasi', 'Aspek', 'Deskripsi', 'Prioritas', 'PIC NRP', 'PIC Nama', 'Deadline', 'Progress (%)', 'Status'];
  const rows = props.findings.map(f => [
    `"${f.finding_code || ''}"`,
    `"${f.finding_date || ''}"`,
    `"${f.year || ''}"`,
    `"${f.week || ''}"`,
    `"${f.area_id || ''}"`,
    `"${(f.location_id || '').replace(/"/g, '""')}"`,
    `"${f.aspect_id || ''}"`,
    `"${(f.finding_description || '').replace(/"/g, '""')}"`,
    `"${f.priority || ''}"`,
    `"${f.pic_user_id || ''}"`,
    `"${(f.pic_nama || '').replace(/"/g, '""')}"`,
    `"${f.due_date || ''}"`,
    f.progress_percentage || 0,
    `"${f.computed_status || f.status || ''}"`
  ]);

  const csvContent = 'data:text/csv;charset=utf-8,' + [headers.join(','), ...rows.map(e => e.join(','))].join('\n');
  const encodedUri = encodeURI(csvContent);
  const link = document.createElement('a');
  link.setAttribute('href', encodedUri);
  link.setAttribute('download', `Laporan_Temuan_GMP_${new Date().toISOString().slice(0, 10)}.csv`);
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
};
</script>

<template>
  <AppLayout :user="user" :notifCounts="notifCounts" :menu="menu">
    <Head title="Temuan & Tindak Lanjut GMP - One-GMP" />

    <div class="space-y-6 pb-12">
      <!-- HERO HEADER -->
      <div
        class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-indigo-950 via-slate-900 to-indigo-900 p-6 sm:p-8 text-white shadow-xl border border-indigo-800/40"
      >
        <div class="absolute -right-16 -top-16 h-64 w-64 rounded-full bg-indigo-500/10 blur-3xl pointer-events-none" />
        <div class="absolute -left-16 -bottom-16 h-64 w-64 rounded-full bg-blue-500/10 blur-3xl pointer-events-none" />

        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
          <div class="space-y-2">
            <div class="inline-flex items-center gap-2 rounded-full bg-indigo-500/20 px-3 py-1 text-xs font-semibold text-indigo-300 border border-indigo-500/30">
              <OctagonAlert class="h-3.5 w-3.5" />
              <span>Good Mining Practice &bull; Monitoring Action Plan</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-white">
              Temuan &amp; Tindak Lanjut GMP
            </h1>
            <p class="text-xs sm:text-sm text-indigo-200/80 max-w-2xl leading-relaxed">
              Pusat kendali dan monitoring tindakan perbaikan (corrective action) temuan Good Mining Practice hingga terverifikasi tuntas.
            </p>
          </div>

          <div class="flex items-center gap-2.5 flex-wrap">
            <button
              type="button"
              @click="exportCsv"
              class="inline-flex items-center gap-2 rounded-xl bg-white/10 hover:bg-white/20 backdrop-blur-md px-4 py-2.5 text-xs font-bold text-white border border-white/20 transition shadow-sm"
              title="Unduh Data CSV"
            >
              <Download class="h-4 w-4" />
              <span>Export Laporan</span>
            </button>

            <button
              type="button"
              @click="openCreateModal"
              class="inline-flex items-center gap-2 rounded-xl bg-white text-indigo-900 hover:bg-indigo-50 px-4 py-2.5 text-xs font-black shadow-lg shadow-black/20 transition hover:-translate-y-0.5"
            >
              <Plus class="h-4 w-4 text-indigo-600" />
              <span>Tambah Temuan</span>
            </button>
          </div>
        </div>
      </div>

      <!-- 7 STATS CARDS -->
      <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-7 gap-3">
        <!-- Total -->
        <div class="rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm hover:shadow transition">
          <div class="flex items-center justify-between text-slate-400 mb-2">
            <span class="text-[10px] font-extrabold uppercase tracking-wider">Total</span>
            <div class="h-7 w-7 rounded-lg bg-slate-100 flex items-center justify-center text-slate-600">
              <ListTodo class="h-4 w-4" />
            </div>
          </div>
          <div class="text-2xl font-black text-slate-800 leading-none">{{ stats.total }}</div>
          <div class="text-[11px] text-slate-400 mt-1 font-medium">Semua Temuan</div>
        </div>

        <!-- Open -->
        <div class="rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm hover:shadow transition">
          <div class="flex items-center justify-between text-indigo-400 mb-2">
            <span class="text-[10px] font-extrabold uppercase tracking-wider">Open</span>
            <div class="h-7 w-7 rounded-lg bg-indigo-50 flex items-center justify-center text-indigo-600">
              <FolderOpen class="h-4 w-4" />
            </div>
          </div>
          <div class="text-2xl font-black text-indigo-600 leading-none">{{ stats.open }}</div>
          <div class="text-[11px] text-slate-400 mt-1 font-medium">PIC Ditugaskan</div>
        </div>

        <!-- Progress -->
        <div class="rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm hover:shadow transition">
          <div class="flex items-center justify-between text-amber-500 mb-2">
            <span class="text-[10px] font-extrabold uppercase tracking-wider">Progress</span>
            <div class="h-7 w-7 rounded-lg bg-amber-50 flex items-center justify-center text-amber-600">
              <Wrench class="h-4 w-4" />
            </div>
          </div>
          <div class="text-2xl font-black text-amber-600 leading-none">{{ stats.progress }}</div>
          <div class="text-[11px] text-slate-400 mt-1 font-medium">Dalam Pengerjaan</div>
        </div>

        <!-- Waiting Verification -->
        <div class="rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm hover:shadow transition">
          <div class="flex items-center justify-between text-blue-500 mb-2">
            <span class="text-[10px] font-extrabold uppercase tracking-wider">Waiting Verif</span>
            <div class="h-7 w-7 rounded-lg bg-blue-50 flex items-center justify-center text-blue-600">
              <ShieldCheck class="h-4 w-4" />
            </div>
          </div>
          <div class="text-2xl font-black text-blue-600 leading-none">{{ stats.waiting }}</div>
          <div class="text-[11px] text-slate-400 mt-1 font-medium">Menunggu Cek</div>
        </div>

        <!-- Overdue -->
        <div class="rounded-2xl border border-rose-200 bg-rose-50/50 p-4 shadow-sm hover:shadow transition">
          <div class="flex items-center justify-between text-rose-500 mb-2">
            <span class="text-[10px] font-extrabold uppercase tracking-wider text-rose-700">Overdue</span>
            <div class="h-7 w-7 rounded-lg bg-rose-100 flex items-center justify-center text-rose-600">
              <AlertTriangle class="h-4 w-4" />
            </div>
          </div>
          <div class="text-2xl font-black text-rose-700 leading-none">{{ stats.overdue }}</div>
          <div class="text-[11px] text-rose-600 mt-1 font-bold">Lewat Deadline</div>
        </div>

        <!-- Closed -->
        <div class="rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm hover:shadow transition">
          <div class="flex items-center justify-between text-emerald-500 mb-2">
            <span class="text-[10px] font-extrabold uppercase tracking-wider">Closed</span>
            <div class="h-7 w-7 rounded-lg bg-emerald-50 flex items-center justify-center text-emerald-600">
              <CheckCircle2 class="h-4 w-4" />
            </div>
          </div>
          <div class="text-2xl font-black text-emerald-600 leading-none">{{ stats.closed }}</div>
          <div class="text-[11px] text-slate-400 mt-1 font-medium">Selesai Diverifikasi</div>
        </div>

        <!-- Closure Rate -->
        <div class="rounded-2xl bg-gradient-to-br from-emerald-600 to-teal-700 p-4 text-white shadow-sm hover:shadow transition col-span-2 sm:col-span-1">
          <div class="flex items-center justify-between text-emerald-200 mb-2">
            <span class="text-[10px] font-extrabold uppercase tracking-wider">Closure Rate</span>
            <div class="h-7 w-7 rounded-lg bg-white/20 flex items-center justify-center text-white">
              <TrendingUp class="h-4 w-4" />
            </div>
          </div>
          <div class="text-2xl font-black text-white leading-none">{{ stats.rate }}%</div>
          <div class="text-[10px] text-emerald-100 mt-1 font-medium">Tingkat Penyelesaian</div>
        </div>
      </div>

      <!-- FILTER PANEL -->
      <FindingFilterPanel
        :filters="filters"
        :yearOptions="yearOptions"
        :areaOptions="areaOptions"
        :priorityOptions="priorityOptions"
        :statusOptions="statusOptions"
        @filters-changed="applyFilters"
      />

      <!-- FINDINGS TABLE -->
      <FindingTable
        :findings="findings"
        :statusLabels="statusOptions"
        @open-requested="handleOpenDrawer"
      />
    </div>

    <!-- ===== OFFCANVAS / DRAWER DETAIL TEMUAN ===== -->
    <div v-if="isDrawerOpen" class="fixed inset-0 z-50 flex justify-end">
      <!-- Backdrop -->
      <div
        class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity"
        @click="closeDrawer"
      />

      <!-- Drawer Panel -->
      <div
        class="relative z-10 flex h-full w-full max-w-3xl flex-col bg-white shadow-2xl animate-in slide-in-from-right duration-200"
      >
        <!-- Drawer Header -->
        <div class="flex items-center justify-between border-b border-indigo-800/20 bg-gradient-to-r from-indigo-950 via-slate-900 to-indigo-900 p-5 text-white">
          <div class="min-w-0 pr-4">
            <div class="flex items-center gap-2">
              <OctagonAlert class="h-5 w-5 text-indigo-400 shrink-0" />
              <h2 class="text-base font-black truncate">
                Detail Temuan
                <span class="ml-2 font-mono text-xs px-2 py-0.5 rounded-lg bg-white/10 text-indigo-200 border border-white/10">
                  {{ drawerFinding?.finding_code || 'Loading...' }}
                </span>
              </h2>
            </div>
            <div class="flex items-center gap-2 text-xs text-indigo-200/80 mt-1">
              <span v-if="drawerFinding" :class="['rounded-full px-2 py-0.5 text-[10px] font-bold uppercase', findingStatusClass(drawerFinding.computed_status)]">
                {{ statusOptions[drawerFinding.computed_status] || drawerFinding.computed_status }}
              </span>
              <span>&bull;</span>
              <span>{{ drawerFinding?.source_type || 'Temuan GMP' }}</span>
            </div>
          </div>

          <button
            type="button"
            @click="closeDrawer"
            class="rounded-xl p-1.5 text-white/70 hover:bg-white/10 hover:text-white transition"
          >
            <X class="h-5 w-5" />
          </button>
        </div>

        <!-- Drawer Tab Navigation -->
        <div class="flex border-b border-slate-200 bg-slate-50 px-4 pt-2 overflow-x-auto gap-1">
          <button
            v-for="t in drawerTabs"
            :key="t.key"
            type="button"
            @click="activeDrawerTab = t.key"
            :class="[
              'flex items-center gap-1.5 px-3.5 py-2.5 text-xs font-bold transition border-b-2 whitespace-nowrap',
              activeDrawerTab === t.key
                ? 'border-indigo-600 text-indigo-600 bg-white rounded-t-xl shadow-sm'
                : 'border-transparent text-slate-500 hover:text-slate-800'
            ]"
          >
            <component :is="t.icon" class="h-3.5 w-3.5" />
            <span>{{ t.label }}</span>
          </button>
        </div>

        <!-- Drawer Body Content -->
        <div class="flex-1 overflow-y-auto p-5">
          <div v-if="isLoadingDetail && !drawerFinding" class="flex h-48 items-center justify-center">
            <div class="text-center text-slate-400 text-xs font-medium">Memuat detail temuan...</div>
          </div>

          <div v-else-if="drawerFinding">
            <FindingInfoTab
              v-if="activeDrawerTab === 'info'"
              :finding="drawerFinding"
            />

            <FindingPicTab
              v-else-if="activeDrawerTab === 'pic'"
              :finding="drawerFinding"
              :employees="employees"
              :priorityOptions="priorityOptions"
              :slaDays="slaDays"
              @saved="refreshActiveFinding"
            />

            <FindingActionTab
              v-else-if="activeDrawerTab === 'action'"
              :finding="drawerFinding"
              @saved="refreshActiveFinding"
            />

            <FindingVerifyTab
              v-else-if="activeDrawerTab === 'verif'"
              :finding="drawerFinding"
              @saved="refreshActiveFinding"
            />

            <FindingHistoryTab
              v-else-if="activeDrawerTab === 'history'"
              :timeline="drawerFinding.timeline || []"
              :statusLabels="statusOptions"
            />
          </div>
        </div>
      </div>
    </div>

    <!-- ===== MODAL TAMBAH TEMUAN MANUAL ===== -->
    <div
      v-if="isModalCreateOpen"
      class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4 overflow-y-auto"
    >
      <div class="bg-white rounded-3xl max-w-2xl w-full p-6 shadow-2xl border border-slate-100 space-y-4 animate-in fade-in zoom-in-95 duration-200 my-8">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
          <div class="flex items-center gap-2">
            <div class="p-2 bg-indigo-50 text-indigo-600 rounded-xl">
              <Plus class="w-5 h-5" />
            </div>
            <div>
              <h3 class="font-black text-slate-800 text-sm">Tambah Temuan Manual (Lapangan)</h3>
              <p class="text-[11px] text-slate-400">Pencatatan langsung ketidaksesuaian standar Good Mining Practice</p>
            </div>
          </div>
          <button @click="isModalCreateOpen = false" class="text-slate-400 hover:text-slate-600 p-1">
            <X class="w-5 h-5" />
          </button>
        </div>

        <form @submit.prevent="submitCreateFinding" class="space-y-4">
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
            <div>
              <label class="block font-bold text-slate-700 mb-1">Tahun</label>
              <input
                type="text"
                v-model="createForm.tahun"
                readonly
                class="w-full p-2.5 bg-slate-100 border-0 rounded-xl text-slate-600 font-semibold"
              />
            </div>

            <div>
              <label class="block font-bold text-slate-700 mb-1">Week <span class="text-rose-500">*</span></label>
              <input
                type="text"
                v-model="createForm.week"
                required
                placeholder="Contoh: Week 25"
                class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500"
              />
            </div>

            <div>
              <label class="block font-bold text-slate-700 mb-1">Area <span class="text-rose-500">*</span></label>
              <select
                v-model="createForm.area_id"
                required
                class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500"
              >
                <option v-for="a in areaOptions" :key="a" :value="a">{{ a }}</option>
              </select>
            </div>

            <div>
              <label class="block font-bold text-slate-700 mb-1">Lokasi Spesifik <span class="text-rose-500">*</span></label>
              <input
                type="text"
                v-model="createForm.location_id"
                required
                placeholder="Nama lokasi / patok / pit..."
                class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500"
              />
            </div>

            <div>
              <label class="block font-bold text-slate-700 mb-1">Aspek GMP</label>
              <select
                v-model="createForm.aspect_id"
                class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500"
              >
                <option v-for="asp in FINDING_ASPECTS" :key="asp" :value="asp">{{ asp }}</option>
              </select>
            </div>

            <div>
              <label class="block font-bold text-slate-700 mb-1">Item Checklist / Standar (Opsional)</label>
              <input
                type="text"
                v-model="createForm.checklist_id"
                placeholder="Kriteria standar..."
                class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500"
              />
            </div>

            <div class="sm:col-span-2">
              <label class="block font-bold text-slate-700 mb-1">Deskripsi Temuan Aktual <span class="text-rose-500">*</span></label>
              <textarea
                v-model="createForm.finding_description"
                required
                rows="2"
                placeholder="Jelaskan ketidaksesuaian yang ditemukan di lapangan..."
                class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500 text-rose-700 font-medium"
              ></textarea>
              <p v-if="createForm.errors.finding_description" class="text-rose-600 text-[11px] mt-0.5">
                {{ createForm.errors.finding_description }}
              </p>
            </div>

            <div class="sm:col-span-2">
              <label class="block font-bold text-slate-700 mb-1">Foto Evidence (Sebelum) <span class="text-rose-500">*</span></label>
              <input
                type="file"
                accept="image/*"
                required
                @change="handleFileBefore"
                class="w-full p-2 bg-slate-50 border border-slate-200 rounded-xl file:mr-3 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100"
              />
              <p v-if="createForm.errors.foto_sebelum" class="text-rose-600 text-[11px] mt-0.5">
                {{ createForm.errors.foto_sebelum }}
              </p>
            </div>

            <div class="sm:col-span-2 pt-2 border-t border-slate-100">
              <h4 class="font-extrabold text-xs text-indigo-700 uppercase tracking-wider mb-2">Penugasan PIC &amp; SLA</h4>
            </div>

            <div>
              <label class="block font-bold text-slate-700 mb-1">PIC Penanggung Jawab <span class="text-rose-500">*</span></label>
              <PicCombobox
                v-model="createForm.pic_user_id"
                :employees="employees"
                @selected="newPicJabatan = $event.jabatan ?? ''"
              />
              <p v-if="createForm.errors.pic_user_id" class="text-rose-600 text-[11px] mt-0.5">
                {{ createForm.errors.pic_user_id }}
              </p>
            </div>

            <div>
              <label class="block font-bold text-slate-700 mb-1">Unit / Departemen</label>
              <input
                type="text"
                :value="newPicJabatan"
                readonly
                placeholder="Otomatis terisi..."
                class="w-full p-2.5 bg-slate-100 border-0 rounded-xl text-slate-600 font-semibold"
              />
            </div>

            <div>
              <label class="block font-bold text-slate-700 mb-1">Prioritas Penanganan <span class="text-rose-500">*</span></label>
              <select
                v-model="createForm.priority"
                required
                @change="handlePriorityChangeCreate"
                class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500"
              >
                <option v-for="p in priorityOptions" :key="p" :value="p">
                  {{ p }}{{ slaDays[p] !== undefined ? ` (${slaDays[p]} Hari)` : '' }}
                </option>
              </select>
            </div>

            <div>
              <label class="block font-bold text-slate-700 mb-1">Target Penyelesaian (Deadline) <span class="text-rose-500">*</span></label>
              <input
                type="date"
                v-model="createForm.due_date"
                required
                class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-xl font-bold text-rose-600 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500"
              />
            </div>

            <div class="sm:col-span-2">
              <label class="block font-bold text-slate-700 mb-1">Rekomendasi Awal (Tindakan Perbaikan)</label>
              <textarea
                v-model="createForm.recommendation"
                rows="2"
                placeholder="Saran atau arahan perbaikan awal..."
                class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500"
              ></textarea>
            </div>
          </div>

          <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
            <button
              type="button"
              @click="isModalCreateOpen = false"
              class="px-4 py-2 text-xs font-bold text-slate-600 hover:bg-slate-100 rounded-xl transition"
            >
              Batal
            </button>
            <button
              type="submit"
              :disabled="createForm.processing"
              class="px-5 py-2 text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-700 rounded-xl shadow-sm transition disabled:opacity-60 flex items-center gap-1.5"
            >
              <Send class="w-3.5 h-3.5" />
              <span>Simpan &amp; Buka Temuan</span>
            </button>
          </div>
        </form>
      </div>
    </div>
  </AppLayout>
</template>
