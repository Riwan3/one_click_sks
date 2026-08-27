<script setup>
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

defineProps({
  employees: Object,
  filters: Object,
  user: Object,
  menu: Object,
})

const filterSearch = ref('')
const filterDepartment = ref('')
const filterUnit = ref('')
const filterShift = ref('')

const handleFilter = () => {
  router.get(route('mpp-departemen.show-employees'), {
    search: filterSearch.value,
    department: filterDepartment.value,
    unit: filterUnit.value,
    shift: filterShift.value,
  })
}

const handleRowClick = (employeeId) => {
  router.get(route('mpp-departemen.employee-detail', employeeId))
}

const handleExportPhotos = (employeeId) => {
  router.post(route('mpp-report.export-employee-photos', employeeId))
}
</script>

<template>
  <AppLayout :user="user" :menu="menu">
    <div class="space-y-6">
      <div class="sm:flex sm:items-center sm:justify-between">
        <div>
          <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">Daftar Karyawan</h1>
          <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Drill-down list karyawan dengan detail checkpoint dan OJK</p>
        </div>
      </div>

      <!-- Filters -->
      <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-800">
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Cari NRP/Nama</label>
            <input
              v-model="filterSearch"
              type="text"
              placeholder="Ketik NRP atau nama..."
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white sm:text-sm"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Departemen</label>
            <input
              v-model="filterDepartment"
              type="text"
              placeholder="Cari departemen..."
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white sm:text-sm"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Unit</label>
            <input
              v-model="filterUnit"
              type="text"
              placeholder="Cari unit..."
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
          <div class="flex items-end">
            <button
              @click="handleFilter"
              class="w-full rounded-md bg-blue-600 px-4 py-2 text-white hover:bg-blue-700 dark:bg-blue-700 dark:hover:bg-blue-600"
            >
              Filter
            </button>
          </div>
        </div>
      </div>

      <!-- Table -->
      <div class="overflow-x-auto rounded-lg border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800">
        <table class="w-full text-left text-sm">
          <thead class="border-b border-gray-200 bg-gray-50 dark:border-gray-700 dark:bg-gray-700">
            <tr>
              <th class="px-6 py-3 font-medium text-gray-900 dark:text-white">NRP</th>
              <th class="px-6 py-3 font-medium text-gray-900 dark:text-white">Nama</th>
              <th class="px-6 py-3 font-medium text-gray-900 dark:text-white">Jabatan</th>
              <th class="px-6 py-3 font-medium text-gray-900 dark:text-white">Departemen</th>
              <th class="px-6 py-3 font-medium text-gray-900 dark:text-white">Unit</th>
              <th class="px-6 py-3 font-medium text-gray-900 dark:text-white">Shift</th>
              <th class="px-6 py-3 font-medium text-gray-900 dark:text-white">Action</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
            <tr v-for="employee in employees?.data" :key="employee.id" class="hover:bg-gray-50 dark:hover:bg-gray-700">
              <td class="whitespace-nowrap px-6 py-3 font-medium">{{ employee.nrp }}</td>
              <td class="px-6 py-3">{{ employee.nama }}</td>
              <td class="px-6 py-3">{{ employee.jabatan }}</td>
              <td class="px-6 py-3">{{ employee.departemen }}</td>
              <td class="px-6 py-3">{{ employee.unit }}</td>
              <td class="px-6 py-3">{{ employee.shift }}</td>
              <td class="px-6 py-3">
                <button
                  @click="handleRowClick(employee.id)"
                  class="rounded-md bg-blue-100 px-3 py-1 text-sm text-blue-700 hover:bg-blue-200 dark:bg-blue-900 dark:text-blue-200"
                >
                  Detail
                </button>
              </td>
            </tr>
            <tr v-if="!employees?.data?.length" class="text-center">
              <td colspan="7" class="px-6 py-8 text-gray-500 dark:text-gray-400">Tidak ada karyawan tersedia</td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div v-if="employees?.links" class="flex items-center justify-between border-t border-gray-200 bg-white px-4 py-3 dark:border-gray-700 dark:bg-gray-800 sm:px-6">
        <div class="text-sm text-gray-700 dark:text-gray-300">
          Showing <span class="font-medium">{{ employees.from || 0 }}</span> to <span class="font-medium">{{ employees.to || 0 }}</span> of
          <span class="font-medium">{{ employees.total || 0 }}</span> results
        </div>
        <div class="flex gap-2">
          <a
            v-for="link in employees.links"
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
