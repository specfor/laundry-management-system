// @ts-check

import { toValue } from "vue"
import { useNotification } from "./notifications"

/**
 * Handles the errors and 
 * FYI, All this shenanigans is just to get autocompletion
 * @param {import("@vueuse/core").UseFetchReturn<any> & PromiseLike<import("@vueuse/core").UseFetchReturn<any>>} fetch 
 * @param {string} origin Origin of the fetch request
 * @param {boolean} notifySuccess
 * @returns {Promise<import("@vueuse/core").UseFetchReturn<any> & PromiseLike<import("@vueuse/core").UseFetchReturn<any>>>} 
 */
export function useFetchResponseHandler({ onFetchError, onFetchResponse, data, ...rest }, origin, notifySuccess) {
    // Add the error handler to the onFetchError listner
    return new Promise((resolve) => {
        const { pushErrorNotification, pushSuccessNotification } = useNotification();

        onFetchError(({ error }) => {
            console.log(error);
            pushErrorNotification(origin, error.message);
        })

        onFetchResponse((x) => {
            // TODO: Check content of x
            console.log(x);
            if (notifySuccess) {
                pushSuccessNotification(origin, data.value.message)
            }

            // There is really no need the return the two on event listners, but just for the sake of it
            resolve({ onFetchError, onFetchResponse, data, ...rest })
        });
    })
}