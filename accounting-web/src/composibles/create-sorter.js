// @ts-check

import { computed } from "vue";
import { useToggleGroupRef } from "./toggle-group-ref";

/**
 * @template T
 * @typedef {(a: T, b: T) => number} SorterPredicate
 */

/**
 * @template R
 * @template {keyof R} DEFAULT
 * @param {import("../types").CreateSorterOptions<R>} options 
 * @param { { sorter: SorterPredicate<R[DEFAULT]>, propertyKey: DEFAULT } } defaults
 */
export const useCreateSorter = (options, defaults) => {
    const keys = Object.keys(options)

    // Refs for Sorting types
    const refs = /** @type {ReturnType<typeof useToggleGroupRef<"ASC" | "DSC">>} */(useToggleGroupRef(keys.length));

    const sortMode = computed(() => [
        ...refs.map((ref, index) => ref.value ? /** @type {const} */ ([/** @type {keyof R} */ (keys[index]), ref.value]) : null)
    ].find(x => x != null) ?? /** @type {const} */ ([undefined, undefined]))

    const sorter = computed(() => {
        const [sortBy, sortDirection] = sortMode.value;

        /**
         * A little helper function
         * @param {"ASC" | "DSC" | undefined} direction 
         * @param {() => number} sorter 
         */
        const sortSideSwtich = (direction, sorter) => direction == "ASC" || !direction ? sorter() : -sorter()

        /**
         * @param {R} a
         * @param {R} b
         */
        return (a, b) => sortSideSwtich(sortDirection, () => {
            if (!sortBy) return defaults.sorter(a[defaults.propertyKey], b[defaults.propertyKey])

            /**
             * @param {*} a 
             * @param {*} b 
             */
            const fallbackFunc = (a, b) => 0

            const sorterFunc = options[sortBy] ?? fallbackFunc

            return sorterFunc(a[sortBy], b[sortBy])
        })
    })

    const clearAll = () => {
        refs.forEach(ref => ref.value = undefined)
    }

    return {
        sorter,
        clearAll,
        ...( /** @type {import("ts-toolbelt").O.Compulsory<import("../types").AllObjectPropsTo<import("../types").AppendAllObjectPropsWith<typeof options, "SortRef">, import("../types").ArrayElement<typeof refs>>>} */ (Object.assign({}, ...keys.map((key, index) => ({ [`${key}SortRef`]: refs[index] })))))
    }
}