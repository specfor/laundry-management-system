import { ref, computed, Ref, ComputedRef } from "vue";
import { useOffsetPagination } from "@vueuse/core";
import { useFuzzySearch } from "../fuzzy-search";

export function useNormalPaginationAdapter<T>(
    filter?: (record: T) => boolean,
    sorter?: (a: T, b: T) => number
): { currentPage: Ref<number>, pageCount: ComputedRef<number>, currentPageSize: Ref<number>, pageData: ComputedRef<T[]> } {
    const records = ref((await getRecords().catch(() => [])).map(record => ({ ...record, selected: false }))) as Ref<InternalRow[]>;

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

    return { currentPage, pageCount, currentPageSize, pageData }
}

export type PaginationAdapter = typeof useNormalPaginationAdapter;