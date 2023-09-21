// @ts-check
import { useStorage } from "@vueuse/core"
import { computed } from "vue"

/**
 * @template RecordType
 * @template {string | number | symbol} ID
 * 
 * @param {string} localStorageKey
 * @param {(record: RecordType) => ID} idSelector
 */
export const useRecordMemoizer = (localStorageKey, idSelector) => {

    const pinned = useStorage(localStorageKey + "-pinned", /** @type {ReturnType<typeof idSelector>[]} */([]), localStorage)
    const recents = useStorage(localStorageKey + "-recents", /** @type {ReturnType<typeof idSelector>[]} */([]), localStorage)
    const usageMap = useStorage(localStorageKey + "-usageMap", /** @type {Record<ID, number>} */({}), localStorage)
    
    const frequents = computed(() => ( /** @type {[ID, number][]} */ (Object.entries(usageMap.value))).sort(([_id1, a], [_id2, b]) => a - b).map(([id, _count]) => id).slice(0, 10))

    /**
     * Marks a record as used and will be used to arrange the most recently used records
     * @param {RecordType[]} records
     */
    const use = (records) => recents.value = [...records.map(idSelector).filter((value, index, array) => array.indexOf(value) === index), ...recents.value].slice(0,20)

    /**
     * Pins a record
     * @param {RecordType} record 
     */
    const pin = (record) => pinned.value = [idSelector(record), ...pinned.value]

    /**
     * Pins a record
     * @param {RecordType} record 
     */
    const unpin = (record) => pinned.value = [...pinned.value.filter(x => x !== idSelector(record) )]

    /**
     * Checks whether a record is pinned
     * @param {RecordType} record 
     */
    const isPinned = (record) => pinned.value.includes(idSelector(record))

    return {
        pin,
        unpin,
        isPinned,
        use,
        recents,
        pinned,
        frequents
    }
}