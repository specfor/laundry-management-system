// @ts-check
import { ref, computed, watch } from "vue";
import { useOffsetPagination, computedAsync } from "@vueuse/core";
import { filterArray } from "../../util/array-util";

/**
 * @template {{[key: string]: any}} T 
 * @param {(currentPage: number, currentPageSize: number, date?: Date, narration?: string) => Promise<{records: T[], recordCount: number}>} recordFetcher
 * @param {(a: T, b: T) => number} [sorter] Method that will handle the sortation of rows
 */
export function useCustomPaginationAdapter(
    recordFetcher,
    sorter = (_a, _b) => 0,
) {
    /**
     * @param {((record: T[]) => T[])[]} filters 
     */
    return async function paginator(...filters) {
        const recordCount = ref(0);
        const records = /** @type {import("vue").Ref<T[]>} */ (ref([]))

        const { currentPage, pageCount, currentPageSize } = useOffsetPagination({
            total: recordCount,
            page: 1,
            pageSize: 10
        });

        const recordData = computedAsync(async () =>
            await recordFetcher(currentPage.value, currentPageSize.value).catch(() => ({
                recordCount: 0,
                records: []
            })), { recordCount: 0, records: [] });

        watch(recordData, (newValue) => {
            recordCount.value = newValue.recordCount
            records.value = filterArray(recordData.value.records, ...filters).sort(sorter)
        })

        return { currentPage, pageCount, currentPageSize, records }
    }
}