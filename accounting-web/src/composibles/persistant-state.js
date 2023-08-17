// @ts-check
import { createGlobalState, useStorage } from '@vueuse/core'

// State will automatically be persisted to localStorage
export const usePersistantState = createGlobalState(() =>
  useStorage('vue-use-locale-storage', {
    token: null,
    isLoggedIn: false,
    userId: null,
  }),
);