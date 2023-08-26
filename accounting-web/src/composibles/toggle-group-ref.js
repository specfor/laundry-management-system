// @ts-check

import { computed, ref } from "vue"

/**
 * @template T
 * @param {number} count
 * @returns {import("vue").WritableComputedRef<T | undefined>[]}
 */
export const useToggleGroupRef = (count) => {
    const refs = /** @type {import("vue").Ref<T | undefined>[]} */ (new Array(count).fill(0).map(_x => ref(undefined)))

    const writableComputedRefs = refs.map((ref, index, refArr) =>
        computed({
            get() {
                return ref.value
            },
            set(value) {
                // Set all the refs to undefined
                refArr.forEach(innerRef => innerRef.value = undefined)
                // Set this ref to the value
                ref.value = value
            }
        })
    )

    return writableComputedRefs;
}