<script setup>
import { X, Phone, PhoneCall } from 'lucide-vue-next';

defineProps({
  show: { type: Boolean, default: false },
});

defineEmits(['close']);

const nomorPentingCategories = [
  {
    category: 'SHE & Emergency Response Team',
    items: [
      { nama: 'Pos Command Center SHE / Darurat Tambang', jabatan: 'Emergency Call 24 Jam', hp: '0811-555-911' },
      { nama: 'Klinik / Medical Center Central', jabatan: 'Tim Medis Darurat', hp: '0812-555-118' },
    ]
  },
  {
    category: 'Human Resources & Operation',
    items: [
      { nama: 'Posko Manpower & Absensi', jabatan: 'HR Admin Staff', hp: '0813-555-7788' },
      { nama: 'Dispatcher Main Control Room', jabatan: 'Mining Operator Control', hp: '0812-555-3344' },
    ]
  }
];
</script>

<template>
  <Teleport to="body">
    <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
      <div class="bg-white w-full max-w-lg rounded-2xl shadow-2xl border border-slate-100 overflow-hidden text-xs animate-in fade-in zoom-in-95 duration-150">
        <div class="p-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
          <h3 class="font-bold text-slate-800 text-sm flex items-center gap-2">
            <Phone class="w-4 h-4 text-sky-500" />
            Nomor Penting Darurat &amp; Operational
          </h3>
          <button @click="$emit('close')" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg hover:bg-slate-100">
            <X class="w-4 h-4" />
          </button>
        </div>

        <div class="p-4 max-h-[70vh] overflow-y-auto space-y-4">
          <div v-for="(cat, idx) in nomorPentingCategories" :key="idx" class="space-y-2">
            <div class="text-[10px] font-extrabold uppercase text-slate-400 tracking-wider">
              {{ cat.category }}
            </div>

            <div class="divide-y divide-slate-100 border border-slate-200 rounded-xl overflow-hidden bg-white">
              <div v-for="(item, iIdx) in cat.items" :key="iIdx" class="p-3 flex items-center justify-between gap-3">
                <div>
                  <div class="font-bold text-slate-800">{{ item.nama }}</div>
                  <div class="text-[10px] text-slate-500">{{ item.jabatan }} &bull; {{ item.hp }}</div>
                </div>

                <a :href="`https://api.whatsapp.com/send/?phone=${item.hp.replace(/[^0-9]/g, '')}`" target="_blank" class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-lg text-[10px] flex items-center gap-1">
                  <PhoneCall class="w-3.5 h-3.5" /> Hubungi
                </a>
              </div>
            </div>
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
