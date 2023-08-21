<template>
    <!-- Table Controls -->
    <div class="w-full flex flex-row justify-end gap-3 items-end lg:mt-7">
        <div class="grow flex flex-row flex-wrap justify-start gap-3 pl-7">
            <!-- I don't know which of the following two suits better -->
            <button class="btn btn-square btn-sm transition-all duration-200" :class="{ 'btn-primary': selectMode }"
                @click="toggleSelectMode">
                <svg class="w-5 h-5" :class="{ 'fill-white': selectMode }" viewBox="0 0 48 48"
                    xmlns="http://www.w3.org/2000/svg">
                    <path d="M0 0h48v48H0z" fill="none" />
                    <g id="Shopicon">
                        <polygon points="30.953,11.905 30.953,8.095 8.095,8.095 8.095,39.905 39.905,39.905 39.905,20.75 36.095,20.75 36.095,36.095 
		11.905,36.095 11.905,11.905 	" />
                        <polygon
                            points="41,7.172 24,24.172 17,17.172 14.171,20 21.172,27 21.171,27 24,29.828 26.828,27 43.828,10 	" />
                    </g>
                </svg>
            </button>
            <!-- <input type="checkbox" aria-label="Select" class="btn btn-sm" @click="toggleSelectMode"> -->

            <slot name="header-actions" :selectedRecords="selectedRecords" :records="records"></slot>
        </div>
        <div class="join">
            <input v-model="searchPhrase" type="text" placeholder="Search ..."
                class="input input-sm input-bordered w-[240px] max-w-xs" />
            <div>
                <slot name="filter-content" :data="extraData"></slot>
            </div>
        </div>
        <div class="form-control w-[150px] max-w-xs">
            <label class="label">
                <span class="label-text">Records per page</span>
            </label>
            <select class="select select-bordered select-sm" v-model="currentPageSize">
                <option :value="10" selected>10</option>
                <option :value="20">20</option>
                <option :value="30">30</option>
                <option :value="1000">All</option>
            </select>
        </div>
    </div>

    <div class="overflow-x-auto m-4">
        <table class="table table-zebra">
            <thead>
                <tr>
                    <th v-if="selectMode">
                        <label>
                            <input type="checkbox" class="checkbox" @click="selectAll" />
                        </label>
                    </th>
                    <slot name="table-headers"></slot>
                </tr>
            </thead>
            <TransitionGroup name="table-rows" tag="tbody">
                <tr v-for="record in pageData" :key="record.order">
                    <th v-if="selectMode">
                        <label>
                            <input type="checkbox" class="checkbox" v-model="record.selected" />
                        </label>
                    </th>

                    <td v-for="slotName in columnSlots">
                        <slot :name="slotName" :record="record" :extra="extraData"></slot>
                    </td>

                    <td>
                        <el-dropdown trigger="click" @command="e => commandHandler(e, record)">
                            <span class="el-dropdown-link">
                                <svg class="w-4 h-4 text-gray-800 dark:text-white fill-gray-800" aria-hidden="true"
                                    xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 4 15">
                                    <path
                                        d="M3.5 1.5a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0Zm0 6.041a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0Zm0 5.959a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0Z" />
                                </svg>
                            </span>
                            <template #dropdown>
                                <el-dropdown-menu>
                                    <slot name="row-actions" :record="record"></slot>
                                </el-dropdown-menu>
                            </template>
                        </el-dropdown>
                    </td>
                </tr>
            </TransitionGroup>
        </table>
    </div>
    <div class="join w-full flex justify-center">
        <button v-for="index in pageCount" :key="index" @click="currentPage = index" class="join-item btn"
            :class="{ 'btn-active': currentPage == index }">{{ index }}</button>
    </div>
</template>

<script lang="ts">
// Exported Types
export type ArrangementData = { order: number, selected: boolean };
</script>

<script setup lang="ts" generic="R, X, ID extends keyof R">
// @ts-check
import { useOffsetPagination } from '@vueuse/core';
import { computed, ref, type Ref } from 'vue';
import { useFuzzySearch } from '../composibles/fuzzy-search';
import { ElDropdownMenu, ElDropdown } from "element-plus";

type InternalRow = R & ArrangementData;

// defineProps(['commandHandler', 'records', 'columnSlots'])
const { getRecords, columnSlots, searchFields, extraDataFetcher, commandHandler, idProperty, filter, sorter } =
    withDefaults(defineProps<{
        getRecords: () => Promise<R[]>
        columnSlots: string[]
        searchFields: (keyof R)[]
        extraDataFetcher?: () => Promise<X>
        commandHandler: (command: string, record: InternalRow) => void,
        filter?: (record: InternalRow) => boolean
        sorter?: (a: InternalRow, b: InternalRow) => number
        idProperty: ID
    }>(), {
        filter: (record: InternalRow) => true,
        sorter: (a: InternalRow, b: InternalRow) => 0
    });

// defineExpose has to be called before any top-level await keyword (https://github.com/vuejs/core/issues/4930) 
defineExpose({
    // These functions are just for keeping the records array inside this component in sync with the actual value

    /**
     * Removes a record from the record list
     */
    removeRecord: (recordId: R[ID]) => {
        records.value = records.value.filter(record => record[idProperty] != recordId)
    },

    /**
     * Adds a new record to the record list
     */
    addRecord: (newRecord: R) => {
        records.value = [{
            ...newRecord,
            order: records.value.length + 1,
            selected: false
        }, ...records.value]
    },
    /**
     * Updates a record in the records list
     */
    updateRecord: (updatedRecord: InternalRow) => {
        records.value = [
            ...records.value.map(record => record[idProperty] == updatedRecord[idProperty] ? updatedRecord : record),
        ]
    }
})

// Cast required (https://github.com/vuejs/core/issues/2136#issuecomment-908269949)
const records = ref((await getRecords().catch(() => [])).map((record, index) => ({ ...record, selected: false, order: index }))) as Ref<InternalRow[]>;

// const extraData = /** @type { X | null } */ (extraDataFetcher ? await extraDataFetcher().catch(() => null) : null)
const extraData = extraDataFetcher ? await extraDataFetcher().catch(() => null) : null

const filteredRows = computed(() => records.value.filter(filter));
const totalRows = computed(() => filteredRows.value.length)

const { currentPage, pageCount, currentPageSize } = useOffsetPagination({
    total: totalRows,
    page: 1,
    pageSize: 10
})

/** Records belonging to the current page */
const pageData = computed(() =>
    useFuzzySearch(
        filteredRows.value,
        searchFields,
        searchPhrase.value
    ).sort(sorter).slice((currentPage.value - 1) * currentPageSize.value, ((currentPage.value - 1) * currentPageSize.value) + currentPageSize.value)
)

const selectedRecords = computed(() => records.value.filter(record => record.selected))

const selectMode = ref(false);
const searchPhrase = ref("");

const selectAll = () => records.value = records.value.map(record => ({ ...record, selected: !record.selected }))

const deselectAll = () => records.value = records.value.map(record => ({ ...record, selected: false }))

const toggleSelectMode = () => {
    selectMode.value = !selectMode.value;
    deselectAll()
}
</script>

<style scoped>
.table-rows-move,
/* apply transition to moving elements */
.table-rows-leave-active {
    transition: all 0.3s ease;
}

.table-rows-enter-active {
    transition: all 0.3s ease;
    transition-delay: 0.3s;
}

.table-rows-enter-from,
.table-rows-leave-to {
    opacity: 0;
    /* transform: translateX(30px); */
}

/* ensure leaving items are taken out of layout flow so that moving
   animations can be calculated correctly. */
/* .table-rows-leave-active {
  position: absolute;
} */
</style>