<template>
    <div class="p-7">
        <h3 class="text-blue-600 text-lg font-bold tracking-wider">{{ name }}</h3>
        <h4 class="text-gray-500">{{ sub }}</h4>

        <div class="overflow-x-auto mt-5">
            <table class="border-t-4 pt-3 border-blue-700 table table-lg table-pin-rows table-pin-cols">
                <thead>
                    <tr class="text-left text-base">
                        <th>Transaction / Reference</th>
                        <th>Date</th>
                        <th>Debit</th>
                        <th>Credit</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(row, index) in rows" :key="index" class="hover">
                        <td>
                            {{ row.description }}
                            <span class="text-slate-500 block">{{ row.reference }}</span>
                        </td>
                        <td>{{ row.timestamp.toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' }) }}
                        </td>
                        <td>{{ row.debit > 0 ? row.debit.toFixed(2) : "" }}</td>
                        <td>{{ row.credit > 0 ? row.credit.toFixed(2) : "" }}</td>
                    </tr>
                    <tr class="hover border-t-4 border-double border-gray-600">
                        <td colspan="2">
                            <b>Net Movement</b>
                        </td>
                        <td>
                            <template v-if="isDebitMore">
                                <span class="text-slate-500 text-sm">RS. </span>
                                <b>{{ diff.toFixed(2) }}</b>
                            </template>
                        </td>
                        <td>
                            <template v-if="!isDebitMore">
                                <span class="text-slate-500 text-sm">RS. </span>
                                <b>{{ diff.toFixed(2) }}</b>
                            </template>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>

<script setup>
// @ts-check

import { useNetMovement } from "../composibles/net-movement";

const { name, sub, rows } = defineProps({
    name: String,
    sub: String,
    rows: {
        // For better IntelliSense
        /** @type {import('vue').PropType<import('../types').LedgerRecord[]>} */
        type: Array
    }
});

const { diff, isDebitMore } = useNetMovement(rows ?? [], 'debit', 'credit');

</script>

<style scoped></style>