<script setup>
import { ref, nextTick, onMounted, onUnmounted } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import html2canvas from 'html2canvas-pro';
import AppLayout from '@/Layouts/AppLayout.vue';
import { 
  Truck, 
  Calendar, 
  Clock, 
  Layers, 
  CheckCircle2, 
  AlertCircle, 
  Activity,
  Fuel,
  MapPin,
  Link,
  Upload,
  Camera
} from 'lucide-vue-next';

const props = defineProps({
  user: Object,
  notifCounts: Object,
  menu: Array,
  filters: Object,
  stats: Object,
  groupedFleet: Object,
  gsheetFleet: Object,
});

const filterDate = ref(props.filters?.date || new Date().toISOString().split('T')[0]);
const filterShift = ref(props.filters?.shift || 1);

const applyFilter = () => {
  router.get('/one-setting/monitoring-fleet', { date: filterDate.value, shift: filterShift.value }, { preserveState: true });
};

// Fleet GSheet Form
const gsheetFleetForm = ref({
  date: filterDate.value,
  url_day: props.gsheetFleet?.day || '',
  url_night: props.gsheetFleet?.night || '',
});

const submitImportFleet = () => {
  gsheetFleetForm.value.date = filterDate.value;
  router.post('/one-setting/fleet/import', gsheetFleetForm.value, {
    preserveScroll: true,
    onSuccess: () => {
      // Otomatis jadikan gambar papan setelah import berhasil
      nextTick(() => {
        setTimeout(() => captureAndSaveBoard(), 800);
      });
    },
  });
};

// Realtime Clock Logic
const actualTime = ref('00:00:00');
const actualDateStr = ref('');

let timer = null;

const updateClock = () => {
  const now = new Date();
  actualTime.value = now.toTimeString().split(' ')[0];
  actualDateStr.value = now.toLocaleDateString('id-ID', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' }).toUpperCase();
};

onMounted(() => {
  updateClock();
  timer = setInterval(updateClock, 1000);
});

onUnmounted(() => {
  if (timer) clearInterval(timer);
});

// ==============================
// Snapshot Board Logic
// ==============================
const captureRef = ref(null);
const isCapturing = ref(false);
const captureToast = ref(null);
const captureDefaultSite = 'ADW';

const showToast = (type, message, durationMs = 4000) => {
  captureToast.value = { type, message };
  if (type !== 'loading') {
    setTimeout(() => captureToast.value = null, durationMs);
  }
};

const captureAndSaveBoard = async () => {
  if (!captureRef.value) return;
  const hasData = props.groupedFleet && Object.keys(props.groupedFleet).length > 0;
  if (!hasData) return;

  isCapturing.value = true;
  showToast('loading', 'Sedang membuat gambar papan fleet...');

  try {
    const canvas = await html2canvas(captureRef.value, {
      scale: 2,
      useCORS: true,
      backgroundColor: '#ffffff',
      logging: false,
      ignoreElements: (el) => el.tagName === 'BUTTON' || el.getAttribute('data-html2canvas-ignore') === 'true',
    });

    const dataUrl = canvas.toDataURL('image/jpeg', 0.92);
    const shiftCode = filterShift.value == 2 ? 'N' : 'D';

    // Get CSRF token from cookie
    const csrfToken = document.cookie
      .split('; ')
      .find(row => row.startsWith('XSRF-TOKEN='))
      ?.split('=')?.[1];
    const csrfDecoded = csrfToken ? decodeURIComponent(csrfToken) : '';

    const res = await fetch('/one-setting/boards/snapshot', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-XSRF-TOKEN': csrfDecoded,
        'X-Requested-With': 'XMLHttpRequest',
      },
      body: JSON.stringify({
        site: captureDefaultSite,
        tanggal: filterDate.value,
        shift: shiftCode,
        type: 'fleet',
        title: `Setting Fleet Dept Produksi (${filterDate.value} Shift ${filterShift.value == 2 ? '2 Malam' : '1 Siang'})`,
        image_b64: dataUrl,
      }),
    });

    const data = await res.json();
    if (data.success) {
      showToast('success', 'Papan Fleet berhasil dijadikan gambar & disimpan ke Papan Settingan!');
    } else {
      showToast('error', data.message || 'Gagal menyimpan gambar papan.');
    }
  } catch (err) {
    console.error('[SnapshotFleet Error]', err);
    showToast('error', `Error: ${err?.message || 'Unknown error'}`);
  } finally {
    isCapturing.value = false;
  }
};


const getStatusBadgeClass = (status) => {
  switch (status) {
    case 'M': return 'bg-blue-600 text-white';
    case 'N': return 'bg-emerald-600 text-white';
    case 'G': case 'J': case 'R': return 'bg-rose-600 text-white';
    default: return 'bg-slate-600 text-white';
  }
};
</script>

<template>
  <Head title="Monitoring Setting Fleet - One-Setting" />

  <AppLayout :user="user" :notif-counts="notifCounts" :menu="menu">
    <div class="space-y-6">
      <!-- Glass Header Banner (Matching V1 Styling) -->
      <div class="bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 text-white p-6 rounded-2xl shadow-xl flex flex-col md:flex-row items-center justify-between gap-4">
        <div class="flex items-center gap-4">
          <div class="w-12 h-12 rounded-2xl bg-indigo-500/20 backdrop-blur border border-indigo-400/30 flex items-center justify-center shrink-0">
            <Truck class="w-6 h-6 text-indigo-400" />
          </div>
          <div>
            <h1 class="text-xl font-black tracking-wide">MONITORING SETTING FLEET ALAT BERAT</h1>
            <p class="text-xs text-indigo-300/80">Pantau alokasi fleet loader, area kerja, dan status peralatan secara real-time</p>
          </div>
        </div>

        <div class="flex items-center gap-6 bg-white/10 backdrop-blur-md px-5 py-2.5 rounded-2xl border border-white/10">
          <div class="text-right">
            <span class="text-xs font-black text-amber-400 tracking-wider flex items-center gap-1.5 justify-end">
              <span class="w-2 h-2 rounded-full bg-emerald-400 animate-ping"></span>
              {{ filterShift == 1 ? 'SHIFT 1 (SIANG)' : 'SHIFT 2 (MALAM)' }}
            </span>
            <p class="text-[10px] text-slate-300 font-medium">{{ actualDateStr }}</p>
          </div>
          <div class="border-l border-white/20 pl-4 font-mono font-black text-xl text-indigo-300">
            {{ actualTime }}
          </div>
        </div>
      </div>

      <!-- Filters & Stats Cards -->
      <div class="grid grid-cols-1 lg:grid-cols-4 gap-4">
        <div class="lg:col-span-2 bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm flex items-center gap-3">
          <div class="flex flex-wrap items-center gap-3 text-xs w-full">
            <div>
              <label class="block text-[10px] font-bold text-slate-600 uppercase mb-1">Tanggal</label>
              <input type="date" v-model="filterDate" @change="applyFilter" class="px-3 py-1.5 border border-slate-300 rounded-xl bg-slate-50/50" />
            </div>

            <div>
              <label class="block text-[10px] font-bold text-slate-600 uppercase mb-1">Shift</label>
              <select v-model="filterShift" @change="applyFilter" class="px-3 py-1.5 border border-slate-300 rounded-xl bg-slate-50/50">
                <option :value="1">Shift 1 (Siang)</option>
                <option :value="2">Shift 2 (Malam)</option>
              </select>
            </div>
          </div>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm flex items-center gap-3">
          <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
            <CheckCircle2 class="w-5 h-5" />
          </div>
          <div>
            <p class="text-[10px] font-bold text-slate-500 uppercase">Unit RFU / Operational</p>
            <p class="text-xl font-black text-emerald-600">{{ stats?.rfuCount || 0 }} Unit</p>
          </div>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm flex items-center gap-3">
          <div class="w-10 h-10 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center shrink-0">
            <AlertCircle class="w-5 h-5" />
          </div>
          <div>
            <p class="text-[10px] font-bold text-slate-500 uppercase">Unit Breakdown / Repair</p>
            <p class="text-xl font-black text-rose-600">{{ stats?.breakdownCount || 0 }} Unit</p>
          </div>
        </div>
      </div>

      <!-- Fleet GSheet Sync Box -->
      <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
          <div>
            <h2 class="text-sm font-bold text-slate-800 flex items-center gap-2">
              <Link class="w-4 h-4 text-indigo-500" />
              Import Alokasi Fleet (Google Sheets)
            </h2>
            <p class="text-xs text-slate-500">Sinkronisasi matriks peralatan &amp; loader dari link spreadsheet untuk tanggal {{ filterDate }} shift {{ filterShift == 1 ? 'Siang' : 'Malam' }}.</p>
          </div>
        </div>

        <form @submit.prevent="submitImportFleet" class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
          <div>
            <label class="block font-bold text-slate-700 mb-1">Link Spreadsheet Siang (Shift 1)</label>
            <input type="url" v-model="gsheetFleetForm.url_day" placeholder="https://docs.google.com/spreadsheets/d/...#gid=..." class="w-full px-3 py-2 border border-slate-300 rounded-xl bg-slate-50/50" />
          </div>

          <div>
            <label class="block font-bold text-slate-700 mb-1">Link Spreadsheet Malam (Shift 2)</label>
            <input type="url" v-model="gsheetFleetForm.url_night" placeholder="https://docs.google.com/spreadsheets/d/...#gid=..." class="w-full px-3 py-2 border border-slate-300 rounded-xl bg-slate-50/50" />
          </div>

          <div class="md:col-span-2 flex items-center justify-end gap-2 pt-2">
            <button type="submit" class="px-5 py-2 text-xs font-semibold rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white shadow-sm transition flex items-center gap-1.5">
              <Upload class="w-3.5 h-3.5" /> Import Fleet Data
            </button>
          </div>
        </form>
      </div>

      <!-- Fleet Groups & Loader Matrix (ref for html2canvas capture) -->
      <div 
        v-if="groupedFleet && Object.keys(groupedFleet).length > 0" 
        ref="captureRef"
        class="space-y-6 bg-white p-5 rounded-2xl border border-slate-200 shadow-xs"
      >
        <!-- Header Papan Snapshot (Muncul di gambar HD Papan Settingan) -->
        <div class="border-b-2 border-slate-800 pb-3 flex items-center justify-between">
          <div>
            <div class="text-[10px] font-extrabold text-indigo-600 uppercase tracking-wider">PT PUTRA PERKASA ABADI</div>
            <h2 class="text-base font-black text-slate-900 tracking-tight">SETTING FLEET DEPARTEMEN PRODUKSI</h2>
            <p class="text-xs font-semibold text-slate-500">Tanggal: {{ filterDate }} &bull; Shift: {{ filterShift == 2 ? '2 (Malam)' : '1 (Siang)' }}</p>
          </div>
          <div class="text-right">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl text-xs font-black bg-indigo-50 text-indigo-700 border border-indigo-200">
              <Truck class="w-3.5 h-3.5" /> Fleet Monitoring
            </span>
          </div>
        </div>

        <div v-for="(loaders, groupName) in groupedFleet" :key="groupName" class="space-y-3">
          <!-- Group Title Bar -->
          <div class="bg-slate-900 text-white px-4 py-2.5 rounded-xl shadow-sm flex items-center justify-between">
            <div class="flex items-center gap-2">
              <Layers class="w-4 h-4 text-indigo-400" />
              <span class="font-bold text-xs uppercase tracking-wider">{{ groupName }}</span>
            </div>
            <span class="px-2.5 py-0.5 text-[10px] font-extrabold bg-indigo-500 text-white rounded-full">
              {{ Object.keys(loaders).length }} Loader Block
            </span>
          </div>

          <!-- Loaders Cards Grid -->
          <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
            <div 
              v-for="(loader, loaderId) in loaders" 
              :key="loaderId"
              class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden flex flex-col"
            >
              <!-- Loader Card Top Bar -->
              <div class="bg-amber-50 px-3 py-1 text-center text-[10px] font-extrabold text-amber-900 border-b border-amber-100 flex items-center justify-center gap-1">
                <MapPin class="w-3 h-3 text-amber-600" />
                <span>{{ loader.area || 'AREA -' }}</span>
              </div>

              <div class="bg-orange-50 px-3 py-1 text-center text-[10px] font-bold text-orange-800 border-b border-orange-100 flex items-center justify-center gap-1">
                <Fuel class="w-3 h-3 text-orange-600" />
                <span>Refuel: {{ loader.refueling || '-' }}</span>
              </div>

              <div class="bg-slate-800 text-white py-2 text-center text-xs font-black tracking-wide border-b border-slate-700">
                {{ loader.front ? loaderId + ' DI ' + loader.front : loaderId }}
              </div>

              <!-- Units List Table -->
              <div class="p-3 flex-1">
                <table class="w-full text-left text-xs">
                  <tbody class="divide-y divide-slate-100">
                    <tr v-for="unit in loader.units" :key="unit.id" class="text-[11px]">
                      <td class="py-1.5 font-bold text-slate-700">{{ unit.unit_nrp }}</td>
                      <td class="py-1.5 text-right">
                        <span class="px-2 py-0.5 text-[10px] font-black rounded" :class="getStatusBadgeClass(unit.unit_status)">
                          {{ unit.unit_status || 'M' }}
                        </span>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>

              <!-- Loader Footer -->
              <div class="bg-slate-50 p-2 border-t border-slate-100 text-[10px] flex items-center justify-between text-slate-600 font-semibold">
                <span>Distance: {{ loader.units[0]?.distance || 0 }}m</span>
                <span class="text-emerald-600 font-bold">{{ loader.units.length }} Unit</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div v-else class="bg-white p-12 rounded-2xl border border-slate-200/80 shadow-sm text-center text-slate-400 space-y-2">
        <Truck class="w-12 h-12 mx-auto text-slate-300" />
        <p class="text-sm font-semibold text-slate-600">Belum ada data alokasi fleet untuk tanggal &amp; shift ini.</p>
        <p class="text-xs text-slate-400">Silakan lakukan import fleet spreadsheet di form atas.</p>
      </div>
    </div>

    <!-- Toast Notification -->
    <Transition name="toast">
      <div 
        v-if="captureToast"
        class="fixed top-5 right-5 z-[9999] flex items-start gap-3 px-4 py-3 rounded-xl shadow-xl border text-xs font-semibold max-w-xs"
        :class="captureToast.type === 'success' ? 'bg-emerald-50 border-emerald-300 text-emerald-800' : captureToast.type === 'error' ? 'bg-rose-50 border-rose-300 text-rose-800' : 'bg-amber-50 border-amber-300 text-amber-800'"
      >
        <CheckCircle2 v-if="captureToast.type === 'success'" class="w-4 h-4 text-emerald-600 shrink-0 mt-0.5" />
        <AlertCircle v-else-if="captureToast.type === 'error'" class="w-4 h-4 text-rose-500 shrink-0 mt-0.5" />
        <Camera v-else class="w-4 h-4 text-amber-600 shrink-0 mt-0.5 animate-bounce" />
        <span>{{ captureToast.message }}</span>
      </div>
    </Transition>
  </AppLayout>
</template>
