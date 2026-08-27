<script setup>
import { X, Pen, ClipboardCheck } from 'lucide-vue-next';

defineProps({
  show: { type: Boolean, default: false },
  notifications: { type: Array, default: () => [] }
});

defineEmits(['close']);
</script>

<template>
  <Teleport to="body">
    <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
      <div class="bg-white w-full max-w-lg rounded-2xl shadow-2xl border border-slate-100 overflow-hidden text-xs animate-in fade-in zoom-in-95 duration-150">
        <div class="p-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
          <h3 class="font-bold text-slate-800 text-sm flex items-center gap-2">
            <Pen class="w-4 h-4 text-blue-600" />
            Notifikasi Penandatanganan
          </h3>
          <button @click="$emit('close')" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg hover:bg-slate-100">
            <X class="w-4 h-4" />
          </button>
        </div>

        <div class="p-4 max-h-[70vh] overflow-y-auto space-y-3">
          <div v-if="notifications && notifications.length > 0" class="space-y-2">
            <div 
              v-for="sig in notifications" 
              :key="sig.id"
              class="p-3 rounded-xl border border-slate-200 bg-white shadow-xs space-y-2"
            >
              <div class="flex items-center justify-between">
                <span class="px-2 py-0.5 text-[10px] font-bold rounded uppercase" :class="sig.notif_type === 'certificate' ? 'bg-blue-100 text-blue-800' : 'bg-rose-100 text-rose-800'">
                  {{ sig.role_label || 'Penandatangan' }}
                </span>
                <span class="text-[10px] text-slate-400">{{ sig.created_at || 'Baru' }}</span>
              </div>

              <div class="font-bold text-slate-800">
                {{ sig.notif_type === 'certificate' ? 'Anda perlu menandatangani sertifikat evaluasi' : 'Anda perlu menandatangani pengajuan pelatihan' }}
              </div>

              <div class="text-[11px] text-slate-500">
                {{ sig.jenis_pengajuan || 'Dokumen Pelatihan' }} &bull; {{ sig.type_unit || 'General' }}
              </div>

              <div class="flex items-center justify-end gap-2 pt-1 border-t border-slate-100">
                <a href="/one-procedure/evaluasi" class="px-3 py-1 text-[11px] font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-lg flex items-center gap-1">
                  <ClipboardCheck class="w-3.5 h-3.5" /> Lihat Assessment
                </a>
                <button class="px-3 py-1 text-[11px] font-bold text-white bg-rose-600 hover:bg-rose-700 rounded-lg flex items-center gap-1">
                  <Pen class="w-3.5 h-3.5" /> TTD Sekarang
                </button>
              </div>
            </div>
          </div>

          <div v-else class="text-center py-8 space-y-2 text-slate-400">
            <ClipboardCheck class="w-10 h-10 mx-auto text-emerald-500" />
            <p class="text-xs font-semibold text-slate-600">Tidak ada dokumen yang perlu ditandatangani.</p>
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
