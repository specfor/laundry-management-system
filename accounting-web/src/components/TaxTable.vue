<template>
    <div v-for="(tax, index) in taxes" :key="index">
        {{ tax.name }} - {{ tax.tax_rate }}
    </div>
</template>

<script setup>
// @ts-check
import { useTaxes } from "../composibles/entity/taxes";

const { getTaxes } = useTaxes();
const taxes = await getTaxes()
</script>

<style>
    
</style>