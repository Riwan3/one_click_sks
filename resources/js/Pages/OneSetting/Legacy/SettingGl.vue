<script setup>
import { confirmDialog } from '@/utils/confirm';
import { ref } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { Settings, Search, Trash2, Zap, History } from 'lucide-vue-next';

const props = defineProps({
  user: Object,
  notifCounts: Object,
  menu: Array,
  filters: Object,
  rows: Array,
  history: Array,
});

const filterDate = ref(props.filters.date);
const filterShift = ref(props.filters.shift);

const applyFilter = () => {
  router.get('/one-setting/gl', { date: filterDate.value, shift: filterShift.value }, { preserveState: true });
};

const form = useForm({
  date: filterDate.value,
  shift: filterShift.value,
  front_text: '',
  disposal_text: '',
});

const submitParse = () => {
  form.date = filterDate.value;
  form.shift = filterShift.value;
  form.post('/one-setting/gl', {
    preserveScroll: true,
    onSuccess: () => {
      form.reset('front_text', 'disposal_text');
    }
  });
};

const deleteItem = async (id) => {
  if (await confirmDialog('Hapus mapping unit ini?')) {
    router.delete(`/one-setting/gl/${id}`, { preserveScroll: true });
  }
};
</script>

<template>
  <Head title="Settingan GL - One-Setting" />
  <AppLayout :user="user" :notif-counts="notifCounts" :menu="menu">
    <div class="space-y-6">
      <div class="bg-gradient-to-r from-slate-900 to-sky-950 text-white p-6 rounded-2xl shadow-xl flex items-center gap-4">
        <div class="w-12 h-12 rounded-2xl bg-sky-500/20 flex items-center justify-center shrink-0">
          <Settings class="w-6 h-6 text-sky-400" />
        </div>
        <div>
          <h1 class="text-xl font-black">SETTINGAN GL (FRONT & DISPOSAL)</h1>
          <p class="text-xs text-sky-300">Parse text alokasi unit dari WhatsApp ke Database</p>
        </div>
      </div>

      <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm flex flex-wrap gap-4 items-end">
        <div>
          <label class="block text-xs font-bold text-slate-600 mb-1">Tanggal</label>
          <input type="date" v-model="filterDate" @change="applyFilter" class="px-3 py-1.5 border border-slate-300 rounded-lg text-sm bg-slate-50" />
        </div>
        <div>
          <label class="block text-xs font-bold text-slate-600 mb-1">Shift</label>
          <select v-model="filterShift" @change="applyFilter" class="px-3 py-1.5 border border-slate-300 rounded-lg text-sm bg-slate-50">
            <option value="1">Shift 1 (Siang)</option>
            <option value="2">Shift 2 (Malam)</option>
          </select>
        </div>
        
        <div class="ml-auto flex items-center gap-2">
            <History class="w-4 h-4 text-slate-400" />
            <select @change="router.get('/one-setting/gl', { date: $event.target.value.split('|')[0], shift: $event.target.value.split('|')[1] })" class="px-3 py-1.5 border border-slate-300 rounded-lg text-xs bg-slate-50 w-48">
                <option value="">-- Riwayat Tersimpan --</option>
                <option v-for="h in history" :key="h.tanggal+h.shift" :value="`${h.tanggal}|${h.shift}`">
                    {{ h.tanggal }} - Shift {{ h.shift }}
                </option>
            </select>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5 space-y-4">
            <div class="flex items-center gap-2 border-b border-slate-100 pb-3">
                <Zap class="w-5 h-5 text-amber-500" />
                <h3 class="font-bold text-slate-800">Parse Otomatis dari Teks</h3>
            </div>
            <form @submit.prevent="submitParse" class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Penanggung Jawab Front</label>
                        <p class="text-[10px] text-slate-500 mb-2">Format: E 1234 Nama</p>
                        <textarea v-model="form.front_text" rows="12" class="w-full text-xs font-mono p-3 border border-slate-300 rounded-xl bg-slate-50" placeholder="E 2046 Irfan"></textarea>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Pic Area Disposal</label>
                        <p class="text-[10px] text-slate-500 mb-2">Format: Pic: Nama \n - E1234</p>
                        <textarea v-model="form.disposal_text" rows="12" class="w-full text-xs font-mono p-3 border border-slate-300 rounded-xl bg-slate-50" placeholder="Pic : Dede&#10;- E2039"></textarea>
                    </div>
                </div>
                <button type="submit" :disabled="form.processing" class="w-full py-2.5 bg-amber-500 hover:bg-amber-600 text-white font-bold rounded-xl text-sm transition">
                    Parse & Simpan ({{ filterDate }} Shift {{ filterShift }})
                </button>
            </form>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="bg-emerald-800 p-4 text-white">
                <h3 class="font-bold">Data Tersimpan - {{ filterDate }} Shift {{ filterShift }}</h3>
                <p class="text-xs text-emerald-200">{{ rows.length }} Unit</p>
            </div>
            <div class="overflow-x-auto max-h-[600px] overflow-y-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 border-b border-slate-200 font-bold text-slate-600 sticky top-0">
                        <tr>
                            <th class="px-4 py-3">#</th>
                            <th class="px-4 py-3">Unit</th>
                            <th class="px-4 py-3">Front PIC</th>
                            <th class="px-4 py-3">Disposal PIC</th>
                            <th class="px-4 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr v-for="(row, i) in rows" :key="row.id" class="hover:bg-slate-50">
                            <td class="px-4 py-2 text-slate-400">{{ i + 1 }}</td>
                            <td class="px-4 py-2 font-bold"><span class="bg-slate-800 text-white px-2 py-0.5 rounded">{{ row.unit }}</span></td>
                            <td class="px-4 py-2">{{ row.front_pic || '-' }}</td>
                            <td class="px-4 py-2">{{ row.disposal_pic || '-' }}</td>
                            <td class="px-4 py-2 text-right">
                                <button @click="deleteItem(row.id)" class="text-rose-500 hover:text-rose-700"><Trash2 class="w-4 h-4"/></button>
                            </td>
                        </tr>
                        <tr v-if="!rows.length">
                            <td colspan="5" class="p-8 text-center text-slate-400 font-medium">Belum ada data. Silakan parse dari teks di sebelah kiri.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
