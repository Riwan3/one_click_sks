<script setup>
import { ref } from 'vue';
import { X, Bell, Search, Clock } from 'lucide-vue-next';

defineProps({
  show: { type: Boolean, default: false },
  notifications: { type: Array, default: () => [] },
  permits: { type: Array, default: () => [] },
});

defineEmits(['close']);

const searchQuery = ref('');
</script>

<template>
  <Teleport to="body">
    <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
      <div class="bg-white w-full max-w-lg rounded-2xl shadow-2xl border border-slate-100 overflow-hidden text-xs animate-in fade-in zoom-in-95 duration-150">
        <div class="p-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
          <h3 class="font-bold text-slate-800 text-sm flex items-center gap-2">
            <Bell class="w-4 h-4 text-blue-600" />
            Notifikasi Sistem
          </h3>
          <button @click="$emit('close')" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg hover:bg-slate-100">
            <X class="w-4 h-4" />
          </button>
        </div>

        <div class="p-4 max-h-[70vh] overflow-y-auto space-y-4">
          <!-- Search bar -->
          <div class="relative">
            <Search class="w-3.5 h-3.5 absolute left-3 top-2.5 text-slate-400" />
            <input type="text" v-model="searchQuery" placeholder="Cari nama / NRP di notifikasi..." class="w-full pl-8 pr-3 py-1.5 border border-slate-300 rounded-xl bg-slate-50/50 text-xs" />
          </div>

          <!-- Section Permits -->
          <div v-if="permits && permits.length > 0" class="border border-blue-200 rounded-xl overflow-hidden bg-blue-50/30">
            <div class="p-3 bg-blue-50 border-b border-blue-200 font-bold text-blue-900 flex items-center justify-between">
              <span class="flex items-center gap-1.5">
                <Clock class="w-4 h-4 text-blue-600" /> IZIN KARYAWAN (MENUNGGU)
              </span>
              <span class="px-2 py-0.5 bg-blue-600 text-white rounded-full text-[10px] font-extrabold">{{ permits.length }}</span>
            </div>

            <div class="divide-y divide-blue-100 p-2">
              <div v-for="p in permits" :key="p.id" class="p-2 flex items-center justify-between gap-2">
                <div>
                  <span class="px-2 py-0.5 bg-blue-600 text-white font-bold rounded text-[9px] uppercase">{{ p.jenis_izin || 'Izin' }}</span>
                  <div class="font-bold text-slate-800 mt-1">{{ p.nama || p.nama_karyawan || 'Karyawan' }}</div>
                  <div class="text-[10px] text-slate-500">NRP: {{ p.nrp || '-' }} &bull; Unit: {{ p.unit || '-' }}</div>
                </div>
                <a :href="`/database/izin_karyawan/edit/${p.id}`" class="px-3 py-1 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-lg text-[10px]">
                  PROSES
                </a>
              </div>
            </div>
          </div>

          <!-- Section Other Notifications -->
          <div v-if="notifications && notifications.length > 0" class="space-y-2">
            <div v-for="(n, idx) in notifications" :key="idx" class="p-3 border border-slate-200 rounded-xl bg-white space-y-1">
              <div class="font-bold text-slate-800 flex items-center justify-between">
                <span>{{ n.title || 'Pemberitahuan Sistem' }}</span>
                <span class="text-[10px] text-slate-400 font-normal">{{ n.created_at || 'Baru' }}</span>
              </div>
              <p class="text-slate-600 text-[11px]">{{ n.message || n.penjelasan_singkat || '-' }}</p>
            </div>
          </div>

          <div v-if="(!permits || permits.length === 0) && (!notifications || notifications.length === 0)" class="text-center py-8 space-y-2 text-slate-400">
            <Bell class="w-10 h-10 mx-auto text-slate-300" />
            <p class="text-xs font-semibold text-slate-600">Tidak ada notifikasi baru saat ini.</p>
          </div>
        </div>

        <div class="p-3 bg-slate-50 border-t border-slate-100">
          <button @click="$emit('close')" class="w-full py-2 font-bold bg-slate-200 hover:bg-slate-300 text-slate-700 rounded-xl transition">
            TUTUP
          </button>
        </div>
      </div>
    </div>
  </Teleport>
</template>
