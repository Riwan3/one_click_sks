<script setup>
import PrintResult from './PrintResult.vue';

defineProps({
    item: { type: Object, required: true },
});
</script>

<template>
    <PrintResult :item="item" />
</template>
