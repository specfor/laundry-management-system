<template>
    <CompactLedger :data="data" :name="'Bank Account'"></CompactLedger>
</template>

<script setup>
import CompactLedger from '../components/CompactLedger.vue';

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

</script>

<style scoped></style>