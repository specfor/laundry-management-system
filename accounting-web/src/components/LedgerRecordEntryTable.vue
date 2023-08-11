<template>
    <RecordEntryTable v-bind="recordEntryTableOptions" @on-save-clickd="data => console.log(data)"></RecordEntryTable>
</template>

<script setup>
// @ts-check
import RecordEntryTable from './RecordEntryTable.vue';

/** @type {import('../types').ComponentOptions<typeof RecordEntryTable>} */
const recordEntryTableOptions = {
    columns: [
        {
            name: "Description",
            type: 'text'
        },
        {
            name: "Account",
            type: 'text'
        },
        {
            name: "Reference",
            type: 'text'
        },
        {
            name: "Debit",
            type: 'number'
        },
        {
            name: "Credit",
            type: 'number'
        },
    ],
    initialRows: 4
}

</script>

<style></style>