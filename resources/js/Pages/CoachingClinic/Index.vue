<script setup>
import { ref, computed } from 'vue';
import { Head, usePage } from '@inertiajs/vue3';
import { Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { Plus, Edit2, Trash2, Eye } from 'lucide-vue-next';

const props = defineProps({
  clinics: { type: Array, required: true },
  canManage: { type: Boolean, default: true },
});

const page = usePage();
const searchQuery = ref('');

const filteredClinics = computed(() => {
  if (!searchQuery.value) return props.clinics;
  const q = searchQuery.value.toLowerCase();
  return props.clinics.filter(c =>
    c.employee?.nama?.toLowerCase().includes(q) ||
    c.employee?.nrp?.toLowerCase().includes(q)
  );
});

const fatigueLabel = (level) => {
  const labels = { 0: 'Normal', 1: 'Ringan', 2: 'Sedang', 3: 'Berat' };
  return labels[level] || '-';
};

const fatigueColor = (level) => {
  if (level >= 3) return 'bg-red-100 text-red-700';
  if (level >= 2) return 'bg-amber-100 text-amber-700';
  return 'bg-emerald-100 text-emerald-700';
};
</script>

<template>
  <Head title="Coaching Clinic" />
  <AppLayout>
    <div class="space-y-6">
      <div>
        <h1 class="text-3xl font-bold text-slate-900">Daftar Coaching Clinic</h1>
        <p class="mt-1 text-slate-600">Kelola sesi coaching dan development untuk setiap anak asuh</p>
      </div>

      <div class="flex gap-4">
        <input
          v-model="searchQuery"
          type="text"
          placeholder="Cari NRP atau nama karyawan..."
          class="flex-1 rounded-lg border border-slate-300 px-4 py-2 text-sm focus:border-blue-500 focus:outline-none"
        />
        <Link v-if="canManage" :href="route('coaching-clinic.create')" class="flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">
          <Plus class="h-4 w-4" /> Tambah Coaching
        </Link>
      </div>

      <div v-if="filteredClinics.length === 0" class="rounded-lg border border-slate-200 bg-white p-8 text-center">
        <p class="text-slate-500">Belum ada data coaching clinic</p>
      </div>

      <div class="grid gap-4 md:grid-cols-2">
        <div
          v-for="clinic in filteredClinics"
          :key="clinic.id"
          class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm hover:shadow-md transition-shadow"
        >
          <div class="flex items-start justify-between gap-4">
            <div class="flex-1">
              <div class="font-semibold text-slate-900">{{ clinic.employee?.nama }}</div>
              <div class="text-xs text-slate-500">{{ clinic.employee?.nrp }}</div>
              <div class="mt-3 space-y-1 text-sm">
                <div><span class="font-medium text-slate-600">Tanggal:</span> {{ clinic.tanggal }}</div>
                <div><span class="font-medium text-slate-600">Lokasi:</span> {{ clinic.lokasi }}</div>
                <div><span class="font-medium text-slate-600">Coach:</span> {{ clinic.coach?.name || '-' }}</div>
                <div class="flex items-center gap-2">
                  <span class="font-medium text-slate-600">Fatigue:</span>
                  <span :class="['rounded-full px-2 py-0.5 text-xs font-semibold', fatigueColor(clinic.fatigue_level)]">
                    {{ fatigueLabel(clinic.fatigue_level) }}
                  </span>
                </div>
              </div>
            </div>
          </div>

          <div class="mt-4 flex gap-2 border-t border-slate-100 pt-4">
            <Link :href="route('coaching-clinic.show', clinic.id)" class="flex flex-1 items-center justify-center gap-2 rounded-md bg-blue-50 px-3 py-2 text-sm font-medium text-blue-600 hover:bg-blue-100">
              <Eye class="h-4 w-4" /> Lihat
            </Link>
            <Link v-if="canManage" :href="route('coaching-clinic.edit', clinic.id)" class="flex flex-1 items-center justify-center gap-2 rounded-md bg-amber-50 px-3 py-2 text-sm font-medium text-amber-600 hover:bg-amber-100">
              <Edit2 class="h-4 w-4" /> Edit
            </Link>
            <Link v-if="canManage" :href="route('coaching-clinic.destroy', clinic.id)" method="delete" as="button" class="flex flex-1 items-center justify-center gap-2 rounded-md bg-red-50 px-3 py-2 text-sm font-medium text-red-600 hover:bg-red-100">
              <Trash2 class="h-4 w-4" /> Hapus
            </Link>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
