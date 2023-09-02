// @ts-check
import { useOnline, useStorage, watchImmediate, whenever } from "@vueuse/core"
import { computed, ref, watch } from "vue"

/** 
 * Creates a Queue where a certain action will get executed per each item.
 * @template ID
 * @template T
 * 
 * @param {(args: T) => Promise<ID>} action
 * @param {(args: T, id: ID) => void} onSuccess
 * @param {(args: T, error: any) => void} onError
 * @param {string} localStorageKey
 * 
 * @returns {ReturnType<import("../types/util").ActionQueue<ID, T>>}
 */
export const useActionQueue = (action, onSuccess, onError, localStorageKey) => {
    const actions =
        localStorageKey ?
            useStorage(localStorageKey, /** @type {import("../types/util").ActionQueueItem<ID, T>[]} */([]), localStorage) :
        /** @type {import("vue").Ref<import("../types/util").ActionQueueItem<ID, T>[]>} */ (ref([]));

    const online = useOnline()

    const completed = computed(() => actions.value.filter(x => x.status == "completed"))
    const failed = computed(() => actions.value.filter(x => x.status == "failed"))
    const pending = computed(() => actions.value.filter(x => x.status == "pending"))

    watch([online, pending], ([isOnline, newPending]) => {
        if (!isOnline || newPending.length == 0) return;

        // Reference to the first item
        newPending.forEach(item => {
            item.status = "processing"
            action(item.args)
                .then(data => {
                    onSuccess(item.args, data);
                    item.resolvedId = data;
                    item.status = "completed";
                })
                .catch(e => {
                    onError(item.args, e);
                    item.status = "failed";
                })
        })
    }, { immediate: true })

    return {
        add: (action) => actions.value.push({
            args: action,
            status: "pending",
            addedOn: new Date(Date.now())
        }),
        completed,
        failed,
        pending
    }
}

// {
//     serializer: {
//         /**
//          * @param {string | null} v
//          * @returns {Draft[]}
//          */
//         read: (v) => v ?
//             (/** @type {RawDraft[]}*/ (JSON.parse(v)))
//                 .map(({ date, draftedAt, ...rest }) => ({
//                     ...rest,
//                     date: new Date(date),
//                     draftedAt: new Date(draftedAt)
//                 })).sort((a, b) => b.draftedAt.getTime() - a.draftedAt.getTime()) : [],

//             /**
//              * @param {Draft[]} v
//              */
//             write: (v) => JSON.stringify(v.map(({ date, draftedAt, ...rest }) => ({
//                 ...rest,
//                 date: date.toUTCString(),
//                 draftedAt: draftedAt.toUTCString()
//             }))),
//     },
// },