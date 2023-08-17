<template>
    <!-- <CompactLedger :data="data" :name="'Bank Account'"></CompactLedger> -->
    <!-- <Filter>
        <template #filter-controls="props">
            <Input :label="'Name'" v-model:value="props.values['name']" :placeholder="'Your Name'"></Input>
            <Input :label="'Age'" v-model:value="props.values['age']" :placeholder="'Your Age'" :type="'number'"></Input>
            <Select :label="'City'" :placeholder="'Select City'" :options="selectOptions" v-model:value="props.values['city']"></Select>
        </template>
    </Filter>
    <DateRangeSelector></DateRangeSelector> -->
    <!-- <LedgerRecordEntryTable></LedgerRecordEntryTable> -->
</template>

<script setup>
// @ts-check
import CompactLedger from '../components/CompactLedger.vue';
import Filter from '../components/inputs/Filter.vue';
import Select from '../components/inputs/Select.vue';
import Input from '../components/inputs/Input.vue';
import DateRangeSelector from '../components/inputs/DateRangeSelector.vue';
import LedgerRecordEntryTable from '../components/LedgerRecordEntryTable.vue';

const data = [
    [
        {
            record_id: 12323,
            account_id: 123213,
            credit: 232.0,
            debit: 0,
            description: "dwdwdwdwd",
            timestamp: new Date('2023/04/05'),
        },
        {
            record_id: 12323,
            account_id: 123213,
            credit: 0,
            debit: 2323.0,
            description: "dwdwdwdwd",
            timestamp: new Date('2023/04/05'),
        },
        {
            record_id: 12323,
            account_id: 123213,
            credit: 0,
            debit: 223.0,
            description: "dwdwdwdwd",
            timestamp: new Date('2023/04/05'),
        },
    ],
    [
        {
            record_id: 12323,
            account_id: 123213,
            credit: 232.0,
            debit: 0,
            description: "dwdwdwdwd",
            timestamp: new Date('2023/05/05'),
        },
        {
            record_id: 12323,
            account_id: 123213,
            credit: 0,
            description: "dwdwdwdwd",
            debit: 2323.0,
            timestamp: new Date('2023/05/05'),
        },
        {
            record_id: 12323,
            account_id: 123213,
            credit: 0,
            description: "dwdwdwdwd",
            debit: 223.0,
            timestamp: new Date('2023/05/05'),
        },
    ],
    [
        {
            record_id: 12323,
            account_id: 123213,
            credit: 232.0,
            debit: 0,
            description: "dwdwdwdwd",
            timestamp: new Date('2023/06/05'),
        },
        {
            record_id: 12323,
            account_id: 123213,
            credit: 0,
            debit: 2323.0,
            description: "dwdwdwdwd",
            timestamp: new Date('2023/06/05'),
        },
        {
            record_id: 12323,
            account_id: 123213,
            credit: 0,
            debit: 223.0,
            description: "dwdwdwdwd",
            timestamp: new Date('2023/06/05'),
        },
    ],
]

const selectOptions = [{text: 'Matara', value: 'matara'}, {text: 'Galle', value: 'galle'}, {text: 'Colombo', value: 'colombo'}]

</script>

<style scoped></style>