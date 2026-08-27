<script setup>
import { computed, ref } from 'vue'
import { router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import { Chart as ChartJS, CategoryScale, LinearScale, PointElement, LineElement, BarElement, ArcElement, Title, Tooltip, Legend } from 'chart.js'
import { Line, Bar, Pie } from 'vue-chartjs'
import { FileDown } from 'lucide-vue-next'

ChartJS.register(CategoryScale, LinearScale, PointElement, LineElement, BarElement, ArcElement, Title, Tooltip, Legend)

defineProps({
  kpis: Object,
  charts: Object,
  filters: Object,
  is_management: Boolean,
})

const dateFrom = ref('')
const dateTo = ref('')
const selectedDepartment = ref('')

const handleFilterChange = () => {
  router.visit(route('coaching-clinic.dashboard'), {
    method: 'get',
    data: {
      date_from: dateFrom.value,
      date_to: dateTo.value,
      department: selectedDepartment.value,
    },
  })
}

const handleExport = (format) => {
  router.post(route('coaching-clinic.export'), {
    format,
    date_from: dateFrom.value,
    date_to: dateTo.value,
    department: selectedDepartment.value,
  })
}

const chartOptions = {
  responsive: true,
  maintainAspectRatio: true,
  plugins: {
    legend: {
      labels: { font: { size: 12 } },
    },
  },
}
</script>

<template>
  <AppLayout title="Coaching Clinic Dashboard">
    <div class="space-y-6 p-6">
      <!-- Filters & Export -->
      <div class="rounded-lg bg-white p-4 shadow">
        <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
          <div>
            <label class="block text-sm font-medium text-gray-700">Dari Tanggal</label>
            <input v-model="dateFrom" type="date" class="mt-1 w-full rounded border-gray-300 shadow-sm" @change="handleFilterChange" />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700">Ke Tanggal</label>
            <input v-model="dateTo" type="date" class="mt-1 w-full rounded border-gray-300 shadow-sm" @change="handleFilterChange" />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700">Departemen</label>
            <select v-model="selectedDepartment" class="mt-1 w-full rounded border-gray-300 shadow-sm" @change="handleFilterChange">
              <option value="">Semua Departemen</option>
              <option v-for="dept in filters.departments" :key="dept" :value="dept">{{ dept }}</option>
            </select>
          </div>
          <div class="flex gap-2 self-end">
            <button @click="handleExport('excel')" class="flex items-center gap-2 rounded bg-green-600 px-4 py-2 text-white hover:bg-green-700">
              <FileDown :size="18" />
              Excel
            </button>
            <button @click="handleExport('pdf')" class="flex items-center gap-2 rounded bg-red-600 px-4 py-2 text-white hover:bg-red-700">
              <FileDown :size="18" />
              PDF
            </button>
          </div>
        </div>
      </div>

      <!-- KPI Cards -->
      <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
        <div class="rounded-lg bg-white p-6 shadow">
          <div class="text-sm font-medium text-gray-600">Total Clinics</div>
          <div class="mt-2 text-3xl font-bold text-emerald-600">{{ kpis.total_clinics }}</div>
        </div>
        <div class="rounded-lg bg-white p-6 shadow">
          <div class="text-sm font-medium text-gray-600">Active Coaches</div>
          <div class="mt-2 text-3xl font-bold text-blue-600">{{ kpis.active_coaches }}</div>
        </div>
        <div class="rounded-lg bg-white p-6 shadow">
          <div class="text-sm font-medium text-gray-600">Materials</div>
          <div class="mt-2 text-3xl font-bold text-purple-600">{{ kpis.total_materials }}</div>
        </div>
        <div class="rounded-lg bg-white p-6 shadow">
          <div class="text-sm font-medium text-gray-600">Pending Reviews</div>
          <div class="mt-2 text-3xl font-bold text-orange-600">{{ kpis.pending_reviews }}</div>
        </div>
      </div>

      <!-- Charts -->
      <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <!-- Line Chart: Clinics per Month -->
        <div class="rounded-lg bg-white p-6 shadow">
          <h3 class="mb-4 text-lg font-semibold text-gray-900">Clinics per Month</h3>
          <Line
            :data="{
              labels: charts.clinics_per_month.map(d => d.month),
              datasets: [
                {
                  label: 'Clinics',
                  data: charts.clinics_per_month.map(d => d.count),
                  borderColor: '#10b981',
                  backgroundColor: 'rgba(16, 185, 129, 0.1)',
                  borderWidth: 2,
                  fill: true,
                  tension: 0.4,
                },
              ],
            }"
            :options="chartOptions"
          />
        </div>

        <!-- Bar Chart: Clinics by Department -->
        <div class="rounded-lg bg-white p-6 shadow">
          <h3 class="mb-4 text-lg font-semibold text-gray-900">Clinics by Department</h3>
          <Bar
            :data="{
              labels: Object.keys(charts.clinics_by_department),
              datasets: [
                {
                  label: 'Count',
                  data: Object.values(charts.clinics_by_department),
                  backgroundColor: '#3b82f6',
                },
              ],
            }"
            :options="chartOptions"
          />
        </div>
      </div>

      <!-- Pie Chart: Material Categories -->
      <div class="rounded-lg bg-white p-6 shadow lg:col-span-2">
        <h3 class="mb-4 text-lg font-semibold text-gray-900">Material Categories</h3>
        <div class="max-w-md">
          <Pie
            :data="{
              labels: Object.keys(charts.material_categories),
              datasets: [
                {
                  data: Object.values(charts.material_categories),
                  backgroundColor: ['#10b981', '#3b82f6', '#f59e0b', '#ef4444', '#8b5cf6'],
                },
              ],
            }"
            :options="{ responsive: true, maintainAspectRatio: true }"
          />
        </div>
      </div>
    </div>
  </AppLayout>
</template>
