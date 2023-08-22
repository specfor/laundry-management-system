// @ts-check

import Decimal from "decimal.js"
import { useAuthorizedFetch } from "../authorized-fetch"
import { whenever } from "@vueuse/core";
import { ref, toValue } from "vue";
import { logicAnd, logicNot } from "@vueuse/math/index.cjs";
import { useNotifications } from "../notification";

/**
 * @typedef {ReturnType<(ReturnType<typeof useNotifications>)['injectNotifications']>} BatchNotificationInjection
 */

/**
 * @param {BatchNotificationInjection} [batchNotificationInjection]
 */
export function useTaxes(batchNotificationInjection) {

    /** Notification provider has to be inject here, which will most likely be run at setup() function of a Component. (inject() can only be used in setup()) */
    const notificationInjection = batchNotificationInjection ?? useNotifications().injectNotifications()
    
    /**
     * Converts the Raw response from the server into a entity object
     * @param {import("../../types").RawTax} rawTax Raw response from the backend.
     * @returns {import("../../types").Tax}
     */
    const serialize = ({ tax_rate, locked, deleted, ...rest }) => ({
        ...rest,
        locked: !!locked,
        deleted: !!deleted,
        tax_rate: new Decimal(tax_rate)
    })

    /**
     * Converts the entity to a object to be sent to the server
     * @param {Partial<import("../../types").Tax>} taxEntity Tax Entity
     * @returns {Partial<import("../../types").RawTax>}
     */
    const deserialize = ({ tax_rate, deleted, locked, ...rest }) => ({
        ...rest,
        deleted: deleted ? +deleted : undefined,
        locked: locked ? +locked : undefined,
        tax_rate: tax_rate ? tax_rate.toString() : undefined
    })

    /**
     * Get all the taxes
     * @returns {Promise<import("../../types").Tax[]>}
     */
    const getTaxes = async () => {
        return new Promise((resolve, reject) => {
            const success = ref(false);
            const { data, isFinished } = useAuthorizedFetch(`/taxes?limit=9999`, 'Get Taxes', success, notificationInjection).json().get();

            whenever(logicAnd(isFinished, success), () => {
                const taxesRaw = /** @type {import("../../types").RawTax[] }*/(toValue(data).taxes)
                resolve(taxesRaw.map(serialize));
            })
            whenever(logicAnd(isFinished, logicNot(success)), () => reject())
        })
    }

    /**
     * Get a single tax record
     * @param {number} id
     * @returns {Promise<import("../../types").Tax | undefined>} Returns undefined if no Tax record was found under the given ID
     */
    const getTax = async (id) => {
        return new Promise((resolve, reject) => {
            const success = ref(false);
            const { data, isFinished } = useAuthorizedFetch(`/taxes?tax-id=${id}`, 'Get Tax', success, notificationInjection).json().get();

            whenever(logicAnd(isFinished, success), () => {
                const taxesRaw = /** @type {import("../../types").RawTax[] }*/(toValue(data).taxes)
                resolve(taxesRaw.map(serialize).findLast(() => true));
            })
            whenever(logicAnd(isFinished, logicNot(success)), () => reject())
        })
    }

    /**
     * Adds a tax
     * @param {Omit<import("../../types").Tax, "tax_id">} tax Tax to be added
     * @returns {Promise<number>}
     */
    const addTax = async (tax) => {
        return new Promise((resolve, reject) => {
            const success = ref(false);
            const { isFinished, data } = useAuthorizedFetch('/taxes/add', 'Add Tax', success, notificationInjection, true).json().post(deserialize(tax));

            whenever(logicAnd(isFinished, success), () => resolve(data.value['tax_id']))
            whenever(logicAnd(isFinished, logicNot(success)), () => reject())
        })
    }

    /**
     * Update a tax record
     * @param {import("ts-toolbelt/out/Object/Compulsory").Compulsory<Partial<import("../../types").Tax>, 'tax_id'>} tax Updated tax record
     * @returns {Promise<void>}
     */
    const updateTax = async (tax) => {
        return new Promise((resolve, reject) => {
            const success = ref(false);
            const { isFinished } = useAuthorizedFetch('/taxes/update', 'Update Tax', success, notificationInjection, true).json().post(deserialize(tax));

            whenever(logicAnd(isFinished, success), () => resolve())
            whenever(logicAnd(isFinished, logicNot(success)), () => reject())
        })
    }

    /**
     * Removes a tax record
     * @param {number} id Tax to be added
     * @returns {Promise<void>}
     */
    const removeTax = async (id) => {
        return new Promise((resolve, reject) => {
            const success = ref(false);
            const { isFinished } = useAuthorizedFetch('/taxes/remove', 'Remove Tax', success, notificationInjection, true).json().post({ 'tax-id': id });

            whenever(logicAnd(isFinished, success), () => resolve())
            whenever(logicAnd(isFinished, logicNot(success)), () => reject())
        })
    }

    return { getTax, getTaxes, addTax, updateTax, removeTax }
}