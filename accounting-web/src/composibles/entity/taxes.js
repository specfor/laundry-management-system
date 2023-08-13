// @ts-check

import Decimal from "decimal.js"
import { useAuthorizedFetch } from "../authorized-fetch"
import { whenever } from "@vueuse/core";
import { toValue } from "vue";

export function useTaxes() {
    /**
     * Converts the Raw response from the server into a entity object
     * @param {import("../../types").RawTax} rawTax Raw response from the backend.
     * @returns {import("../../types").Tax}
     */
    const transform = ({ tax_rate, ...rest }) => ({
        ...rest,
        tax_rate: new Decimal(tax_rate)
    })

    /**
     * Gets all the taxes
     * @returns {Promise<import("../../types").Tax[]>}
     */
    const getTaxes = async () => {
        return new Promise((resolve) => {
            const { data, isFinished } = useAuthorizedFetch('/taxes').json().get();
            
            whenever(isFinished, () => {
                const taxesRaw = /** @type {import("../../types").RawTax[] }*/(toValue(data).taxes)
                resolve(taxesRaw.map(transform));
            })
        })
    }

    return { getTaxes }
}