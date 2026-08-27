<script setup>
import { ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { 
  Sliders, 
  Users, 
  Truck, 
  Database, 
  Calendar, 
  Link, 
  RefreshCw, 
  Plus, 
  Upload, 
  Trash2, 
  Pencil, 
  AlertTriangle, 
  CheckCircle2,
  X,
  FileSpreadsheet
} from 'lucide-vue-next';

const props = defineProps({
  user: Object,
  notifCounts: Object,
  menu: Array,
  activeTab: String,
  filters: Object,
  gsheetManpower: Object,
  gsheetFleet: Object,
  rosterData: Array,
  conflicts: Object,
  fleetUnits: Array,
});

const currentSubTab = ref(props.activeTab || 'manpower');
const filterDate = ref(props.filters?.date || new Date().toISOString().split('T')[0]);
const filterShift = ref(props.filters?.shift || 'D');

const switchSubTab = (tabName) => {
  currentSubTab.value = tabName;
  router.get('/one-setting/settingan', { sub: tabName, date: filterDate.value, shift: filterShift.value }, { preserveState: true, replace: true });
};

const applyFilter = () => {
  router.get('/one-setting/settingan', { sub: currentSubTab.value, date: filterDate.value, shift: filterShift.value }, { preserveState: true, replace: true });
};

// Manpower GSheet Sync Form
const gsheetManpowerForm = ref({
  date: filterDate.value,
  url_day: props.gsheetManpower?.day || '',
  url_night: props.gsheetManpower?.night || '',
});

const submitSyncManpower = () => {
  router.post('/one-setting/manpower/sync-gsheet', gsheetManpowerForm.value, { preserveScroll: true });
};

// Manual Roster Entry Form
const manualRosterForm = ref({
  tanggal: filterDate.value,
  shift: filterShift.value,
  nrp: '',
  unit: '',
  csa_type: 'CSA GENERAL',
});

const submitManualRoster = () => {
  router.post('/one-setting/roster/manual', manualRosterForm.value, {
    preserveScroll: true,
    onSuccess: () => {
      manualRosterForm.value.nrp = '';
      manualRosterForm.value.unit = '';
    }
  });
};

// Edit Roster Modal
const showEditRosterModal = ref(false);
const editingRosterItem = ref(null);
const editRosterForm = ref({ id: null, unit: '', csa_type: '' });

const openEditRoster = (item) => {
  editingRosterItem.value = item;
  editRosterForm.value = { id: item.id, unit: item.unit, csa_type: item.csa_type || 'CSA GENERAL' };
  showEditRosterModal.value = true;
};

const submitEditRoster = () => {
  router.post(`/one-setting/roster/update/${editRosterForm.value.id}`, editRosterForm.value, {
    preserveScroll: true,
    onSuccess: () => showEditRosterModal.value = false
  });
};

const deleteRosterItem = (id) => {
  if (confirm('Hapus data roster ini?')) {
    router.delete(`/one-setting/roster/delete/${id}`, { preserveScroll: true });
  }
};

const clearRosterData = () => {
  if (confirm(`Clear semua data roster tanggal ${filterDate.value} shift ${filterShift.value}?`)) {
    router.post('/one-setting/roster/clear', { date: filterDate.value, shift: filterShift.value }, { preserveScroll: true });
  }
};

// Fleet GSheet Form
const gsheetFleetForm = ref({
  date: filterDate.value,
  url_day: props.gsheetFleet?.day || '',
  url_night: props.gsheetFleet?.night || '',
});

const submitImportFleet = () => {
  router.post('/one-setting/fleet/import', gsheetFleetForm.value, { preserveScroll: true });
};

// Master Unit Fleet Modals
const showUnitModal = ref(false);
const editingFleetUnit = ref(null);
const unitFleetForm = ref({ id: null, name: '', category_type: 'nonStaff', jumlah_unit: 0 });

const openUnitAdd = () => {
  editingFleetUnit.value = null;
  unitFleetForm.value = { id: null, name: '', category_type: 'nonStaff', jumlah_unit: 0 };
  showUnitModal.value = true;
};

const openUnitEdit = (u) => {
  editingFleetUnit.value = u;
  unitFleetForm.value = { id: u.id, name: u.name, category_type: u.category_type, jumlah_unit: u.jumlah_unit || 0 };
  showUnitModal.value = true;
};

const submitUnitFleet = () => {
  router.post('/one-setting/unit-fleet/store', unitFleetForm.value, {
    preserveScroll: true,
    onSuccess: () => showUnitModal.value = false
  });
};

const deleteUnitFleet = (id) => {
  if (confirm('Hapus master unit fleet ini?')) {
    router.delete(`/one-setting/unit-fleet/delete/${id}`, { preserveScroll: true });
  }
};
</script>

<template>
  <Head title="Settingan - One-Setting" />

  <AppLayout :user="user" :notif-counts="notifCounts" :menu="menu">
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm">
        <div>
          <div class="flex items-center gap-2 text-xs font-semibold text-sky-600 mb-1">
            <span>Operation Settings</span>
            <span>/</span>
            <span class="text-slate-500">Settingan</span>
          </div>
          <h1 class="text-xl font-bold text-slate-800 tracking-tight flex items-center gap-2">
            <Sliders class="w-6 h-6 text-sky-500" />
            Konfigurasi &amp; Setting Alokasi
          </h1>
          <p class="text-xs text-slate-500 mt-0.5">Kelola input alokasi manpower, fleet Google Sheets, dan data roster</p>
        </div>
      </div>

      <!-- Navigation Sub-Tabs -->
      <div class="flex items-center gap-2 border-b border-slate-200 pb-2">
        <button 
          @click="switchSubTab('manpower')"
          class="px-4 py-2 text-xs font-bold rounded-xl transition flex items-center gap-2"
          :class="currentSubTab === 'manpower' ? 'bg-sky-600 text-white shadow-sm' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200'"
        >
          <Users class="w-4 h-4" />
          Input Setting Manpower
        </button>

        <button 
          @click="switchSubTab('fleet')"
          class="px-4 py-2 text-xs font-bold rounded-xl transition flex items-center gap-2"
          :class="currentSubTab === 'fleet' ? 'bg-sky-600 text-white shadow-sm' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200'"
        >
          <Truck class="w-4 h-4" />
          Input Setting Fleet
        </button>

        <button 
          @click="switchSubTab('roster')"
          class="px-4 py-2 text-xs font-bold rounded-xl transition flex items-center gap-2"
          :class="currentSubTab === 'roster' ? 'bg-sky-600 text-white shadow-sm' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200'"
        >
          <Database class="w-4 h-4" />
          Data Alokasi Manpower ({{ rosterData?.length || 0 }})
        </button>
      </div>

      <!-- SUB-TAB 1: INPUT SETTING MANPOWER -->
      <div v-if="currentSubTab === 'manpower'" class="space-y-6">
        <!-- Google Sheet Sync Box -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm space-y-4">
          <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <div>
              <h2 class="text-sm font-bold text-slate-800 flex items-center gap-2">
                <Link class="w-4 h-4 text-sky-500" />
                Import &amp; Sinkronisasi Google Sheets Manpower
              </h2>
              <p class="text-xs text-slate-500">Masukkan link publik Google Spreadsheet untuk penarikan data otomatis.</p>
            </div>
          </div>

          <form @submit.prevent="submitSyncManpower" class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
            <div>
              <label class="block font-bold text-slate-700 mb-1">Link Spreadsheet Siang (Shift 1)</label>
              <input type="url" v-model="gsheetManpowerForm.url_day" placeholder="https://docs.google.com/spreadsheets/d/..." class="w-full px-3 py-2 border border-slate-300 rounded-xl bg-slate-50/50" />
            </div>

            <div>
              <label class="block font-bold text-slate-700 mb-1">Link Spreadsheet Malam (Shift 2)</label>
              <input type="url" v-model="gsheetManpowerForm.url_night" placeholder="https://docs.google.com/spreadsheets/d/..." class="w-full px-3 py-2 border border-slate-300 rounded-xl bg-slate-50/50" />
            </div>

            <div class="md:col-span-2 flex items-center justify-end gap-2 pt-2">
              <button type="submit" class="px-5 py-2 text-xs font-semibold rounded-xl bg-sky-600 hover:bg-sky-700 text-white shadow-sm transition flex items-center gap-1.5">
                <RefreshCw class="w-3.5 h-3.5" />
                Simpan &amp; Sinkron Google Sheets
              </button>
            </div>
          </form>
        </div>

        <!-- Manual Input Form -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm space-y-4">
          <h2 class="text-sm font-bold text-slate-800 flex items-center gap-2 border-b border-slate-100 pb-3">
            <Plus class="w-4 h-4 text-emerald-500" />
            Input Manual Alokasi Operator &amp; Unit
          </h2>

          <form @submit.prevent="submitManualRoster" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-3 text-xs items-end">
            <div>
              <label class="block font-bold text-slate-700 mb-1">NRP Operator *</label>
              <input type="text" v-model="manualRosterForm.nrp" placeholder="Contoh: 20045..." required class="w-full px-3 py-2 border border-slate-300 rounded-xl bg-slate-50/50" />
            </div>

            <div>
              <label class="block font-bold text-slate-700 mb-1">Kode Unit *</label>
              <input type="text" v-model="manualRosterForm.unit" placeholder="Contoh: HD785-012..." required class="w-full px-3 py-2 border border-slate-300 rounded-xl bg-slate-50/50" />
            </div>

            <div>
              <label class="block font-bold text-slate-700 mb-1">Tanggal *</label>
              <input type="date" v-model="manualRosterForm.tanggal" required class="w-full px-3 py-2 border border-slate-300 rounded-xl bg-slate-50/50" />
            </div>

            <div>
              <label class="block font-bold text-slate-700 mb-1">Shift *</label>
              <select v-model="manualRosterForm.shift" required class="w-full px-3 py-2 border border-slate-300 rounded-xl bg-slate-50/50">
                <option value="D">Shift 1 (Siang)</option>
                <option value="N">Shift 2 (Malam)</option>
              </select>
            </div>

            <div>
              <button type="submit" class="w-full py-2 px-4 bg-slate-900 hover:bg-slate-800 text-white font-semibold text-xs rounded-xl shadow-sm transition flex items-center justify-center gap-1.5">
                <Plus class="w-3.5 h-3.5" /> Simpan Manual
              </button>
            </div>
          </form>
        </div>
      </div>

      <!-- SUB-TAB 2: INPUT SETTING FLEET -->
      <div v-if="currentSubTab === 'fleet'" class="space-y-6">
        <!-- Fleet GSheet Box -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm space-y-4">
          <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <div>
              <h2 class="text-sm font-bold text-slate-800 flex items-center gap-2">
                <Link class="w-4 h-4 text-indigo-500" />
                Import Alokasi Fleet (Google Sheets)
              </h2>
              <p class="text-xs text-slate-500">Sinkronisasi matriks peralatan &amp; loader dari link spreadsheet.</p>
            </div>
          </div>

          <form @submit.prevent="submitImportFleet" class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
            <div>
              <label class="block font-bold text-slate-700 mb-1">Link Spreadsheet Siang (Shift 1)</label>
              <input type="url" v-model="gsheetFleetForm.url_day" placeholder="https://docs.google.com/spreadsheets/d/..." class="w-full px-3 py-2 border border-slate-300 rounded-xl bg-slate-50/50" />
            </div>

            <div>
              <label class="block font-bold text-slate-700 mb-1">Link Spreadsheet Malam (Shift 2)</label>
              <input type="url" v-model="gsheetFleetForm.url_night" placeholder="https://docs.google.com/spreadsheets/d/..." class="w-full px-3 py-2 border border-slate-300 rounded-xl bg-slate-50/50" />
            </div>

            <div class="md:col-span-2 flex items-center justify-end gap-2 pt-2">
              <button type="submit" class="px-5 py-2 text-xs font-semibold rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white shadow-sm transition flex items-center gap-1.5">
                <Upload class="w-3.5 h-3.5" /> Import Fleet Data
              </button>
            </div>
          </form>
        </div>

        <!-- Master Unit Fleet List -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
          <div class="p-4 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
            <div>
              <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider">Master Daftar Unit Fleet</h3>
              <p class="text-[11px] text-slate-500">Master unit alat berat staff &amp; non-staff</p>
            </div>

            <button @click="openUnitAdd" class="px-3.5 py-1.5 text-xs font-semibold rounded-xl bg-slate-900 hover:bg-slate-800 text-white transition flex items-center gap-1">
              <Plus class="w-4 h-4" /> Tambah Master Unit
            </button>
          </div>

          <table class="w-full text-left text-xs">
            <thead class="bg-slate-50 text-slate-500 uppercase tracking-wider text-[10px] font-bold border-b border-slate-100">
              <tr>
                <th class="px-4 py-3.5 w-12 text-center">#</th>
                <th class="px-4 py-3.5">Nama Unit</th>
                <th class="px-4 py-3.5">Kategori</th>
                <th class="px-4 py-3.5 text-center">Jumlah Unit</th>
                <th class="px-4 py-3.5 text-right w-24">Aksi</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-slate-700">
              <tr v-for="(u, idx) in fleetUnits" :key="u.id" class="hover:bg-slate-50/60 transition">
                <td class="px-4 py-3.5 text-center font-bold text-slate-400">{{ idx + 1 }}</td>
                <td class="px-4 py-3.5 font-bold text-sky-600">{{ u.name }}</td>
                <td class="px-4 py-3.5">
                  <span class="px-2.5 py-0.5 text-[10px] font-bold rounded-full" :class="u.category_type === 'staff' ? 'bg-sky-100 text-sky-800' : 'bg-emerald-100 text-emerald-800'">
                    {{ u.category_type }}
                  </span>
                </td>
                <td class="px-4 py-3.5 text-center font-bold text-slate-800">{{ u.jumlah_unit }}</td>
                <td class="px-4 py-3.5 text-right space-x-1">
                  <button @click="openUnitEdit(u)" class="p-1.5 text-slate-400 hover:text-sky-600 rounded-lg hover:bg-sky-50 transition">
                    <Pencil class="w-4 h-4" />
                  </button>
                  <button @click="deleteUnitFleet(u.id)" class="p-1.5 text-slate-400 hover:text-rose-600 rounded-lg hover:bg-rose-50 transition">
                    <Trash2 class="w-4 h-4" />
                  </button>
                </td>
              </tr>
              <tr v-if="!fleetUnits || fleetUnits.length === 0">
                <td colspan="5" class="px-4 py-10 text-center text-slate-400 text-xs">Belum ada master unit fleet</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- SUB-TAB 3: DATA ALOKASI MANPOWER -->
      <div v-if="currentSubTab === 'roster'" class="space-y-4">
        <!-- Filter Bar -->
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
          <form @submit.prevent="applyFilter" class="flex flex-wrap items-center gap-3">
            <div>
              <label class="block text-[10px] font-bold text-slate-600 uppercase mb-1">Tanggal</label>
              <input type="date" v-model="filterDate" @change="applyFilter" class="px-3 py-1.5 text-xs font-medium border border-slate-300 rounded-xl bg-slate-50/50" />
            </div>

            <div>
              <label class="block text-[10px] font-bold text-slate-600 uppercase mb-1">Shift</label>
              <select v-model="filterShift" @change="applyFilter" class="px-3 py-1.5 text-xs font-medium border border-slate-300 rounded-xl bg-slate-50/50">
                <option value="D">Shift 1 (Siang)</option>
                <option value="N">Shift 2 (Malam)</option>
              </select>
            </div>
          </form>

          <div class="flex items-center gap-2">
            <button @click="clearRosterData" class="px-3.5 py-1.5 text-xs font-bold rounded-xl bg-rose-50 text-rose-700 hover:bg-rose-100 border border-rose-200 transition flex items-center gap-1.5">
              <Trash2 class="w-4 h-4" /> Clear Roster Tanggal Ini
            </button>
          </div>
        </div>

        <!-- Conflict Warnings Banner -->
        <div v-if="conflicts?.units?.length > 0 || conflicts?.nrps?.length > 0" class="p-4 rounded-2xl bg-amber-50 border border-amber-200 text-amber-900 text-xs space-y-1">
          <div class="font-bold flex items-center gap-2">
            <AlertTriangle class="w-4 h-4 text-amber-600" />
            Peringatan Bentrok Alokasi Roster
          </div>
          <div v-if="conflicts.units.length > 0">
            <span class="font-semibold">Bentrok Unit (terpakai > 1 kali):</span> {{ conflicts.units.join(', ') }}
          </div>
          <div v-if="conflicts.nrps.length > 0">
            <span class="font-semibold">Bentrok NRP (terjadwal > 1 unit):</span> {{ conflicts.nrps.join(', ') }}
          </div>
        </div>

        <!-- Roster Data Table -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
          <table class="w-full text-left text-xs">
            <thead class="bg-slate-50 text-slate-500 uppercase tracking-wider text-[10px] font-bold border-b border-slate-100">
              <tr>
                <th class="px-4 py-3.5 w-12 text-center">#</th>
                <th class="px-4 py-3.5">Unit</th>
                <th class="px-4 py-3.5">NRP</th>
                <th class="px-4 py-3.5">Nama Operator</th>
                <th class="px-4 py-3.5">No Telepon</th>
                <th class="px-4 py-3.5">Tipe CSA</th>
                <th class="px-4 py-3.5 text-right w-24">Aksi</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-slate-700">
              <tr 
                v-for="(row, idx) in rosterData" 
                :key="row.id"
                class="hover:bg-slate-50/60 transition"
                :class="{
                  'bg-rose-50/60': conflicts?.units?.includes(row.unit) || conflicts?.nrps?.includes(row.nrp)
                }"
              >
                <td class="px-4 py-3.5 text-center font-bold text-slate-400">{{ idx + 1 }}</td>
                <td class="px-4 py-3.5 font-extrabold text-sky-600">
                  {{ row.unit }}
                  <span v-if="conflicts?.units?.includes(row.unit)" class="ml-1 text-[9px] bg-rose-600 text-white font-black px-1.5 py-0.5 rounded">BENTROK UNIT</span>
                </td>
                <td class="px-4 py-3.5 font-bold text-slate-800">
                  {{ row.nrp }}
                  <span v-if="conflicts?.nrps?.includes(row.nrp)" class="ml-1 text-[9px] bg-rose-600 text-white font-black px-1.5 py-0.5 rounded">BENTROK NRP</span>
                </td>
                <td class="px-4 py-3.5 font-medium text-slate-800">{{ row.employee_name }}</td>
                <td class="px-4 py-3.5 text-slate-500">{{ row.phone }}</td>
                <td class="px-4 py-3.5">
                  <span class="px-2 py-0.5 text-[10px] font-semibold rounded-md bg-purple-50 text-purple-700 border border-purple-200">
                    {{ row.csa_type || 'CSA GENERAL' }}
                  </span>
                </td>
                <td class="px-4 py-3.5 text-right space-x-1">
                  <button @click="openEditRoster(row)" class="p-1.5 text-slate-400 hover:text-sky-600 rounded-lg hover:bg-sky-50 transition">
                    <Pencil class="w-4 h-4" />
                  </button>
                  <button @click="deleteRosterItem(row.id)" class="p-1.5 text-slate-400 hover:text-rose-600 rounded-lg hover:bg-rose-50 transition">
                    <Trash2 class="w-4 h-4" />
                  </button>
                </td>
              </tr>
              <tr v-if="!rosterData || rosterData.length === 0">
                <td colspan="7" class="px-4 py-12 text-center text-slate-400 text-xs">
                  Tidak ada data alokasi roster untuk tanggal &amp; shift ini
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- EDIT ROSTER MODAL -->
    <div v-if="showEditRosterModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-xs">
      <div class="bg-white w-full max-w-md rounded-2xl shadow-xl border border-slate-100 p-5 space-y-4 text-xs">
        <h3 class="font-bold text-slate-800 text-sm">Edit Alokasi Roster</h3>
        <form @submit.prevent="submitEditRoster" class="space-y-3">
          <div>
            <label class="block font-bold text-slate-700 mb-1">Kode Unit *</label>
            <input type="text" v-model="editRosterForm.unit" required class="w-full px-3 py-2 border border-slate-300 rounded-xl bg-slate-50/50" />
          </div>

          <div>
            <label class="block font-bold text-slate-700 mb-1">Tipe CSA</label>
            <input type="text" v-model="editRosterForm.csa_type" placeholder="CSA GIBSON, CSA MONTE BARU..." class="w-full px-3 py-2 border border-slate-300 rounded-xl bg-slate-50/50" />
          </div>

          <div class="flex justify-end gap-2 pt-2 border-t border-slate-100">
            <button type="button" @click="showEditRosterModal = false" class="px-4 py-2 font-semibold bg-slate-100 rounded-xl">Batal</button>
            <button type="submit" class="px-4 py-2 font-semibold text-white bg-sky-600 rounded-xl">Simpan Changes</button>
          </div>
        </form>
      </div>
    </div>

    <!-- MASTER FLEET UNIT MODAL -->
    <div v-if="showUnitModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-xs">
      <div class="bg-white w-full max-w-md rounded-2xl shadow-xl border border-slate-100 p-5 space-y-4 text-xs">
        <h3 class="font-bold text-slate-800 text-sm">{{ editingFleetUnit ? 'Edit Master Unit Fleet' : 'Tambah Master Unit Fleet' }}</h3>
        <form @submit.prevent="submitUnitFleet" class="space-y-3">
          <div>
            <label class="block font-bold text-slate-700 mb-1">Nama Unit *</label>
            <input type="text" v-model="unitFleetForm.name" placeholder="Contoh: HD785-7..." required class="w-full px-3 py-2 border border-slate-300 rounded-xl bg-slate-50/50" />
          </div>

          <div>
            <label class="block font-bold text-slate-700 mb-1">Kategori *</label>
            <select v-model="unitFleetForm.category_type" required class="w-full px-3 py-2 border border-slate-300 rounded-xl bg-slate-50/50">
              <option value="nonStaff">Non-Staff (Operator / Unit)</option>
              <option value="staff">Staff (Pengawas)</option>
            </select>
          </div>

          <div>
            <label class="block font-bold text-slate-700 mb-1">Jumlah Unit</label>
            <input type="number" v-model="unitFleetForm.jumlah_unit" min="0" class="w-full px-3 py-2 border border-slate-300 rounded-xl bg-slate-50/50" />
          </div>

          <div class="flex justify-end gap-2 pt-2 border-t border-slate-100">
            <button type="button" @click="showUnitModal = false" class="px-4 py-2 font-semibold bg-slate-100 rounded-xl">Batal</button>
            <button type="submit" class="px-4 py-2 font-semibold text-white bg-sky-600 rounded-xl">Simpan Unit</button>
          </div>
        </form>
      </div>
    </div>
  </AppLayout>
</template>
