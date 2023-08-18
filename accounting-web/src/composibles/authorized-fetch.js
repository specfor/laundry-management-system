// @ts-check
import { createFetch } from "@vueuse/core";
import { usePersistantState } from "./persistant-state";

const API_BASE_URL = "http://laundry-api.localhost/api/v1"

// Automatically adds the Authorization token
/**
 * Sends a fetch with Authorization token added automatically
 * @param {string} url URL to send the fetch to
 * @param {string} origin Origin of the fetch request (For notifications)
 * @param {import("vue").Ref<boolean>} successRef A Ref to be used as a container to notify of complete success. It's value will be set to true when the request has been completed successfully and no error messages were present in the response.
 * @param {boolean} [notifySuccess = false] Whether a notification should be sent on fetch success
 * @returns 
 */
export const useAuthorizedFetch = (url, origin, successRef, notifySuccess = false) => (createFetch({
    baseUrl: API_BASE_URL,
    combination: "chain",
    options: {
        async beforeFetch({ options, cancel }) {
            const state = usePersistantState()

            if (!state.value.token) {
                console.log("No Authorization Token found!");
                cancel();
                return;
            }

            options.headers = {
                ...options.headers,
                Authorization: `Bearer ${state.value.token}`,
            }

            return { options }
        },
        onFetchError(ctx) {
            console.log(`Error has occured while fetching something: ${ctx.error}`);
            return ctx;
        },
        afterFetch(ctx) {
            // Transform the response a bit
            // console.log(ctx.response);
            if (ctx.data.statusCode !== undefined && ctx.data.statusMessage !== undefined && ctx.data.body !== undefined) {
                successRef.value = true;
                if (ctx.data.statusCode != 200 || ctx.data.statusMessage == "error") {
                    successRef.value = false;
                    handleRequestErrors({ origin, status: ctx.data.statusCode, statusText: ctx.data.statusMessage })
                }
                else if (notifySuccess) handleNotifySuccess({ origin, status: ctx.data.statusCode, statusText: ctx.data.statusMessage })

                ctx.data = ctx.data.body
            }

            return ctx;
        }
    },
    fetchOptions: {
        mode: 'cors'
    },
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