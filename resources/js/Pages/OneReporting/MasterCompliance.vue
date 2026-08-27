<script setup>
import { ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { 
  Database, 
  MapPin, 
  Truck, 
  ListCheck, 
  Plus, 
  Pencil, 
  Trash2, 
  X,
  CheckCircle2,
  XCircle
} from 'lucide-vue-next';

const props = defineProps({
  user: Object,
  notifCounts: Object,
  menu: Array,
  tab: String,
  areas: Array,
  units: Array,
  aktivitas: Array,
});

const activeTab = ref(props.tab || 'area');

// Modals State
const showAreaModal = ref(false);
const editingArea = ref(null);
const areaForm = ref({ nama: '', keterangan: '', aktif: true });

const showUnitModal = ref(false);
const editingUnit = ref(null);
const unitForm = ref({ kode: '', nama: '', area: '', keterangan: '', aktif: true });

const showAktModal = ref(false);
const editingAkt = ref(null);
const aktForm = ref({ nama: '', kategori: '', keterangan: '', aktif: true });

// Area CRUD
const openAreaAdd = () => {
  editingArea.value = null;
  areaForm.value = { nama: '', keterangan: '', aktif: true };
  showAreaModal.value = true;
};
const openAreaEdit = (item) => {
  editingArea.value = item;
  areaForm.value = { nama: item.nama, keterangan: item.keterangan || '', aktif: Boolean(item.aktif) };
  showAreaModal.value = true;
};
const saveArea = () => {
  if (editingArea.value) {
    router.post(`/one-reporting/master/area/update/${editingArea.value.id}`, areaForm.value, {
      onSuccess: () => showAreaModal.value = false
    });
  } else {
    router.post('/one-reporting/master/area/store', areaForm.value, {
      onSuccess: () => showAreaModal.value = false
    });
  }
};
const deleteArea = (id) => {
  if (confirm('Hapus area ini?')) {
    router.delete(`/one-reporting/master/area/delete/${id}`);
  }
};

// Unit CRUD
const openUnitAdd = () => {
  editingUnit.value = null;
  unitForm.value = { kode: '', nama: '', area: '', keterangan: '', aktif: true };
  showUnitModal.value = true;
};
const openUnitEdit = (item) => {
  editingUnit.value = item;
  unitForm.value = { kode: item.kode || '', nama: item.nama, area: item.area || '', keterangan: item.keterangan || '', aktif: Boolean(item.aktif) };
  showUnitModal.value = true;
};
const saveUnit = () => {
  if (editingUnit.value) {
    router.post(`/one-reporting/master/unit/update/${editingUnit.value.id}`, unitForm.value, {
      onSuccess: () => showUnitModal.value = false
    });
  } else {
    router.post('/one-reporting/master/unit/store', unitForm.value, {
      onSuccess: () => showUnitModal.value = false
    });
  }
};
const deleteUnit = (id) => {
  if (confirm('Hapus unit ini?')) {
    router.delete(`/one-reporting/master/unit/delete/${id}`);
  }
};

// Aktivitas CRUD
const openAktAdd = () => {
  editingAkt.value = null;
  aktForm.value = { nama: '', kategori: '', keterangan: '', aktif: true };
  showAktModal.value = true;
};
const openAktEdit = (item) => {
  editingAkt.value = item;
  aktForm.value = { nama: item.nama, kategori: item.kategori || '', keterangan: item.keterangan || '', aktif: Boolean(item.aktif) };
  showAktModal.value = true;
};
const saveAkt = () => {
  if (editingAkt.value) {
    router.post(`/one-reporting/master/aktivitas/update/${editingAkt.value.id}`, aktForm.value, {
      onSuccess: () => showAktModal.value = false
    });
  } else {
    router.post('/one-reporting/master/aktivitas/store', aktForm.value, {
      onSuccess: () => showAktModal.value = false
    });
  }
};
const deleteAkt = (id) => {
  if (confirm('Hapus aktivitas ini?')) {
    router.delete(`/one-reporting/master/aktivitas/delete/${id}`);
  }
};
</script>

<template>
  <Head title="Master Compliance - One-Reporting" />

  <AppLayout :user="user" :notif-counts="notifCounts" :menu="menu">
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm">
        <div>
          <div class="flex items-center gap-2 text-xs font-semibold text-sky-600 mb-1">
            <span>Operation Compliance Report</span>
            <span>/</span>
            <span class="text-slate-500">Master Data</span>
          </div>
          <h1 class="text-xl font-bold text-slate-800 tracking-tight flex items-center gap-2">
            <Database class="w-6 h-6 text-sky-500" />
            Master Data Compliance
          </h1>
          <p class="text-xs text-slate-500 mt-0.5">Kelola data master area, unit/lokasi, dan daftar aktivitas compliance</p>
        </div>

        <div>
          <button v-if="activeTab === 'area'" @click="openAreaAdd" class="px-4 py-2 text-xs font-semibold rounded-xl bg-sky-600 hover:bg-sky-700 text-white shadow-sm transition flex items-center gap-1.5">
            <Plus class="w-4 h-4" /> Tambah Area
          </button>
          <button v-if="activeTab === 'unit'" @click="openUnitAdd" class="px-4 py-2 text-xs font-semibold rounded-xl bg-sky-600 hover:bg-sky-700 text-white shadow-sm transition flex items-center gap-1.5">
            <Plus class="w-4 h-4" /> Tambah Unit
          </button>
          <button v-if="activeTab === 'aktivitas'" @click="openAktAdd" class="px-4 py-2 text-xs font-semibold rounded-xl bg-sky-600 hover:bg-sky-700 text-white shadow-sm transition flex items-center gap-1.5">
            <Plus class="w-4 h-4" /> Tambah Aktivitas
          </button>
        </div>
      </div>

      <!-- Navigation Tabs -->
      <div class="flex items-center gap-2 border-b border-slate-200 pb-2">
        <button 
          @click="activeTab = 'area'" 
          class="px-4 py-2 text-xs font-bold rounded-xl transition flex items-center gap-2"
          :class="activeTab === 'area' ? 'bg-sky-600 text-white shadow-sm' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200'"
        >
          <MapPin class="w-4 h-4" /> Master Area ({{ areas.length }})
        </button>
        <button 
          @click="activeTab = 'unit'" 
          class="px-4 py-2 text-xs font-bold rounded-xl transition flex items-center gap-2"
          :class="activeTab === 'unit' ? 'bg-sky-600 text-white shadow-sm' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200'"
        >
          <Truck class="w-4 h-4" /> Master Unit/Lokasi ({{ units.length }})
        </button>
        <button 
          @click="activeTab = 'aktivitas'" 
          class="px-4 py-2 text-xs font-bold rounded-xl transition flex items-center gap-2"
          :class="activeTab === 'aktivitas' ? 'bg-sky-600 text-white shadow-sm' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200'"
        >
          <ListCheck class="w-4 h-4" /> Master Aktivitas ({{ aktivitas.length }})
        </button>
      </div>

      <!-- TAB 1: AREA -->
      <div v-if="activeTab === 'area'" class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <table class="w-full text-left text-xs">
          <thead class="bg-slate-50 text-slate-500 uppercase tracking-wider text-[10px] font-bold border-b border-slate-100">
            <tr>
              <th class="px-4 py-3.5 w-12 text-center">#</th>
              <th class="px-4 py-3.5">Nama Area</th>
              <th class="px-4 py-3.5">Keterangan</th>
              <th class="px-4 py-3.5 text-center">Status</th>
              <th class="px-4 py-3.5 text-right w-24">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 text-slate-700">
            <tr v-for="(item, idx) in areas" :key="item.id" class="hover:bg-slate-50/60 transition">
              <td class="px-4 py-3.5 text-center font-bold text-slate-400">{{ idx + 1 }}</td>
              <td class="px-4 py-3.5 font-bold text-slate-800">{{ item.nama }}</td>
              <td class="px-4 py-3.5 text-slate-500">{{ item.keterangan || '-' }}</td>
              <td class="px-4 py-3.5 text-center">
                <span class="px-2.5 py-0.5 text-[10px] font-bold rounded-full" :class="item.aktif ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800'">
                  {{ item.aktif ? 'Aktif' : 'Nonaktif' }}
                </span>
              </td>
              <td class="px-4 py-3.5 text-right space-x-1">
                <button @click="openAreaEdit(item)" class="p-1.5 text-slate-400 hover:text-sky-600 rounded-lg hover:bg-sky-50 transition">
                  <Pencil class="w-4 h-4" />
                </button>
                <button @click="deleteArea(item.id)" class="p-1.5 text-slate-400 hover:text-rose-600 rounded-lg hover:bg-rose-50 transition">
                  <Trash2 class="w-4 h-4" />
                </button>
              </td>
            </tr>
            <tr v-if="!areas || areas.length === 0">
              <td colspan="5" class="px-4 py-10 text-center text-slate-400 text-xs">Belum ada data area</td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- TAB 2: UNIT -->
      <div v-if="activeTab === 'unit'" class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <table class="w-full text-left text-xs">
          <thead class="bg-slate-50 text-slate-500 uppercase tracking-wider text-[10px] font-bold border-b border-slate-100">
            <tr>
              <th class="px-4 py-3.5 w-12 text-center">#</th>
              <th class="px-4 py-3.5">Kode Unit</th>
              <th class="px-4 py-3.5">Nama Unit/Lokasi</th>
              <th class="px-4 py-3.5">Area</th>
              <th class="px-4 py-3.5">Keterangan</th>
              <th class="px-4 py-3.5 text-center">Status</th>
              <th class="px-4 py-3.5 text-right w-24">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 text-slate-700">
            <tr v-for="(item, idx) in units" :key="item.id" class="hover:bg-slate-50/60 transition">
              <td class="px-4 py-3.5 text-center font-bold text-slate-400">{{ idx + 1 }}</td>
              <td class="px-4 py-3.5 font-bold text-sky-600">{{ item.kode || '-' }}</td>
              <td class="px-4 py-3.5 font-bold text-slate-800">{{ item.nama }}</td>
              <td class="px-4 py-3.5 font-semibold text-slate-600">{{ item.area || '-' }}</td>
              <td class="px-4 py-3.5 text-slate-500">{{ item.keterangan || '-' }}</td>
              <td class="px-4 py-3.5 text-center">
                <span class="px-2.5 py-0.5 text-[10px] font-bold rounded-full" :class="item.aktif ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800'">
                  {{ item.aktif ? 'Aktif' : 'Nonaktif' }}
                </span>
              </td>
              <td class="px-4 py-3.5 text-right space-x-1">
                <button @click="openUnitEdit(item)" class="p-1.5 text-slate-400 hover:text-sky-600 rounded-lg hover:bg-sky-50 transition">
                  <Pencil class="w-4 h-4" />
                </button>
                <button @click="deleteUnit(item.id)" class="p-1.5 text-slate-400 hover:text-rose-600 rounded-lg hover:bg-rose-50 transition">
                  <Trash2 class="w-4 h-4" />
                </button>
              </td>
            </tr>
            <tr v-if="!units || units.length === 0">
              <td colspan="7" class="px-4 py-10 text-center text-slate-400 text-xs">Belum ada data unit</td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- TAB 3: AKTIVITAS -->
      <div v-if="activeTab === 'aktivitas'" class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <table class="w-full text-left text-xs">
          <thead class="bg-slate-50 text-slate-500 uppercase tracking-wider text-[10px] font-bold border-b border-slate-100">
            <tr>
              <th class="px-4 py-3.5 w-12 text-center">#</th>
              <th class="px-4 py-3.5">Nama Aktivitas</th>
              <th class="px-4 py-3.5">Kategori</th>
              <th class="px-4 py-3.5">Keterangan</th>
              <th class="px-4 py-3.5 text-center">Status</th>
              <th class="px-4 py-3.5 text-right w-24">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 text-slate-700">
            <tr v-for="(item, idx) in aktivitas" :key="item.id" class="hover:bg-slate-50/60 transition">
              <td class="px-4 py-3.5 text-center font-bold text-slate-400">{{ idx + 1 }}</td>
              <td class="px-4 py-3.5 font-bold text-slate-800">{{ item.nama }}</td>
              <td class="px-4 py-3.5">
                <span class="px-2 py-0.5 text-[10px] font-semibold bg-purple-50 text-purple-700 rounded-md border border-purple-200">
                  {{ item.kategori || 'General' }}
                </span>
              </td>
              <td class="px-4 py-3.5 text-slate-500">{{ item.keterangan || '-' }}</td>
              <td class="px-4 py-3.5 text-center">
                <span class="px-2.5 py-0.5 text-[10px] font-bold rounded-full" :class="item.aktif ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800'">
                  {{ item.aktif ? 'Aktif' : 'Nonaktif' }}
                </span>
              </td>
              <td class="px-4 py-3.5 text-right space-x-1">
                <button @click="openAktEdit(item)" class="p-1.5 text-slate-400 hover:text-sky-600 rounded-lg hover:bg-sky-50 transition">
                  <Pencil class="w-4 h-4" />
                </button>
                <button @click="deleteAkt(item.id)" class="p-1.5 text-slate-400 hover:text-rose-600 rounded-lg hover:bg-rose-50 transition">
                  <Trash2 class="w-4 h-4" />
                </button>
              </td>
            </tr>
            <tr v-if="!aktivitas || aktivitas.length === 0">
              <td colspan="6" class="px-4 py-10 text-center text-slate-400 text-xs">Belum ada data aktivitas</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- MODAL AREA -->
    <div v-if="showAreaModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-xs">
      <div class="bg-white w-full max-w-md rounded-2xl shadow-xl border border-slate-100 p-5 space-y-4 text-xs">
        <h3 class="font-bold text-slate-800 text-sm">{{ editingArea ? 'Edit Master Area' : 'Tambah Master Area' }}</h3>
        <form @submit.prevent="saveArea" class="space-y-3">
          <div>
            <label class="block font-bold text-slate-700 mb-1">Nama Area *</label>
            <input type="text" v-model="areaForm.nama" required class="w-full px-3 py-2 border border-slate-300 rounded-xl" />
          </div>
          <div>
            <label class="block font-bold text-slate-700 mb-1">Keterangan</label>
            <textarea v-model="areaForm.keterangan" rows="2" class="w-full px-3 py-2 border border-slate-300 rounded-xl"></textarea>
          </div>
          <div v-if="editingArea">
            <label class="flex items-center gap-2 cursor-pointer font-bold text-slate-700">
              <input type="checkbox" v-model="areaForm.aktif" class="rounded text-sky-600" /> Aktif
            </label>
          </div>
          <div class="flex justify-end gap-2 pt-2">
            <button type="button" @click="showAreaModal = false" class="px-4 py-2 font-semibold bg-slate-100 rounded-xl">Batal</button>
            <button type="submit" class="px-4 py-2 font-semibold text-white bg-sky-600 rounded-xl">Simpan</button>
          </div>
        </form>
      </div>
    </div>

    <!-- MODAL UNIT -->
    <div v-if="showUnitModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-xs">
      <div class="bg-white w-full max-w-md rounded-2xl shadow-xl border border-slate-100 p-5 space-y-4 text-xs">
        <h3 class="font-bold text-slate-800 text-sm">{{ editingUnit ? 'Edit Master Unit' : 'Tambah Master Unit' }}</h3>
        <form @submit.prevent="saveUnit" class="space-y-3">
          <div>
            <label class="block font-bold text-slate-700 mb-1">Kode Unit</label>
            <input type="text" v-model="unitForm.kode" placeholder="DT-001..." class="w-full px-3 py-2 border border-slate-300 rounded-xl" />
          </div>
          <div>
            <label class="block font-bold text-slate-700 mb-1">Nama Unit/Lokasi *</label>
            <input type="text" v-model="unitForm.nama" required class="w-full px-3 py-2 border border-slate-300 rounded-xl" />
          </div>
          <div>
            <label class="block font-bold text-slate-700 mb-1">Area</label>
            <select v-model="unitForm.area" class="w-full px-3 py-2 border border-slate-300 rounded-xl">
              <option value="">-- Pilih Area --</option>
              <option v-for="a in areas" :key="a.id" :value="a.nama">{{ a.nama }}</option>
            </select>
          </div>
          <div>
            <label class="block font-bold text-slate-700 mb-1">Keterangan</label>
            <textarea v-model="unitForm.keterangan" rows="2" class="w-full px-3 py-2 border border-slate-300 rounded-xl"></textarea>
          </div>
          <div v-if="editingUnit">
            <label class="flex items-center gap-2 cursor-pointer font-bold text-slate-700">
              <input type="checkbox" v-model="unitForm.aktif" class="rounded text-sky-600" /> Aktif
            </label>
          </div>
          <div class="flex justify-end gap-2 pt-2">
            <button type="button" @click="showUnitModal = false" class="px-4 py-2 font-semibold bg-slate-100 rounded-xl">Batal</button>
            <button type="submit" class="px-4 py-2 font-semibold text-white bg-sky-600 rounded-xl">Simpan</button>
          </div>
        </form>
      </div>
    </div>

    <!-- MODAL AKTIVITAS -->
    <div v-if="showAktModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-xs">
      <div class="bg-white w-full max-w-md rounded-2xl shadow-xl border border-slate-100 p-5 space-y-4 text-xs">
        <h3 class="font-bold text-slate-800 text-sm">{{ editingAkt ? 'Edit Master Aktivitas' : 'Tambah Master Aktivitas' }}</h3>
        <form @submit.prevent="saveAkt" class="space-y-3">
          <div>
            <label class="block font-bold text-slate-700 mb-1">Nama Aktivitas *</label>
            <input type="text" v-model="aktForm.nama" required class="w-full px-3 py-2 border border-slate-300 rounded-xl" />
          </div>
          <div>
            <label class="block font-bold text-slate-700 mb-1">Kategori</label>
            <input type="text" v-model="aktForm.kategori" placeholder="GMP, Safety, Inspeksi..." class="w-full px-3 py-2 border border-slate-300 rounded-xl" />
          </div>
          <div>
            <label class="block font-bold text-slate-700 mb-1">Keterangan</label>
            <textarea v-model="aktForm.keterangan" rows="2" class="w-full px-3 py-2 border border-slate-300 rounded-xl"></textarea>
          </div>
          <div v-if="editingAkt">
            <label class="flex items-center gap-2 cursor-pointer font-bold text-slate-700">
              <input type="checkbox" v-model="aktForm.aktif" class="rounded text-sky-600" /> Aktif
            </label>
          </div>
          <div class="flex justify-end gap-2 pt-2">
            <button type="button" @click="showAktModal = false" class="px-4 py-2 font-semibold bg-slate-100 rounded-xl">Batal</button>
            <button type="submit" class="px-4 py-2 font-semibold text-white bg-sky-600 rounded-xl">Simpan</button>
          </div>
        </form>
      </div>
    </div>
  </AppLayout>
</template>
