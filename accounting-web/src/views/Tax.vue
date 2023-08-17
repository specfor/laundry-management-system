<template>
    <Suspense>
        <TaxTable></TaxTable>
        <template #fallback>
            
        </template>
    </Suspense>
</template>

<script setup>
// @ts-check
import { Suspense } from "vue";
import TaxTable from "../components/TaxTable.vue";
</script>

<style>
    
</style>