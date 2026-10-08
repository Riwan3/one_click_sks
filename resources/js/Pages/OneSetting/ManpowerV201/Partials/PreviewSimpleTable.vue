<script setup>
// Kotak GROUP LEADER / LAIN - LAIN pada gambar preview: kolom pertama = unit (GL) atau keterangan (Lain-lain).
defineProps({
  title: { type: String, required: true },
  rows: { type: Array, default: () => [] }, // [{ key, label, phone, nrp, name }]
});
</script>

<template>
  <table class="pv-table">
    <colgroup><col style="width: 112px"><col style="width: 100px"><col style="width: 74px"><col></colgroup>
    <thead><tr><th colspan="4" class="pv-cat pv-cat-gl">{{ title }}</th></tr></thead>
    <tbody>
      <tr v-for="row in rows" :key="row.key">
        <td class="pv-unit">{{ row.label }}</td>
        <td class="pv-c">{{ row.phone }}</td>
        <td class="pv-c">{{ row.nrp }}</td>
        <td class="pv-name">{{ row.name }}</td>
      </tr>
      <tr v-if="!rows.length"><td colspan="4" class="pv-c">-</td></tr>
    </tbody>
  </table>
</template>
