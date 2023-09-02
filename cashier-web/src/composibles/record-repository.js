// @ts-check

import { useOnline } from "@vueuse/core";
import { ref } from "vue"

/**
 * @template RecordType
 * @template {Record<string, import("../types/util").RecordRepositoryMethod<RecordType, any>>} T
 * 
 * @param {T} methods 
 * @param {(record: RecordType) => number | string} idSelector Specify the record type
 * @param {number} [cacheInvalidateAfter] 
 * @param {() => Promise<RecordType[]>} [onInvalidate] Method to be run after cache has been invalidated. Should be a method that take 0 arguments.
 * 
 * @returns {import("../types/util").RecordRepositoryReturnMethods<RecordType, T> & { invalidate: () => void }}
 */
export const useRecordRepository = (methods, idSelector, cacheInvalidateAfter = 5000, onInvalidate) => {
    const records = /** @type {import("vue").Ref<RecordType[]>} */ (ref([]));

    const online = useOnline()

    const methods_ = Object.fromEntries((Object.entries(methods).map(([key, method]) => /** @type {const} */([
        key,
        /**
         * @param  {...any} args
         */
        async (...args) => {
            const newRecords = online.value ? await method.online(args) : method.offline(args)
            records.value = mergeRecords(newRecords, records.value)
            return newRecords;
        }
    ]))));

    if (onInvalidate) {
        setInterval(() => {
            if (online.value) {
                onInvalidate()
                    .then(data => records.value = data)
                    .catch(e => console.log("Failed to refresh cache"))
            }
        }, cacheInvalidateAfter)
    }

    /**
     * Merges 2 arrays of Records based on the idSelector return value
     * @param {RecordType[]} newRecords 
     * @param {RecordType[]} records 
     * @returns {RecordType[]}
     */
    const mergeRecords = (newRecords, records) => {
        const newIds = newRecords.map(idSelector);
        return records.map(record => {
            const index = newIds.indexOf(idSelector(record))
            return index == -1 ? record : newRecords[index]
        })
    }

    const invalidate = () => {
        if (onInvalidate) {
            if (online.value) {
                onInvalidate()
                    .then(data => records.value = data)
                    .catch(e => console.log("Failed to refresh cache"))
            }
        }
    }

    return {
        ...( /** @type {import("../types/util").RecordRepositoryReturnMethods<RecordType, T>} */ (methods_)),
        invalidate
    }
}