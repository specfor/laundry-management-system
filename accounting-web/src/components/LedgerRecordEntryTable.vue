<template>
    <ModelTemplate v-slot="{ resolve, reject, args }">
        <div class="fixed inset-0 bg-gray-300/30 flex items-center justify-center z-30">
            <!-- Draft save Model -->
            <template v-if="args[0] == 'draft-save'">
                <div class="card w-96 bg-white shadow-lg">
                    <div class="card-body items-center text-center">
                        <h2 class="card-title">Save Draft</h2>
                        <input ref="draftNameInput" type="text" placeholder="Draft Name"
                            class="input input-bordered input-sm w-full max-w-xs" />
                        <div class="card-actions justify-end">
                            <button class="btn btn-primary"
                                @click="e => draftNameInput?.value != '' || draftNameInput?.value != undefined ? resolve({ response: 'save', draftSaveName: draftNameInput?.value }) : null">Save</button>
                            <button class="btn btn-ghost" @click="reject({ response: 'cancel' })">Cancel</button>
                        </div>
                    </div>
                </div>
            </template>

            <!-- Draft delete Model -->
            <template v-else-if="args[0] == 'delete-draft-confirm'">
                <div class="card w-96 bg-white shadow-lg">
                    <div class="card-body items-center text-center">
                        <h2 class="card-title">Delete Draft</h2>
                        <p>You're about to delete a saved Draft</p>
                        <div class="card-actions justify-end">
                            <button class="btn btn-error" @click="resolve({ response: 'delete' })">Delete</button>
                            <button class="btn btn-ghost" @click="reject({ response: 'cancel' })">Cancel</button>
                        </div>
                    </div>
                </div>
            </template>

            <!-- Draft Load but data exists Model -->
            <template v-else-if="args[0] == 'draft-load-data-exists-warning'">
                <div class="card w-96 bg-white shadow-lg">
                    <div class="card-body items-center text-center">
                        <h2 class="card-title">Load Draft</h2>
                        <p>You're about to load a Draft, but there is some data already the entry table. Those records will
                            be lost if you continue. You can either save them into another draft if you need to.</p>
                        <div class="card-actions justify-end">
                            <button class="btn btn-error" @click="resolve({ response: 'load' })">Load Anyway</button>
                            <button class="btn btn-ghost" @click="reject({ response: 'cancel' })">Cancel</button>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </ModelTemplate>

    <!-- Saving records Model -->
    <Teleport to="body">
        <template v-if="isLoadingModelVisible">
            <div class="fixed inset-0 bg-gray-300/30 flex items-center justify-center z-30">
                <form method="dialog" class="modal-box flex flex-row justify-center w-auto">
                    <span class="loading loading-spinner loading-lg"></span>
                    <div class="ml-5 prose">
                        <h3 class="font-bold text-xl p-0 m-0">Saving the records</h3>
                        <p>Please wait .... </p>
                    </div>
                </form>
            </div>
        </template>
    </Teleport>

    <h1 class="text-2xl m-5">Ledger Record Entry</h1>
    <div class="alert alert-warning mb-4 md:w-full w-[600px]" :class="{ 'hidden': topWarningHidden }">
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
            <el-date-picker v-model="entryDate" type="date" placeholder="Date" :shortcuts="datePickerShortcuts"
                size="default" />
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
    <div class="overflow-x-auto bg-slate-100 p-3 transition-all duration-200"
        :class="{ 'table-error-glow': errorMessages.length > 0 }">
        <table class="table table-lg md:table-fixed">
            <thead>
                <tr class="text-base">
                    <th class="md:w-4"></th>
                    <th class="md:w-[320px]">Description</th>
                    <th class="px-0 pl-1">Account</th>
                    <th class="px-0 pl-1">Tax Rate</th>
                    <th class="px-0 pl-1">Debit LKR</th>
                    <th class="px-0 pl-1">Credit LKR</th>
                    <th class="md:w-4"></th>
                </tr>
            </thead>
            <draggable v-model="rows" :disabled="dragDisabled" item-key="order" handle=".entry-table-handle" tag='tbody'
                animation="200">
                <template #item="{ element }">
                    <tr class="entry-row">
                        <!-- Drag Handler icon -->
                        <td class="!p-1 !pl-3">
                            <img class="entry-table-handle cursor-move " src="../assets/6-vertical-dots.svg">
                        </td>
                        <!-- Description -->
                        <td>    
                            <input v-model="element.description" type="text" placeholder="Description"
                                class="input input-ghost input-sm w-full max-w-xs" />
                        </td>
                        <!-- Accounts -->
                        <td>
                            <el-select class="h-full border-none" v-model="element.accountId" filterable
                                placeholder="Account" @change="e => handleAccountChange(e, element)">
                                <el-option v-for="item in accounts" :key="item.account_id" :label="item.name"
                                    :value="item.account_id" />
                                <template #empty>
                                    No accounts ...
                                </template>
                            </el-select>
                        </td>
                        <!-- Tax Rate -->
                        <td>
                            <el-select class="h-full border-none" v-model="element.taxId" filterable placeholder="Tax Type">
                                <el-option v-for="item in taxes" :key="item.tax_id" :label="item.name" :value="item.tax_id">
                                    <span class="float-left mr-2">{{ item.name }}</span>
                                    <span class="text-gray-400 float-right">
                                        {{ toReadable(item.tax_rate) + "%" }}
                                    </span>
                                </el-option>
                                <template #empty>
                                    <span>No accounts ...</span>
                                </template>
                            </el-select>
                        </td>
                        <!-- Debit -->
                        <td>
                            <input v-model="element.debit" type="text" placeholder="Debit"
                                class="input input-ghost w-full max-w-xs input-sm"
                                :class="{ 'input-error': element.debit ? !isRowDebitCreditValid(element) : false }" />
                        </td>
                        <!-- Credit -->
                        <td>
                            <input v-model="element.credit" type="text" placeholder="Credit"
                                class="input input-ghost w-full max-w-xs input-sm"
                                :class="{ 'input-error': element.credit ? !isRowDebitCreditValid(element) : false }" />
                        </td>
                        <!-- Remove Row Icon -->
                        <td class="!p-1 !pl-3">
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
                            toReadableTotal(subTotal.debit) }}</td>
                        <td class="text-right border-b-[1px] border-gray-700 border-l-[1px] border-l-gray-300">{{
                            toReadableTotal(subTotal.credit) }}</td>
                        <td></td>
                    </tr>
                    <tr class="table-summary-row">
                        <td colspan="3">
                        </td>
                        <td class="font-bold border-b-2 border-gray-700">Taxes</td>
                        <td class="text-right border-b-2 border-gray-700 border-l-[1px] border-l-gray-300">{{
                            toReadableTotal(taxTotal.debit) }}</td>
                        <td class="text-right border-b-2 border-gray-700 border-l-[1px] border-l-gray-300">{{
                            toReadableTotal(taxTotal.credit) }}</td>
                        <td></td>
                    </tr>
                    <tr class="table-summary-row">
                        <td colspan="3"></td>
                        <td class="font-bold border-b-4 border-gray-700 border-double">Total</td>
                        <td class="text-right border-b-4 border-gray-700 border-double border-l-[1px] border-l-gray-300">
                            {{ toReadableTotal(total.debit) }}
                        </td>
                        <td class="text-right border-b-4 border-gray-700 border-double border-l-[1px] border-l-gray-300">
                            {{ toReadableTotal(total.credit) }}
                        </td>
                        <td></td>
                    </tr>
                </template>
            </draggable>
        </table>

        <div>
            <!-- Error Messages -->
            <TransitionGroup name="error-list" tag="ul">
                <div v-for="item in errorMessages" :key="item" class="alert alert-error my-2 w-full">
                    <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>{{ item }}</span>
                </div>
            </TransitionGroup>
        </div>
    </div>

    <div class="w-full flex justify-between px-6 m-4">
        <template v-if="!dataExists">
            <div class="tooltip tooltip-right" data-tip="There is no data to be saved in the draft">
                <button class="btn btn-accent rounded-md btn-disabled self-end w-[150px]">Save as Draft</button>
            </div>
        </template>
        <button v-else @click="saveDraft" class="btn btn-primary rounded-md w-[150px]">Save as
            Draft</button>
        <div class="flex flex-row gap-4">
            <template v-if="errorMessages.length > 0">
                <div class="tooltip" data-tip="Please resolve the errors before saving">
                    <button class="btn btn-accent rounded-md btn-disabled self-end w-[90px]">Save</button>
                </div>
            </template>
            <template v-else-if="!dataExists">
                <div class="tooltip" data-tip="Write some entries before saving">
                    <button class="btn btn-accent rounded-md btn-disabled self-end w-[90px]">Save</button>
                </div>
            </template>
            <button v-else @click="saveRecords" class="btn btn-accent rounded-md  self-end w-[90px]">Save</button>
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
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(draft, index) in getDrafts()" :key="index">
                            <td>{{ draft.name }}</td>
                            <td>{{ formatTimeAgo(draft.date) }}</td>
                            <td>
                                <button @click="loadDraft(draft)" class="btn btn-primary rounded-md btn-sm">Load</button>
                                <!-- <button @click="removeDraft(draft)" class="btn btn-error rounded-md btn-sm">Remove</button> -->

                            </td>
                            <td>
                                <svg @click="removeDraft(draft)"
                                    class="w-5 h-5 text-gray-800 dark:text-white fill-red-500 hover:fill-red-600 cursor-pointer transition-all duration-200"
                                    aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor"
                                    viewBox="0 0 18 20">
                                    <path
                                        d="M17 4h-4V2a2 2 0 0 0-2-2H7a2 2 0 0 0-2 2v2H1a1 1 0 0 0 0 2h1v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V6h1a1 1 0 1 0 0-2ZM7 2h4v2H7V2Zm1 14a1 1 0 1 1-2 0V8a1 1 0 0 1 2 0v8Zm4 0a1 1 0 0 1-2 0V8a1 1 0 0 1 2 0v8Z" />
                                </svg>
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
import { useStorage, useConfirmDialog, formatTimeAgo, createTemplatePromise } from '@vueuse/core';
import { computed, ref, Teleport } from 'vue';
// https://github.com/SortableJS/vue.draggable.next
import draggable from 'vuedraggable'
import { useTaxes } from '../composibles/entity/taxes';
import { useUndoRedo } from "../composibles/undo-redo";
import { useFinancialAccounts } from '../composibles/entity/financial-accounts';
import { useLedgerRecords } from '../composibles/entity/ledger-records';
import { ElSelect, ElOption, ElDatePicker } from "element-plus";
import Decimal from 'decimal.js';
import { toReadable, toReadableTotal, toDecimal, decimalReducer, isProperNumberString } from "../util/decimal-util";
import { NotTakeOne } from "../util/func-wrappers";

const { getTaxes } = useTaxes();
const { getFinanctialAccounts } = useFinancialAccounts();
const { addLedgerRecord } = useLedgerRecords();

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

// For showing models

/**
 * @typedef ModelReturnType
 * @property {string} response
 * @property {string} [draftSaveName]
 */

/** @typedef {"draft-load-data-exists-warning" | "saving-records" | "delete-draft-confirm" | "draft-save"} ModelType */

const ModelTemplate = /** @type {ReturnType<typeof createTemplatePromise<ModelReturnType, [ModelType]>>} */(createTemplatePromise({
    transition: {
        name: 'model-content',
        appear: true,
    },
}));

/** A Ref for the Save draft Model Draft name input */
const draftNameInput = /** @type { import('vue').Ref<HTMLInputElement | null> } */ (ref(null));

// For the Loading model after clicking save records
const { isRevealed: isLoadingModelVisible, reveal: revealLoadingModel, cancel: hideLoadingModel } = useConfirmDialog()

// Types

/**
 * Represents a Row of the table
 * @typedef Row
 * @property {number} order
 * @property {string} [description]
 * @property {number} [accountId]
 * @property {number} [taxId]
 * @property {string} [credit]
 * @property {string} [debit]
 */

/**
 * @typedef ComputedRowCreditDebitData
 * @property {Decimal} taxAmount Amount of tax applied
 * @property {Decimal} amount Value with tax applied
 * @property {Decimal} baseAmount Value without any tax
 */

/**
 * @typedef ComputedRowBase
 * @property {number} order
 * @property {string} [description]
 * @property {number} [accountId]
 * @property {number} [taxId]
 * @property {ComputedRowCreditDebitData} [credit]
 * @property {ComputedRowCreditDebitData} [debit]
 */

/**
 * Represents a Row but with more computed information on debit and credit fields
 * @typedef {import('ts-toolbelt').O.Either<ComputedRowBase, "credit" | "debit">} ComputedRow
 * 
 * @typedef {import('ts-toolbelt').O.Required<Omit<ComputedRowBase, "credit">, "debit">} ComputedRowWithOnlyDebit
 * @typedef {import('ts-toolbelt').O.Required<Omit<ComputedRowBase, "debit">, "credit">} ComputedRowWithOnlyCredit
 */


/**
 * @typedef { {name: string, draftedAt: Date, date: Date, taxType: "no tax" | "tax exclusive" | "tax inclusive", narration: string, rows: Row[]} } Draft
 * @typedef { Omit<Draft, "date" | "draftedAt"> & { date: string, draftedAt: string } } RawDraft
 */

/**
* @typedef {(Omit<Row, "debit" | "credit"> & { credit: Decimal.Value; debit: Decimal.Value })} RowWithProbablyValidCreditOrDebitValue
* @typedef { Omit<RowWithProbablyValidCreditOrDebitValue, "debit"> } RowWithProbablyValidCreditValue
* @typedef { Omit<RowWithProbablyValidCreditOrDebitValue, "credit"> } RowWithProbablyValidDebitValue
*/

// Draft System

const drafts = useStorage('record-entry-drafts', [], localStorage,
    {
        serializer: {
            /**
             * @param {string | null} v
             * @returns {Draft[]}
             */
            read: (v) => v ?
                (/** @type {RawDraft[]}*/ (JSON.parse(v)))
                    .map(({ date, draftedAt, ...rest }) => ({
                        ...rest,
                        date: new Date(date),
                        draftedAt: new Date(draftedAt)
                    })).sort((a, b) => b.draftedAt.getTime() - a.draftedAt.getTime()) : [],

            /**
             * @param {Draft[]} v 
             */
            write: (v) => JSON.stringify(v.map(({ date, draftedAt, ...rest }) => ({
                ...rest,
                date: date.toUTCString(),
                draftedAt: draftedAt.toUTCString()
            }))),
        },
    },
)

/**
 * Saves a new Draft from the current state
 */
const saveDraft = async () => {
    const name = await ModelTemplate.start("draft-save").then(({ response, draftSaveName }) => response == "save" ? draftSaveName : undefined).catch(() => undefined)
    if (!name) return;

    drafts.value = [{
        date: entryDate.value,
        draftedAt: new Date(Date.now()),
        name,
        narration: narration.value,
        taxType: taxType.value,
        rows: rows.value
    }, ...drafts.value]
}

/**
 * Saves a new Draft
 * @param {Draft} draft 
 */
const loadDraft = async ({ rows: rowsFromDraft, date, narration: narrationFromDraft, taxType: taxTypeFromDraft }) => {
    // TODO: Check if the state is changed, if changed notify of data loss
    if (dataExists.value &&
        await ModelTemplate.start("draft-load-data-exists-warning").then(({ response }) => response != "load").catch(() => true))
        return;

    narration.value = narrationFromDraft
    entryDate.value = date
    rows.value = rowsFromDraft
    taxType.value = taxTypeFromDraft
}

const getDrafts = () => drafts.value.slice(0, 10) // First 10 elements or if the array is shorter than 10, the whole array

/**
 * Removes a Draft
 * @param {Draft} draftToRemove 
 */
const removeDraft = async (draftToRemove) => {
    if (await ModelTemplate.start("delete-draft-confirm").then(({ response }) => response != "delete").catch(() => true)) return;

    drafts.value = [...drafts.value.filter(x => x != draftToRemove)]
}

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
const entryDate = ref(new Date());
const taxType = /** @type {import('vue').Ref<"no tax" | "tax exclusive" | "tax inclusive">}*/ (ref("tax exclusive"))

const topWarningHidden = ref(false);

useUndoRedo(rows);

const taxes = await getTaxes().catch(() => [])
const accounts = await getFinanctialAccounts().catch(() => [])

// Computed Values

const dataExists = computed(() => {
    /**
     * @param {Record<string, string | number>} obj
     */
    const atleastOneObjectPropertyIsNotEmpty = (obj) => ( /** @type {Array<keyof typeof obj>} */ (Object.keys(obj))).some(key => obj[key] != "" && obj[key] != undefined)
    return rows.value.map(({ order, ...rest }) => rest).some(atleastOneObjectPropertyIsNotEmpty)
})

/** @type {import('vue').Ref<string[]>} */
const errorMessages = computed(() => {
    if (!dataExists.value) return [];

    // If an entire row is empty ignore that row
    const rowsWithData = rows.value.filter(rowHasData)

    return /** @type {string[]} */([
        !total.value.credit.equals(total.value.debit) ? "Credit and Debit sides must equalize" : null,
        rowsWithData.some(creditAndDebit) ? "Both Debit and Credit Value cannot be set for a record" : null,
        rowsWithData.some(NotTakeOne(eitherCreditOrDebitAndValid)) ? "Credit and Debit values has to be numerical" : null,
        rowsWithData.some(row => !row.accountId) ? "Account ID has to be selected for every record" : null,
        narration.value == "" || narration.value == undefined ? "Narration has to be provided" : null,
    ].filter(x => x != null));
})

/** 
 * Contains rows with extra details
 * @type {import('vue').ComputedRef<ComputedRow[]>} */
const computedRows = computed(() => {
    const computableRows = rows.value.filter(isRowDebitCreditValid).filter(row => taxType.value == "no tax" || row.taxId != undefined)
    return computableRows.map(({ credit, debit, taxId, ...rest }) => ({
        ...rest,
        taxId,
        ...(credit ?
            {
                credit: {
                    amount: taxStrategy.value.addTax(toDecimal(credit), taxId),
                    taxAmount: taxStrategy.value.getTaxAmount(toDecimal(credit), taxId),
                    baseAmount: taxType.value == "tax inclusive" ? toDecimal(credit).minus(taxStrategy.value.getTaxAmount(toDecimal(credit), taxId)) : toDecimal(credit)
                }
            } : debit ? {
                debit: {
                    amount: taxStrategy.value.addTax(toDecimal(debit), taxId),
                    taxAmount: taxStrategy.value.getTaxAmount(toDecimal(debit), taxId),
                    baseAmount: taxType.value == "tax inclusive" ? toDecimal(debit).minus(taxStrategy.value.getTaxAmount(toDecimal(debit), taxId)) : toDecimal(debit)
                }
            } : {})
    }));
})

/** 
 * Provides two methods to calculate taxes
 * @type { import('vue').ComputedRef<{ addTax: (value: Decimal, taxId?: number) => Decimal; getTaxAmount: (value: Decimal, taxId?: number) => Decimal }>} */
const taxStrategy = computed(() => {
    /** @param {number} [taxId] */
    const getTaxRate = (taxId) => taxId ? (taxes.find(tax => tax.tax_id == taxId) ?? { tax_rate: new Decimal(0) }).tax_rate : new Decimal(0);

    /** 
     * This method will calculate the tax added value
     * @type { (value: Decimal, taxId?: number) => Decimal } */
    const addTax = taxType.value == "tax exclusive" ? (value, taxId) => {
        return value.mul((getTaxRate(taxId)).plus(100)).dividedBy(100)
    } : (value, _taxId) => value;

    /** 
     * This method will calculate the tax amount
     * @type { (value: Decimal, taxId?: number) => Decimal } */
    const getTaxAmount =
        taxType.value == "tax exclusive" ? (value, taxId) => value.mul(getTaxRate(taxId)).dividedBy(100) :
            taxType.value == "tax inclusive" ? (value, taxId) => value.times(getTaxRate(taxId)).dividedBy((getTaxRate(taxId)).plus(100)) :
                (_value, _taxId) => new Decimal(0);

    return { addTax, getTaxAmount }
})

const subTotal = computed(() => {
    const { creditRows, debitRows } = splitComputedRowsToCreditDebit(computedRows.value)

    const credit = creditRows.map(x => x.credit.baseAmount).reduce(decimalReducer, new Decimal(0))
    const debit = debitRows.map(x => x.debit.baseAmount).reduce(decimalReducer, new Decimal(0))
    // if (hasErrors.value) return { credit: new Decimal(0), debit: new Decimal(0) }

    // const { creditRows, debitRows } = splitRowsToCreditDebit(rows.value)

    // const credit = creditRows.map(row => row.credit).map(toDecimal).reduce(decimalReducer, new Decimal(0))
    // const debit = debitRows.map(row => row.debit).map(toDecimal).reduce(decimalReducer, new Decimal(0))
    return { debit, credit };
})

const taxTotal = computed(() => {
    const { creditRows, debitRows } = splitComputedRowsToCreditDebit(computedRows.value)

    const credit = creditRows.map(x => x.credit.taxAmount).reduce(decimalReducer, new Decimal(0))
    const debit = debitRows.map(x => x.debit.taxAmount).reduce(decimalReducer, new Decimal(0))

    // if (hasErrors.value) return { credit: new Decimal(0), debit: new Decimal(0) }

    // /** @param {number} taxId */
    // const getTaxRate = (taxId) => taxes.find(tax => tax.tax_id == taxId)?.tax_rate

    // /** @type { (row: RowWithProbablyValidCreditValue | RowWithProbablyValidDebitValue) => row is RowWithProbablyValidCreditValue } */
    // const rowTypePredicateCredit = (row) => "credit" in row;

    // /** 
    //  * @param { RowWithProbablyValidCreditValue | RowWithProbablyValidDebitValue } row
    //  */
    // const mapToTaxValue = (row) => new Decimal(rowTypePredicateCredit(row) ? row.credit : row.debit).times(getTaxRate(row.taxId ?? 0) ?? new Decimal(0)).dividedBy(100)

    // const { creditRows, debitRows } = splitRowsToCreditDebit(rows.value)

    // const debit = debitRows.map(row => mapToTaxValue(row)).reduce(decimalReducer, new Decimal(0))
    // const credit = creditRows.map(row => mapToTaxValue(row)).reduce(decimalReducer, new Decimal(0))
    return { debit, credit };
})

const total = computed(() => {
    const { creditRows, debitRows } = splitComputedRowsToCreditDebit(computedRows.value)

    const credit = creditRows.map(x => x.credit.amount).reduce(decimalReducer, new Decimal(0))
    const debit = debitRows.map(x => x.debit.amount).reduce(decimalReducer, new Decimal(0))

    return { debit, credit };
})

// Table row management

const addItem = () => rows.value = ([...rows.value, { order: count.value++ }]);

/** @param {Row} item */
const removeItem = (item) => rows.value = [...rows.value.filter(x => x != item)];

const saveRecords = async () => {
    revealLoadingModel()

    /** @type {import('../types').LedgerRecordAddOptions} */
    const data = {
        body: [
            ...splitComputedRowsToCreditDebit(computedRows.value).creditRows.map(({ credit, taxId, accountId, description }) => ({
                description: description ?? "",
                tax_id: /** @type {number} */ (taxId),
                account_id: /** @type {number} */ (accountId),
                credit: credit.baseAmount
            })),
            ...splitComputedRowsToCreditDebit(computedRows.value).debitRows.map(({ debit, taxId, accountId, description }) => ({
                description: description ?? "",
                tax_id: /** @type {number} */ (taxId),
                account_id: /** @type {number} */ (accountId), 
                debit: debit.baseAmount
            }))
        ],
        date: entryDate.value,
        narration: narration.value,
        taxType: taxType.value
    }

    await addLedgerRecord(data).catch(() => null);

    hideLoadingModel()
}

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
 * If a debit value is set check if it's valid, likewise for credit value
 * @param {Row} row 
 */
const eitherCreditOrDebitAndValid = (row) => isProperNumberString(row.credit) || isProperNumberString(row.debit);

/**
 * Checks if the row's credit and debit fields are valid
 * @param {Row} row 
 */
const isRowDebitCreditValid = (row) => !creditAndDebit(row) && eitherCreditOrDebitAndValid(row);

/**
 * Checks if the row's data is valid
 * @param {Row} row 
 */
const isRowValid = (row) => isRowDebitCreditValid(row) || !(row.accountId) || !(row.taxId) || !(row.description);

/**
 * Checks if there's any data in the row
 * @param {Row} row 
 */
const rowHasData = (row) => row.accountId || row.credit || row.debit || row.description || row.taxId

// Helper Functions

/**
 * Splits the ComputedRow array into credit and debit parts
 * @param {ComputedRow[]} rows 
 * @returns {{ creditRows: ComputedRowWithOnlyCredit[], debitRows: ComputedRowWithOnlyDebit[] }}
 */
const splitComputedRowsToCreditDebit = (rows) => ({
    creditRows: rows.filter(row => "credit" in row).map(({ debit, ...rest }) => /** @type {ComputedRowWithOnlyCredit} */({ ...rest })),
    debitRows: rows.filter(row => "debit" in row).map(({ credit, ...rest }) => /** @type {ComputedRowWithOnlyDebit} */({ ...rest }))
})
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
}

.table-error-glow {
    box-shadow: 0 0 30px 4px #ff3a3a9f;
}

.error-list-enter-active,
.error-list-leave-active {
    transition: all 0.5s ease;
}

.error-list-enter-from,
.error-list-leave-to {
    opacity: 0;
    transform: translateX(30px);
}

.model-content-enter-active,
.model-content-leave-active {
    transition: opacity 0.5s ease;
}

.model-content-enter-from,
.model-content-leave-to {
    opacity: 0;
}
</style>