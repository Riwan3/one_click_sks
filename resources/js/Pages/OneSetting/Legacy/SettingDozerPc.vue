<script setup>
import { confirmDialog } from '@/utils/confirm';
import { ref, computed } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { Settings, Search, Trash2, Zap, History, Target } from 'lucide-vue-next';

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
  router.get('/one-setting/dozer-pc', { date: filterDate.value, shift: filterShift.value }, { preserveState: true });
};

const form = useForm({
  date: filterDate.value,
  shift: filterShift.value,
  raw_text: '',
});

const submitParse = () => {
  form.date = filterDate.value;
  form.shift = filterShift.value;
  form.post('/one-setting/dozer-pc', {
    preserveScroll: true,
    onSuccess: () => { form.reset('raw_text'); }
  });
};

const deleteItem = async (id) => {
  if (await confirmDialog('Hapus unit ini?')) {
    router.delete(`/one-setting/dozer-pc/${id}`, { preserveScroll: true });
  }
};

const groupedRows = computed(() => {
    const groups = {};
    props.rows.forEach(r => {
        const cat = r.kategori || 'LAINNYA';
        if (!groups[cat]) groups[cat] = [];
        groups[cat].push(r);
    });
    return groups;
});
</script>

<template>
  <Head title="Posisi Dozer & PC - One-Setting" />
  <AppLayout :user="user" :notif-counts="notifCounts" :menu="menu">
    <div class="space-y-6">
      <div class="bg-gradient-to-r from-slate-900 to-sky-950 text-white p-6 rounded-2xl shadow-xl flex items-center gap-4">
        <div class="w-12 h-12 rounded-2xl bg-sky-500/20 flex items-center justify-center shrink-0">
          <Target class="w-6 h-6 text-sky-400" />
        </div>
        <div>
          <h1 class="text-xl font-black">POSISI DOZER & PC</h1>
          <p class="text-xs text-sky-300">Update posisi alat berat per kategori (Dozer, Excavator)</p>
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
            <select @change="router.get('/one-setting/dozer-pc', { date: $event.target.value.split('|')[0], shift: $event.target.value.split('|')[1] })" class="px-3 py-1.5 border border-slate-300 rounded-lg text-xs bg-slate-50 w-48">
                <option value="">-- Riwayat Tersimpan --</option>
                <option v-for="h in history" :key="h.tanggal+h.shift" :value="`${h.tanggal}|${h.shift}`">
                    {{ h.tanggal }} - Shift {{ h.shift }}
                </option>
            </select>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <div class="lg:col-span-5 bg-white rounded-2xl border border-slate-200 shadow-sm p-5 space-y-4">
            <div class="flex items-center gap-2 border-b border-slate-100 pb-3">
                <Zap class="w-5 h-5 text-amber-500" />
                <h3 class="font-bold text-slate-800">Parse Otomatis dari Teks Posisi</h3>
            </div>
            <form @submit.prevent="submitParse" class="space-y-4">
                <div>
                    <p class="text-[10px] text-slate-500 mb-2">Format: - Dz 1544 : Rom 3</p>
                    <textarea v-model="form.raw_text" rows="16" class="w-full text-xs font-mono p-3 border border-slate-300 rounded-xl bg-slate-50" placeholder="*UPDATE POSISI DOZER*&#10;- Dz 1544   : Prancis&#10;&#10;*UPDATE POSISI PC*&#10;- Ex 337  : Rom 2"></textarea>
                </div>
                <button type="submit" :disabled="form.processing" class="w-full py-2.5 bg-amber-500 hover:bg-amber-600 text-white font-bold rounded-xl text-sm transition">
                    Parse & Simpan ({{ filterDate }} Shift {{ filterShift }})
                </button>
            </form>
        </div>

        <div class="lg:col-span-7 bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="bg-indigo-900 p-4 text-white">
                <h3 class="font-bold">Data Tersimpan - {{ filterDate }} Shift {{ filterShift }}</h3>
                <p class="text-xs text-indigo-200">{{ rows.length }} Unit</p>
            </div>
            
            <div v-if="!rows.length" class="p-8 text-center text-slate-400 font-medium">
                Belum ada data. Paste teks posisi di sebelah kiri.
            </div>
            <div v-else class="divide-y divide-slate-100">
                <div v-for="(units, kategori) in groupedRows" :key="kategori" class="p-4">
                    <h4 class="font-bold text-indigo-800 text-sm mb-3">
                        {{ kategori }}
                    </h4>
                    <table class="w-full text-left text-xs bg-slate-50 rounded-xl overflow-hidden">
                        <thead class="bg-slate-100 font-bold text-slate-600">
                            <tr>
                                <th class="px-3 py-2 w-10">#</th>
                                <th class="px-3 py-2">Unit</th>
                                <th class="px-3 py-2">Label Asli</th>
                                <th class="px-3 py-2">Posisi</th>
                                <th class="px-3 py-2 text-right"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="(row, i) in units" :key="row.id" class="hover:bg-white">
                                <td class="px-3 py-2 text-slate-400">{{ i + 1 }}</td>
                                <td class="px-3 py-2 font-bold"><span class="bg-slate-800 text-white px-2 py-0.5 rounded">{{ row.unit }}</span></td>
                                <td class="px-3 py-2 text-slate-400">{{ row.unit_label }}</td>
                                <td class="px-3 py-2 font-semibold text-slate-700">{{ row.posisi }}</td>
                                <td class="px-3 py-2 text-right">
                                    <button @click="deleteItem(row.id)" class="text-rose-500 hover:text-rose-700"><Trash2 class="w-4 h-4"/></button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
