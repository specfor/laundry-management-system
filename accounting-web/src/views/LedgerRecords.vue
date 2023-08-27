<template>
    <Suspense>
        <RecordTable ref="recordTableRef" :paginator="paginator" :column-slots="['narration', 'date', 'amount', 'view']"
            :id-property="'record_id'" :show-row-actions="false" @update:searchValue="(e: string) => searchPhrase = e"
            :search-fields="[]">

            <template #table-headers>
                <th>Narration</th>
                <th>Date</th>
                <th>Total Amount</th>
                <th></th>
            </template>

            <template #header-actions="{ selectedRecords }">
                <TransitionGroup name="header-buttons">
                    <button class="btn btn-sm btn-neutral" key="add-but" @click="handleAddNewRecord">
                        <svg class="w-4 h-4 text-gray-800 dark:text-white stroke-white" aria-hidden="true"
                            xmlns="http://www.w3.org/2000/svg" viewBox="0 0 18 18">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 1v16M1 9h16" />
                        </svg>
                        Add New Record
                    </button>
                    <button class="btn btn-sm btn-neutral" key="view-but" v-if="selectedRecords.length > 0"
                        @click="handleViewRecord(selectedRecords.map(record => record.record_id))">
                        <svg class="w-4 h-4 text-gray-800 dark:text-white stroke-white" aria-hidden="true"
                            xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 18">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M10 16.5c0-1-8-2.7-9-2V1.8c1-1 9 .707 9 1.706M10 16.5V3.506M10 16.5c0-1 8-2.7 9-2V1.8c-1-1-9 .707-9 1.706" />
                        </svg>
                        View Selected
                    </button>
                </TransitionGroup>
            </template>

            <template #filter-content="{ data }">
                <el-popover :width="300" placement="bottom" :visible="filterPopoverVisible"
                    popper-style="box-shadow: rgb(14 18 22 / 35%) 0px 10px 38px -10px, rgb(14 18 22 / 20%) 0px 10px 20px -15px;">
                    <template #reference>
                        <button class="btn btn-sm btn-neutral btn-square group"
                            @click="filterPopoverVisible = !filterPopoverVisible">
                            <svg class="w-[17px] h-[17px] text-gray-800 dark:text-white stroke-white" aria-hidden="true"
                                xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 18">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="m2.133 2.6 5.856 6.9L8 14l4 3 .011-7.5 5.856-6.9a1 1 0 0 0-.804-1.6H2.937a1 1 0 0 0-.804 1.6Z" />
                            </svg>
                        </button>
                    </template>
                    <template #default>
                        <div class="w-full flex flex-row justify-between">
                            <h2 class="text-lg font-medium">Filter Ledger Records</h2>
                            <svg @click="filterPopoverVisible = false" xmlns="http://www.w3.org/2000/svg"
                                class="h-6 w-6 cursor-pointer" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </div>

                        <!-- Date -->
                        <div class="form-control w-full max-w-xs">
                            <label class="label">
                                <span class="label-text">Show records from,</span>
                            </label>
                            <el-date-picker v-model="selectedDate" type="date" placeholder="Pick a date" size="default" />
                        </div>

                        <!-- Sort by date ASC/DSC -->
                        <div class="form-control w-full max-w-xs flex">
                            <label class="label">
                                <span class="label-text">Sort by Date</span>
                            </label>
                            <div class="flex flex-row items-center gap-4">
                                <el-radio-group v-model="dateSortRef" size="small">
                                    <el-radio-button label="ASC">
                                        <svg class="w-3 h-3 text-gray-800 dark:text-white stroke-black" aria-hidden="true"
                                            xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 8">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M13 7 7.674 1.3a.91.91 0 0 0-1.348 0L1 7" />
                                        </svg>
                                    </el-radio-button>
                                    <el-radio-button label="DSC">
                                        <svg class="w-3 h-3 text-gray-800 dark:text-white stroke-black" aria-hidden="true"
                                            xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 8">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="m1 1 5.326 5.7a.909.909 0 0 0 1.348 0L13 1" />
                                        </svg>
                                    </el-radio-button>
                                </el-radio-group>
                                <svg @click="dateSortRef = undefined"
                                    class="w-4 h-4 cursor-pointer text-gray-800 dark:text-white fill-gray-800"
                                    aria-hidden="true" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path
                                        d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5Zm3.707 11.793a1 1 0 1 1-1.414 1.414L10 11.414l-2.293 2.293a1 1 0 0 1-1.414-1.414L8.586 10 6.293 7.707a1 1 0 0 1 1.414-1.414L10 8.586l2.293-2.293a1 1 0 0 1 1.414 1.414L11.414 10l2.293 2.293Z" />
                                </svg>
                            </div>
                        </div>

                        <!-- Sort by record created date ASC/DSC -->
                        <div class="form-control w-full max-w-xs flex">
                            <label class="label">
                                <span class="label-text">Sort by record created date</span>
                            </label>
                            <div class="flex flex-row items-center gap-4">
                                <el-radio-group v-model="createdAtSortRef" size="small">
                                    <el-radio-button label="ASC">
                                        <svg class="w-3 h-3 text-gray-800 dark:text-white stroke-black" aria-hidden="true"
                                            xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 8">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M13 7 7.674 1.3a.91.91 0 0 0-1.348 0L1 7" />
                                        </svg>
                                    </el-radio-button>
                                    <el-radio-button label="DSC">
                                        <svg class="w-3 h-3 text-gray-800 dark:text-white stroke-black" aria-hidden="true"
                                            xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 8">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="m1 1 5.326 5.7a.909.909 0 0 0 1.348 0L13 1" />
                                        </svg>
                                    </el-radio-button>
                                </el-radio-group>
                                <svg @click="dateSortRef = undefined"
                                    class="w-4 h-4 cursor-pointer text-gray-800 dark:text-white fill-gray-800"
                                    aria-hidden="true" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path
                                        d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5Zm3.707 11.793a1 1 0 1 1-1.414 1.414L10 11.414l-2.293 2.293a1 1 0 0 1-1.414-1.414L8.586 10 6.293 7.707a1 1 0 0 1 1.414-1.414L10 8.586l2.293-2.293a1 1 0 0 1 1.414 1.414L11.414 10l2.293 2.293Z" />
                                </svg>
                            </div>
                        </div>

                        <!-- Sort by narration ASC/DSC -->
                        <div class="form-control w-full max-w-xs flex">
                            <label class="label">
                                <span class="label-text">Sort by Narration</span>
                            </label>
                            <div class="flex flex-row items-center gap-4">
                                <el-radio-group v-model="narrationSortRef" size="small">
                                    <el-radio-button label="ASC">
                                        <svg class="w-3 h-3 text-gray-800 dark:text-white stroke-black" aria-hidden="true"
                                            xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 8">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M13 7 7.674 1.3a.91.91 0 0 0-1.348 0L1 7" />
                                        </svg>
                                    </el-radio-button>
                                    <el-radio-button label="DSC">
                                        <svg class="w-3 h-3 text-gray-800 dark:text-white stroke-black" aria-hidden="true"
                                            xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 8">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="m1 1 5.326 5.7a.909.909 0 0 0 1.348 0L13 1" />
                                        </svg>
                                    </el-radio-button>
                                </el-radio-group>
                                <svg @click="narrationSortRef = undefined"
                                    class="w-4 h-4 cursor-pointer text-gray-800 dark:text-white fill-gray-800"
                                    aria-hidden="true" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path
                                        d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5Zm3.707 11.793a1 1 0 1 1-1.414 1.414L10 11.414l-2.293 2.293a1 1 0 0 1-1.414-1.414L8.586 10 6.293 7.707a1 1 0 0 1 1.414-1.414L10 8.586l2.293-2.293a1 1 0 0 1 1.414 1.414L11.414 10l2.293 2.293Z" />
                                </svg>
                            </div>
                        </div>

                        <!-- Sort by amount ASC/DSC -->
                        <div class="form-control w-full max-w-xs flex">
                            <label class="label">
                                <span class="label-text">Sort by Total amount</span>
                            </label>
                            <div class="flex flex-row items-center gap-4">
                                <el-radio-group v-model="totalAmountSortRef" size="small">
                                    <el-radio-button label="ASC">
                                        <svg class="w-3 h-3 text-gray-800 dark:text-white stroke-black" aria-hidden="true"
                                            xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 8">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M13 7 7.674 1.3a.91.91 0 0 0-1.348 0L1 7" />
                                        </svg>
                                    </el-radio-button>
                                    <el-radio-button label="DSC">
                                        <svg class="w-3 h-3 text-gray-800 dark:text-white stroke-black" aria-hidden="true"
                                            xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 8">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="m1 1 5.326 5.7a.909.909 0 0 0 1.348 0L13 1" />
                                        </svg>
                                    </el-radio-button>
                                </el-radio-group>
                                <svg @click="totalAmountSortRef = undefined"
                                    class="w-4 h-4 cursor-pointer text-gray-800 dark:text-white fill-gray-800"
                                    aria-hidden="true" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path
                                        d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5Zm3.707 11.793a1 1 0 1 1-1.414 1.414L10 11.414l-2.293 2.293a1 1 0 0 1-1.414-1.414L8.586 10 6.293 7.707a1 1 0 0 1 1.414-1.414L10 8.586l2.293-2.293a1 1 0 0 1 1.414 1.414L11.414 10l2.293 2.293Z" />
                                </svg>
                            </div>
                        </div>

                        <div class="flex flex-row justify-between mt-2">
                            <!-- <button class="btn btn-primary btn-sm">Apply Filters</button> -->
                            <button class="btn btn-sm" @click="clearFilters">Clear Filters</button>
                        </div>
                    </template>
                </el-popover>
            </template>

            <template #date="{ record }">
                <span class="text-base block">
                    {{ record.date.toDateString() }}
                </span>
                <span class="text-sm text-slate-700">
                    <UseTimeAgo v-slot="{ timeAgo }" :time="record.createdAt">
                        Created: {{ timeAgo }}
                    </UseTimeAgo>
                </span>
            </template>

            <template #narration="{ record }">
                <span class="text-base font-medium">
                    {{ record.narration }}
                </span>
            </template>

            <template #amount="{ record }">
                {{ toReadable(record.totalAmount) }}
            </template>

            <template #view="{ record }">
                <button @click="handleViewRecord([record.record_id])" class="btn btn-sm btn-neutral">View</button>
            </template>

        </RecordTable>
        <template #fallback>
            <div class="fixed top-1/2 left-1/2 flex flex-row justify-center">
                <span class="loading loading-spinner loading-lg"></span>
            </div>
        </template>
    </Suspense>
</template>

<script lang="ts" setup>
// @ts-check
import RecordTableGeneric from '../components/RecordTable.vue';
import { ElPopover, ElDatePicker, ElRadioGroup, ElRadioButton } from "element-plus";
import { toReadable } from '../util/decimal-util';
import { Decimal } from 'decimal.js'
import { computed, ref, watch, type Ref } from 'vue';
import type { LedgerRecord, GenericComponentInstance } from '../types';
import { useCustomPaginationAdapter } from '../composibles/pagination/custom-pagination-adapter';
import { useLedgerRecords } from '../composibles/entity/ledger-records';
import router from '../router';
import { UseTimeAgo } from "@vueuse/components";
import { alphabeticalSorter, dateSorter, decimalSorter } from '../util/sorters';
import { useToggleGroupRef } from '../composibles/toggle-group-ref';
import { useCreateSorter } from '../composibles/create-sorter';

// Defining generics of above imported generic elements
// https://play.vuejs.org/#eNp9U8Fu2zAM/RVCl3RAER96C5wAWxEM22ErstyiHDybTtTakiDKTQLD/z5KTmN72HKxRfGR4iMfW/HZ2vl7g2IhUsqdsh4IfWOhyvRhKYUnKVZSq9oa56EFOmZVZU4bLKGD0pkaZhw9k/qG+YoancqfTW17gBTzJFjhGSkiUnt0ZZYjPDfkTb3B3LgCWqkBVLEA8k7pQ7B0VuNgdyE4N5o8KI81LIeQmSpmj0PELPzCRQeUeUWlQpo8Ns1EnGpglo6Bu/3qYRcw+09DTO+KFJdjwpNIblua9D3lIxucxFaZR7YA0lGORayBux3/UkASY0d48ciT4LdLdZi/ktE8rkhdipwTqArdT+sV1ybFom9K8EVC3+Oddw3G/sSYI+Zv/7h/pXO4k+LFIaF753HdfD5zB/S9e/3rB575fHPWpmgqRt9xbpBM1YQae9iXRhdc9ggXq/0WRcTT3tL67FHTB6lQaEB2ES8Fiyn07n/Uh3Kf5k8xjrXAXfwQ4j29w6GfKJtbYC6oC4K19spf4jIkCazP1zJBEWjMkShzF/jdBD3Bybg3OCl/hH4nIu6m+YzAXyyTwZhk5Onf6Ek4zAqjq8tkIeICDPgXZyxddyAoZwHb3X6yJjYillBgqTRGfBq/qweW8z15Woertr0m6Lo0CRd/q7L7A7+qe+c=
// https://github.com/vuejs/rfcs/discussions/436#discussioncomment-6317743
const RecordTable = RecordTableGeneric<LedgerRecord, never, 'record_id'>;

const { getLedgerRecordsFiltered } = useLedgerRecords()

// References to template elements
// https://github.com/vuejs/core/issues/8373
const recordTableRef = ref<GenericComponentInstance<typeof RecordTable> | null>(null);

const filterPopoverVisible = ref<boolean>(false);

const searchPhrase = ref("");
const selectedDate = ref(undefined) as Ref<undefined | Date>

// Refs for Sorting types
const { createdAtSortRef, dateSortRef, narrationSortRef, totalAmountSortRef, sorter, clearAll } = useCreateSorter<LedgerRecord, "date">({
    createdAt: dateSorter,
    date: dateSorter,
    narration: alphabeticalSorter,
    totalAmount: decimalSorter
}, {
    propertyKey: "date",
    sorter: dateSorter
})

const abortController = new AbortController()

const loadAccount = computed(() => {
    const currentSelectedDate = selectedDate.value;
    const currentSearchPhrase = searchPhrase.value == "" ? undefined : searchPhrase.value

    return async (currentPage: number, currentPageSize: number) =>
        await getLedgerRecordsFiltered(
            currentPageSize,
            currentPage,
            currentSelectedDate,
            currentSearchPhrase,
            abortController.signal
        )
})

const paginator = useCustomPaginationAdapter<LedgerRecord>(
    loadAccount,
    abortController,
    sorter
)

const handleViewRecord = async (selectedRecordIds: number[]) => {
    // Redirect to the Ledger Record View page
    router.push({ name: 'ViewLedgerRecord', query: { ids: selectedRecordIds.join(",") } })
}

const handleAddNewRecord = () => {
    // Redirect to the Ledger entry page
    router.push({ name: 'LedgerEntry' })
}

const clearFilters = () => clearAll()
</script>

<style scoped>
.header-buttons-leave-active {
    transition: all 0.3s ease;
}

.header-buttons-enter-active {
    transition: all 0.3s ease;
}

.header-buttons-enter-from,
.header-buttons-leave-to {
    opacity: 0;
    transform: translateX(-30px);
}
</style>