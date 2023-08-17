<template>
    <Suspense>
        <LedgerRecordEntryTable :initial-rows="4"></LedgerRecordEntryTable>
        <template #fallback>
            <span class="loading loading-spinner loading-lg"></span>
        </template>
    </Suspense>
</template>

<script setup>
// @ts-check
import LedgerRecordEntryTable from '../components/LedgerRecordEntryTable.vue';
</script>

<style></style>