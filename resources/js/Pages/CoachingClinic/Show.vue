<script setup>
import { computed } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import { ArrowLeft } from 'lucide-vue-next'

const props = defineProps({
  clinic: Object,
})

const fatigueLabel = (level) => {
  const labels = { 0: 'Normal', 1: 'Ringan', 2: 'Sedang', 3: 'Berat' }
  return labels[level] || '-'
}

const fatigueColor = (level) => {
  if (level >= 3) return 'bg-red-100 text-red-700'
  if (level >= 2) return 'bg-amber-100 text-amber-700'
  return 'bg-emerald-100 text-emerald-700'
}
</script>

<template>
  <Head title="Detail Coaching Clinic" />
  <AppLayout>
    <div class="space-y-6">
      <div class="flex items-center gap-4">
        <Link :href="route('coaching-clinic.index')" class="rounded-lg border border-slate-300 p-2 text-slate-600 hover:bg-slate-100">
          <ArrowLeft class="h-5 w-5" />
        </Link>
        <div>
          <h1 class="text-2xl font-bold text-slate-900">Detail Coaching Clinic</h1>
          <p class="text-slate-600">{{ clinic.employee?.nama }} ({{ clinic.employee?.nrp }})</p>
        </div>
      </div>

      <div class="grid gap-6 lg:grid-cols-3">
        <!-- Main Info -->
        <div class="lg:col-span-2 space-y-6">
          <!-- Basic Info -->
          <div class="rounded-lg border border-slate-200 bg-white p-6">
            <h2 class="mb-4 text-lg font-semibold text-slate-900">Informasi Umum</h2>
            <div class="grid gap-4 md:grid-cols-2">
              <div>
                <label class="text-xs font-semibold text-slate-500">Tanggal</label>
                <p class="mt-1 text-slate-900">{{ clinic.tanggal }}</p>
              </div>
              <div>
                <label class="text-xs font-semibold text-slate-500">Waktu Pertemuan</label>
                <p class="mt-1 text-slate-900">{{ clinic.waktu_pertemuan }}</p>
              </div>
              <div>
                <label class="text-xs font-semibold text-slate-500">Lokasi</label>
                <p class="mt-1 text-slate-900">{{ clinic.lokasi }}</p>
              </div>
              <div>
                <label class="text-xs font-semibold text-slate-500">Coach</label>
                <p class="mt-1 text-slate-900">{{ clinic.coach?.name || '-' }}</p>
              </div>
              <div>
                <label class="text-xs font-semibold text-slate-500">Fatigue Level</label>
                <div class="mt-1">
                  <span :class="['rounded-full px-2 py-0.5 text-xs font-semibold', fatigueColor(clinic.fatigue_level)]">
                    {{ fatigueLabel(clinic.fatigue_level) }}
                  </span>
                </div>
              </div>
              <div>
                <label class="text-xs font-semibold text-slate-500">Status</label>
                <p class="mt-1 text-slate-900">{{ clinic.status ? clinic.status.charAt(0).toUpperCase() + clinic.status.slice(1) : 'Published' }}</p>
              </div>
            </div>
          </div>

          <!-- Materials -->
          <div class="rounded-lg border border-slate-200 bg-white p-6">
            <h2 class="mb-4 text-lg font-semibold text-slate-900">Materi Coaching</h2>
            <div class="space-y-4">
              <div v-if="clinic.materi_pribadi">
                <label class="text-xs font-semibold text-slate-500">Materi Pribadi</label>
                <p class="mt-1 text-slate-900">{{ clinic.materi_pribadi }}</p>
              </div>
              <div v-if="clinic.materi_keluarga">
                <label class="text-xs font-semibold text-slate-500">Materi Keluarga</label>
                <p class="mt-1 text-slate-900">{{ clinic.materi_keluarga }}</p>
              </div>
              <div v-if="clinic.materi_lingkungan">
                <label class="text-xs font-semibold text-slate-500">Materi Lingkungan</label>
                <p class="mt-1 text-slate-900">{{ clinic.materi_lingkungan }}</p>
              </div>
              <div v-if="clinic.materi_pekerjaan">
                <label class="text-xs font-semibold text-slate-500">Materi Pekerjaan</label>
                <p class="mt-1 text-slate-900">{{ clinic.materi_pekerjaan }}</p>
              </div>
            </div>
          </div>

          <!-- Description & Recommendation -->
          <div class="rounded-lg border border-slate-200 bg-white p-6">
            <div class="grid gap-6 md:grid-cols-2">
              <div>
                <label class="text-xs font-semibold text-slate-500">Deskripsi</label>
                <p class="mt-1 text-slate-900">{{ clinic.deskripsi || '-' }}</p>
              </div>
              <div>
                <label class="text-xs font-semibold text-slate-500">Rekomendasi</label>
                <p class="mt-1 text-slate-900">{{ clinic.rekomendasi || '-' }}</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
          <!-- Employee Info -->
          <div class="rounded-lg border border-slate-200 bg-white p-6">
            <h2 class="mb-4 text-lg font-semibold text-slate-900">Anak Asuh</h2>
            <div class="space-y-3">
              <div>
                <label class="text-xs font-semibold text-slate-500">Nama</label>
                <p class="mt-1 font-semibold text-slate-900">{{ clinic.employee?.nama }}</p>
              </div>
              <div>
                <label class="text-xs font-semibold text-slate-500">NRP</label>
                <p class="mt-1 text-slate-900">{{ clinic.employee?.nrp }}</p>
              </div>
              <div>
                <label class="text-xs font-semibold text-slate-500">Departemen</label>
                <p class="mt-1 text-slate-900">{{ clinic.employee?.departemen }}</p>
              </div>
            </div>
          </div>

          <!-- Creator Info -->
          <div class="rounded-lg border border-slate-200 bg-white p-6">
            <h2 class="mb-4 text-lg font-semibold text-slate-900">Pembuat</h2>
            <div class="space-y-3">
              <div>
                <label class="text-xs font-semibold text-slate-500">Nama</label>
                <p class="mt-1 text-slate-900">{{ clinic.creator?.name }}</p>
              </div>
              <div>
                <label class="text-xs font-semibold text-slate-500">Email</label>
                <p class="mt-1 text-slate-900">{{ clinic.creator?.email }}</p>
              </div>
              <div>
                <label class="text-xs font-semibold text-slate-500">Dibuat</label>
                <p class="mt-1 text-slate-900">{{ clinic.created_at }}</p>
              </div>
            </div>
          </div>

          <!-- Actions -->
          <div class="flex gap-3">
            <Link :href="route('coaching-clinic.edit', clinic.id)" class="flex-1 rounded-lg bg-amber-600 px-4 py-2 text-sm font-semibold text-white hover:bg-amber-700 text-center">
              Edit
            </Link>
            <Link :href="route('coaching-clinic.destroy', clinic.id)" method="delete" as="button" class="flex-1 rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700">
              Hapus
            </Link>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
