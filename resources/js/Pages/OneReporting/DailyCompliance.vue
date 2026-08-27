<script setup>
import { ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { 
  ClipboardCheck, 
  Calendar, 
  Filter, 
  Plus, 
  Trash2, 
  CheckCircle2, 
  Circle, 
  Clock, 
  AlertCircle,
  X,
  PlusCircle
} from 'lucide-vue-next';

const props = defineProps({
  user: Object,
  notifCounts: Object,
  menu: Array,
  filters: Object,
  assignments: Array,
  allAreas: Array,
  allUnits: Array,
  allAktivitas: Array,
});

const filterForm = ref({
  tanggal: props.filters?.tanggal || new Date().toISOString().split('T')[0],
  shift: props.filters?.shift || '',
  area: props.filters?.area || '',
  status: props.filters?.status || '',
});

const applyFilter = () => {
  router.get('/one-reporting/daily', filterForm.value, { preserveState: true, replace: true });
};

const resetFilter = () => {
  filterForm.value = {
    tanggal: new Date().toISOString().split('T')[0],
    shift: '',
    area: '',
    status: '',
  };
  applyFilter();
};

// Task Toggle via Inertia router.post
const toggleTask = (item, taskIndex, currentValue) => {
  const newValue = !currentValue;
  router.post('/one-reporting/daily/task-toggle', {
    id: item.id,
    task_index: taskIndex,
    value: newValue,
  }, {
    preserveScroll: true,
    preserveState: true,
  });
};

// Modal Tambah Assignment
const showAddModal = ref(false);
const newAssignment = ref({
  tanggal: props.filters?.tanggal || new Date().toISOString().split('T')[0],
  shift: 'Shift 1',
  area: '',
  unit: '',
  pengawas: '',
  tasks: [''],
  keterangan: '',
});

const addTaskField = () => {
  newAssignment.value.tasks.push('');
};

const removeTaskField = (idx) => {
  if (newAssignment.value.tasks.length > 1) {
    newAssignment.value.tasks.splice(idx, 1);
  }
};

const submitAddAssignment = () => {
  router.post('/one-reporting/daily/store', newAssignment.value, {
    onSuccess: () => {
      showAddModal.value = false;
      newAssignment.value = {
        tanggal: props.filters?.tanggal || new Date().toISOString().split('T')[0],
        shift: 'Shift 1',
        area: '',
        unit: '',
        pengawas: '',
        tasks: [''],
        keterangan: '',
      };
    }
  });
};

const deleteAssignment = (id) => {
  if (confirm('Hapus assignment ini?')) {
    router.delete(`/one-reporting/daily/delete/${id}`, { preserveScroll: true });
  }
};
</script>

<template>
  <Head title="Daily Compliance - One-Reporting" />

  <AppLayout :user="user" :notif-counts="notifCounts" :menu="menu">
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm">
        <div>
          <div class="flex items-center gap-2 text-xs font-semibold text-sky-600 mb-1">
            <span>Operation Compliance Report</span>
            <span>/</span>
            <span class="text-slate-500">Daily Compliance</span>
          </div>
          <h1 class="text-xl font-bold text-slate-800 tracking-tight flex items-center gap-2">
            <ClipboardCheck class="w-6 h-6 text-sky-500" />
            Daily Compliance Checklist
          </h1>
          <p class="text-xs text-slate-500 mt-0.5">Checklist tugas harian pengawas per area &amp; update real-time</p>
        </div>

        <button @click="showAddModal = true" class="px-4 py-2 text-xs font-semibold rounded-xl bg-sky-600 hover:bg-sky-700 text-white shadow-sm transition flex items-center gap-2">
          <Plus class="w-4 h-4" />
          Tambah Assignment
        </button>
      </div>

      <!-- Filter Bar -->
      <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm">
        <form @submit.prevent="applyFilter" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-3 items-end">
          <div>
            <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1">Tanggal</label>
            <input type="date" v-model="filterForm.tanggal" class="w-full px-3 py-2 text-xs font-medium border border-slate-300 rounded-xl focus:ring-2 focus:ring-sky-500 bg-slate-50/50" />
          </div>

          <div>
            <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1">Shift</label>
            <select v-model="filterForm.shift" class="w-full px-3 py-2 text-xs font-medium border border-slate-300 rounded-xl focus:ring-2 focus:ring-sky-500 bg-slate-50/50">
              <option value="">Semua Shift</option>
              <option value="Shift 1">Shift 1</option>
              <option value="Shift 2">Shift 2</option>
            </select>
          </div>

          <div>
            <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1">Area</label>
            <select v-model="filterForm.area" class="w-full px-3 py-2 text-xs font-medium border border-slate-300 rounded-xl focus:ring-2 focus:ring-sky-500 bg-slate-50/50">
              <option value="">Semua Area</option>
              <option v-for="a in allAreas" :key="a" :value="a">{{ a }}</option>
            </select>
          </div>

          <div>
            <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1">Status</label>
            <select v-model="filterForm.status" class="w-full px-3 py-2 text-xs font-medium border border-slate-300 rounded-xl focus:ring-2 focus:ring-sky-500 bg-slate-50/50">
              <option value="">Semua Status</option>
              <option value="belum">Belum Dimulai</option>
              <option value="progress">Progress</option>
              <option value="selesai">Complete</option>
            </select>
          </div>

          <div class="flex items-center gap-2">
            <button type="submit" class="flex-1 py-2 px-4 bg-sky-600 hover:bg-sky-700 text-white font-semibold text-xs rounded-xl shadow-sm transition flex items-center justify-center gap-1.5">
              <Filter class="w-3.5 h-3.5" />
              Filter
            </button>
            <button type="button" @click="resetFilter" class="py-2 px-3 bg-slate-100 hover:bg-slate-200 text-slate-600 font-semibold text-xs rounded-xl transition">
              Reset
            </button>
          </div>
        </form>
      </div>

      <!-- Main Assignments Table -->
      <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="p-4 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
          <span class="text-xs font-bold text-slate-700">
            Daftar Assignment ({{ assignments.length }} Data)
          </span>
          <span class="text-[11px] text-slate-500 font-medium">Klik icon lingkaran untuk mencentang task harian</span>
        </div>

        <div class="overflow-x-auto">
          <table class="w-full text-left text-xs">
            <thead class="bg-slate-50 text-slate-500 uppercase tracking-wider text-[10px] font-bold border-b border-slate-100">
              <tr>
                <th class="px-4 py-3.5 w-12 text-center">#</th>
                <th class="px-4 py-3.5">Area &amp; Unit</th>
                <th class="px-4 py-3.5">Pengawas</th>
                <th class="px-4 py-3.5">Aktivitas &amp; Task Checklist</th>
                <th class="px-4 py-3.5 text-center">Compliance</th>
                <th class="px-4 py-3.5 text-center">Status</th>
                <th class="px-4 py-3.5 text-right w-16">Aksi</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-slate-700">
              <tr v-for="(item, idx) in assignments" :key="item.id" class="hover:bg-slate-50/60 transition">
                <td class="px-4 py-4 text-center font-bold text-slate-400">{{ idx + 1 }}</td>
                <td class="px-4 py-4">
                  <div class="font-bold text-slate-800">{{ item.area }}</div>
                  <div class="text-[11px] text-slate-500">{{ item.unit }} • {{ item.shift }}</div>
                </td>
                <td class="px-4 py-4 font-semibold text-slate-800">{{ item.pengawas }}</td>
                <td class="px-4 py-4">
                  <div class="flex flex-wrap gap-3">
                    <button 
                      v-for="(task, tIdx) in item.tasks" 
                      :key="tIdx"
                      @click="toggleTask(item, tIdx, item.task_done[tIdx])"
                      class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl border text-[11px] font-medium transition text-left"
                      :class="item.task_done[tIdx] ? 'bg-emerald-50 text-emerald-800 border-emerald-200' : 'bg-slate-50 text-slate-600 border-slate-200 hover:bg-slate-100'"
                    >
                      <CheckCircle2 v-if="item.task_done[tIdx]" class="w-4 h-4 text-emerald-600 shrink-0" />
                      <Circle v-else class="w-4 h-4 text-slate-400 shrink-0" />
                      <span>{{ task }}</span>
                    </button>
                  </div>
                </td>
                <td class="px-4 py-4 text-center">
                  <div class="font-black text-sm" :class="item.pct === 100 ? 'text-emerald-600' : item.pct > 0 ? 'text-amber-600' : 'text-rose-600'">
                    {{ item.pct }}%
                  </div>
                  <div class="w-16 h-1.5 bg-slate-100 rounded-full mx-auto mt-1 overflow-hidden">
                    <div 
                      class="h-full rounded-full transition-all duration-300"
                      :class="item.pct === 100 ? 'bg-emerald-500' : item.pct > 0 ? 'bg-amber-500' : 'bg-rose-500'"
                      :style="{ width: `${item.pct}%` }"
                    ></div>
                  </div>
                  <div class="text-[10px] text-slate-400 mt-0.5">{{ item.doneCount }}/{{ item.totalTasks }} selesai</div>
                </td>
                <td class="px-4 py-4 text-center">
                  <span 
                    class="px-3 py-1 text-[11px] font-extrabold rounded-full"
                    :class="{
                      'bg-emerald-100 text-emerald-800': item.status === 'selesai',
                      'bg-amber-100 text-amber-800': item.status === 'progress',
                      'bg-rose-100 text-rose-800': item.status === 'belum'
                    }"
                  >
                    {{ item.status === 'selesai' ? 'Complete' : item.status === 'progress' ? 'Progress' : 'Belum Dimulai' }}
                  </span>
                </td>
                <td class="px-4 py-4 text-right">
                  <button @click="deleteAssignment(item.id)" class="p-1.5 text-slate-400 hover:text-rose-600 rounded-lg hover:bg-rose-50 transition">
                    <Trash2 class="w-4 h-4" />
                  </button>
                </td>
              </tr>
              <tr v-if="!assignments || assignments.length === 0">
                <td colspan="7" class="px-4 py-12 text-center text-slate-400 text-xs">
                  Tidak ada data assignment untuk filter yang dipilih
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Modal Tambah Assignment -->
    <div v-if="showAddModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-xs">
      <div class="bg-white w-full max-w-lg rounded-2xl shadow-xl border border-slate-100 overflow-hidden animate-in fade-in zoom-in duration-200">
        <div class="p-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
          <h3 class="text-sm font-bold text-slate-800 flex items-center gap-2">
            <PlusCircle class="w-4 h-4 text-sky-500" />
            Tambah Daily Assignment
          </h3>
          <button @click="showAddModal = false" class="p-1 text-slate-400 hover:text-slate-600 rounded-lg">
            <X class="w-5 h-5" />
          </button>
        </div>

        <form @submit.prevent="submitAddAssignment" class="p-5 space-y-4 text-xs">
          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block font-bold text-slate-700 mb-1">Tanggal *</label>
              <input type="date" v-model="newAssignment.tanggal" required class="w-full px-3 py-2 border border-slate-300 rounded-xl bg-slate-50/50" />
            </div>
            <div>
              <label class="block font-bold text-slate-700 mb-1">Shift *</label>
              <select v-model="newAssignment.shift" required class="w-full px-3 py-2 border border-slate-300 rounded-xl bg-slate-50/50">
                <option value="Shift 1">Shift 1</option>
                <option value="Shift 2">Shift 2</option>
              </select>
            </div>
          </div>

          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block font-bold text-slate-700 mb-1">Area *</label>
              <input type="text" v-model="newAssignment.area" placeholder="MFOB, Dewatering..." required class="w-full px-3 py-2 border border-slate-300 rounded-xl bg-slate-50/50" />
            </div>
            <div>
              <label class="block font-bold text-slate-700 mb-1">Unit/Lokasi *</label>
              <input type="text" v-model="newAssignment.unit" placeholder="EX-201, Disposal A..." required class="w-full px-3 py-2 border border-slate-300 rounded-xl bg-slate-50/50" />
            </div>
          </div>

          <div>
            <label class="block font-bold text-slate-700 mb-1">Nama Pengawas *</label>
            <input type="text" v-model="newAssignment.pengawas" placeholder="Nama Supervisor..." required class="w-full px-3 py-2 border border-slate-300 rounded-xl bg-slate-50/50" />
          </div>

          <div>
            <div class="flex items-center justify-between mb-1">
              <label class="font-bold text-slate-700">Daftar Aktivitas / Task Checklist *</label>
              <button type="button" @click="addTaskField" class="text-sky-600 font-bold hover:underline">
                + Tambah Task
              </button>
            </div>
            <div class="space-y-2 max-h-40 overflow-y-auto pr-1">
              <div v-for="(t, idx) in newAssignment.tasks" :key="idx" class="flex items-center gap-2">
                <input type="text" v-model="newAssignment.tasks[idx]" placeholder="Detail aktivitas..." required class="flex-1 px-3 py-2 border border-slate-300 rounded-xl bg-slate-50/50" />
                <button type="button" @click="removeTaskField(idx)" class="p-2 text-rose-500 hover:bg-rose-50 rounded-lg">
                  <X class="w-4 h-4" />
                </button>
              </div>
            </div>
          </div>

          <div>
            <label class="block font-bold text-slate-700 mb-1">Keterangan / Catatan</label>
            <textarea v-model="newAssignment.keterangan" rows="2" class="w-full px-3 py-2 border border-slate-300 rounded-xl bg-slate-50/50" placeholder="Catatan tambahan..."></textarea>
          </div>

          <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
            <button type="button" @click="showAddModal = false" class="px-4 py-2 font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl transition">
              Batal
            </button>
            <button type="submit" class="px-5 py-2 font-semibold text-white bg-sky-600 hover:bg-sky-700 rounded-xl shadow-sm transition">
              Simpan Assignment
            </button>
          </div>
        </form>
      </div>
    </div>
  </AppLayout>
</template>
