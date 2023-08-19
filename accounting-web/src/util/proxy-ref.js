// @ts-check

import { ref } from "vue"

/**
 * @template T
 * @param {() => Promise<T>} fetcher
 */
export const proxyRef = (fetcher) => {
    const _innerRef = /** @type {import("vue").Ref<T | null>} */(ref(null))

    const value = async () => {
        if (_innerRef.value == null) {
            _innerRef.value = await fetcher()
        }

        return _innerRef.value
    }

    return { value };
}