<script setup>
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import { formatDate } from '@/utils/formatters'

defineProps({
  employee: Object,
  photos: Array,
  recentAtl: Array,
  user: Object,
  menu: Object,
})

const handleBack = () => {
  router.get(route('mpp-departemen.show-employees'))
}

const handleDownloadPhotos = () => {
  router.post(route('mpp-report.export-employee-photos', employee.id))
}

const lightboxPhoto = ref(null)
const isLightboxOpen = ref(false)

const openLightbox = (photo) => {
  lightboxPhoto.value = photo
  isLightboxOpen.value = true
}

const closeLightbox = () => {
  isLightboxOpen.value = false
  lightboxPhoto.value = null
}

const checkpointPhotos = photos?.filter(p => p.jenis === 'Checkpoint') || []
const ojkPhotos = photos?.filter(p => p.jenis === 'OJK') || []
</script>

<template>
  <AppLayout :user="user" :menu="menu">
    <div class="space-y-6">
      <!-- Header with Back Button -->
      <div class="flex items-center justify-between">
        <div>
          <button
            @click="handleBack"
            class="mb-4 rounded-md bg-gray-200 px-4 py-2 text-gray-700 hover:bg-gray-300 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600"
          >
            ← Kembali
          </button>
          <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">Detail Karyawan</h1>
        </div>
        <button
          @click="handleDownloadPhotos"
          class="rounded-md bg-green-600 px-4 py-2 text-white hover:bg-green-700 dark:bg-green-700 dark:hover:bg-green-600"
        >
          Download Foto (ZIP)
        </button>
      </div>

      <!-- Employee Info -->
      <div class="rounded-lg border border-gray-200 bg-white p-6 dark:border-gray-700 dark:bg-gray-800">
        <h2 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">Informasi Karyawan</h2>
        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
          <div>
            <dt class="text-sm font-medium text-gray-700 dark:text-gray-300">NRP</dt>
            <dd class="mt-1 text-lg font-semibold text-gray-900 dark:text-white">{{ employee.nrp }}</dd>
          </div>
          <div>
            <dt class="text-sm font-medium text-gray-700 dark:text-gray-300">Nama</dt>
            <dd class="mt-1 text-lg font-semibold text-gray-900 dark:text-white">{{ employee.nama }}</dd>
          </div>
          <div>
            <dt class="text-sm font-medium text-gray-700 dark:text-gray-300">Jabatan</dt>
            <dd class="mt-1 text-lg font-semibold text-gray-900 dark:text-white">{{ employee.jabatan }}</dd>
          </div>
          <div>
            <dt class="text-sm font-medium text-gray-700 dark:text-gray-300">Departemen</dt>
            <dd class="mt-1 text-lg font-semibold text-gray-900 dark:text-white">{{ employee.departemen }}</dd>
          </div>
          <div>
            <dt class="text-sm font-medium text-gray-700 dark:text-gray-300">Unit</dt>
            <dd class="mt-1 text-lg font-semibold text-gray-900 dark:text-white">{{ employee.unit }}</dd>
          </div>
          <div>
            <dt class="text-sm font-medium text-gray-700 dark:text-gray-300">Shift</dt>
            <dd class="mt-1 text-lg font-semibold text-gray-900 dark:text-white">{{ employee.shift }}</dd>
          </div>
        </div>
      </div>

      <!-- Checkpoint Photos -->
      <div class="rounded-lg border border-gray-200 bg-white p-6 dark:border-gray-700 dark:bg-gray-800">
        <h2 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">Checkpoint Photos ({{ checkpointPhotos.length }})</h2>
        <div v-if="checkpointPhotos.length > 0" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
          <div
            v-for="photo in checkpointPhotos"
            :key="photo.id"
            class="overflow-hidden rounded-lg border border-gray-200 dark:border-gray-700"
          >
            <img
              :src="`/${photo.url}`"
              :alt="photo.nama"
              class="h-32 w-full cursor-pointer object-cover hover:opacity-75"
              @click="openLightbox(photo)"
            />
            <div class="p-2">
              <p class="text-xs font-medium text-gray-900 dark:text-white">{{ formatDate(photo.tanggal) }}</p>
              <p class="text-xs text-gray-500 dark:text-gray-400">{{ photo.shift }}</p>
            </div>
          </div>
        </div>
        <div v-else class="py-8 text-center text-gray-500 dark:text-gray-400">
          Tidak ada checkpoint photos
        </div>
      </div>

      <!-- OJK Photos -->
      <div class="rounded-lg border border-gray-200 bg-white p-6 dark:border-gray-700 dark:bg-gray-800">
        <h2 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">OJK Photos ({{ ojkPhotos.length }})</h2>
        <div v-if="ojkPhotos.length > 0" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
          <div
            v-for="photo in ojkPhotos"
            :key="photo.id"
            class="overflow-hidden rounded-lg border border-gray-200 dark:border-gray-700"
          >
            <img
              :src="`/${photo.url}`"
              :alt="photo.nama"
              class="h-32 w-full cursor-pointer object-cover hover:opacity-75"
              @click="openLightbox(photo)"
            />
            <div class="p-2">
              <p class="text-xs font-medium text-gray-900 dark:text-white">{{ formatDate(photo.tanggal) }}</p>
              <p class="text-xs text-gray-500 dark:text-gray-400">{{ photo.shift }}</p>
            </div>
          </div>
        </div>
        <div v-else class="py-8 text-center text-gray-500 dark:text-gray-400">
          Tidak ada OJK photos
        </div>
      </div>

      <!-- Recent ATR History -->
      <div class="rounded-lg border border-gray-200 bg-white p-6 dark:border-gray-700 dark:bg-gray-800">
        <h2 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">Recent ATR History (Last 30 Days)</h2>
        <div class="overflow-x-auto">
          <table class="w-full text-left text-sm">
            <thead class="border-b border-gray-200 bg-gray-50 dark:border-gray-700 dark:bg-gray-700">
              <tr>
                <th class="px-4 py-3 font-medium text-gray-900 dark:text-white">Tanggal</th>
                <th class="px-4 py-3 text-right font-medium text-gray-900 dark:text-white">Plan MP</th>
                <th class="px-4 py-3 text-right font-medium text-gray-900 dark:text-white">Sakit</th>
                <th class="px-4 py-3 text-right font-medium text-gray-900 dark:text-white">Izin</th>
                <th class="px-4 py-3 text-right font-medium text-gray-900 dark:text-white">Alfa</th>
                <th class="px-4 py-3 text-right font-medium text-gray-900 dark:text-white">Actual MP</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
              <tr v-for="row in recentAtl" :key="row.id" class="hover:bg-gray-50 dark:hover:bg-gray-700">
                <td class="px-4 py-3">{{ formatDate(row.tanggal) }}</td>
                <td class="px-4 py-3 text-right">{{ row.jumlah }}</td>
                <td class="px-4 py-3 text-right">{{ row.sakit }}</td>
                <td class="px-4 py-3 text-right">{{ row.izin }}</td>
                <td class="px-4 py-3 text-right">{{ row.alfa }}</td>
                <td class="px-4 py-3 text-right font-medium">{{ row.jumlah - (row.sakit + row.izin + row.alfa) }}</td>
              </tr>
              <tr v-if="!recentAtl?.length" class="text-center">
                <td colspan="6" class="px-4 py-8 text-gray-500 dark:text-gray-400">Tidak ada data ATR tersedia</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Lightbox Modal -->
      <div
        v-if="isLightboxOpen && lightboxPhoto"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-75 p-4"
      >
        <div class="max-h-[90vh] max-w-[90vw] overflow-auto rounded-lg bg-white dark:bg-gray-800">
          <div class="sticky top-0 flex items-center justify-between border-b border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-700">
            <h2 class="text-lg font-medium text-gray-900 dark:text-white">{{ lightboxPhoto.jenis }} - {{ formatDate(lightboxPhoto.tanggal) }}</h2>
            <button
              @click="closeLightbox"
              class="rounded-md bg-gray-200 p-2 text-gray-600 hover:bg-gray-300 dark:bg-gray-600 dark:text-gray-400"
            >
              ✕
            </button>
          </div>
          <img :src="`/${lightboxPhoto.url}`" :alt="lightboxPhoto.nama" class="w-full" />
        </div>
      </div>
    </div>
  </AppLayout>
</template>
