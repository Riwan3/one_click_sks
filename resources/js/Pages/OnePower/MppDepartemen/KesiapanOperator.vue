<script setup>
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import { formatDate } from '@/utils/formatters'

defineProps({
  kesiapanOperator: Object,
  filters: Object,
  user: Object,
  menu: Object,
})

const filterDateFrom = ref(null)
const filterDateTo = ref(null)
const filterCsaCode = ref('')
const isEditModalOpen = ref(false)
const editingItem = ref(null)
const editForm = ref({
  operator_count: 0,
  ready_count: 0,
})

const handleFilter = () => {
  router.get(route('mpp-departemen.kesiapan-operator'), {
    date_from: filterDateFrom.value,
    date_to: filterDateTo.value,
    csa_code: filterCsaCode.value,
  })
}

const handleExport = () => {
  router.post(route('mpp-report.export-kesiapan-trend'), {
    date_from: filterDateFrom.value,
    date_to: filterDateTo.value,
    csa_code: filterCsaCode.value,
  })
}

const openEditModal = (item) => {
  editingItem.value = item
  editForm.value = {
    operator_count: item.operator_count,
    ready_count: item.ready_count,
  }
  isEditModalOpen.value = true
}

const closeEditModal = () => {
  isEditModalOpen.value = false
  editingItem.value = null
}

const calculatePercentage = () => {
  if (editForm.value.operator_count <= 0) return 0
  return (editForm.value.ready_count / editForm.value.operator_count) * 100
}

const getStatusColor = (percentage) => {
  if (percentage >= 95) return 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200'
  if (percentage >= 90) return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200'
  return 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200'
}

const getStatusText = (percentage) => {
  if (percentage >= 95) return 'Green'
  if (percentage >= 90) return 'Yellow'
  return 'Red'
}

const handleSaveEdit = () => {
  // This would be a POST/PUT request in real implementation
  router.put(route('mpp-departemen.update-kesiapan', editingItem.value.id), {
    operator_count: editForm.value.operator_count,
    ready_count: editForm.value.ready_count,
  })
  closeEditModal()
}

const readyPercentage = computed(() => calculatePercentage())
</script>

<template>
  <AppLayout :user="user" :menu="menu">
    <div class="space-y-6">
      <div class="sm:flex sm:items-center sm:justify-between">
        <div>
          <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">Kesiapan Operator</h1>
          <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Monitoring kesiapan peralatan operator</p>
        </div>
      </div>

      <!-- Filters -->
      <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-800">
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
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
          <span class="text-sm text-gray-700 dark:text-gray-300">&gt;= 95% (Siap)</span>
        </div>
        <div class="flex items-center gap-2">
          <div class="h-4 w-4 rounded bg-yellow-500"></div>
          <span class="text-sm text-gray-700 dark:text-gray-300">90-94% (Waspada)</span>
        </div>
        <div class="flex items-center gap-2">
          <div class="h-4 w-4 rounded bg-red-500"></div>
          <span class="text-sm text-gray-700 dark:text-gray-300">&lt; 90% (Kritis)</span>
        </div>
      </div>

      <!-- Table -->
      <div class="overflow-x-auto rounded-lg border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800">
        <table class="w-full text-left text-sm">
          <thead class="border-b border-gray-200 bg-gray-50 dark:border-gray-700 dark:bg-gray-700">
            <tr>
              <th class="px-6 py-3 font-medium text-gray-900 dark:text-white">Tanggal</th>
              <th class="px-6 py-3 font-medium text-gray-900 dark:text-white">CSA</th>
              <th class="px-6 py-3 text-right font-medium text-gray-900 dark:text-white">Operator Count</th>
              <th class="px-6 py-3 text-right font-medium text-gray-900 dark:text-white">Ready Count</th>
              <th class="px-6 py-3 text-right font-medium text-gray-900 dark:text-white">Percentage</th>
              <th class="px-6 py-3 font-medium text-gray-900 dark:text-white">Status</th>
              <th class="px-6 py-3 font-medium text-gray-900 dark:text-white">Action</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
            <tr v-for="row in kesiapanOperator?.data" :key="row.id" class="hover:bg-gray-50 dark:hover:bg-gray-700">
              <td class="whitespace-nowrap px-6 py-3">{{ formatDate(row.tanggal) }}</td>
              <td class="px-6 py-3">{{ row.csa_code }}</td>
              <td class="px-6 py-3 text-right">{{ row.operator_count }}</td>
              <td class="px-6 py-3 text-right">{{ row.ready_count }}</td>
              <td class="px-6 py-3 text-right font-medium">
                {{
                  row.operator_count > 0
                    ? ((row.ready_count / row.operator_count) * 100).toFixed(2)
                    : 0
                }}%
              </td>
              <td class="px-6 py-3">
                <span
                  :class="[
                    'inline-block rounded-full px-3 py-1 text-sm font-semibold',
                    getStatusColor(
                      row.operator_count > 0 ? (row.ready_count / row.operator_count) * 100 : 0
                    ),
                  ]"
                >
                  {{ getStatusText(row.operator_count > 0 ? (row.ready_count / row.operator_count) * 100 : 0) }}
                </span>
              </td>
              <td class="px-6 py-3">
                <button
                  v-if="user.role === 'admin'"
                  @click="openEditModal(row)"
                  class="rounded-md bg-blue-100 px-3 py-1 text-sm text-blue-700 hover:bg-blue-200 dark:bg-blue-900 dark:text-blue-200"
                >
                  Edit
                </button>
              </td>
            </tr>
            <tr v-if="!kesiapanOperator?.data?.length" class="text-center">
              <td colspan="7" class="px-6 py-8 text-gray-500 dark:text-gray-400">Tidak ada data tersedia</td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div v-if="kesiapanOperator?.links" class="flex items-center justify-between border-t border-gray-200 bg-white px-4 py-3 dark:border-gray-700 dark:bg-gray-800 sm:px-6">
        <div class="text-sm text-gray-700 dark:text-gray-300">
          Showing <span class="font-medium">{{ kesiapanOperator.from || 0 }}</span> to <span class="font-medium">{{ kesiapanOperator.to || 0 }}</span> of
          <span class="font-medium">{{ kesiapanOperator.total || 0 }}</span> results
        </div>
        <div class="flex gap-2">
          <a
            v-for="link in kesiapanOperator.links"
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

      <!-- Edit Modal -->
      <div v-if="isEditModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 p-4">
        <div class="w-full max-w-md rounded-lg bg-white dark:bg-gray-800">
          <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
            <h2 class="text-lg font-medium text-gray-900 dark:text-white">Edit Kesiapan Operator</h2>
          </div>
          <div class="space-y-4 p-6">
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tanggal</label>
              <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ formatDate(editingItem?.tanggal) }}</p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">CSA Code</label>
              <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ editingItem?.csa_code }}</p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Operator Count</label>
              <input
                v-model.number="editForm.operator_count"
                type="number"
                min="0"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white sm:text-sm"
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Ready Count</label>
              <input
                v-model.number="editForm.ready_count"
                type="number"
                min="0"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white sm:text-sm"
              />
            </div>
            <div class="rounded-lg bg-blue-50 p-3 dark:bg-blue-900">
              <p class="text-sm font-medium text-gray-700 dark:text-gray-300">Ready Percentage: {{ readyPercentage.toFixed(2) }}%</p>
            </div>
          </div>
          <div class="flex gap-3 border-t border-gray-200 px-6 py-4 dark:border-gray-700">
            <button
              @click="closeEditModal"
              class="flex-1 rounded-md border border-gray-300 bg-white px-4 py-2 text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600"
            >
              Batal
            </button>
            <button
              @click="handleSaveEdit"
              class="flex-1 rounded-md bg-blue-600 px-4 py-2 text-white hover:bg-blue-700 dark:bg-blue-700 dark:hover:bg-blue-600"
            >
              Simpan
            </button>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
