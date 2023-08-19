<template>
    <Suspense>
        <LedgerRecordEntryTable :initial-rows="4"></LedgerRecordEntryTable>
        <template #fallback>
            <div class="fixed top-1/2 left-1/2 flex flex-row justify-center">
                <span class="loading loading-spinner loading-lg"></span>
            </div>
        </template>
    </Suspense>
</template>

<script setup>
// @ts-check
import LedgerRecordEntryTable from '../components/LedgerRecordEntryTable.vue';
</script>

<style></style>