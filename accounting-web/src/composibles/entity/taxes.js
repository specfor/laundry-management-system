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
    const serialize = ({ tax_rate, ...rest }) => ({
        ...rest,
        tax_rate: new Decimal(tax_rate)
    })

    /**
     * Converts the entity to a object to be sent to the server
     * @param {Partial<import("../../types").Tax>} taxEntity Tax Entity
     * @returns {Partial<import("../../types").RawTax>}
     */
    const deserialize = ({ tax_rate, ...rest }) => ({
        ...rest,
        tax_rate: tax_rate ? tax_rate.toString() : undefined
    })

    /**
     * Get all the taxes
     * @returns {Promise<import("../../types").Tax[]>}
     */
    const getTaxes = async () => {
        return new Promise((resolve) => {
            const { data, isFinished } = useAuthorizedFetch(`/taxes?limit=9999`, 'Get Taxes').json().get();

            whenever(isFinished, () => {
                const taxesRaw = /** @type {import("../../types").RawTax[] }*/(toValue(data).taxes)
                resolve(taxesRaw.map(serialize));
            })
        })
    }

    /**
     * Get a single tax record
     * @param {number} id
     * @returns {Promise<import("../../types").Tax | undefined>} Returns undefined if no Tax record was found under the given ID
     */
    const getTax = async (id) => {
        return new Promise((resolve) => {
            const { data, isFinished } = useAuthorizedFetch(`/taxes?tax-id=${id}`, 'Get Tax').json().get();

            whenever(isFinished, () => {
                const taxesRaw = /** @type {import("../../types").RawTax[] }*/(toValue(data).taxes)
                resolve(taxesRaw.map(serialize).findLast(() => true));
            })
        })
    }

    /**
     * Adds a tax
     * @param {import("../../types").Tax} tax Tax to be added
     * @returns {Promise<void>}
     */
    const addTax = async (tax) => {
        return new Promise((resolve) => {
            const { isFinished } = useAuthorizedFetch('/taxes/add', 'Add Tax', true).json().post(deserialize(tax));

            whenever(isFinished, () => resolve())
        })
    }

    /**
     * Update a tax record
     * @param {import("ts-toolbelt/out/Object/Compulsory").Compulsory<Partial<import("../../types").Tax>, 'tax_id'>} tax Updated tax record
     * @returns {Promise<void>}
     */
    const updateTax = async (tax) => {
        return new Promise((resolve) => {
            const { isFinished } = useAuthorizedFetch('/taxes/update', 'Update Tax', true).json().post(deserialize(tax));

            whenever(isFinished, () => resolve())
        })
    }

    /**
     * Removes a tax record
     * @param {number} id Tax to be added
     * @returns {Promise<void>}
     */
    const removeTax = async (id) => {
        return new Promise((resolve) => {
            const { isFinished } = useAuthorizedFetch('/taxes/remove', 'Remove Tax', true).json().post({ 'tax-id': id });

            whenever(isFinished, () => resolve())
        })
    }

    return { getTax, getTaxes, addTax, updateTax, removeTax }
}