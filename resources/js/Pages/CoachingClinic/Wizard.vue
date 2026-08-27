<script setup>
import { ref } from 'vue'
import { useForm } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import { ChevronRight, ChevronLeft } from 'lucide-vue-next'

const props = defineProps({
  clinics: Array,
  tahapans: Array,
})

const currentStep = ref(1)

const form = useForm({
  clinic_id: null,
  tahapan_ids: [],
  progress_pct: 0,
  notes: '',
})

const selectedTahapans = ref([])

const nextStep = () => {
  if (currentStep.value < 4) {
    currentStep.value++
  }
}

const prevStep = () => {
  if (currentStep.value > 1) {
    currentStep.value--
  }
}

const toggleTahapan = (id) => {
  const index = selectedTahapans.value.indexOf(id)
  if (index > -1) {
    selectedTahapans.value.splice(index, 1)
  } else {
    selectedTahapans.value.push(id)
  }
}

const submit = () => {
  form.tahapan_ids = selectedTahapans.value
  form.post(route('coaching-progress.store'))
}

const getSelectedClinicName = () => {
  return props.clinics.find(c => c.id === form.clinic_id)?.employee?.nama || 'N/A'
}

const getSelectedTahapanNames = () => {
  return selectedTahapans.value.map(id => props.tahapans.find(t => t.id === id)?.nama).join(', ')
}
</script>

<template>
  <AppLayout title="Progress Matrix Wizard">
    <div class="mx-auto max-w-2xl space-y-6 p-6">
      <!-- Progress Steps -->
      <div class="rounded-lg bg-white p-6 shadow">
        <div class="mb-8 flex justify-between">
          <div v-for="step in 4" :key="step" :class="{ 'flex items-center flex-1': true }">
            <div :class="{ 'w-10 h-10 rounded-full flex items-center justify-center font-bold text-white': true, 'bg-emerald-600': step <= currentStep, 'bg-gray-300': step > currentStep }">
              {{ step }}
            </div>
            <div v-if="step < 4" :class="{ 'flex-1 h-1 mx-2': true, 'bg-emerald-600': step < currentStep, 'bg-gray-300': step >= currentStep }" />
          </div>
        </div>

        <!-- Step 1: Select Clinic -->
        <div v-if="currentStep === 1" class="space-y-4">
          <h2 class="text-lg font-bold text-gray-900">Step 1: Select Coaching Clinic</h2>
          <div>
            <label class="block text-sm font-medium text-gray-700">Clinic</label>
            <select v-model="form.clinic_id" class="mt-1 w-full rounded border-gray-300">
              <option value="">Select Clinic...</option>
              <option v-for="clinic in clinics" :key="clinic.id" :value="clinic.id">
                {{ clinic.employee?.nama }} ({{ clinic.employee?.nrp }})
              </option>
            </select>
            <p v-if="form.errors.clinic_id" class="mt-1 text-sm text-red-600">{{ form.errors.clinic_id }}</p>
          </div>
        </div>

        <!-- Step 2: Select Tahapan -->
        <div v-if="currentStep === 2" class="space-y-4">
          <h2 class="text-lg font-bold text-gray-900">Step 2: Select Tahapan</h2>
          <div class="space-y-3">
            <label v-for="tahapan in tahapans" :key="tahapan.id" class="flex items-center gap-2">
              <input
                type="checkbox"
                :checked="selectedTahapans.includes(tahapan.id)"
                @change="toggleTahapan(tahapan.id)"
                class="rounded"
              />
              <span class="text-sm text-gray-900">{{ tahapan.nama }}</span>
            </label>
          </div>
          <p v-if="form.errors.tahapan_ids" class="text-sm text-red-600">{{ form.errors.tahapan_ids }}</p>
        </div>

        <!-- Step 3: Enter Progress -->
        <div v-if="currentStep === 3" class="space-y-4">
          <h2 class="text-lg font-bold text-gray-900">Step 3: Enter Progress</h2>
          <div>
            <label class="block text-sm font-medium text-gray-700">Progress Percentage</label>
            <input v-model.number="form.progress_pct" type="range" min="0" max="100" class="mt-1 w-full" />
            <span class="mt-1 block text-center text-lg font-bold text-emerald-600">{{ form.progress_pct }}%</span>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700">Notes</label>
            <textarea v-model="form.notes" class="mt-1 w-full rounded border-gray-300" rows="4" />
          </div>
        </div>

        <!-- Step 4: Review -->
        <div v-if="currentStep === 4" class="space-y-4">
          <h2 class="text-lg font-bold text-gray-900">Step 4: Review</h2>
          <div class="space-y-3 rounded-lg bg-gray-50 p-4">
            <div>
              <p class="text-sm text-gray-600">Clinic</p>
              <p class="font-semibold text-gray-900">{{ getSelectedClinicName() }}</p>
            </div>
            <div>
              <p class="text-sm text-gray-600">Tahapan</p>
              <p class="font-semibold text-gray-900">{{ getSelectedTahapanNames() }}</p>
            </div>
            <div>
              <p class="text-sm text-gray-600">Progress</p>
              <p class="font-semibold text-gray-900">{{ form.progress_pct }}%</p>
            </div>
            <div v-if="form.notes">
              <p class="text-sm text-gray-600">Notes</p>
              <p class="text-gray-900">{{ form.notes }}</p>
            </div>
          </div>
          <p v-if="form.errors.message" class="text-sm text-red-600">{{ form.errors.message }}</p>
        </div>
      </div>

      <!-- Navigation Buttons -->
      <div class="flex justify-between">
        <button
          @click="prevStep"
          :disabled="currentStep === 1"
          :class="{ 'flex items-center gap-2 px-4 py-2 rounded font-medium': true, 'bg-gray-600 text-white hover:bg-gray-700': currentStep > 1, 'bg-gray-300 text-gray-500 cursor-not-allowed': currentStep === 1 }"
        >
          <ChevronLeft :size="18" />
          Back
        </button>

        <button
          v-if="currentStep < 4"
          @click="nextStep"
          :class="{ 'flex items-center gap-2 px-4 py-2 rounded font-medium bg-blue-600 text-white hover:bg-blue-700': true }"
        >
          Next
          <ChevronRight :size="18" />
        </button>

        <button
          v-if="currentStep === 4"
          @click="submit"
          :disabled="form.processing"
          :class="{ 'px-4 py-2 rounded font-medium bg-emerald-600 text-white hover:bg-emerald-700': true, 'opacity-50 cursor-not-allowed': form.processing }"
        >
          Submit
        </button>
      </div>
    </div>
  </AppLayout>
</template>
