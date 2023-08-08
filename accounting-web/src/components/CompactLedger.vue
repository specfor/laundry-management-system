<template>
    <div class="overflow-x-auto flex items-center flex-col w-[300px]">
        <h3>{{ name }}</h3>
        <table class="table">
            <thead>
                <tr>
                    <th>Debit</th>
                    <th>Credit</th>
                </tr>
            </thead>
            <tbody>
                <template v-for="(records, index) in mappedData" :key="index"
                    :set="isCreditRecordsLonger = records.credit.length > records.debit.length">
                    <tr class="month-start">
                        <td></td>
                        <td></td>
                    </tr>
                    <CompactLedgerRows :data="records"></CompactLedgerRows>
                </template>
            </tbody>
        </table>
    </div>
</template>

<script setup>
import CompactLedgerRows from './CompactLedgerRows.vue';
/*

data should be of type,

{
    record_id: number
    account_id: number
    reference: string
    description: string
    credit: number
    debit: number
    tax: number
    timestamp: Date
}[][]

*/
let { data, name } = defineProps(['data', 'name']);

const mappedData = data.map(x => ({
    credit: x.filter(y => y.credit > 0),
    debit: x.filter(y => y.debit > 0)
}));

// https://playcode.io/1556128 (If needed for grouping records into months)
</script>

<style scoped>
tr th:last-child,
tr td:last-child {
    text-align: right;
}

tr {
    border-bottom: 0;
}

th {
    border-bottom: solid 2px rgb(177, 177, 177);
}

tbody tr td:first-child {
    border-right: solid 1px rgb(209, 209, 209);
}

td {
    width: 50%;
    padding-top: 0.25rem;
    padding-bottom: 0.25rem;
}

tr.month-start td {
    padding-top: 2rem;
}
</style>