<script setup>
import { confirmDialog } from '@/utils/confirm';
import { ref } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { Settings, Trash2, Zap, KeyRound } from 'lucide-vue-next';

const props = defineProps({
  user: Object,
  notifCounts: Object,
  menu: Array,
  rows: Array,
});

const form = useForm({
  raw_text: '',
});

const submitParse = () => {
  form.post('/one-setting/pw-cp', {
    preserveScroll: true,
    onSuccess: () => { form.reset('raw_text'); }
  });
};

const deleteItem = async (id) => {
  if (await confirmDialog('Hapus password ini?')) {
    router.delete(`/one-setting/pw-cp/${id}`, { preserveScroll: true });
  }
};

const clearAll = async () => {
  if (await confirmDialog('Hapus SEMUA data password?')) {
    router.post('/one-setting/pw-cp/clear', {}, { preserveScroll: true });
  }
};
</script>

<template>
  <Head title="Password CP - One-Setting" />
  <AppLayout :user="user" :notif-counts="notifCounts" :menu="menu">
    <div class="space-y-6">
      <div class="bg-gradient-to-r from-slate-900 to-sky-950 text-white p-6 rounded-2xl shadow-xl flex items-center gap-4">
        <div class="w-12 h-12 rounded-2xl bg-sky-500/20 flex items-center justify-center shrink-0">
          <KeyRound class="w-6 h-6 text-sky-400" />
        </div>
        <div>
          <h1 class="text-xl font-black">PASSWORD SPEAK UP CP</h1>
          <p class="text-xs text-sky-300">Tempel data dari excel untuk update password per hari</p>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <div class="lg:col-span-5 bg-white rounded-2xl border border-slate-200 shadow-sm p-5 space-y-4">
            <div class="flex items-center gap-2 border-b border-slate-100 pb-3">
                <Zap class="w-5 h-5 text-amber-500" />
                <h3 class="font-bold text-slate-800">Import dari Excel</h3>
            </div>
            <form @submit.prevent="submitParse" class="space-y-4">
                <div>
                    <p class="text-[10px] text-slate-500 mb-2">Kolom Excel: NO | TANGGAL | SHIFT | PASSWORD</p>
                    <textarea v-model="form.raw_text" rows="16" class="w-full text-xs font-mono p-3 border border-slate-300 rounded-xl bg-slate-50" placeholder="1  2026-07-01  I  Safety First&#10;   2026-07-01  II Pakai Helm"></textarea>
                </div>
                <button type="submit" :disabled="form.processing" class="w-full py-2.5 bg-amber-500 hover:bg-amber-600 text-white font-bold rounded-xl text-sm transition">
                    Parse & Simpan
                </button>
            </form>
        </div>

        <div class="lg:col-span-7 bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="bg-red-900 p-4 text-white flex justify-between items-center">
                <div>
                    <h3 class="font-bold">Data Password Tersimpan</h3>
                    <p class="text-xs text-red-200">{{ rows.length }} Data</p>
                </div>
                <button v-if="rows.length" @click="clearAll" class="px-3 py-1.5 bg-red-800 hover:bg-red-700 text-xs font-bold rounded-lg border border-red-700 transition flex gap-1 items-center">
                    <Trash2 class="w-3.5 h-3.5" /> Hapus Semua
                </button>
            </div>
            
            <div class="overflow-x-auto max-h-[600px] overflow-y-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 border-b border-slate-200 font-bold text-slate-600 sticky top-0">
                        <tr>
                            <th class="px-4 py-3">#</th>
                            <th class="px-4 py-3">Tanggal</th>
                            <th class="px-4 py-3">Shift</th>
                            <th class="px-4 py-3">Password</th>
                            <th class="px-4 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr v-for="(row, i) in rows" :key="row.id" class="hover:bg-slate-50">
                            <td class="px-4 py-2 text-slate-400">{{ i + 1 }}</td>
                            <td class="px-4 py-2 font-bold">{{ row.tanggal }}</td>
                            <td class="px-4 py-2">
                                <span :class="['px-2 py-0.5 rounded text-[10px] font-bold', row.shift === '2' ? 'bg-purple-100 text-purple-700' : 'bg-blue-100 text-blue-700']">
                                    Shift {{ row.shift }}
                                </span>
                            </td>
                            <td class="px-4 py-2 font-semibold text-slate-800">{{ row.password }}</td>
                            <td class="px-4 py-2 text-right">
                                <button @click="deleteItem(row.id)" class="text-rose-500 hover:text-rose-700"><Trash2 class="w-4 h-4"/></button>
                            </td>
                        </tr>
                        <tr v-if="!rows.length">
                            <td colspan="5" class="p-8 text-center text-slate-400 font-medium">Belum ada data. Silakan paste dari Excel di sebelah kiri.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
