<template>
    <div class="overflow-x-auto flex items-center flex-col w-[300px] bg-gray-100 p-5 rounded-lg">
        <h3 class="font-bold text-lg tracking-wide">{{ name }}</h3>
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
// @ts-check
import CompactLedgerRows from './CompactLedgerRows.vue';

const { data, name } = defineProps({
    data: {
        /** @type {import('vue').PropType<import('../types').LedgerRecord[][]>} */
        type: Array,
        default() {
            return [];
        }
    },
    name: String
});

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

.month-start:first-of-type td {
    padding-top: 1rem;
}

tr.month-start td {
    padding-top: 2rem;
}
</style>