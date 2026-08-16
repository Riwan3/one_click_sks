<script setup>
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import StatCard from '@/Components/ui/StatCard.vue';
import ReportFilterCard from './Partials/ReportFilterCard.vue';
import ComplianceTable from './Partials/ComplianceTable.vue';

defineProps({
    user: { type: Object, required: true },
    notifCounts: { type: Object, required: true },
    menu: { type: Array, required: true },
    filters: { type: Object, required: true },
    stats: { type: Object, required: true },
    rows: { type: Array, required: true },
});
</script>

<template>
    <Head title="One-Reporting - OneClick" />

    <AppLayout :user="user" :notif-counts="notifCounts" :menu="menu">
        <ReportFilterCard :filters="filters" />

        <div class="mb-4 grid grid-cols-2 gap-3 lg:grid-cols-4">
            <StatCard label="Total Assignment" :value="stats.totalAssignment" color="primary" />
            <StatCard label="Compliant" :value="stats.totalCompliant" color="success" />
            <StatCard label="Non-Compliant" :value="stats.totalNonCompliant" color="danger" />
            <StatCard label="Compliance Rate" :value="`${stats.complianceRate}%`" color="warning" />
        </div>

        <ComplianceTable :rows="rows" />
    </AppLayout>
</template>
