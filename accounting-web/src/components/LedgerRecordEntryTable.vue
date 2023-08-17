<template>
    <div class="alert alert-warning mb-4" :class="{'hidden': topWarningHidden}">
        <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
        </svg>
        <span>We recommend that only your accountant/bookkeeper create journals, unless you have experience making your
            general ledger</span>
        <button class="btn btn-circle btn-sm" @click="topWarningHidden = true">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>
    <div class="flex flex-row gap-3 justify-around mb-10">
        <div class="form-control w-[320px]">
            <label class="label">
                <span class="label-text">Narration</span>
            </label>
            <textarea v-model="narration" class="textarea textarea-bordered h-16" placeholder="Narration"></textarea>
        </div>

        <div class="form-control">
            <label class="label">
                <span class="label-text">Date of Entry</span>
            </label>
            <el-date-picker v-model="date" type="date" placeholder="Date" :shortcuts="datePickerShortcuts" size="default" />
        </div>

        <div class="form-control w-[170px] max-w-xs">
            <label class="label">
                <span class="label-text">Amounts are</span>
            </label>
            <select class="select select-sm select-bordered" v-model="taxType">
                <option value="tax exclusive" selected>Tax Exclusive</option>
                <option value="tax inclusive">Tax Inclusive</option>
                <option value="no tax">No Tax</option>
            </select>
        </div>
    </div>
    <div class="overflow-x-auto bg-slate-100 p-3">
        <table class="table table-lg md:table-fixed">
            <thead>
                <tr class="text-base">
                    <th class="md:w-4"></th>
                    <th class="md:w-[320px]">Description</th>
                    <th>Account</th>
                    <th>Tax Rate</th>
                    <th>Debit LKR</th>
                    <th>Credit LKR</th>
                    <th class="md:w-4"></th>
                </tr>
            </thead>
            <draggable v-model="rows" :disabled="dragDisabled" item-key="order" handle=".entry-table-handle" tag='tbody'
                animation="200">
                <template #item="{ element }">
                    <tr class="entry-row">
                        <td class="!p-3">
                            <img class="entry-table-handle cursor-move " src="../assets/6-vertical-dots.svg">
                        </td>
                        <td>
                            <!-- Description -->
                            <input v-model="element.description" type="text" placeholder="Description"
                                class="input input-ghost w-full max-w-xs" />
                        </td>
                        <td>
                            <!-- Accounts -->
                            <el-select class="h-full border-none" v-model="element.accountId" filterable
                                placeholder="Account" @change="e => handleAccountChange(e, element)">
                                <el-option v-for="item in accounts" :key="item.account_id" :label="item.name"
                                    :value="item.account_id" />
                                <template #empty>
                                    No accounts ...
                                </template>
                            </el-select>
                        </td>
                        <td>
                            <!-- Tax Rate -->
                            <el-select class="h-full border-none" v-model="element.taxId" filterable placeholder="Tax Type">
                                <el-option v-for="item in taxes" :key="item.tax_id" :label="item.name" :value="item.tax_id">
                                    <span class="float-left mr-2">{{ item.name }}</span>
                                    <span class="text-gray-400 float-right">
                                        {{ item.tax_rate.mul(new Decimal(100)).toDecimalPlaces(2).toString() + "%" }}
                                    </span>
                                </el-option>
                                <template #empty>
                                    No accounts ...
                                </template>
                            </el-select>
                        </td>
                        <td>
                            <!-- Debit -->
                            <input v-model="element.debit" type="text" placeholder="Debit"
                                class="input input-ghost w-full max-w-xs"
                                :class="{ 'input-error': creditAndDebit(element) || !isProperNumberString(element.debit) }" />
                        </td>
                        <td>
                            <!-- Credit -->
                            <input v-model="element.credit" type="text" placeholder="Credit"
                                class="input input-ghost w-full max-w-xs"
                                :class="{ 'input-error': creditAndDebit(element) || !isProperNumberString(element.credit) }" />
                        </td>
                        <td class="!p-3">
                            <svg @click="removeItem(element)"
                                class="w-6 h-6 text-gray-800 dark:text-white fill-gray-800 hover:fill-red-600 cursor-pointer transition-all duration-200"
                                aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor"
                                viewBox="0 0 18 20">
                                <path
                                    d="M17 4h-4V2a2 2 0 0 0-2-2H7a2 2 0 0 0-2 2v2H1a1 1 0 0 0 0 2h1v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V6h1a1 1 0 1 0 0-2ZM7 2h4v2H7V2Zm1 14a1 1 0 1 1-2 0V8a1 1 0 0 1 2 0v8Zm4 0a1 1 0 0 1-2 0V8a1 1 0 0 1 2 0v8Z" />
                            </svg>
                        </td>
                    </tr>
                </template>
                <template #footer>
                    <tr class="table-summary-row">
                        <td colspan="3">
                            <button class="btn btn-primary btn-sm" @click="addItem">
                                <svg class="w-4 h-4 text-gray-800 dark:text-white" aria-hidden="true"
                                    xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none"
                                    viewBox="0 0 20 20">
                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M10 5.757v8.486M5.757 10h8.486M19 10a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                </svg>
                                Add new Entry
                            </button>
                        </td>
                        <td class="font-bold border-b-[1px] border-gray-700">Subtotal</td>
                        <td class="text-right border-b-[1px] border-gray-700 border-l-[1px] border-l-gray-300">{{
                            subTotal.debit.isZero() ? "-" : subTotal.debit.toDecimalPlaces(3).toString() }}</td>
                        <td class="text-right border-b-[1px] border-gray-700 border-l-[1px] border-l-gray-300">{{
                            subTotal.credit.isZero() ? "-" : subTotal.credit.toDecimalPlaces(3).toString() }}</td>
                        <td></td>
                    </tr>
                    <tr class="table-summary-row">
                        <td colspan="3">
                        </td>
                        <td class="font-bold border-b-2 border-gray-700">Taxes</td>
                        <td class="text-right border-b-2 border-gray-700 border-l-[1px] border-l-gray-300">{{
                            taxTotal.debit.isZero() ? "-" : taxTotal.debit.toDecimalPlaces(3).toString() }}</td>
                        <td class="text-right border-b-2 border-gray-700 border-l-[1px] border-l-gray-300">{{
                            taxTotal.credit.isZero() ? "-" : taxTotal.credit.toDecimalPlaces(3).toString() }}</td>
                        <td></td>
                    </tr>
                    <tr class="table-summary-row">
                        <td colspan="3"></td>
                        <td class="font-bold border-b-4 border-gray-700 border-double">Total</td>
                        <td class="text-right border-b-4 border-gray-700 border-double border-l-[1px] border-l-gray-300">
                            {{ subTotal.debit.minus(taxTotal.debit).toDecimalPlaces(3).toString() + hasErrors ? "⚠" : "" }}
                        </td>
                        <td class="text-right border-b-4 border-gray-700 border-double border-l-[1px] border-l-gray-300">
                            {{ subTotal.credit.minus(taxTotal.credit).toDecimalPlaces(3).toString() + hasErrors ? "⚠" : ""
                            }}
                        </td>
                        <td></td>
                    </tr>
                </template>
            </draggable>
        </table>
    </div>

    <div class="w-full flex justify-between m-4">
        <button class="btn btn-primary rounded-md self-start w-[150px]">Save as Draft</button>
        <div class="flex flex-row gap-4">
            <button @click="emitOnSaveClicked" class="btn btn-accent rounded-md  self-end w-[90px]">Save</button>
            <button class="btn btn-active rounded-md self-end w-[90px]">Clear</button>
        </div>
    </div>

    <div class="flex flex-row justify-around flex-wrap">
        <div class="card max-w-sm w-auto m-5 bg-slate-600 text-neutral-content">
            <div class="card-body text-center flex flex-col items-start">
                <h2 class="card-title">Shortcut Keys</h2>
                <div class="">
                    <div class="m-2">
                        <span class="mr-4">Undo</span>
                        <kbd class="kbd text-gray-700">Ctrl</kbd>
                        +
                        <kbd class="kbd text-gray-700">Z</kbd>
                    </div>
                    <div class="m-2">
                        <span class="mr-4">Redo</span>
                        <kbd class="kbd text-gray-700">Ctrl</kbd>
                        +
                        <kbd class="kbd text-gray-700">Y</kbd>
                    </div>
                </div>
            </div>
        </div>

        <div class="p-5 flex flex-col items-center">
            <h3>Saved Drafts</h3>
            <div class="overflow-x-auto w-[400px] mt-4">
                <table class="table table-zebra">
                    <!-- head -->
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Date</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Cy Ganderton</td>
                            <td>Quality Control Specialist</td>
                            <td>
                                <button class="btn btn-primary rounded-md btn-sm">Load</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>

<script setup>
// @ts-check
import { useMagicKeys, useRefHistory, whenever } from '@vueuse/core';
import { computed, ref } from 'vue';
// https://github.com/SortableJS/vue.draggable.next
import draggable from 'vuedraggable'
import { useTaxes } from '../composibles/entity/taxes';
import { useFinancialAccounts } from '../composibles/entity/financial-accounts';
import { ElSelect, ElOption, ElDatePicker } from "element-plus";
import Decimal from 'decimal.js';

const { getTaxes } = useTaxes();
const { getFinanctialAccounts } = useFinancialAccounts()

const datePickerShortcuts = [
    {
        text: 'Today',
        value: new Date(),
    },
    {
        text: 'Yesterday',
        value: () => {
            const date = new Date()
            date.setTime(date.getTime() - 3600 * 1000 * 24)
            return date
        },
    }
]

const topWarningHidden = ref(false);

/**
 * @typedef Row
 * @property {number} order
 * @property {string} [description]
 * @property {number} [accountId]
 * @property {number} [taxId]
 * @property {string} [credit]
 * @property {string} [debit]
 */

const { initialRows, ...otherProps } = defineProps({
    initialRows: {
        type: Number,
        default: 3,
    },
    dragDisabled: {
        type: Boolean,
        default: false
    },
})

const emit = defineEmits(['onSaveClickd']);

const count = ref(0);

const rows = ref(/** @type {Row[]}*/(
    (new Array(initialRows).fill(0))
        .map(() => ({ order: count.value++ }))
));

const narration = ref("");
const date = ref(new Date());
const taxType = ref("tax exclusive")
const { undo, redo } = useRefHistory(rows)

const { Ctrl_Z, Ctrl_Y } = useMagicKeys();

whenever(Ctrl_Z, () => undo())
whenever(Ctrl_Y, () => redo())

const taxes = await getTaxes()
const accounts = await getFinanctialAccounts()


const hasErrors = computed(() => rows.value.every((row) => {
    return creditAndDebit(row) ||
        !isProperNumberString(row.credit) ||
        !isProperNumberString(row.debit)
}))

/** @param {Decimal.Value} val */
const toDecimal = (val) => new Decimal(val);

/** 
 * @param {Row[]} rows
 */
const validRows = (rows) =>
    /** @type {(Omit<Row, "debit" | "credit"> & { credit: Decimal.Value; debit: Decimal.Value })[]} */
    (rows.filter(row => !creditAndDebit(row) && isProperNumberString(row.credit) && isProperNumberString(row.debit)))

const subTotal = computed(() => {
    if (hasErrors.value) return { credit: new Decimal(0), debit: new Decimal(0) }

    const credit = validRows(rows.value).map(row => row.credit).map(toDecimal).reduce(Decimal.add, new Decimal(0))
    const debit = validRows(rows.value).map(row => row.debit).map(toDecimal).reduce(Decimal.add, new Decimal(0))
    return { debit, credit };
})

const taxTotal = computed(() => {
    if (hasErrors.value) return { credit: new Decimal(0), debit: new Decimal(0) }

    /** @param {number} taxId */
    const getTaxRate = (taxId) => taxes.find(tax => tax.tax_id == taxId)?.tax_rate

    /** 
     * @param {import('ts-toolbelt').O.Merge<Row, { debit: Decimal.Value; credit: Decimal.Value }>} row
     * @param {keyof Row} key
     */
    const mapToTaxValue = (row, key) => new Decimal(row[key] ?? "0").times(getTaxRate(row.taxId ?? 0) ?? new Decimal(0)).dividedBy(100)

    const debit = validRows(rows.value).map(row => mapToTaxValue(row, 'debit')).reduce(Decimal.add, new Decimal(0))
    const credit = validRows(rows.value).map(row => mapToTaxValue(row, 'credit')).reduce(Decimal.add, new Decimal(0))
    return { debit, credit };
})

const addItem = () => rows.value = ([...rows.value, { order: count.value++ }]);

/** @param {Row} item */
const removeItem = (item) => rows.value = [...rows.value.filter(x => x != item)];

const emitOnSaveClicked = () =>
    emit('onSaveClickd',
        // Remove out the order property
        rows.value.map(row => {
            const { order, ...rest } = row;
            return /** @type {Object.<string, ( string | number )>}*/(rest);
        })
    )

/**
 * Sets the row's tax rate to the one in the account automatically
 * @param {number} accountId 
 * @param {Row} element 
 */
const handleAccountChange = (accountId, element) => {
    element.taxId = accounts.find(account => account.account_id == accountId)?.tax_id
}

// Validation functions

/**
 * Checks if  both credit and debit values of the row is set
 * @param {Row} row 
 */
const creditAndDebit = (row) => (row.credit != "" && row.credit != undefined) && (row.debit != "" && row.debit != undefined)

/**
 * Checks if the string is a value Decimal constructor can take
 * @type {(text: string | undefined) => boolean}
 */
const isProperNumberString = (text) => {
    if (text === undefined) return false;

    try {
        new Decimal(text)
        return true; // All good
    } catch (_e) {
        // Constructior threw an error
        return false;
    }
}
</script>

<style scoped>
.table-summary-row td {
    @apply py-2 text-sm;
}

.icon-row {
    @apply p-2;
}

.entry-row td {
    @apply border-l-[1px] border-gray-300 p-0;
}

.entry-row td:first-of-type {
    @apply border-l-0;
}</style>