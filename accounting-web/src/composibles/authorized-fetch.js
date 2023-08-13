// @ts-check
import { createFetch } from "@vueuse/core";
import { usePersistantState } from "./persistant-state";

const API_BASE_URL = "http://laundry-api.localhost/api/v1"

// Automatically adds the Authorization token
export const useAuthorizedFetch = createFetch({
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
            console.log(ctx.response);
            if (ctx.data.statusCode !== undefined && ctx.data.statusMessage !== undefined && ctx.data.body !== undefined) {
                ctx.response = {
                    ...ctx.response,
                    status: ctx.data.statusCode,
                    statusText: ctx.data.statusMessage
                }

                ctx.data = ctx.data.body
            }
            
            console.log(ctx.response);

            return ctx;
        }
    },
    fetchOptions: {
        mode: 'cors'
    },
});