<template>
    <!-- Table Controls -->
    <div class="w-full flex flex-row justify-end gap-3 items-end">
        <div class="grow flex flex-row justify-start gap-3">
            <input type="checkbox" aria-label="Select" class="btn btn-sm" @click="toggleSelectMode"/>
            <slot name="header-actions" :selectedRecords="selectedRecords" :records="records"></slot>
        </div>
        <input v-model="searchPhrase" type="text" placeholder="Search ..."
            class="input input-sm input-bordered w-full max-w-xs" />
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

<script setup generic="R extends Record<string, any>, X">
// @ts-check
import { useOffsetPagination } from '@vueuse/core';
import { computed, ref } from 'vue';
import { useFuzzySearch } from '../composibles/fuzzy-search';
import { ElDropdownMenu, ElDropdown } from "element-plus";

// defineProps(['commandHandler', 'records', 'columnSlots'])
const { getRecords, columnSlots, searchFields, extraDataFetcher, commandHandler, selectId } = defineProps({
    getRecords: {
        type: /** @type { import('vue').PropType<() => Promise<R[]>> } */ (Function),
        required: true
    },
    columnSlots: {
        type: /** @type { import('vue').PropType<Array<string>> } */ (Array),
        required: true
    },
    searchFields: {
        type: /** @type { import('vue').PropType<Array<keyof R>> } */ (Array),
        required: true
    },
    extraDataFetcher: {
        type:  /** @type { import('vue').PropType<() => Promise<X>> } */ (Function),
        required: false,
    },
    commandHandler: {
        type: /** @type { import('vue').PropType<(command: string, record: R) => void> } */ (Function),
        required: true
    },
    selectId: {
        type: /** @type { import('vue').PropType<(record: R) => number | string> } */ (Function),
        required: true
    }
})

// Cast required (https://github.com/vuejs/core/issues/2136#issuecomment-908269949)
const records = /** @type { import('vue').Ref<(R & { selected: boolean, order: number })[]> } */ (ref((await getRecords().catch(() => [])).map((record, index) => ({ ...record, selected: false, order: index }))));

// const extraData = /** @type { X | null } */ (extraDataFetcher ? await extraDataFetcher().catch(() => null) : null)
const extraData = extraDataFetcher ? await extraDataFetcher().catch(() => null) : null

const { currentPage, pageCount, currentPageSize } = useOffsetPagination({
    total: records.value.length,
    page: 1,
    pageSize: 10
})

/** Records belonging to the current page */
const pageData = computed(() =>
    useFuzzySearch(
        records.value,
        searchFields,
        searchPhrase.value
    ).slice((currentPage.value - 1) * currentPageSize.value, ((currentPage.value - 1) * currentPageSize.value) + currentPageSize.value)
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

defineExpose({
    // These functions are just for keeping the records array inside this component in sync with the actual value
    
    /**
     * Removes a record from the record list
     * @param {string | number} recordId
     */
    removeRecord: (recordId) => {
        records.value = records.value.filter(record => selectId(record) == recordId)
    },
    /**
     * Adds a new record to the record list
     * @param {string | number} recordId
     * @param {R} newRecord
     */
    addRecord: (recordId, newRecord) => {
        records.value = [...records.value, {
            ...newRecord,
            order: records.value.length + 1,
            selected: false
        }]
    },
    /**
     * Updates a record in the records list
     * @param {string | number} recordId
     * @param {R & {order: number, selected: boolean}} updatedRecord
     */
    editRecord: (recordId, updatedRecord) => {
        records.value = [
            ...records.value.filter(record => selectId(record) != recordId),
            updatedRecord
        ]
    }
})
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
} */</style>