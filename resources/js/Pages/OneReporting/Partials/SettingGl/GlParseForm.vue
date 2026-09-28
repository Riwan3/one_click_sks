<script setup>
import { useForm } from '@inertiajs/vue3';
import { Wand2, Truck, UserSquare, Zap } from 'lucide-vue-next';

const props = defineProps({
  tanggal: { type: String, required: true },
  shift: { type: String, required: true },
});

const FRONT_PLACEHOLDER = 'JL.JERMAN ARAH SPAYOL\nE 2046  Irfan\nE 3904  Taufik\n\nIPPKH Atas / Bawah\nE 2042  Agung\nE 6107  Gutasean\n...\n\n(atau format kolom Tab: Unit [Tab] PIC)';
const DISPOSAL_PLACEHOLDER = 'Disposal Monte RL +120\tArif\nDisposal Barat Monte\tMuh Ridwan Dali\n\n(atau format teks: judul Area lalu baris E 1234  Nama di bawahnya)';

const form = useForm({ tanggal: props.tanggal, shift: props.shift, disposal_text: '', front_text: '' });

const textareaClass = 'w-full rounded-lg border border-slate-200 p-2 font-mono text-xs focus:border-amber-400 focus:ring-2 focus:ring-amber-100';

const handleSubmit = () => form.post('/one-reporting/setting-gl/parse', { preserveScroll: true, onSuccess: () => form.reset('disposal_text', 'front_text') });
</script>

<template>
  <div class="overflow-hidden rounded-2xl bg-white shadow-sm">
    <h6 class="flex items-center gap-2 bg-amber-50 px-4 py-3 text-sm font-bold text-slate-800"><Wand2 class="h-4 w-4" />Parse Otomatis dari Teks Alokasi / Excel</h6>
    <form class="space-y-3 p-4" @submit.prevent="handleSubmit">
      <div class="flex flex-wrap gap-3">
        <div>
          <label class="mb-1 block text-xs font-semibold text-slate-700">Tanggal</label>
          <input v-model="form.tanggal" type="date" required class="w-40 rounded-lg border border-slate-200 px-3 py-1.5 text-sm" />
        </div>
        <div>
          <label class="mb-1 block text-xs font-semibold text-slate-700">Shift</label>
          <input v-model="form.shift" type="text" list="gl-shift-options" required class="w-32 rounded-lg border border-slate-200 px-3 py-1.5 text-sm" />
        </div>
      </div>

      <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
        <div>
          <label class="flex items-center gap-1 text-[13px] font-semibold text-slate-800"><Truck class="h-4 w-4 text-amber-500" />Penanggung Jawab Area Disposal</label>
          <p class="mb-1 text-xs text-slate-500">
            Bisa paste teks alokasi (judul Area, lalu <code class="text-pink-600">Pic : Nama</code>, lalu baris <code class="text-pink-600">- E1234</code> per unit — baris
            <code class="text-pink-600">Fleet :</code> otomatis diabaikan; sama seperti sheet lama), atau format kolom Tab dari Excel:
            <code class="text-pink-600">Area</code> [Tab] <code class="text-pink-600">PIC</code> (PIC untuk 1 area penuh) atau
            <code class="text-pink-600">Area</code> [Tab] <code class="text-pink-600">Unit</code> [Tab] <code class="text-pink-600">PIC</code> (PIC per unit spesifik).
          </p>
          <textarea v-model="form.disposal_text" rows="18" :placeholder="DISPOSAL_PLACEHOLDER" :class="textareaClass" />
        </div>
        <div>
          <label class="flex items-center gap-1 text-[13px] font-semibold text-slate-800"><UserSquare class="h-4 w-4 text-blue-600" />Penanggung Jawab Masing-Masing Area Front</label>
          <p class="mb-1 text-xs text-slate-500">
            Bisa paste teks alokasi (judul Area lalu baris <code class="text-pink-600">E 1234  Nama</code> di bawahnya — sama seperti sheet lama), atau format kolom Tab dari Excel:
            <code class="text-pink-600">Unit</code> [Tab] <code class="text-pink-600">PIC</code> atau
            <code class="text-pink-600">Area</code> [Tab] <code class="text-pink-600">Unit</code> [Tab] <code class="text-pink-600">PIC</code>.
          </p>
          <textarea v-model="form.front_text" rows="18" :placeholder="FRONT_PLACEHOLDER" :class="textareaClass" />
        </div>
      </div>

      <div class="flex flex-wrap items-center gap-2">
        <button type="submit" :disabled="form.processing" class="inline-flex items-center gap-1 rounded-lg bg-amber-400 px-3 py-1.5 text-sm font-semibold text-slate-900 hover:bg-amber-500 disabled:opacity-60">
          <Zap class="h-4 w-4" />Parse & Simpan Otomatis
        </button>
        <small class="text-xs text-slate-500">Data lama untuk tanggal & shift yang sama akan dihapus dan diganti data baru. Tanggal/shift lain tidak terpengaruh.</small>
      </div>
    </form>
  </div>
</template>
