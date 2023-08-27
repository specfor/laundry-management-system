// @ts-check
import { ref, computed, watch } from "vue";
import { useOffsetPagination, computedAsync } from "@vueuse/core";
import { filterArray } from "../../util/array-util";

/**
 * @template {{[key: string]: any}} T 
 * @param {() => Promise<T[]>} recordFetcher
 * @param {import("../../types").MaybeRefOrComputedRef<(a: T, b: T) => number>} [sorter] Method that will handle the sortation of rows
 * @param {import("../../types").MaybeRefOrComputedRef<(record: T) => boolean>} [filter] Method to filter the records
 */
export function useNormalPaginationAdapter(
    recordFetcher,
    sorter = ref(() => 0),
    filter = ref(() => true)
) {
    /**
     * @param {(import("../../types").MaybeRefOrComputedRef<(record: T[]) => T[]>)[]} filters 
     */
    return async function paginator(...filters) {
        const _records = await recordFetcher().catch(() => []);
        
        const { currentPage, pageCount, currentPageSize } = useOffsetPagination({
            total: _records.length,
            page: 1,
            pageSize: 10
        });

        const records = /** @type {import("vue").Ref<T[]>} */ (ref(
            filterArray(_records, ...filters.map(refs => refs.value)).filter(filter.value).sort(sorter.value).slice((currentPage.value - 1) * currentPageSize.value, ((currentPage.value - 1) * currentPageSize.value) + currentPageSize.value)
        ))

        watch([currentPage, currentPageSize, filter, sorter, ...filters], ([newCurrentPage, newCurrentPageSize, newFilter, newSorter, ...newFilters]) => {
            records.value = filterArray(_records, ...newFilters).filter(newFilter).sort(newSorter).slice((newCurrentPage - 1) * newCurrentPageSize, ((newCurrentPage - 1) * newCurrentPageSize) + newCurrentPageSize)
        })

        return { currentPage, pageCount, currentPageSize, records }
    }
}