// @ts-check

import { ref } from "vue";
import { useNotifications } from "./notification"

/**
 * @template T
 * @param {(batchNotificationInjection: import("./entity/financial-accounts").BatchNotificationInjection) => T} entityFetcher
 */
export const useBatchFetch = (entityFetcher) => {
    const { showError, showSuccess } = useNotifications().injectNotifications();

    const errors = ref(/** @type {{ origin: string; status: number; statusText: string }[]} */([]));
    const successes = ref(/** @type {{ origin: string; status: number; statusText: string }[]} */([])); // I don't know if that's even a word

    /**
     * Batches the show error
     * @param { { origin: string; status: number; statusText: string } } options 
     */
    const batchShowError = (options) => errors.value.push(options);

    /**
     * Batches the show syccess
     * @param { { origin: string; status: number; statusText: string } } options 
     */
    const batchShowSuccess = (options) => successes.value.push(options)

    /**
     * @template R
     * @param {() => Promise<R>} handler 
     * @param {string} batchName
     * @param {boolean} notifySuccess
     * @returns {Promise<R>}
     */
    const batch = (handler, batchName, notifySuccess = false) => {
        return new Promise((resolve, reject) => {
            handler()
                .then((response) => {
                    notifySuccess ? showSuccess({ origin: batchName, status: 200, statusText: "Complete!" }) : null;
                    resolve(response);
                })
                .catch((e) => {
                    console.log("Errpr on batch process" + e);
                    showError({ origin: batchName, status: 0, statusText: "Batch operation failed" })
                    reject(null);
                })
        })
    }

    const entityFetcherReturns = entityFetcher({ showError: batchShowError, showSuccess: batchShowSuccess })

    return { ...entityFetcherReturns, batch }
}