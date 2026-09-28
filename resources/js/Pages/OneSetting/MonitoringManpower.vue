<script setup>
import { confirmDialog } from '@/utils/confirm';
import { ref, computed, nextTick, onMounted, onUnmounted } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import html2canvas from 'html2canvas-pro';
import AppLayout from '@/Layouts/AppLayout.vue';
import {
  Users,
  Search,
  Layers,
  Link,
  RefreshCw,
  Pencil,
  Trash2,
  MapPin,
  Plus,
  X,
  Camera,
  CheckCircle2,
  AlertCircle,
  AlertTriangle,
  MessageCircle,
  RotateCcw
} from 'lucide-vue-next';

const props = defineProps({
  user: Object,
  notifCounts: Object,
  menu: Array,
  filters: Object,
  totalRoster: Number,
  groupedByUnit: Object,
  rosterData: Array,
  gsheetManpower: Object,
  locationLocks: { type: Array, default: () => [] },
  categoryLocks: { type: Array, default: () => [] },
  passwordCheckPoint: { type: String, default: null },
});

const filterDate = ref(props.filters?.date || new Date().toISOString().split('T')[0]);
const filterShift = ref(props.filters?.shift || 'D');
const searchQuery = ref('');

const filteredRosterData = computed(() => {
  if (!props.rosterData) return [];
  if (!searchQuery.value) return props.rosterData;
  const q = searchQuery.value.toLowerCase();
  return props.rosterData.filter(row => {
    return (row.unit && row.unit.toLowerCase().includes(q)) ||
           (row.nrp && row.nrp.toLowerCase().includes(q)) ||
           (row.employee_name && row.employee_name.toLowerCase().includes(q));
  });
});

// Urutan kategori unit di papan (LAINNYA selalu paling akhir).
const CATEGORY_ORDER = ['HAUL TRUCK', 'DUMP TRUCK', 'BULLDOZER', 'EXCAVATOR', 'WHEEL LOADER', 'MOTOR GRADER', 'LAINNYA'];

// Deteksi otomatis kategori dari awalan kode unit (dikonfirmasi dari data roster real: HD78xxx,
// C77xxx/C5770xxAMM, GD8xx, W5xxx/WA5xxx, D15xx). Bisa dikoreksi lewat "Kunci Kategori Unit".
const detectCategoryFromUnit = (unit) => {
  const u = (unit || '').toUpperCase().trim();
  if (/^HD/.test(u)) return 'DUMP TRUCK';
  if (/^C\d/.test(u)) return 'HAUL TRUCK';
  if (/^GD/.test(u)) return 'MOTOR GRADER';
  if (/^WA?\d/.test(u)) return 'WHEEL LOADER';
  if (/^D\d/.test(u)) return 'BULLDOZER';
  if (/^PC/.test(u)) return 'EXCAVATOR';
  return 'LAINNYA';
};

const getCategoryForUnit = (unit) => {
  if (!unit) return 'LAINNYA';
  const upper = unit.toUpperCase();
  const lock = props.categoryLocks?.find(l => l.units.includes(upper));
  return lock ? lock.category : detectCategoryFromUnit(unit);
};

// Papan setting dikelompokkan per CSA, lalu per kategori unit di dalamnya (bukan satu
// list panjang) supaya 1 gambar snapshot tersusun rapi berkolom & bersubbagian.
const groupedByCsa = computed(() => {
  const csaGroups = {};
  for (const item of filteredRosterData.value) {
    const csa = item.csa_type || 'CSA GENERAL';
    const category = getCategoryForUnit(item.unit);
    csaGroups[csa] ??= {};
    (csaGroups[csa][category] ??= []).push(item);
  }

  const sortedCsaKeys = Object.keys(csaGroups).sort((a, b) => {
    if (a === 'CSA GENERAL') return 1;
    if (b === 'CSA GENERAL') return -1;
    return a.localeCompare(b);
  });

  return sortedCsaKeys.reduce((acc, csaKey) => {
    const categories = csaGroups[csaKey];
    const sortedCategoryKeys = Object.keys(categories).sort(
      (a, b) => CATEGORY_ORDER.indexOf(a) - CATEGORY_ORDER.indexOf(b)
    );
    acc[csaKey] = sortedCategoryKeys.reduce((catAcc, catKey) => {
      catAcc[catKey] = categories[catKey];
      return catAcc;
    }, {});
    return acc;
  }, {});
});

const applyFilter = () => {
  router.get('/one-setting/monitoring-manpower', { date: filterDate.value, shift: filterShift.value }, { preserveState: true });
};

// Manpower GSheet Sync Form
const gsheetManpowerForm = ref({
  date: filterDate.value,
  url_day: props.gsheetManpower?.day || '',
  url_night: props.gsheetManpower?.night || '',
});

const submitSyncManpower = () => {
  gsheetManpowerForm.value.date = filterDate.value;
  router.post('/one-setting/manpower/sync-gsheet', gsheetManpowerForm.value, {
    preserveScroll: true,
    onSuccess: () => {
      // Otomatis jadikan gambar papan setelah import berhasil
      nextTick(() => {
        setTimeout(() => captureAndSaveBoard(), 800);
      });
    },
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

const deleteRosterItem = async (id) => {
  if (await confirmDialog('Hapus data roster ini?')) {
    router.delete(`/one-setting/roster/delete/${id}`, { preserveScroll: true });
  }
};

const resetRosterData = async () => {
  if (await confirmDialog(`Hapus SEMUA data roster tanggal ${filterDate.value} shift ${filterShift.value}? Tindakan ini tidak bisa dibatalkan.`)) {
    router.post('/one-setting/roster/clear', { date: filterDate.value, shift: filterShift.value }, { preserveScroll: true });
  }
};

// Deteksi bentrok unit/NRP (padanan v1: unit yang bukan placeholder/off-duty dihitung
// dobel kalau dipakai >1 personil; NRP dobel kalau muncul di >1 baris roster).
const EXCLUDE_UNIT_KEYWORDS = ['SPARE', '-', 'OFF', 'STANDBY', 'ALL DISPOSAL', 'ALL FRONT', 'COAL', 'DISPOSAL', 'FRONT', 'HRM', 'MANPOWER', 'PSV', 'MCU', 'TR', 'ID', 'IDS'];

const conflicts = computed(() => {
  const unitCounts = {};
  const nrpCounts = {};
  for (const row of props.rosterData || []) {
    const unit = (row.unit || '').toUpperCase().trim();
    const nrp = (row.nrp || '').trim();
    const isExcluded = EXCLUDE_UNIT_KEYWORDS.some((kw) => unit.includes(kw));
    if (unit && !isExcluded) unitCounts[unit] = (unitCounts[unit] || 0) + 1;
    if (nrp) nrpCounts[nrp] = (nrpCounts[nrp] || 0) + 1;
  }
  return {
    units: Object.keys(unitCounts).filter((u) => unitCounts[u] > 1),
    nrps: Object.keys(nrpCounts).filter((n) => nrpCounts[n] > 1),
  };
});

const isUnitConflict = (unit) => conflicts.value.units.includes((unit || '').toUpperCase().trim());
const isNrpConflict = (nrp) => conflicts.value.nrps.includes((nrp || '').trim());

const waLink = (phone) => {
  let digits = (phone || '').replace(/\D/g, '');
  if (!digits) return null;
  if (digits.startsWith('0')) digits = '62' + digits.slice(1);
  else if (!digits.startsWith('62')) digits = '62' + digits;
  return `https://wa.me/${digits}`;
};

// Kunci Lokasi Unit
const showLocationModal = ref(false);
const locationLockForm = ref({ location: '', unitsText: '' });

const submitLocationLock = () => {
  const units = locationLockForm.value.unitsText.split(',').map(u => u.trim()).filter(u => u !== '');
  if (!locationLockForm.value.location || units.length === 0) {
    alert('Lokasi dan setidaknya 1 Unit wajib diisi.');
    return;
  }

  router.post('/one-setting/location-locks', {
    location: locationLockForm.value.location,
    units,
  }, {
    preserveScroll: true,
    onSuccess: () => {
      locationLockForm.value.location = '';
      locationLockForm.value.unitsText = '';
    },
  });
};

const deleteLocationLock = async (id) => {
  if (await confirmDialog('Hapus kunci lokasi ini?')) {
    router.delete(`/one-setting/location-locks/${id}`, { preserveScroll: true });
  }
};

// Kunci Kategori Unit
const showCategoryModal = ref(false);
const categoryLockForm = ref({ category: '', unitsText: '' });

const submitCategoryLock = () => {
  const units = categoryLockForm.value.unitsText.split(',').map(u => u.trim()).filter(u => u !== '');
  if (!categoryLockForm.value.category || units.length === 0) {
    alert('Kategori dan setidaknya 1 Unit wajib diisi.');
    return;
  }

  router.post('/one-setting/category-locks', {
    category: categoryLockForm.value.category,
    units,
  }, {
    preserveScroll: true,
    onSuccess: () => {
      categoryLockForm.value.category = '';
      categoryLockForm.value.unitsText = '';
    },
  });
};

const deleteCategoryLock = async (id) => {
  if (await confirmDialog('Hapus kunci kategori ini?')) {
    router.delete(`/one-setting/category-locks/${id}`, { preserveScroll: true });
  }
};

const totalInCsa = (categories) => Object.values(categories).reduce((sum, arr) => sum + arr.length, 0);

// Setiap kolom papan dibatasi tegas 30 baris (lintas kategori) supaya tinggi semua CSA
// konsisten dan tidak ada yang memanjang ke bawah — kalau personil lebih banyak, otomatis
// menambah KOLOM ke samping (bukan menambah tinggi). Kategori yang kepotong di tengah kolom
// dilanjut di kolom berikutnya dengan label "(lanjutan)".
const CSA_MAX_ROWS_PER_COLUMN = 30;
const paginateCsaColumns = (categories, maxRows = CSA_MAX_ROWS_PER_COLUMN) => {
  const columns = [];
  let currentColumn = [];
  let currentCount = 0;

  for (const [categoryName, items] of Object.entries(categories)) {
    let remaining = items;
    let isContinuation = false;
    while (remaining.length > 0) {
      const spaceLeft = maxRows - currentCount;
      if (spaceLeft <= 0) {
        columns.push(currentColumn);
        currentColumn = [];
        currentCount = 0;
        continue;
      }
      const take = remaining.slice(0, spaceLeft);
      currentColumn.push({
        categoryName: isContinuation ? `${categoryName} (lanjutan)` : categoryName,
        items: take,
      });
      currentCount += take.length;
      remaining = remaining.slice(take.length);
      isContinuation = true;
    }
  }
  if (currentColumn.length > 0) columns.push(currentColumn);

  return columns.length > 0 ? columns : [[]];
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
const captureToast = ref(null); // { type: 'success'|'error'|'loading', message: '' }
const captureDefaultSite = 'ADW';

const showToast = (type, message, durationMs = 4000) => {
  captureToast.value = { type, message };
  if (type !== 'loading') {
    setTimeout(() => captureToast.value = null, durationMs);
  }
};

const captureAndSaveBoard = async () => {
  if (!captureRef.value) return;
  if (!props.rosterData || props.rosterData.length === 0) return;

  isCapturing.value = true;
  showToast('loading', 'Sedang membuat gambar papan manpower...');

  // Papan melebar ke samping (scroll horizontal), dan parent card-nya sendiri overflow-hidden
  // — supaya bagian yang cuma kebaca lewat scroll tetap ikut ter-capture utuh (tidak kepotong
  // oleh parent manapun), papan di-clone dulu ke container lepas di luar layout halaman
  // (posisi fixed di luar layar), baru di-screenshot dari situ, lalu dibuang.
  const original = captureRef.value;
  const fullWidth = original.scrollWidth;
  const fullHeight = original.scrollHeight;

  const clone = original.cloneNode(true);
  clone.style.overflow = 'visible';
  clone.style.width = `${fullWidth}px`;
  clone.style.height = 'auto';

  const tempContainer = document.createElement('div');
  tempContainer.style.position = 'fixed';
  tempContainer.style.top = '0';
  tempContainer.style.left = '-99999px';
  tempContainer.style.zIndex = '-1';
  tempContainer.style.width = `${fullWidth}px`;
  tempContainer.style.background = '#ffffff';
  tempContainer.appendChild(clone);
  document.body.appendChild(tempContainer);

  try {
    const canvas = await html2canvas(clone, {
      scale: 2,
      useCORS: true,
      backgroundColor: '#ffffff',
      logging: false,
      width: fullWidth,
      height: fullHeight,
      windowWidth: fullWidth,
      ignoreElements: (node) => node.tagName === 'BUTTON' || node.getAttribute('data-html2canvas-ignore') === 'true',
    });

    const dataUrl = canvas.toDataURL('image/jpeg', 0.92);

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
        shift: filterShift.value,
        type: 'manpower',
        title: `Setting Manpower Departement Produksi (${filterDate.value} Shift ${filterShift.value === 'D' ? '1 Siang' : '2 Malam'})`,
        image_b64: dataUrl,
      }),
    });

    const data = await res.json();
    if (data.success) {
      showToast('success', 'Papan Manpower berhasil dijadikan gambar & disimpan ke Papan Settingan!');
    } else {
      showToast('error', data.message || 'Gagal menyimpan gambar papan.');
    }
  } catch (err) {
    console.error('[SnapshotBoard Error]', err);
    showToast('error', `Error: ${err?.message || 'Unknown error'}`);
  } finally {
    document.body.removeChild(tempContainer);
    isCapturing.value = false;
  }
};

</script>

<template>
  <Head title="Monitoring Setting Manpower - One-Setting" />

  <AppLayout :user="user" :notif-counts="notifCounts" :menu="menu">
    <div class="space-y-6">
      <!-- Glass Header Banner (Matching V1 Styling) -->
      <div class="bg-gradient-to-r from-slate-900 via-sky-950 to-slate-900 text-white p-6 rounded-2xl shadow-xl flex flex-col md:flex-row items-center justify-between gap-4">
        <div class="flex items-center gap-4">
          <div class="w-12 h-12 rounded-2xl bg-sky-500/20 backdrop-blur border border-sky-400/30 flex items-center justify-center shrink-0">
            <Users class="w-6 h-6 text-sky-400" />
          </div>
          <div>
            <h1 class="text-xl font-black tracking-wide">MONITORING SETTINGAN MANPOWER</h1>
            <p class="text-xs text-sky-300/80">Pantau alokasi penugasan manpower &amp; operator unit per shift secara real-time</p>
          </div>
        </div>

        <div class="flex items-center gap-6 bg-white/10 backdrop-blur-md px-5 py-2.5 rounded-2xl border border-white/10">
          <div class="text-right">
            <span class="text-xs font-black text-amber-400 tracking-wider flex items-center gap-1.5 justify-end">
              <span class="w-2 h-2 rounded-full bg-emerald-400 animate-ping"></span>
              {{ filterShift === 'D' ? 'SHIFT 1 (SIANG)' : 'SHIFT 2 (MALAM)' }}
            </span>
            <p class="text-[10px] text-slate-300 font-medium">{{ actualDateStr }}</p>
          </div>
          <div class="border-l border-white/20 pl-4 font-mono font-black text-xl text-sky-300">
            {{ actualTime }}
          </div>
        </div>
      </div>

      <!-- Filters & Stats -->
      <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex flex-wrap items-center gap-3 text-xs">
          <div>
            <label class="block text-[10px] font-bold text-slate-600 uppercase mb-1">Tanggal</label>
            <input type="date" v-model="filterDate" @change="applyFilter" class="px-3 py-1.5 border border-slate-300 rounded-xl bg-slate-50/50" />
          </div>

          <div>
            <label class="block text-[10px] font-bold text-slate-600 uppercase mb-1">Shift</label>
            <select v-model="filterShift" @change="applyFilter" class="px-3 py-1.5 border border-slate-300 rounded-xl bg-slate-50/50">
              <option value="D">Shift 1 (Siang)</option>
              <option value="N">Shift 2 (Malam)</option>
            </select>
          </div>

          <div>
            <label class="block text-[10px] font-bold text-slate-600 uppercase mb-1">Cari Operator / Unit</label>
            <div class="relative">
              <Search class="w-3.5 h-3.5 absolute left-3 top-2.5 text-slate-400" />
              <input type="text" v-model="searchQuery" placeholder="Cari NRP, Nama, Unit..." class="pl-8 pr-3 py-1.5 border border-slate-300 rounded-xl bg-slate-50/50 w-52" />
            </div>
          </div>
        </div>

        <div class="flex items-center gap-3 flex-wrap">
          <button @click="showLocationModal = true" class="px-4 py-2.5 text-xs font-bold rounded-xl bg-white border border-slate-300 text-slate-600 hover:bg-slate-50 transition flex items-center gap-1.5">
            <MapPin class="w-3.5 h-3.5 text-sky-500" /> Kunci Lokasi Unit
          </button>
          <button @click="showCategoryModal = true" class="px-4 py-2.5 text-xs font-bold rounded-xl bg-white border border-slate-300 text-slate-600 hover:bg-slate-50 transition flex items-center gap-1.5">
            <Layers class="w-3.5 h-3.5 text-sky-500" /> Kunci Kategori Unit
          </button>
          <!-- SNAPSHOT BUTTON -->
          <button 
            @click="captureAndSaveBoard"
            :disabled="isCapturing || !rosterData || rosterData.length === 0"
            class="px-4 py-2.5 text-xs font-bold rounded-xl transition flex items-center gap-1.5 shadow-sm cursor-pointer disabled:opacity-50"
            :class="isCapturing ? 'bg-amber-500 text-white' : 'bg-emerald-600 hover:bg-emerald-700 text-white'"
          >
            <Camera class="w-3.5 h-3.5" />
            <span>{{ isCapturing ? 'Memproses...' : 'Jadikan Gambar Papan' }}</span>
          </button>
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

      <!-- Google Sheet Sync Box -->
      <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
          <div>
            <h2 class="text-sm font-bold text-slate-800 flex items-center gap-2">
              <Link class="w-4 h-4 text-sky-500" />
              Import &amp; Sinkronisasi Google Sheets Manpower
            </h2>
            <p class="text-xs text-slate-500">Tarik data roster dari link GSheet untuk tanggal {{ filterDate }} shift {{ filterShift === 'D' ? 'Siang' : 'Malam' }}.</p>
          </div>
        </div>

        <form @submit.prevent="submitSyncManpower" class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
          <div>
            <label class="block font-bold text-slate-700 mb-1">Link Spreadsheet Siang (Shift 1)</label>
            <input type="url" v-model="gsheetManpowerForm.url_day" placeholder="https://docs.google.com/spreadsheets/d/...#gid=..." class="w-full px-3 py-2 border border-slate-300 rounded-xl bg-slate-50/50" />
          </div>

          <div>
            <label class="block font-bold text-slate-700 mb-1">Link Spreadsheet Malam (Shift 2)</label>
            <input type="url" v-model="gsheetManpowerForm.url_night" placeholder="https://docs.google.com/spreadsheets/d/...#gid=..." class="w-full px-3 py-2 border border-slate-300 rounded-xl bg-slate-50/50" />
          </div>

          <div class="md:col-span-2 flex items-center justify-end gap-2 pt-2">
            <button type="submit" class="px-5 py-2 text-xs font-semibold rounded-xl bg-sky-600 hover:bg-sky-700 text-white shadow-sm transition flex items-center gap-1.5">
              <RefreshCw class="w-3.5 h-3.5" />
              Simpan &amp; Sinkron Google Sheets
            </button>
          </div>
        </form>
      </div>

      <!-- Table View for Roster Data (ref for html2canvas capture) -->
      <div v-if="rosterData && rosterData.length > 0" class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div ref="captureRef" class="overflow-x-auto bg-white p-5">
          <!-- Header Papan Snapshot (Muncul di gambar HD Papan Settingan) -->
          <div class="border-b-2 border-slate-800 pb-3 mb-4">
            <div class="flex items-center justify-between gap-4">
              <img src="/images/logo_safe_strong.png" alt="SAFE & STRONG" class="h-10 object-contain shrink-0">
              <div class="text-center flex-1">
                <h2 class="text-base font-black text-slate-900 tracking-tight uppercase">Setting Manpower Departement Produksi</h2>
                <p class="text-xs font-bold text-slate-600 mt-0.5">{{ actualDateStr }} &bull; Shift {{ filterShift === 'N' ? '2 (Malam)' : '1 (Siang)' }}</p>
              </div>
              <div class="w-10 shrink-0"></div>
            </div>

            <div class="mt-2 grid grid-cols-2 border border-slate-800 rounded-lg overflow-hidden text-xs font-black uppercase text-center">
              <div class="px-3 py-1.5 bg-slate-100 border-r border-slate-800 text-slate-800">
                Password Check Point<span v-if="passwordCheckPoint"> : {{ passwordCheckPoint }}</span>
              </div>
              <div class="px-3 py-1.5 bg-slate-100 text-slate-800">Lapor Tindakan Tidak Aman</div>
            </div>
          </div>

          <!-- Grouped Per CSA, lalu per kategori unit di dalamnya. Semua meluas ke SAMPING
               (scroll horizontal), tidak pernah ke bawah — supaya tinggi antar CSA konsisten.
               Scroll horizontalnya ditangani oleh #captureRef (parent) — di sini SENGAJA
               tidak dikasih overflow-x-auto sendiri, supaya scrollWidth parent kebaca benar
               (dua overflow-x-auto bersarang bikin lebar aslinya salah kebaca saat capture). -->
          <div class="flex items-start gap-4 pb-2">
            <div
              v-for="(categories, csaName) in groupedByCsa"
              :key="csaName"
              class="shrink-0 bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden flex flex-col"
            >
              <!-- CSA Title Bar -->
              <div class="bg-slate-900 text-white px-3 py-2 flex items-center justify-between gap-2">
                <span class="font-bold text-[11px] uppercase tracking-wider flex items-center gap-1.5">
                  <MapPin class="w-3.5 h-3.5 text-sky-400 shrink-0" /> {{ csaName }}
                </span>
                <span class="px-2 py-0.5 text-[10px] font-extrabold bg-sky-500 text-white rounded-full shrink-0">
                  {{ totalInCsa(categories) }} Personil
                </span>
              </div>

              <!-- Maks 30 baris per kolom (lintas kategori); kalau total personil CSA ini
                   lebih banyak, otomatis nambah kolom ke samping (bukan makin tinggi) -->
              <div class="flex divide-x divide-slate-200">
                <div v-for="(segments, colIdx) in paginateCsaColumns(categories)" :key="colIdx" class="w-[300px] shrink-0 overflow-hidden divide-y divide-slate-100">
                  <div v-for="(segment, segIdx) in segments" :key="segIdx">
                    <!-- Category Sub-Header -->
                    <div class="bg-sky-50 px-3 py-1.5 flex items-center gap-1.5 border-b border-sky-100">
                      <Layers class="w-3 h-3 text-sky-600 shrink-0" />
                      <span class="text-[10px] font-extrabold text-sky-900 uppercase tracking-wider truncate">{{ segment.categoryName }}</span>
                      <span class="ml-auto text-[9px] font-bold text-sky-700 shrink-0">{{ segment.items.length }}</span>
                    </div>

                    <!-- Baris kompak 2 baris (bukan tabel 4 kolom sempit) supaya tetap terbaca -->
                    <div class="divide-y divide-slate-100">
                      <div
                        v-for="item in segment.items"
                        :key="item.id"
                        class="px-3 py-1.5 hover:bg-slate-50 transition-colors"
                        :class="{ 'bg-rose-50 hover:bg-rose-100': isUnitConflict(item.unit) }"
                      >
                        <div class="flex items-center justify-between gap-2">
                          <div class="flex items-center gap-1.5 min-w-0">
                            <span class="shrink-0 px-1.5 py-0.5 bg-sky-50 text-sky-700 font-bold rounded border border-sky-100 text-[10px]">{{ item.unit }}</span>
                            <span v-if="isUnitConflict(item.unit)" class="shrink-0 text-[9px] bg-rose-600 text-white font-black px-1.5 py-0.5 rounded">DOBEL</span>
                            <span class="text-[10px] text-slate-500 truncate">{{ item.phone }}</span>
                          </div>
                          <div data-html2canvas-ignore="true" class="flex items-center gap-0.5 shrink-0">
                            <button @click="openEditRoster(item)" class="p-1 text-slate-400 hover:text-sky-600 rounded-lg hover:bg-sky-50 transition">
                              <Pencil class="w-3 h-3" />
                            </button>
                            <button @click="deleteRosterItem(item.id)" class="p-1 text-slate-400 hover:text-rose-600 rounded-lg hover:bg-rose-50 transition">
                              <Trash2 class="w-3 h-3" />
                            </button>
                          </div>
                        </div>
                        <div class="mt-0.5 flex items-baseline gap-1.5">
                          <span class="text-[10px] font-semibold text-slate-500 shrink-0">{{ item.nrp }}</span>
                          <span class="text-xs font-black text-slate-800 leading-tight">{{ item.employee_name }}</span>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div v-else class="bg-white p-12 rounded-2xl border border-slate-200/80 shadow-sm text-center text-slate-400 space-y-2">
        <Users class="w-12 h-12 mx-auto text-slate-300" />
        <p class="text-sm font-semibold text-slate-600">Belum ada data monitoring manpower untuk tanggal &amp; shift ini.</p>
        <p class="text-xs text-slate-400">Silakan lakukan sinkronisasi Google Sheets di form atas untuk mengisi data roster.</p>
      </div>

      <!-- MONITORING ALOKASI MANPOWER (tabel datar, padanan v1 settings/manpower?view=roster) -->
      <div v-if="rosterData && rosterData.length > 0" class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="p-4 border-b border-slate-100 bg-slate-50/50 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
          <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider flex items-center gap-2">
            <Layers class="w-4 h-4 text-sky-500" /> Monitoring Alokasi Manpower
            <span class="px-2 py-0.5 text-[10px] font-extrabold bg-sky-500 text-white rounded-full">{{ filteredRosterData.length }} Data</span>
          </h3>
          <button type="button" @click="resetRosterData" class="px-3.5 py-1.5 text-xs font-bold rounded-xl bg-rose-50 text-rose-700 hover:bg-rose-100 border border-rose-200 transition flex items-center gap-1.5 shrink-0">
            <RotateCcw class="w-3.5 h-3.5" /> Reset Data
          </button>
        </div>

        <!-- Conflict Banner -->
        <div v-if="conflicts.units.length > 0 || conflicts.nrps.length > 0" class="m-4 p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-900 text-xs space-y-1">
          <div class="font-bold flex items-center gap-2">
            <AlertTriangle class="w-4 h-4 text-rose-600" />
            Peringatan Bentrok Alokasi Roster
          </div>
          <div v-if="conflicts.units.length > 0">
            <span class="font-semibold">Bentrok Unit (dipakai &gt; 1 personil):</span> {{ conflicts.units.join(', ') }}
          </div>
          <div v-if="conflicts.nrps.length > 0">
            <span class="font-semibold">Bentrok NRP (terjadwal &gt; 1 unit):</span> {{ conflicts.nrps.join(', ') }}
          </div>
        </div>

        <div class="overflow-x-auto">
          <table class="w-full text-left text-xs">
            <thead class="bg-slate-50 text-slate-500 uppercase tracking-wider text-[10px] font-bold border-b border-slate-100">
              <tr>
                <th class="px-4 py-3 w-12 text-center">No</th>
                <th class="px-4 py-3">NRP</th>
                <th class="px-4 py-3">Nama Operator</th>
                <th class="px-4 py-3">Nomor Telepon</th>
                <th class="px-4 py-3">Unit Tugas</th>
                <th class="px-4 py-3">Lokasi (CSA)</th>
                <th class="px-4 py-3 text-right w-24">Aksi</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-slate-700">
              <tr v-if="filteredRosterData.length === 0">
                <td colspan="7" class="px-4 py-10 text-center text-slate-400 text-xs">Data tidak ditemukan.</td>
              </tr>
              <tr
                v-for="(row, idx) in filteredRosterData"
                :key="row.id"
                class="hover:bg-slate-50/60 transition"
                :class="{ 'bg-rose-50/60': isNrpConflict(row.nrp) || isUnitConflict(row.unit) }"
              >
                <td class="px-4 py-3 text-center font-bold text-slate-400">{{ idx + 1 }}</td>
                <td class="px-4 py-3 font-bold text-sky-600">
                  {{ row.nrp }}
                  <span v-if="isNrpConflict(row.nrp)" class="ml-1 text-[9px] bg-rose-600 text-white font-black px-1.5 py-0.5 rounded">NRP DOBEL</span>
                </td>
                <td class="px-4 py-3 font-semibold text-slate-800">{{ row.employee_name }}</td>
                <td class="px-4 py-3">
                  <a v-if="waLink(row.phone)" :href="waLink(row.phone)" target="_blank" class="inline-flex items-center gap-1 text-emerald-600 hover:underline font-semibold">
                    <MessageCircle class="w-3.5 h-3.5" /> {{ row.phone }}
                  </a>
                  <span v-else class="text-slate-400">Tidak ada nomor</span>
                </td>
                <td class="px-4 py-3">
                  <span class="px-2 py-0.5 bg-sky-50 text-sky-700 font-bold rounded border border-sky-100">{{ row.unit }}</span>
                  <span v-if="isUnitConflict(row.unit)" class="ml-1 text-[9px] bg-rose-600 text-white font-black px-1.5 py-0.5 rounded">UNIT DOBEL</span>
                </td>
                <td class="px-4 py-3">
                  <span class="px-2 py-0.5 text-[10px] font-semibold rounded-md bg-purple-50 text-purple-700 border border-purple-200">{{ row.csa_type || '-' }}</span>
                </td>
                <td class="px-4 py-3 text-right space-x-1">
                  <button @click="openEditRoster(row)" class="p-1.5 text-slate-400 hover:text-sky-600 rounded-lg hover:bg-sky-50 transition">
                    <Pencil class="w-4 h-4" />
                  </button>
                  <button @click="deleteRosterItem(row.id)" class="p-1.5 text-slate-400 hover:text-rose-600 rounded-lg hover:bg-rose-50 transition">
                    <Trash2 class="w-4 h-4" />
                  </button>
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

    <!-- KUNCI LOKASI UNIT MODAL -->
    <div v-if="showLocationModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-xs">
      <div class="bg-white w-full max-w-2xl rounded-2xl shadow-xl border border-slate-100 p-5 space-y-4 text-xs">
        <div class="flex items-center justify-between border-b border-slate-100 pb-2">
          <h3 class="font-bold text-slate-800 text-sm flex items-center gap-2">
            <MapPin class="w-4 h-4 text-sky-500" /> Kunci Lokasi Unit
          </h3>
          <button @click="showLocationModal = false" class="p-1 hover:bg-slate-100 rounded-lg text-slate-400 hover:text-slate-600">
            <X class="w-4 h-4" />
          </button>
        </div>

        <div class="space-y-4 py-2 max-h-[70vh] overflow-y-auto">
          <p class="text-slate-500">Unit yang dikunci ke suatu lokasi akan otomatis memakai lokasi (CSA) tersebut saat sync roster, berapapun lokasi yang tertulis di sumber data.</p>

          <form @submit.prevent="submitLocationLock" class="bg-slate-50 p-4 rounded-xl border border-slate-200 space-y-3">
            <div>
              <label class="block font-bold text-slate-700 mb-1">Nama Lokasi *</label>
              <input type="text" v-model="locationLockForm.location" placeholder="Contoh: Lokasi A" required class="w-full px-3 py-2 border border-slate-300 rounded-xl bg-white" />
            </div>
            <div>
              <label class="block font-bold text-slate-700 mb-1">Daftar Unit *</label>
              <textarea v-model="locationLockForm.unitsText" placeholder="Contoh: Unit A, Unit B, Unit C (Pisahkan dengan koma)" required rows="2" class="w-full px-3 py-2 border border-slate-300 rounded-xl bg-white"></textarea>
            </div>
            <div class="flex justify-end">
              <button type="submit" class="px-4 py-2 font-semibold text-white bg-sky-600 hover:bg-sky-700 rounded-xl transition flex items-center gap-1.5">
                <Plus class="w-3.5 h-3.5" /> Tambahkan
              </button>
            </div>
          </form>

          <div class="mt-4">
            <h4 class="font-bold text-slate-800 mb-2">Daftar Lokasi Terkunci</h4>
            <div v-if="!locationLocks || locationLocks.length === 0" class="text-center text-slate-400 py-4 bg-slate-50 rounded-xl border border-dashed border-slate-200">
              Belum ada data kunci lokasi
            </div>
            <div v-else class="space-y-2">
              <div v-for="lock in locationLocks" :key="lock.id" class="flex flex-col sm:flex-row sm:items-center justify-between p-3 rounded-xl border border-slate-200 bg-white shadow-sm gap-2">
                <div>
                  <div class="font-bold text-sky-700 flex items-center gap-1.5">
                    <MapPin class="w-3.5 h-3.5" /> {{ lock.location }}
                  </div>
                  <div class="text-[10px] text-slate-500 mt-1 font-medium bg-slate-100 inline-block px-2 py-0.5 rounded-md">
                    {{ lock.units.join(', ') }}
                  </div>
                </div>
                <button @click="deleteLocationLock(lock.id)" class="p-1.5 text-rose-500 hover:bg-rose-50 rounded-lg transition self-end sm:self-auto shrink-0">
                  <Trash2 class="w-4 h-4" />
                </button>
              </div>
            </div>
          </div>
        </div>

        <div class="flex justify-end gap-2 pt-2 border-t border-slate-100">
          <button type="button" @click="showLocationModal = false" class="px-4 py-2 font-semibold bg-slate-100 rounded-xl">Tutup</button>
        </div>
      </div>
    </div>

    <!-- KUNCI KATEGORI UNIT MODAL -->
    <div v-if="showCategoryModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-xs">
      <div class="bg-white w-full max-w-2xl rounded-2xl shadow-xl border border-slate-100 p-5 space-y-4 text-xs">
        <div class="flex items-center justify-between border-b border-slate-100 pb-2">
          <h3 class="font-bold text-slate-800 text-sm flex items-center gap-2">
            <Layers class="w-4 h-4 text-sky-500" /> Kunci Kategori Unit
          </h3>
          <button @click="showCategoryModal = false" class="p-1 hover:bg-slate-100 rounded-lg text-slate-400 hover:text-slate-600">
            <X class="w-4 h-4" />
          </button>
        </div>

        <div class="space-y-4 py-2 max-h-[70vh] overflow-y-auto">
          <p class="text-slate-500">
            Kategori unit di papan setting (Haul Truck, Dump Truck, Bulldozer, Excavator, Wheel Loader, Motor Grader, dll) dideteksi otomatis dari awalan kode unit.
            Kunci unit tertentu ke sini kalau kategorinya perlu dikoreksi/dibedakan manual (mis. "EXCAVATOR SUPPORT").
          </p>

          <form @submit.prevent="submitCategoryLock" class="bg-slate-50 p-4 rounded-xl border border-slate-200 space-y-3">
            <div>
              <label class="block font-bold text-slate-700 mb-1">Nama Kategori *</label>
              <input type="text" v-model="categoryLockForm.category" placeholder="Contoh: Excavator Support" required class="w-full px-3 py-2 border border-slate-300 rounded-xl bg-white" />
            </div>
            <div>
              <label class="block font-bold text-slate-700 mb-1">Daftar Unit *</label>
              <textarea v-model="categoryLockForm.unitsText" placeholder="Contoh: PC2001, PC2002 (Pisahkan dengan koma)" required rows="2" class="w-full px-3 py-2 border border-slate-300 rounded-xl bg-white"></textarea>
            </div>
            <div class="flex justify-end">
              <button type="submit" class="px-4 py-2 font-semibold text-white bg-sky-600 hover:bg-sky-700 rounded-xl transition flex items-center gap-1.5">
                <Plus class="w-3.5 h-3.5" /> Tambahkan
              </button>
            </div>
          </form>

          <div class="mt-4">
            <h4 class="font-bold text-slate-800 mb-2">Daftar Kategori Terkunci</h4>
            <div v-if="!categoryLocks || categoryLocks.length === 0" class="text-center text-slate-400 py-4 bg-slate-50 rounded-xl border border-dashed border-slate-200">
              Belum ada data kunci kategori
            </div>
            <div v-else class="space-y-2">
              <div v-for="lock in categoryLocks" :key="lock.id" class="flex flex-col sm:flex-row sm:items-center justify-between p-3 rounded-xl border border-slate-200 bg-white shadow-sm gap-2">
                <div>
                  <div class="font-bold text-sky-700 flex items-center gap-1.5">
                    <Layers class="w-3.5 h-3.5" /> {{ lock.category }}
                  </div>
                  <div class="text-[10px] text-slate-500 mt-1 font-medium bg-slate-100 inline-block px-2 py-0.5 rounded-md">
                    {{ lock.units.join(', ') }}
                  </div>
                </div>
                <button @click="deleteCategoryLock(lock.id)" class="p-1.5 text-rose-500 hover:bg-rose-50 rounded-lg transition self-end sm:self-auto shrink-0">
                  <Trash2 class="w-4 h-4" />
                </button>
              </div>
            </div>
          </div>
        </div>

        <div class="flex justify-end gap-2 pt-2 border-t border-slate-100">
          <button type="button" @click="showCategoryModal = false" class="px-4 py-2 font-semibold bg-slate-100 rounded-xl">Tutup</button>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
