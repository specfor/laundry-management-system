import type { AuthData, AuthInjection } from "@/types/util";
import { AuthInjectionKey } from "@/util/injection-keys";
import { createSharedComposable, useFetch, useStorage, whenever } from "@vueuse/core"
import { logicAnd, logicNot } from "@vueuse/math"
import { computed, provide } from "vue";
import { useRouter } from "vue-router";

// @ts-ignore
const API_BASE_URL = import.meta.env.VITE_API_BASE_URL ?? "/api/v1"

export const useAuthentication = createSharedComposable(() => {

    const state = useStorage<AuthData>('login-state', {});
    const { currentRoute, push } = useRouter();

    const login = (username: string, password: string) => new Promise<boolean>((resolve, reject) => {
        const { data, isFinished, onFetchError } = useFetch(API_BASE_URL + '/login').json().post({
            username,
            password
        })

        whenever(logicAnd(isFinished, data), () => {
            state.value = {
                ...state.value,
                authToken: data.value.body.token,
            }

            resolve(true);
        })

        onFetchError(ctx => reject(ctx.error))
    })

    const logout = () => {
        state.value = {
            ...state.value,
            authToken: undefined,
        }
    }

    const invalidateToken = () => {
        state.value = {
            ...state.value,
            authToken: undefined,
        }

        push({
            name: "Login",
            query: {
                "redirect": "true"
            }
        })
    }

    const authToken = computed(() => state.value.authToken)

    return { authToken, login, logout, invalidateToken }
})