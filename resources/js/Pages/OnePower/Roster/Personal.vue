<script setup>
import { ref, computed } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import { CalendarRange, Printer, ChevronLeft, ChevronRight } from '@lucide/vue';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    user: { type: Object, required: true },
    notifCounts: { type: Object, required: true },
    menu: { type: Array, required: true },
    isAdmin: { type: Boolean, required: true },
    filters: { type: Object, required: true },
    employee: { type: Object, default: null },
    days: { type: Array, default: () => [] },
});

const DAY_NAMES = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];

const KODE_STYLES = {
    d: 'text-emerald-600',
    n: 'text-blue-600',
    off: 'text-red-600',
    ct: 'text-violet-600',
    h: 'text-orange-500',
    id: 'text-orange-500',
    ids: 'text-orange-500',
    mcu: 'text-orange-500',
};

const kodeClass = (kode) => KODE_STYLES[(kode || '').toLowerCase()] ?? 'text-slate-700';

const monthInput = ref(props.filters.bulan);
const yearInput = ref(props.filters.tahun);

const employeeQuery = ref(props.employee ? `${props.employee.nrp} - ${props.employee.nama}` : '');
const employeeResults = ref([]);
const showEmployeeResults = ref(false);
const selectedNrp = ref(props.filters.nrp);
let searchTimer = null;

const monthLabel = computed(() => props.filters.monthNames[props.filters.bulan] ?? '');

const goTo = (nrp, bulan, tahun) => {
    router.get('/one-power/roster-personal', {
        nrp: nrp || undefined,
        bulan,
        tahun,
    }, { preserveScroll: true, preserveState: true });
};

const submitFilter = () => {
    goTo(selectedNrp.value, monthInput.value, yearInput.value);
};

const prevMonth = () => {
    let m = monthInput.value - 1;
    let y = yearInput.value;
    if (m < 1) { m = 12; y -= 1; }
    monthInput.value = m;
    yearInput.value = y;
    goTo(selectedNrp.value, m, y);
};

const nextMonth = () => {
    let m = monthInput.value + 1;
    let y = yearInput.value;
    if (m > 12) { m = 1; y += 1; }
    monthInput.value = m;
    yearInput.value = y;
    goTo(selectedNrp.value, m, y);
};

const onEmployeeQueryInput = () => {
    clearTimeout(searchTimer);
    selectedNrp.value = '';
    const keyword = employeeQuery.value.trim();
    if (keyword.length < 1) {
        employeeResults.value = [];
        return;
    }
    searchTimer = setTimeout(async () => {
        const response = await fetch(`/one-power/roster-personal/search-employee?keyword=${encodeURIComponent(keyword)}`);
        employeeResults.value = await response.json();
        showEmployeeResults.value = true;
    }, 300);
};

const pickEmployee = (emp) => {
    selectedNrp.value = emp.nrp;
    employeeQuery.value = `${emp.nrp} - ${emp.name}`;
    showEmployeeResults.value = false;
    goTo(emp.nrp, monthInput.value, yearInput.value);
};

const printPage = () => window.print();
</script>

<template>
    <Head title="Roster Personal - OneClick" />

    <AppLayout :user="user" :notif-counts="notifCounts" :menu="menu">
        <div class="mb-5 flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between print:hidden">
            <div>
                <h1 class="mb-1 flex items-center gap-2 text-xl font-extrabold text-slate-800">
                    <CalendarRange class="h-5 w-5 text-blue-500" /> Roster Personal
                </h1>
                <p class="m-0 text-sm text-slate-500">
                    {{ isAdmin ? 'Pilih karyawan untuk melihat roster kalender bulanannya.' : 'Roster kalender bulanan Anda.' }}
                </p>
            </div>
        </div>

        <form class="mb-4 flex flex-wrap items-center gap-2 print:hidden" @submit.prevent="submitFilter">
            <div v-if="isAdmin" class="relative w-full max-w-xs">
                <input
                    v-model="employeeQuery"
                    type="text"
                    autocomplete="off"
                    placeholder="Cari NRP / Nama karyawan..."
                    class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500"
                    @input="onEmployeeQueryInput"
                    @focus="showEmployeeResults = true"
                >
                <div v-if="showEmployeeResults && employeeResults.length" class="absolute z-10 mt-1 w-full rounded-lg border border-slate-200 bg-white shadow-lg">
                    <button
                        v-for="emp in employeeResults"
                        :key="emp.id"
                        type="button"
                        class="block w-full px-3 py-2 text-left text-sm hover:bg-slate-50"
                        @click="pickEmployee(emp)"
                    >
                        <span class="font-semibold text-slate-700">{{ emp.nrp }}</span>
                        <span class="text-slate-500"> &ndash; {{ emp.name }}</span>
                    </button>
                </div>
            </div>

            <button type="button" class="rounded-lg border border-slate-200 p-2 text-slate-500 hover:bg-slate-50" @click="prevMonth">
                <ChevronLeft class="h-4 w-4" />
            </button>
            <select v-model.number="monthInput" class="rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500">
                <option v-for="(name, num) in filters.monthNames" :key="num" :value="Number(num)">{{ name }}</option>
            </select>
            <select v-model.number="yearInput" class="rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500">
                <option v-for="y in filters.years" :key="y" :value="y">{{ y }}</option>
            </select>
            <button type="button" class="rounded-lg border border-slate-200 p-2 text-slate-500 hover:bg-slate-50" @click="nextMonth">
                <ChevronRight class="h-4 w-4" />
            </button>
            <button type="submit" class="rounded-lg bg-blue-500 px-4 py-2 text-sm font-bold text-white hover:bg-blue-600">Tampilkan</button>
            <button type="button" class="flex items-center gap-1.5 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-bold text-white hover:bg-emerald-700" @click="printPage">
                <Printer class="h-4 w-4" /> Print
            </button>
        </form>

        <div v-if="isAdmin && !employee" class="mb-4 rounded-lg border border-blue-100 bg-blue-50 px-3 py-2 text-sm text-blue-700 print:hidden">
            Silakan pilih karyawan untuk melihat roster.
        </div>

        <div v-else-if="!employee" class="mb-4 rounded-lg border border-amber-100 bg-amber-50 px-3 py-2 text-sm text-amber-700 print:hidden">
            Data karyawan tidak ditemukan.
        </div>

        <div v-else class="mx-auto max-w-2xl rounded-xl border border-slate-200 bg-white p-6 shadow-sm print:max-w-full print:border-0 print:shadow-none">
            <div class="mb-3 border-y-2 border-slate-800 py-1.5 text-center text-xs font-bold uppercase">
                Roster Karyawan Dept. Produksi - PPA Jobsite Adaro Indonesia
            </div>

            <div class="mb-4 flex items-start justify-between gap-4">
                <div class="space-y-1 text-xs">
                    <div class="flex"><span class="w-20 font-bold">NAMA</span><span class="w-4">:</span><span>{{ employee.nama }}</span></div>
                    <div class="flex"><span class="w-20 font-bold">NRP</span><span class="w-4">:</span><span>{{ employee.nrp }}</span></div>
                    <div class="flex"><span class="w-20 font-bold">JABATAN</span><span class="w-4">:</span><span>{{ employee.jabatan || '-' }}</span></div>
                </div>
                <div class="min-w-32.5 border-2 border-slate-800 text-center">
                    <div class="border-b-2 border-slate-800 px-3 py-1 text-xs font-bold">UNIT</div>
                    <div class="flex min-h-9 items-center justify-center px-3 py-1.5 text-lg font-bold">{{ employee.unit || '-' }}</div>
                </div>
            </div>

            <div class="mb-3 border border-slate-800 py-1.5 text-center text-xs font-bold">
                PERIODE {{ monthLabel.toUpperCase() }} {{ filters.tahun }}
            </div>

            <table class="w-full border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-200">
                        <th class="border border-slate-800 px-1 py-1 font-bold">NO</th>
                        <th class="border border-slate-800 px-1 py-1 font-bold">HARI</th>
                        <th class="border border-slate-800 px-2 py-1 font-bold">TANGGAL</th>
                        <th class="border border-slate-800 px-1 py-1 font-bold">ROSTER</th>
                        <th class="border border-slate-800 px-1 py-1 font-bold">KETERANGAN</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="d in days" :key="d.tanggal" :class="d.dayOfWeek === 0 ? 'bg-amber-50' : ''">
                        <td class="border border-slate-800 px-1 py-0.5 text-center">{{ d.day }}</td>
                        <td class="border border-slate-800 px-1 py-0.5 text-center">{{ DAY_NAMES[d.dayOfWeek] }}</td>
                        <td class="border border-slate-800 px-2 py-0.5 text-center">{{ String(d.day).padStart(2, '0') }} {{ monthLabel }} {{ filters.tahun }}</td>
                        <td class="border border-slate-800 px-1 py-0.5 text-center font-bold" :class="kodeClass(d.kode)">{{ d.kode }}</td>
                        <td class="border border-slate-800 px-1 py-0.5 text-center">{{ d.keterangan }}</td>
                    </tr>
                </tbody>
            </table>

            <p class="mt-3 text-[0.65rem] text-red-600">
                **Note: Update roster per hari harap menyesuaikan dengan SS6.
            </p>
        </div>
    </AppLayout>
</template>
