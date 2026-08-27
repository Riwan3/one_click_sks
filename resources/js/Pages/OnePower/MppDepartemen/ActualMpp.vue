<script setup>
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import { formatDate } from '@/utils/formatters'

defineProps({
  actualMpp: Object,
  filters: Object,
  user: Object,
  menu: Object,
})

const filterDateFrom = ref(null)
const filterDateTo = ref(null)
const filterShift = ref('')
const filterKategori = ref('')

const handleFilter = () => {
  router.get(route('mpp-departemen.actual-mpp'), {
    date_from: filterDateFrom.value,
    date_to: filterDateTo.value,
    shift: filterShift.value,
    kategori: filterKategori.value,
  })
}

const handleExport = () => {
  router.post(route('mpp-report.export-actual-mpp'), {
    date_from: filterDateFrom.value,
    date_to: filterDateTo.value,
    shift: filterShift.value,
    kategori: filterKategori.value,
  })
}

const getStatusColor = (percentage) => {
  if (percentage >= 97) return 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200'
  if (percentage >= 94) return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200'
  return 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200'
}
</script>

<template>
  <AppLayout :user="user" :menu="menu">
    <div class="space-y-6">
      <div class="sm:flex sm:items-center sm:justify-between">
        <div>
          <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">Actual MPP (dengan Adjustment)</h1>
          <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Monitoring MPP aktual dengan perhitungan deviasi dan achievement</p>
        </div>
      </div>

      <!-- Filters -->
      <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-800">
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
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
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Kategori</label>
            <input
              v-model="filterKategori"
              type="text"
              placeholder="Ketik kategori..."
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
              PDF
            </button>
          </div>
        </div>
      </div>

      <!-- Legend -->
      <div class="flex flex-wrap gap-4 rounded-lg bg-blue-50 p-4 dark:bg-blue-900">
        <div class="flex items-center gap-2">
          <div class="h-4 w-4 rounded bg-green-500"></div>
          <span class="text-sm text-gray-700 dark:text-gray-300">&gt;= 97% (Optimal)</span>
        </div>
        <div class="flex items-center gap-2">
          <div class="h-4 w-4 rounded bg-yellow-500"></div>
          <span class="text-sm text-gray-700 dark:text-gray-300">94-96% (Warning)</span>
        </div>
        <div class="flex items-center gap-2">
          <div class="h-4 w-4 rounded bg-red-500"></div>
          <span class="text-sm text-gray-700 dark:text-gray-300">&lt; 94% (Critical)</span>
        </div>
      </div>

      <!-- Table -->
      <div class="overflow-x-auto rounded-lg border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800">
        <table class="w-full text-left text-sm">
          <thead class="border-b border-gray-200 bg-gray-50 dark:border-gray-700 dark:bg-gray-700">
            <tr>
              <th class="px-6 py-3 font-medium text-gray-900 dark:text-white">Tanggal</th>
              <th class="px-6 py-3 font-medium text-gray-900 dark:text-white">Kategori</th>
              <th class="px-6 py-3 font-medium text-gray-900 dark:text-white">Shift</th>
              <th class="px-6 py-3 text-right font-medium text-gray-900 dark:text-white">Plan MP</th>
              <th class="px-6 py-3 text-right font-medium text-gray-900 dark:text-white">Deduction</th>
              <th class="px-6 py-3 text-right font-medium text-gray-900 dark:text-white">Actual MP</th>
              <th class="px-6 py-3 text-right font-medium text-gray-900 dark:text-white">Deviasi %</th>
              <th class="px-6 py-3 text-right font-medium text-gray-900 dark:text-white">Achievement %</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
            <tr v-for="row in actualMpp?.data" :key="row.id" class="hover:bg-gray-50 dark:hover:bg-gray-700">
              <td class="whitespace-nowrap px-6 py-3">{{ formatDate(row.tanggal) }}</td>
              <td class="px-6 py-3">{{ row.kategori }}</td>
              <td class="px-6 py-3">{{ row.shift }}</td>
              <td class="px-6 py-3 text-right">{{ row.plan_mp }}</td>
              <td class="px-6 py-3 text-right">{{ row.deductions }}</td>
              <td class="px-6 py-3 text-right font-medium">{{ row.actual_mp }}</td>
              <td class="px-6 py-3 text-right">{{ row.deviation_percentage }}%</td>
              <td class="px-6 py-3 text-right">
                <span
                  :class="[
                    'inline-block rounded-full px-3 py-1 text-sm font-semibold',
                    getStatusColor(row.achievement_percentage),
                  ]"
                >
                  {{ row.achievement_percentage }}%
                </span>
              </td>
            </tr>
            <tr v-if="!actualMpp?.data?.length" class="text-center">
              <td colspan="8" class="px-6 py-8 text-gray-500 dark:text-gray-400">Tidak ada data tersedia</td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div v-if="actualMpp?.links" class="flex items-center justify-between border-t border-gray-200 bg-white px-4 py-3 dark:border-gray-700 dark:bg-gray-800 sm:px-6">
        <div class="text-sm text-gray-700 dark:text-gray-300">
          Showing <span class="font-medium">{{ actualMpp.from || 0 }}</span> to <span class="font-medium">{{ actualMpp.to || 0 }}</span> of
          <span class="font-medium">{{ actualMpp.total || 0 }}</span> results
        </div>
        <div class="flex gap-2">
          <a
            v-for="link in actualMpp.links"
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
