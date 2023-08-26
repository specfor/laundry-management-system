// @ts-check
import { ref, computed, watch } from "vue";
import { useOffsetPagination, computedAsync, watchDebounced } from "@vueuse/core";
import { filterArray } from "../../util/array-util";

/**
 * @template {{[key: string]: any}} T 
 * @param {import("vue").ComputedRef<(currentPage: number, currentPageSize: number) => Promise<{records: T[], recordCount: number}>>} recordFetcher
 * @param {AbortController} abortController
 * @param {import("../../types").MaybeRefOrComputedRef<(a: T, b: T) => number>} [sorter] Method that will handle the sortation of rows
 */
export function useCustomPaginationAdapter(
    recordFetcher,
    abortController,
    sorter = ref((_a, _b) => 0),
) {
    /**
     * @param {((record: T[]) => T[])[]} _filters 
     */
    return async function paginator(..._filters) {
        const recordCount = ref(0);
        const records = /** @type {import("vue").Ref<T[]>} */ (ref([]))

        const { currentPage, pageCount, currentPageSize } = useOffsetPagination({
            total: recordCount,
            page: 1,
            pageSize: 10,
        });

        const initialData = await recordFetcher.value(currentPage.value, currentPageSize.value)
            .catch(() => ({
                records: /** @type {T[]} */ ([]),
                recordCount: 0
            }));

        records.value = initialData.records.sort(sorter.value);
        recordCount.value = initialData.recordCount;

        watchDebounced([currentPage, currentPageSize, recordFetcher], ([newCurrentPage, newCurrentPageSize, newRecordFetcher]) => {
            abortController.abort()

            newRecordFetcher(newCurrentPage, newCurrentPageSize)
                .then(data => {
                    records.value = data.records.sort(sorter.value);
                    recordCount.value = data.recordCount;
                })
                .catch(() => console.error("Eror occured while fetching Ledger records"))
        }, { debounce: 250, maxWait: 1000 });

        // Watch sorter seperately because it will not send any requests to the backend
        watch(sorter, (newSorter) => {
            records.value = records.value.sort(newSorter);
        })

        return { currentPage, pageCount, currentPageSize, records }
    }
}