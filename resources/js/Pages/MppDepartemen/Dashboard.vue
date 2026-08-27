<template>
  <AppLayout>
    <div class="min-h-screen bg-gray-50">
      <!-- Header -->
      <div class="bg-white border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
          <div class="flex items-center justify-between">
            <div>
              <h1 class="text-3xl font-bold text-gray-900">MPP Departemen</h1>
              <p class="mt-1 text-sm text-gray-600">Manpower Performance Planning - Phase 1</p>
            </div>
            <div class="text-right">
              <p class="text-sm text-gray-600">
                <span class="font-semibold text-gray-900">{{ todayCount }}</span> penyesuaian hari ini
              </p>
            </div>
          </div>
        </div>
      </div>

      <!-- Content -->
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Summary Stats -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
          <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <div class="flex items-center justify-center h-12 w-12 rounded-md bg-blue-500 text-white">
                  <span class="text-xl">📊</span>
                </div>
              </div>
              <div class="ml-4">
                <p class="text-sm text-gray-600">Total Kategori</p>
                <p class="text-2xl font-bold text-gray-900">{{ categories.length }}</p>
              </div>
            </div>
          </div>

          <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <div class="flex items-center justify-center h-12 w-12 rounded-md bg-green-500 text-white">
                  <span class="text-xl">📸</span>
                </div>
              </div>
              <div class="ml-4">
                <p class="text-sm text-gray-600">Foto Terbaru</p>
                <p class="text-2xl font-bold text-gray-900">{{ recentPhotos.length }}</p>
              </div>
            </div>
          </div>

          <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <div class="flex items-center justify-center h-12 w-12 rounded-md bg-purple-500 text-white">
                  <span class="text-xl">👤</span>
                </div>
              </div>
              <div class="ml-4">
                <p class="text-sm text-gray-600">Peran Anda</p>
                <p class="text-2xl font-bold text-gray-900 capitalize">{{ userRole }}</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Navigation Grid -->
        <div class="mb-8">
          <h2 class="text-xl font-bold text-gray-900 mb-4">Modul Tersedia</h2>
          <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            <Link
              v-for="page in pages"
              :key="page.id"
              :href="getPageRoute(page.id)"
              class="bg-white rounded-lg shadow hover:shadow-lg transition-shadow p-6 group"
            >
              <div class="flex items-start">
                <div class="flex-shrink-0">
                  <div class="flex items-center justify-center h-12 w-12 rounded-lg bg-indigo-100 text-indigo-600 group-hover:bg-indigo-600 group-hover:text-white transition-colors">
                    <span class="text-xl">{{ getPageIcon(page.icon) }}</span>
                  </div>
                </div>
                <div class="ml-4">
                  <h3 class="text-lg font-medium text-gray-900 group-hover:text-indigo-600">{{ page.title }}</h3>
                  <p class="mt-1 text-sm text-gray-600">{{ page.description }}</p>
                </div>
              </div>
            </Link>
          </div>
        </div>

        <!-- Recent Photos -->
        <div v-if="recentPhotos.length > 0" class="mt-8">
          <h2 class="text-xl font-bold text-gray-900 mb-4">Foto Terbaru</h2>
          <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-4">
            <div
              v-for="photo in recentPhotos"
              :key="photo.id"
              class="bg-white rounded-lg shadow overflow-hidden hover:shadow-lg transition-shadow"
            >
              <img
                :src="photo.url"
                :alt="photo.nama"
                class="w-full h-32 object-cover"
              >
              <div class="p-3">
                <p class="text-xs font-semibold text-gray-900">{{ photo.nama }}</p>
                <p class="text-xs text-gray-600">{{ photo.tanggal }}</p>
                <p class="text-xs text-indigo-600">{{ photo.jenis }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

defineProps({
  todayCount: Number,
  recentPhotos: Array,
  categories: Array,
  pages: Array,
  userRole: String,
});

const getPageIcon = (iconName) => {
  const icons = {
    'layout-grid': '📋',
    'calendar': '📅',
    'chart-line': '📈',
    'camera': '📷',
    'image': '🖼️',
    'users': '👥',
    'upload-cloud': '☁️',
    'zap': '⚡',
    'file-text': '📄',
  };
  return icons[iconName] || '📌';
};

const getPageRoute = (pageId) => {
  const routes = {
    'master-categories': route('mpp-master-categories.index'),
    'daily-atr': '#',
    'actual-mpp': '#',
    'checkpoint': route('mpp-photo.by-jenis', { jenis: 'Checkpoint' }),
    'ojk': route('mpp-photo.by-jenis', { jenis: 'OJK' }),
    'employees': '#',
    'photo-upload': route('mpp-photo.create'),
    'kesiapan-operators': '#',
    'reports': '#',
  };
  return routes[pageId] || '#';
};
</script>
