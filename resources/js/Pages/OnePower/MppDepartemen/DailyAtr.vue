<script setup>
import { ref, computed } from 'vue'
import { usePage, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import { formatDate } from '@/utils/formatters'

defineProps({
  dailyAtr: Object,
  filters: Object,
  user: Object,
  menu: Object,
})

const filterDateFrom = ref(null)
const filterDateTo = ref(null)
const filterShift = ref('')
const filterKategori = ref('')

const handleFilter = () => {
  router.get(route('mpp-departemen.daily-atr'), {
    date_from: filterDateFrom.value,
    date_to: filterDateTo.value,
    shift: filterShift.value,
    kategori: filterKategori.value,
  })
}

const handleExport = () => {
  router.post(route('mpp-report.export-daily-atr'), {
    date_from: filterDateFrom.value,
    date_to: filterDateTo.value,
    shift: filterShift.value,
    kategori: filterKategori.value,
  })
}

const calculateTotals = (data) => {
  if (!data?.data?.length) return {}
  return {
    plan_mp: data.data.reduce((sum, row) => sum + (row.plan_mp || 0), 0),
    sakit: data.data.reduce((sum, row) => sum + (row.sakit || 0), 0),
    izin: data.data.reduce((sum, row) => sum + (row.izin || 0), 0),
    alfa: data.data.reduce((sum, row) => sum + (row.alfa || 0), 0),
    training: data.data.reduce((sum, row) => sum + (row.training || 0), 0),
    induksi: data.data.reduce((sum, row) => sum + (row.induksi || 0), 0),
    tidak_checkin: data.data.reduce((sum, row) => sum + (row.tidak_checkin || 0), 0),
    actual_mp: data.data.reduce((sum, row) => sum + (row.actual_mp || 0), 0),
  }
}

const totals = computed(() => calculateTotals(dailyAtr))
</script>

<template>
  <AppLayout :user="user" :menu="menu">
    <div class="space-y-6">
      <div class="sm:flex sm:items-center sm:justify-between">
        <div>
          <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">Daily ATR (Actual To Roster)</h1>
          <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Monitor tenaga kerja harian dengan perhitungan aktual</p>
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
              Export
            </button>
          </div>
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
              <th class="px-6 py-3 text-right font-medium text-gray-900 dark:text-white">Sakit</th>
              <th class="px-6 py-3 text-right font-medium text-gray-900 dark:text-white">Izin</th>
              <th class="px-6 py-3 text-right font-medium text-gray-900 dark:text-white">Alfa</th>
              <th class="px-6 py-3 text-right font-medium text-gray-900 dark:text-white">Training</th>
              <th class="px-6 py-3 text-right font-medium text-gray-900 dark:text-white">Induksi</th>
              <th class="px-6 py-3 text-right font-medium text-gray-900 dark:text-white">Tidak Checkin</th>
              <th class="px-6 py-3 text-right font-medium text-gray-900 dark:text-white">Actual MP</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
            <tr v-for="row in dailyAtr?.data" :key="row.id" class="hover:bg-gray-50 dark:hover:bg-gray-700">
              <td class="whitespace-nowrap px-6 py-3">{{ formatDate(row.tanggal) }}</td>
              <td class="px-6 py-3">{{ row.kategori }}</td>
              <td class="px-6 py-3">{{ row.shift }}</td>
              <td class="px-6 py-3 text-right">{{ row.plan_mp }}</td>
              <td class="px-6 py-3 text-right">{{ row.sakit }}</td>
              <td class="px-6 py-3 text-right">{{ row.izin }}</td>
              <td class="px-6 py-3 text-right">{{ row.alfa }}</td>
              <td class="px-6 py-3 text-right">{{ row.training }}</td>
              <td class="px-6 py-3 text-right">{{ row.induksi }}</td>
              <td class="px-6 py-3 text-right">{{ row.tidak_checkin }}</td>
              <td class="px-6 py-3 text-right font-medium">{{ row.actual_mp }}</td>
            </tr>
            <tr v-if="dailyAtr?.data?.length === 0" class="text-center">
              <td colspan="11" class="px-6 py-8 text-gray-500 dark:text-gray-400">Tidak ada data tersedia</td>
            </tr>
            <!-- Totals Row -->
            <tr v-if="dailyAtr?.data?.length > 0" class="border-t-2 border-gray-300 bg-gray-100 font-bold dark:border-gray-600 dark:bg-gray-700">
              <td colspan="3" class="px-6 py-3">TOTAL</td>
              <td class="px-6 py-3 text-right">{{ totals.plan_mp }}</td>
              <td class="px-6 py-3 text-right">{{ totals.sakit }}</td>
              <td class="px-6 py-3 text-right">{{ totals.izin }}</td>
              <td class="px-6 py-3 text-right">{{ totals.alfa }}</td>
              <td class="px-6 py-3 text-right">{{ totals.training }}</td>
              <td class="px-6 py-3 text-right">{{ totals.induksi }}</td>
              <td class="px-6 py-3 text-right">{{ totals.tidak_checkin }}</td>
              <td class="px-6 py-3 text-right">{{ totals.actual_mp }}</td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div v-if="dailyAtr?.links" class="flex items-center justify-between border-t border-gray-200 bg-white px-4 py-3 dark:border-gray-700 dark:bg-gray-800 sm:px-6">
        <div class="text-sm text-gray-700 dark:text-gray-300">
          Showing <span class="font-medium">{{ dailyAtr.from || 0 }}</span> to <span class="font-medium">{{ dailyAtr.to || 0 }}</span> of
          <span class="font-medium">{{ dailyAtr.total || 0 }}</span> results
        </div>
        <div class="flex gap-2">
          <a
            v-for="link in dailyAtr.links"
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
