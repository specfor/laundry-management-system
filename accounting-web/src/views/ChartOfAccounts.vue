<template>
    <Suspense>
        <RecordTable ref="recordTable" :get-records="loadAccounts" :column-slots="['name', 'type', 'tax']"
            :search-fields="['name', 'description']" :extra-data-fetcher="fetchExtraData" :select-id="selectAccountId">
            <template #table-headers>
                <th>Name</th>
                <th>Type</th>
                <th>Tax Rate</th>
            </template>

            <template #row-actions="{ record }">
                <el-dropdown-item :icon="Delete" command="delete">Delete</el-dropdown-item>
                <el-dropdown-item :icon="TakeawayBox" command="archive">Archive</el-dropdown-item>
                <el-dropdown-item :icon="Edit" command="delete">Change Tax Rate</el-dropdown-item>
            </template>

            <template #header-actions="{ selectedRecords }">
                <div class="tooltip"
                    :data-tip="selectedRecords.length > 0 ? 'Delete Selected Accounts' : 'No selected accounts'">
                    <button :disabled="selectedRecords.length == 0" @click="console.log(selectedRecords)"
                        class="btn btn-square btn-error btn-sm">
                        <svg class="w-4 h-4 text-gray-800 dark:text-white stroke-white" aria-hidden="true"
                            xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 18 20">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M1 5h16M7 8v8m4-8v8M7 1h4a1 1 0 0 1 1 1v3H6V2a1 1 0 0 1 1-1ZM3 5h12v13a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V5Z" />
                        </svg>
                    </button>
                </div>
            </template>

            <template #tax="{ record, extra }">
                <span class="text-base block">
                    {{
                        // @ts-ignore
                        // typeof extra is unknown. This causes an error. But really it's not unknown
                        toTaxType(record.tax_id, extra)?.name
                    }}
                </span>
                <span class="text-sm text-slate-700">
                    {{
                        // @ts-ignore
                        toReadable(toTaxType(record.tax_id, extra)?.tax_rate ?? new
                            Decimal(0))
                    }}%
                </span>
            </template>

            <template #name="{ record }">
                <span class="text-base font-medium">
                    {{ record.name }} - {{ record.account_id }}
                    <div class="badge badge-warning gap-2" v-if="!record.archived">
                        <svg class="w-3 h-3 stroke-current" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                            fill="none" viewBox="0 0 20 16">
                            <path stroke="currentColor" stroke-linejoin="round" stroke-width="2"
                                d="M8 8v1h4V8m4 7H4a1 1 0 0 1-1-1V5h14v9a1 1 0 0 1-1 1ZM2 1h16a1 1 0 0 1 1 1v2a1 1 0 0 1-1 1H2a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1Z" />
                        </svg>
                        Archived
                    </div>
                </span>
                <p class="text-ellipsis">{{ record.description.length > 100 ? record.description.slice(0, 100)
                    +
                    "..." : record.description }} </p>
            </template>

            <template #type="{ record }">
                {{ record.type }}
            </template>
        </RecordTable>
        <template #fallback>
            <div class="fixed top-1/2 left-1/2 flex flex-row justify-center">
                <span class="loading loading-spinner loading-lg"></span>
            </div>
        </template>
    </Suspense>
</template>

<script setup>
// @ts-check
import RecordTable from '../components/RecordTable.vue';
import { useFinancialAccounts } from '../composibles/entity/financial-accounts';
import { ElDropdownItem } from "element-plus";
import { Delete, TakeawayBox, Edit } from '@element-plus/icons-vue';
import { useTaxes } from '../composibles/entity/taxes';
import { toReadable } from '../util/decimal-util';
import { proxyRef } from '../util/proxy-ref';
import { Decimal } from 'decimal.js'
import { ref } from 'vue';

const loadAccounts = () => useFinancialAccounts().getFinanctialAccounts();

const fetchExtraData = async () => await useTaxes().getTaxes().catch(() => [])

const recordTable = /** @type {import('vue').Ref<InstanceType<typeof RecordTable> | null>} */ (ref());

/**
 * 
 * @param {string} command Emitted command
 * @param {import('../types').FinancialAccount} record Record on which it was called
 */
const handleCommand = (command, record) => {

}

/**
 * @param {import('../types').FinancialAccount} account 
 */
const selectAccountId = (account) => account.account_id;

/** 
 * @param {number} taxId
 * @param {import('../types').Tax[]} taxes 
 */
const toTaxType = (taxId, taxes) => taxes.find(tax => tax.tax_id == taxId)
</script>

<style></style>