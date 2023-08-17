<template>
    <tr v-for="(item, index) in rowArray" :key="index" class="hover">
        <td>{{ item[0]?.description ?? "" }}</td>
        <td>{{ item[1]?.description ?? "" }}</td>
    </tr>
</template>

<script setup>
const { data } = defineProps(['data']);

const rowArray = [];
const maxIndex = Math.max(data.credit.length, data.debit.length);

for (let index = 0; index < maxIndex; index++) {
    rowArray.push([data.credit[index] || null, data.debit[index] || null]);
}
</script>

<style scoped>
tr td:last-child {
    text-align: right;
}

tr {
    border-bottom: 0;
}

td:first-child {
    border-right: solid 1px rgb(209, 209, 209);
}

td {
    width: 50%;
    padding-top: 0.25rem;
    padding-bottom: 0.25rem;
}
</style>