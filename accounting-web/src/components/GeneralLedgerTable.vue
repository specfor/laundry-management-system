<template>
    <div class="p-7">
        <h3 class="text-blue-600 text-lg font-bold tracking-wider">View Ledger entry</h3>
        <h4 class="text-gray-500">{{ ids.map(id => `#${id}`).join(", ") }}</h4>

        <div class="overflow-x-auto mt-5">
            <table class="border-t-4 pt-3 border-blue-700 table table-lg table-pin-rows table-pin-cols">
                <thead>
                    <tr class="text-left text-base">
                        <th></th>
                        <th>Description</th>
                        <th>Account</th>
                        <th>Tax Rate</th>
                        <th>Debit</th>
                        <th>Credit</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Ledger records -->
                    <template v-for="(row, index) in enrichedRecords" :key="index">
                        <!-- Record Seperator -->
                        <tr class="entry-data-row">
                            <td class="px-0 pl-3">
                                #{{ row.record_id }}
                            </td>
                            <td colspan="5" class="pl-3">
                                <span class="font-medium">{{ row.narration }}</span>
                                <span class="text-base inline"> | {{ row.date.toDateString() }}</span>
                                <span class="block text-xs text-gray-700">
                                    <UseTimeAgo v-slot="{ timeAgo }" :time="row.createdAt">
                                        Created: {{ timeAgo }}
                                    </UseTimeAgo>
                                </span>
                            </td>
                        </tr>

                        <!-- Records belonging to the entry -->
                        <tr v-for="(record, index) in row.body" :key="index" class="record-row">
                            <td></td>
                            <td>{{ record.description }}</td>
                            <td>
                                <span>{{ record.account.name }}</span>
                                <p v-if="record.account.description" class="text-ellipsis">{{ record.account.description.length > 100 ?
                                    record.account.description.slice(0, 60)
                                    +
                                    "..." : record.account.description }} </p>
                            </td>
                            <td>
                                <!-- Check if the account's tax rate has been overridden -->
                                <template v-if="record.account.tax_id != record.tax.tax_id">
                                    <div class="tooltip tooltip-top" data-tip="Overridden">
                                        <span class="text-base block">
                                            {{ record.tax.name }}
                                        </span>
                                        <span class="text-sm text-slate-700">
                                            {{ toReadable(record.tax.tax_rate) }}%
                                        </span>
                                    </div>
                                </template>
                                <template v-else>
                                    <span class="text-base block">
                                        {{ record.tax.name }}
                                    </span>
                                    <span class="text-sm text-slate-700">
                                        {{ toReadable(record.tax.tax_rate) }}%
                                    </span>
                                </template>
                            </td>
                            <td class="!text-right">
                                <span class="!text-base">{{ record.credit ? toReadable(record.credit) : "" }}</span>
                                
                            </td>
                            <td class="!text-right">
                                <span class="!text-base">{{ record.debit ? toReadable(record.debit) : "" }}</span>
                                
                            </td>
                        </tr>

                        <!-- This row will only be shown if there is more than 1 entry to be shown -->
                        <template v-if="enrichedRecords.length > 1">
                            <!-- Entry taxes -->
                            <tr class="entry-summary-row">
                                <td></td>
                                <td colspan="2"></td>
                                <td>Taxes</td>
                                <td>{{ toReadable(row.totalTaxes.debit) }}</td>
                                <td>{{ toReadable(row.totalTaxes.credit) }}</td>
                            </tr>

                            <!-- Entry totals -->
                            <tr class="entry-summary-row">
                                <td></td>
                                <td colspan="2"></td>
                                <td>Total</td>
                                <td>{{ toReadable(row.totalAmount) }}</td>
                                <td>{{ toReadable(row.totalAmount) }}</td>
                            </tr>
                        </template>
                    </template>
                    <!-- Final total of taxes -->
                    <tr class="summary-row">
                        <td colspan="3"></td>
                        <td class="font-semibold">Total Taxes</td>
                        <td class="top-bottom-border">{{ toReadable(totalTaxes.debit) }}</td>
                        <td class="top-bottom-border">{{ toReadable(totalTaxes.credit) }}</td>
                    </tr>

                    <!-- Final total of totals -->
                    <tr class="summary-row">
                        <td colspan="3"></td>
                        <td class="font-semibold">NET TOTAL</td>
                        <td class="double-under">{{ toReadable(netTotal) }}</td>
                        <td class="double-under">{{ toReadable(netTotal) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>

<script setup lang="ts">
// @ts-check
import Decimal from "decimal.js";
import { useLedgerRecords } from "../composibles/entity/ledger-records";
import { useTaxes } from "../composibles/entity/taxes";
import type { FinancialAccount, LedgerRecord, Tax } from "../types";
import type { Object } from 'ts-toolbelt'
import { UseTimeAgo } from '@vueuse/components'
import { useFinancialAccounts } from "../composibles/entity/financial-accounts";
import { toReadable } from '../util/decimal-util';

type EnrichedLedgerRecord = {
    record_id: number
    narration: string
    date: Date
    totalAmount: Decimal
    totalTaxes: {
        debit: Decimal
        credit: Decimal
    },
    createdAt: Date
    body: Object.Either<{
        account: FinancialAccount
        debit: Decimal
        credit: Decimal
        description: string
        tax: Tax
    }, 'credit' | 'debit'>[]
}

const { getLedgerRecordById } = useLedgerRecords();
const { getTax } = useTaxes()
const { getFinancialAccountById, getSalesTaxAccount } = useFinancialAccounts()

const { ids } = defineProps<{
    ids: number[]
}>();

const records =
    await Promise.all(ids.map(id => getLedgerRecordById(id).catch(() => undefined)))
        .then(data => data.filter(x => x) as LedgerRecord[])

const taxes = await Promise.all(
    records.map(
        record => record.body
            .map(x => x.tax_id)
            .map(id => getTax(id).catch(() => undefined))
    ).flat()
).then(data => data.filter(x => x) as Tax[])

const accounts = await Promise.all(
    records.map(
        record => record.body
            .map(x => x.account_id)
            .map(id => getFinancialAccountById(id).catch(() => undefined))
    ).flat()
).then(data => data.filter(x => x) as FinancialAccount[])

const salesTaxAccount = await getSalesTaxAccount().catch(() => undefined);

const enrichedRecords: EnrichedLedgerRecord[] = records.map(({ body, ...rest }) => {
    const [taxRecords, normalRecords] = salesTaxAccount ?
        [
            body.filter(record => record.account_id == salesTaxAccount.account_id),
            body.filter(record => record.account_id != salesTaxAccount.account_id)
        ] :
        [[], []]

    const [debitTaxTotal, creditTaxTotal] = [
        taxRecords.reduce((acc, curr) => {
            return acc.add(curr.debit ?? new Decimal(0))
        }, new Decimal(0)),
        taxRecords.reduce((acc, curr) => {
            return acc.add(curr.credit ?? new Decimal(0))
        }, new Decimal(0))
    ]

    return {
        ...rest,
        totalTaxes: {
            debit: debitTaxTotal,
            credit: creditTaxTotal
        },
        body: normalRecords.map(({ account_id, tax_id, ...remainder }) => ({
            ...remainder,
            account: accounts.find(acc => acc.account_id == account_id),
            tax: taxes.find(tax => tax.tax_id == tax_id)
        })).filter(item => item.account && item.tax) as EnrichedLedgerRecord['body']
    }
})

const totalTaxes = enrichedRecords.reduce((acc, curr) => ({
    credit: acc.credit.add(curr.totalTaxes.credit),
    debit: acc.debit.add(curr.totalTaxes.debit)
}), {
    credit: new Decimal(0),
    debit: new Decimal(0)
})

const netTotal = enrichedRecords.reduce((acc, curr) => acc.add(curr.totalAmount), new Decimal(0))
</script>

<style scoped>
.record-row {
    @apply p-1;
}

.record-row td {
    @apply text-sm p-3;
}

.entry-data-row {
    @apply bg-gray-200;
}

.entry-data-row td {
    @apply py-2;
}

.summary-row:nth-child(2) {
    @apply font-semibold;
}

.summary-row {
    @apply text-right;
}

.summary-row td {
    @apply p-3;
}

.entry-summary-row td{
    @apply py-1 text-gray-600 px-3;
}

.entry-summary-row {
    @apply text-right border-none;
}

.double-under {
    border-bottom: 3px double #494949;
}

.top-bottom-border {
    border-bottom: 2px solid #2b2b2b;
    border-top: 1px solid #2b2b2b;
}
</style>