import { createFetch } from "@vueuse/core";
import { apiBaseUrl } from "../js-modules/website-constants";
import { usePersistantState } from "./persistant-state";

// Automatically adds the Authorization token
export const useAuthorizedFetch = createFetch({
    baseUrl: apiBaseUrl,
    combination: 'chaining',
    options: {
        async beforeFetch({ url, options, cancel }) {
            const state = usePersistantState()
            
            if(!state.value.token) {
                console.log("No Authorization Token found!");
                cancel();
            }

            options.headers.Authorization = `Bearer ${state.value.token}`

            return { options }
        },
    },
    fetchOptions: {
        mode: 'cors'
    },
});

export const useErrorHandledFetch = ({ origin }) => {
    
}