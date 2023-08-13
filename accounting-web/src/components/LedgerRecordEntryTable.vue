<template>
    <!-- @vue-ignore -->
    <DefineInputTemplate v-slot="{ value, placeholder }">
        <input v-model="value" type="text" :placeholder="placeholder" class="input input-ghost w-full max-w-xs" />
    </DefineInputTemplate>
    
    <ReuseInputTemplate :value="'hi'" :placeholder="'fuck'"></ReuseInputTemplate>
    
    <div class="overflow-x-auto">
        <table class="table table-lg">
            <thead>
                <tr>
                    <th></th>
                    <th v-for="(column, index) in columns" :key="index">
                        {{ column.name }}
                    </th>
                    <th></th>
                </tr>
            </thead>
            <draggable v-model="rows" :disabled="dragDisabled" item-key="order" handle=".entry-table-handle" tag='tbody'
                animation="200">
                <template #item="{ element }">
                    <tr class="entry-row">
                        <td class="p-2">
                            <img class="entry-table-handle cursor-move " src="../assets/6-vertical-dots.svg">
                        </td>
                        <template v-for="(column, index) in columns" :key="index">
                            <template v-if="column.type == 'text'">
                                <td class="p-0">
                                    <input v-model="element[column.name]" type="text" :placeholder="column.name"
                                        class="input input-ghost w-full max-w-xs" />
                                </td>
                            </template>
                            <template v-else>
                                <td class="text-right p-0">
                                    <input v-model="element[column.name]" type="number" :placeholder="column.name"
                                        class="input input-ghost w-full max-w-[100px] text-right" />
                                </td>
                            </template>
                        </template>
                        <td class="p-2">
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
                        <td colspan="2">
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
                        <td class="font-bold border-b-2 border-gray-700">Subtotal</td>
                        <td class="text-right border-b-2 border-gray-700 border-l-[1px] border-l-gray-300">0.00</td>
                        <td class="text-right border-b-2 border-gray-700 border-l-[1px] border-l-gray-300">0.00</td>
                        <td></td>
                    </tr>
                    <tr class="table-summary-row">
                        <td></td>
                        <td></td>
                        <td class="font-bold border-b-4 border-gray-700 border-double">Total</td>
                        <td class="text-right border-b-4 border-gray-700 border-double border-l-[1px] border-l-gray-300">
                            0.00</td>
                        <td class="text-right border-b-4 border-gray-700 border-double border-l-[1px] border-l-gray-300">
                            0.00</td>
                        <td></td>
                    </tr>
                </template>
            </draggable>
        </table>
        <div>
            <button @click="emitOnSaveClicked" class="btn btn-sm btn-accent">Save</button>
        </div>
    </div>
</template>

<script setup>
// @ts-check
import { createReusableTemplate } from '@vueuse/core';
import { ref } from 'vue';
// https://github.com/SortableJS/vue.draggable.next
import draggable from 'vuedraggable'


const [DefineInputTemplate, ReuseInputTemplate] = createReusableTemplate()

/** 
 * @template T
 * @typedef {import('vue').PropType<T>} Prop
 */

/**
 * @typedef {Object.<string, (string | number)>} Row
 * @typedef {{ name: string; type: 'text' | 'number' }} Column
 */

const { rows: rowsProp, columns: colsProp, ...restProps } = defineProps({
    initialRows: {
        type: Number,
        default: 3,
    },
    dragDisabled: {
        type: Boolean,
        default: false
    },
    columns: {
        /** @type {Prop<Column[]>} */
        type: Array,
        required: true
    },
    rows: {
        /** @type {Prop<Row[]>} */
        type: Array,

        /**
         * @param {{columns: Column[]; initialRows: number}} rawProps 
         * @returns {Row[]}
         */
        default({ columns, initialRows }) {
            return initialRows > 0 ?
                [...Array(initialRows).keys()] // Like python Range(1..3)
                    .map(_i =>
                        // Creates an object from an array like,
                        // [['description','LLL'], ['name', 'me']] => { description: 'LLL', name: 'me' }
                        Object.fromEntries(columns.map(
                            col => col.type == 'number' ?
                                [col.name, 0] :
                                [col.name, '']
                        ))
                    ) : [];
        }
    }
})

const emit = defineEmits(['onSaveClickd']);

const count = ref(0);

const rows = ref(
    // Add an order property to rows
    rowsProp.map(row =>
        ({ ...row, order: count.value++ })
    )
);

const addItem = () => rows.value = ([...rows.value, newRow(colsProp, count.value++)]);

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
 * @param {Column[]} columns 
 * @param {number} order
 */
const newRow = (columns, order) => Object.assign(
    Object.fromEntries(
        columns.map(
            col => col.type == 'number' ?
                [col.name, 0] :
                [col.name, '']
        )), { order });
</script>

<style scoped>
.table-summary-row td {
    @apply py-2 text-sm;
}

.icon-row {
    @apply p-2;
}

.entry-row td {
    @apply border-l-[1px] border-gray-300;
}

.entry-row td:first-of-type {
    @apply border-l-0;
}
</style>
<!-- columns: [
        {
            name: "Description",
            type: 'text'
        },
        {
            name: "Account",
            type: 'text'
        },
        {
            name: "Reference",
            type: 'text'
        },
        {
            name: "Debit",
            type: 'number'
        },
        {
            name: "Credit",
            type: 'number'
        },
    ], -->