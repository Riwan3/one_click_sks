<script setup>
import { computed } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { X, Save, PlusCircle, Pencil } from 'lucide-vue-next';

const props = defineProps({
  type: { type: String, required: true },
  row: { type: Object, default: null },
  areaOptions: { type: Array, default: () => [] },
  kategoriOptions: { type: Array, default: () => [] },
  documentOptions: { type: Array, default: () => [] },
});

const emit = defineEmits(['closed']);

const TITLES = { area: 'Area', unit: 'Unit/Lokasi', aktivitas: 'Aktivitas' };
const isEdit = computed(() => !!props.row);

const form = useForm({
  nama: props.row?.nama ?? '',
  kode: props.row?.kode ?? '',
  area: props.row?.area ?? '',
  kategori: props.row?.kategori ?? '',
  keterangan: props.row?.keterangan ?? '',
  dokumen: [...(props.row?.dokumen ?? [])],
  aktif: props.row ? props.row.aktif : true,
});

const areas = computed(() => (form.area && !props.areaOptions.includes(form.area) ? [form.area, ...props.areaOptions] : props.areaOptions));

const fieldClass = 'w-full rounded-lg border border-slate-200 px-3 py-1.5 text-sm focus:border-blue-400 focus:ring-2 focus:ring-blue-100';
const labelClass = 'mb-1 block text-[13px] font-semibold text-slate-700';

const handleSubmit = () => {
  const url = `/one-reporting/master/${props.type}`;
  const options = { preserveScroll: true, onSuccess: () => emit('closed') };
  if (isEdit.value) {
    form.put(`${url}/${props.row.id}`, options);
    return;
  }
  form.post(url, options);
};
</script>

<template>
  <div class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-slate-900/60 p-4">
    <form class="my-8 w-full max-w-lg space-y-3 rounded-2xl bg-white p-5 shadow-2xl" @submit.prevent="handleSubmit">
      <div class="flex items-center justify-between">
        <h6 class="flex items-center gap-2 text-sm font-bold text-slate-800">
          <component :is="isEdit ? Pencil : PlusCircle" :class="['h-4 w-4', isEdit ? 'text-blue-600' : 'text-red-600']" />
          {{ isEdit ? 'Edit' : 'Tambah' }} {{ TITLES[type] }}
        </h6>
        <button type="button" class="text-slate-400 hover:text-slate-600" @click="emit('closed')"><X class="h-5 w-5" /></button>
      </div>

      <div v-if="type === 'unit'">
        <label :class="labelClass">Kode Unit</label>
        <input v-model="form.kode" type="text" placeholder="Contoh: DT-001, EX-023" :class="fieldClass" />
      </div>

      <div>
        <label :class="labelClass">Nama {{ TITLES[type] }} <span class="text-red-600">*</span></label>
        <input
          v-model="form.nama"
          type="text"
          required
          :placeholder="{ area: 'Contoh: MFOB, Disposal...', unit: 'Nama unit atau lokasi...', aktivitas: 'Nama aktivitas...' }[type]"
          :class="fieldClass"
        />
        <p v-if="form.errors.nama" class="mt-1 text-[11px] text-red-600">{{ form.errors.nama }}</p>
      </div>

      <div v-if="type === 'unit'">
        <label :class="labelClass">Area</label>
        <select v-model="form.area" :class="fieldClass">
          <option value="">-- Pilih Area --</option>
          <option v-for="a in areas" :key="a" :value="a">{{ a }}</option>
        </select>
      </div>

      <template v-if="type === 'aktivitas'">
        <div>
          <label :class="labelClass">Kategori</label>
          <input v-model="form.kategori" type="text" list="odr-kategori-list" placeholder="Contoh: GMP, Safety, Produksi..." :class="fieldClass" />
          <datalist id="odr-kategori-list"><option v-for="k in kategoriOptions" :key="k" :value="k" /></datalist>
        </div>
        <div>
          <label :class="labelClass">Dokumen Prosedur (Opsional)</label>
          <div class="max-h-[150px] overflow-y-auto rounded-lg border border-slate-200 p-2">
            <p v-if="documentOptions.length === 0" class="text-xs text-slate-400">Belum ada dokumen prosedur.</p>
            <label v-for="doc in documentOptions" :key="doc.id" class="mb-1 flex items-start gap-2 text-xs text-slate-700">
              <input v-model="form.dokumen" type="checkbox" :value="doc.file_dokumen" class="mt-0.5 rounded" />
              {{ doc.judul }}
            </label>
          </div>
          <p v-if="form.errors['dokumen.0']" class="mt-1 text-[11px] text-red-600">{{ form.errors['dokumen.0'] }}</p>
        </div>
      </template>

      <div v-else>
        <label :class="labelClass">Keterangan</label>
        <textarea v-model="form.keterangan" rows="2" :class="fieldClass" />
      </div>

      <div v-if="isEdit">
        <label :class="labelClass">Status</label>
        <select v-model="form.aktif" :class="fieldClass">
          <option :value="true">Aktif</option>
          <option :value="false">Nonaktif</option>
        </select>
      </div>

      <div class="flex justify-end gap-2 pt-1">
        <button type="button" class="rounded-lg border border-slate-400 px-3 py-1.5 text-sm text-slate-600" @click="emit('closed')">Batal</button>
        <button
          type="submit"
          :disabled="form.processing"
          :class="['inline-flex items-center gap-1 rounded-lg px-4 py-1.5 text-sm font-semibold text-white disabled:opacity-60', isEdit ? 'bg-blue-600 hover:bg-blue-700' : 'bg-red-600 hover:bg-red-700']"
        >
          <Save class="h-3.5 w-3.5" />Simpan
        </button>
      </div>
    </form>
  </div>
</template>
