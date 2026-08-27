<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { 
  Users, 
  Search, 
  Calendar, 
  Clock, 
  Phone, 
  Briefcase, 
  Building2, 
  ShieldCheck, 
  Layers 
} from 'lucide-vue-next';

const props = defineProps({
  user: Object,
  notifCounts: Object,
  menu: Array,
  filters: Object,
  totalRoster: Number,
  groupedByUnit: Object,
  rosterData: Array,
});

const filterDate = ref(props.filters?.date || new Date().toISOString().split('T')[0]);
const filterShift = ref(props.filters?.shift || 'D');
const searchQuery = ref('');

const applyFilter = () => {
  router.get('/one-setting/monitoring-manpower', { date: filterDate.value, shift: filterShift.value }, { preserveState: true });
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

        <div class="flex items-center gap-3">
          <div class="bg-sky-50 px-4 py-2 rounded-xl border border-sky-100 text-right">
            <span class="text-[10px] font-bold text-slate-500 uppercase">Total Roster Allocated</span>
            <p class="text-lg font-black text-sky-700">{{ totalRoster || 0 }} Unit</p>
          </div>
        </div>
      </div>

      <!-- Grid Cards Grouped by Unit -->
      <div v-if="groupedByUnit && Object.keys(groupedByUnit).length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <div 
          v-for="(items, unitName) in groupedByUnit" 
          :key="unitName"
          class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden flex flex-col"
        >
          <!-- Unit Header -->
          <div class="bg-slate-900 text-white p-3.5 flex items-center justify-between">
            <div class="flex items-center gap-2">
              <Layers class="w-4 h-4 text-sky-400" />
              <span class="font-extrabold text-sm tracking-wide">{{ unitName }}</span>
            </div>
            <span class="px-2.5 py-0.5 text-[10px] font-bold rounded-full bg-sky-500/20 text-sky-300 border border-sky-400/30">
              {{ items.length }} Operator
            </span>
          </div>

          <!-- Operator Items -->
          <div class="p-4 divide-y divide-slate-100 flex-1 space-y-3">
            <div v-for="item in items" :key="item.id" class="pt-2 first:pt-0 space-y-1.5">
              <div class="flex items-center justify-between">
                <span class="font-black text-slate-800 text-xs">{{ item.employee_name }}</span>
                <span class="text-[10px] font-bold px-2 py-0.5 rounded bg-purple-50 text-purple-700 border border-purple-200">
                  {{ item.csa_type }}
                </span>
              </div>

              <div class="grid grid-cols-2 gap-1 text-[11px] text-slate-500">
                <div class="flex items-center gap-1">
                  <ShieldCheck class="w-3 h-3 text-sky-500" />
                  <span>NRP: {{ item.nrp }}</span>
                </div>

                <div class="flex items-center gap-1">
                  <Phone class="w-3 h-3 text-emerald-500" />
                  <span>{{ item.phone }}</span>
                </div>

                <div class="flex items-center gap-1 col-span-2">
                  <Briefcase class="w-3 h-3 text-slate-400" />
                  <span>{{ item.jabatan }} - {{ item.departemen }}</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div v-else class="bg-white p-12 rounded-2xl border border-slate-200/80 shadow-sm text-center text-slate-400 space-y-2">
        <Users class="w-12 h-12 mx-auto text-slate-300" />
        <p class="text-sm font-semibold text-slate-600">Belum ada data monitoring manpower untuk tanggal &amp; shift ini.</p>
        <p class="text-xs text-slate-400">Silakan lalukan sync atau input roster di menu Settingan.</p>
      </div>
    </div>
  </AppLayout>
</template>
