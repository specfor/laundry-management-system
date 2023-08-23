// @ts-check
import { createGlobalState, useStorage } from '@vueuse/core'

// State will automatically be persisted to localStorage
export const usePersistantState = createGlobalState(() =>
  useStorage('vue-use-locale-storage', {
    token: /** @type {string | null} */(null),
    isLoggedIn: false,
    userId: /** @type {number | null} */(null),
  }),
);