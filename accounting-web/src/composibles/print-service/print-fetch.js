// @ts-check
import { createFetch } from "@vueuse/core";

const BASE_URL = "http://127.0.0.1"

/**
 * Sends a fetch to the print service running in the local computer
 * @param {string} url URL to send the fetch to
 * @param {string} origin Origin of the fetch request (For notifications)
 * @param {boolean} [notifySuccess = false]
 * @returns 
 */
export const usePrintFetch = (url, origin, notifySuccess = false) => (createFetch({
    baseUrl: BASE_URL,
    combination: "chain",
    options: {
        onFetchError(ctx) {
            console.log(ctx);
            console.log(`Error has occured while fetching something: ${ctx.error}`);
            return ctx;
        },
        afterFetch(ctx) {
            // console.log(ctx.response);
            if (ctx.response.ok && notifySuccess)
                handleNotifySuccess({ origin, status: ctx.data.statusCode, statusText: ctx.data.statusMessage })

            return ctx;
        }
    }
}))(url);

/**
 * @param {{origin: string, status: number, statusText: string}} request 
 */
const handleRequestErrors = ({ origin, status, statusText }) => {
    window.alert(`Error Occured. ${origin}: ${status} ${statusText}`)
}
/**
 * @param {{origin: string, status: number, statusText: string}} request 
 */
const handleNotifySuccess = ({ origin, status, statusText }) => {
    window.alert(`Success. ${origin}: ${status} ${statusText}`)
}