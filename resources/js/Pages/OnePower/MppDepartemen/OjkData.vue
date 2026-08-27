<script setup>
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import { formatDate } from '@/utils/formatters'

defineProps({
  photos: Object,
  filters: Object,
  user: Object,
  menu: Object,
})

const filterDateFrom = ref(null)
const filterDateTo = ref(null)
const filterCsaCode = ref('')
const filterShift = ref('')
const filterSearch = ref('')
const lightboxPhoto = ref(null)
const isLightboxOpen = ref(false)

const handleFilter = () => {
  router.get(route('mpp-departemen.ojk-data'), {
    date_from: filterDateFrom.value,
    date_to: filterDateTo.value,
    csa_code: filterCsaCode.value,
    shift: filterShift.value,
    search: filterSearch.value,
  })
}

const handleExport = () => {
  router.post(route('mpp-report.export-ojk'), {
    date_from: filterDateFrom.value,
    date_to: filterDateTo.value,
    csa_code: filterCsaCode.value,
    shift: filterShift.value,
  })
}

const openLightbox = (photo) => {
  lightboxPhoto.value = photo
  isLightboxOpen.value = true
}

const closeLightbox = () => {
  isLightboxOpen.value = false
  lightboxPhoto.value = null
}

const handleDelete = (photoId) => {
  if (confirm('Apakah Anda yakin ingin menghapus foto ini?')) {
    router.delete(route('mpp-photo.destroy', photoId))
  }
}
</script>

<template>
  <AppLayout :user="user" :menu="menu">
    <div class="space-y-6">
      <div class="sm:flex sm:items-center sm:justify-between">
        <div>
          <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">OJK Photo Archive</h1>
          <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Galeri foto OJK (Operator Jet Cleaning) dari operator</p>
        </div>
      </div>

      <!-- Filters -->
      <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-800">
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-6">
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Dari Tanggal</label>
            <input
              v-model="filterDateFrom"
              type="date"
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white sm:text-sm"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Sampai Tanggal</label>
            <input
              v-model="filterDateTo"
              type="date"
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white sm:text-sm"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">CSA Code</label>
            <input
              v-model="filterCsaCode"
              type="text"
              placeholder="Cari CSA..."
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white sm:text-sm"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Shift</label>
            <select
              v-model="filterShift"
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white sm:text-sm"
            >
              <option value="">Semua</option>
              <option value="D">Day</option>
              <option value="N">Night</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Cari</label>
            <input
              v-model="filterSearch"
              type="text"
              placeholder="Nama atau NRP..."
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white sm:text-sm"
            />
          </div>
          <div class="flex items-end gap-2">
            <button
              @click="handleFilter"
              class="flex-1 rounded-md bg-blue-600 px-4 py-2 text-white hover:bg-blue-700 dark:bg-blue-700 dark:hover:bg-blue-600"
            >
              Filter
            </button>
            <button
              @click="handleExport"
              class="flex-1 rounded-md bg-green-600 px-4 py-2 text-white hover:bg-green-700 dark:bg-green-700 dark:hover:bg-green-600"
            >
              Excel
            </button>
          </div>
        </div>
      </div>

      <!-- Photo Grid -->
      <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div
          v-for="photo in photos?.data"
          :key="photo.id"
          class="overflow-hidden rounded-lg border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800"
        >
          <div class="cursor-pointer" @click="openLightbox(photo)">
            <img
              :src="`/${photo.url}`"
              :alt="photo.nama"
              class="h-48 w-full object-cover hover:opacity-75"
            />
          </div>
          <div class="p-3">
            <p class="text-sm font-medium text-gray-900 dark:text-white">{{ photo.nama }}</p>
            <p class="text-xs text-gray-500 dark:text-gray-400">{{ photo.nrp }}</p>
            <p class="text-xs text-gray-500 dark:text-gray-400">{{ formatDate(photo.tanggal) }} ({{ photo.shift }})</p>
            <p class="text-xs text-gray-500 dark:text-gray-400">CSA: {{ photo.unit_code }}</p>
            <div v-if="user.role === 'admin'" class="mt-2">
              <button
                @click="handleDelete(photo.id)"
                class="w-full rounded-md bg-red-100 px-2 py-1 text-xs text-red-700 hover:bg-red-200 dark:bg-red-900 dark:text-red-200"
              >
                Hapus
              </button>
            </div>
          </div>
        </div>

        <div v-if="!photos?.data?.length" class="col-span-full py-8 text-center text-gray-500 dark:text-gray-400">
          Tidak ada foto tersedia
        </div>
      </div>

      <!-- Lightbox Modal -->
      <div
        v-if="isLightboxOpen && lightboxPhoto"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-75 p-4"
      >
        <div class="max-h-[90vh] max-w-[90vw] overflow-auto rounded-lg bg-white dark:bg-gray-800">
          <div class="sticky top-0 flex items-center justify-between border-b border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-700">
            <h2 class="text-lg font-medium text-gray-900 dark:text-white">{{ lightboxPhoto.nama }}</h2>
            <button
              @click="closeLightbox"
              class="rounded-md bg-gray-200 p-2 text-gray-600 hover:bg-gray-300 dark:bg-gray-600 dark:text-gray-400"
            >
              ✕
            </button>
          </div>
          <img :src="`/${lightboxPhoto.url}`" :alt="lightboxPhoto.nama" class="w-full" />
          <div class="border-t border-gray-200 p-4 dark:border-gray-700">
            <dl class="grid gap-2 text-sm">
              <div>
                <dt class="font-medium text-gray-700 dark:text-gray-300">Tanggal</dt>
                <dd class="text-gray-600 dark:text-gray-400">{{ formatDate(lightboxPhoto.tanggal) }}</dd>
              </div>
              <div>
                <dt class="font-medium text-gray-700 dark:text-gray-300">NRP</dt>
                <dd class="text-gray-600 dark:text-gray-400">{{ lightboxPhoto.nrp }}</dd>
              </div>
              <div>
                <dt class="font-medium text-gray-700 dark:text-gray-300">Shift</dt>
                <dd class="text-gray-600 dark:text-gray-400">{{ lightboxPhoto.shift }}</dd>
              </div>
              <div>
                <dt class="font-medium text-gray-700 dark:text-gray-300">CSA Code</dt>
                <dd class="text-gray-600 dark:text-gray-400">{{ lightboxPhoto.unit_code }}</dd>
              </div>
            </dl>
          </div>
        </div>
      </div>

      <!-- Pagination -->
      <div v-if="photos?.links" class="flex items-center justify-between border-t border-gray-200 bg-white px-4 py-3 dark:border-gray-700 dark:bg-gray-800 sm:px-6">
        <div class="text-sm text-gray-700 dark:text-gray-300">
          Showing <span class="font-medium">{{ photos.from || 0 }}</span> to <span class="font-medium">{{ photos.to || 0 }}</span> of
          <span class="font-medium">{{ photos.total || 0 }}</span> results
        </div>
        <div class="flex gap-2">
          <a
            v-for="link in photos.links"
            :key="link.label"
            :href="link.url"
            :class="[
              'px-3 py-1 rounded text-sm',
              link.active ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300 dark:bg-gray-700 dark:text-gray-300',
            ]"
            v-html="link.label"
          ></a>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
