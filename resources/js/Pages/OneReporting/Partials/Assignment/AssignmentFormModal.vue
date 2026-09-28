<script setup>
import { computed, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import Swal from 'sweetalert2';
import { X, Plus, Trash2, Save, PlusCircle, Pencil } from 'lucide-vue-next';

const props = defineProps({
  assignment: { type: Object, default: null },
  defaults: { type: Object, required: true },
  areaOptions: { type: Array, default: () => [] },
  unitOptions: { type: Array, default: () => [] },
  aktivitasOptions: { type: Array, default: () => [] },
  pengawasOptions: { type: Array, default: () => [] },
  shiftOptions: { type: Array, default: () => [] },
});

const emit = defineEmits(['closed']);

const MAX_TASKS = 5;
const isEdit = computed(() => !!props.assignment);

const form = useForm({
  tanggal: props.assignment?.tanggal ?? props.defaults.tanggal,
  shift: props.assignment?.shift ?? (props.defaults.shift === 'Shift 2' ? 'Shift 2' : 'Shift 1'),
  area: props.assignment?.area ?? '',
  unit: props.assignment?.unit ?? '',
  aktivitas: props.assignment?.tasks.length ? [...props.assignment.tasks] : [''],
  pengawas: props.assignment?.pengawas ?? '',
  keterangan: props.assignment?.keterangan ?? '',
  status: props.assignment?.status ?? 'belum',
});

/** Tambahkan nilai lama yang tidak ada di master agar tetap tampil saat edit (sama seperti v1). */
const withCurrent = (options, current) => (current && !options.includes(current) ? [current, ...options] : options);

const areas = computed(() => withCurrent(props.areaOptions, props.assignment?.area));
const pengawasList = computed(() => withCurrent(props.pengawasOptions, props.assignment?.pengawas));
const units = computed(() => {
  const filtered = props.unitOptions.filter((u) => !form.area || u.area === form.area);
  const current = props.assignment?.unit;
  return current && !props.unitOptions.some((u) => u.value === current) ? [{ value: current, label: current }, ...filtered] : filtered;
});

const fieldClass = 'w-full rounded-lg border border-slate-200 px-3 py-1.5 text-sm focus:border-blue-400 focus:ring-2 focus:ring-blue-100';
const labelClass = 'mb-1 block text-[13px] font-semibold text-slate-700';

const addTask = () => {
  if (form.aktivitas.length >= MAX_TASKS) {
    Swal.fire({ icon: 'warning', text: `Maksimal ${MAX_TASKS} task per assignment.` });
    return;
  }
  form.aktivitas.push('');
};

const removeTask = (idx) => {
  if (form.aktivitas.length > 1) form.aktivitas.splice(idx, 1);
};

const handleSubmit = () => {
  const options = { preserveScroll: true, onSuccess: () => emit('closed') };
  if (isEdit.value) {
    form.put(`/one-reporting/daily/assignment/${props.assignment.id}`, options);
    return;
  }
  form.post('/one-reporting/daily/assignment', options);
};

watch(() => form.area, () => {
  if (form.unit && !units.value.some((u) => u.value === form.unit)) form.unit = '';
});
</script>

<template>
  <div class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-slate-900/60 p-4">
    <form class="my-8 w-full max-w-lg space-y-3 rounded-2xl bg-white p-5 shadow-2xl" @submit.prevent="handleSubmit">
      <div class="flex items-center justify-between">
        <h6 class="flex items-center gap-2 text-sm font-bold text-slate-800">
          <component :is="isEdit ? Pencil : PlusCircle" :class="['h-4 w-4', isEdit ? 'text-blue-600' : 'text-red-600']" />
          {{ isEdit ? 'Edit Assignment' : 'Tambah Assignment' }}
        </h6>
        <button type="button" class="text-slate-400 hover:text-slate-600" @click="emit('closed')"><X class="h-5 w-5" /></button>
      </div>

      <p v-if="Object.keys(form.errors).length" class="rounded-lg bg-red-50 p-2 text-xs text-red-700">{{ Object.values(form.errors)[0] }}</p>

      <div>
        <label :class="labelClass">Tanggal <span class="text-red-600">*</span></label>
        <input v-model="form.tanggal" type="date" required :class="fieldClass" />
      </div>
      <div>
        <label :class="labelClass">Shift <span class="text-red-600">*</span></label>
        <select v-model="form.shift" required :class="fieldClass">
          <option v-for="s in shiftOptions" :key="s" :value="s">{{ s }}</option>
        </select>
      </div>
      <div>
        <label :class="labelClass">Area <span class="text-red-600">*</span></label>
        <select v-model="form.area" required :class="fieldClass">
          <option value="">-- Pilih Area --</option>
          <option v-for="a in areas" :key="a" :value="a">{{ a }}</option>
        </select>
      </div>
      <div>
        <label :class="labelClass">Unit <span class="text-red-600">*</span></label>
        <select v-model="form.unit" required :class="fieldClass">
          <option value="">-- Pilih Unit --</option>
          <option v-for="u in units" :key="u.value" :value="u.value">{{ u.label }}</option>
        </select>
      </div>
      <div>
        <div class="mb-1 flex items-center justify-between">
          <label class="text-[13px] font-semibold text-slate-700">Aktivitas / Task</label>
          <button type="button" class="inline-flex items-center gap-1 rounded-md border border-blue-500 px-2.5 py-0.5 text-[11px] text-blue-600 hover:bg-blue-50" @click="addTask">
            <Plus class="h-3 w-3" />Tambah Task
          </button>
        </div>
        <div v-for="(task, idx) in form.aktivitas" :key="idx" class="mb-2 flex items-center gap-2">
          <span class="flex h-[22px] w-[22px] shrink-0 items-center justify-center rounded-full bg-indigo-100 text-[11px] font-bold text-indigo-800">{{ idx + 1 }}</span>
          <select v-model="form.aktivitas[idx]" :class="fieldClass">
            <option value="">-- Pilih Aktivitas --</option>
            <option v-if="task && !aktivitasOptions.some((a) => a.value === task)" :value="task">{{ task }}</option>
            <option v-for="a in aktivitasOptions" :key="a.value" :value="a.value">{{ a.label }}</option>
          </select>
          <button
            type="button"
            :disabled="form.aktivitas.length <= 1"
            class="shrink-0 rounded-md border border-red-400 p-1.5 text-red-600 hover:bg-red-50 disabled:opacity-40"
            @click="removeTask(idx)"
          >
            <Trash2 class="h-3 w-3" />
          </button>
        </div>
      </div>
      <div>
        <label :class="labelClass">Nama Pengawas <span class="text-red-600">*</span></label>
        <select v-model="form.pengawas" required :class="fieldClass">
          <option value="">-- Pilih Pengawas --</option>
          <option v-for="p in pengawasList" :key="p" :value="p">{{ p }}</option>
        </select>
      </div>
      <div>
        <label :class="labelClass">Keterangan</label>
        <textarea v-model="form.keterangan" rows="2" placeholder="Keterangan tambahan..." :class="fieldClass" />
      </div>
      <div v-if="isEdit">
        <label :class="labelClass">Status</label>
        <select v-model="form.status" :class="fieldClass">
          <option value="belum">Belum Dilaksanakan</option>
          <option value="progress">Progress</option>
          <option value="selesai">Selesai</option>
        </select>
      </div>

      <div class="flex justify-end gap-2 pt-1">
        <button type="button" class="rounded-lg border border-slate-400 px-3 py-1.5 text-sm text-slate-600" @click="emit('closed')">Batal</button>
        <button
          type="submit"
          :disabled="form.processing"
          :class="['inline-flex items-center gap-1 rounded-lg px-4 py-1.5 text-sm font-semibold text-white disabled:opacity-60', isEdit ? 'bg-blue-600 hover:bg-blue-700' : 'bg-red-600 hover:bg-red-700']"
        >
          <Save class="h-3.5 w-3.5" />{{ isEdit ? 'Simpan Perubahan' : 'Simpan' }}
        </button>
      </div>
    </form>
  </div>
</template>
