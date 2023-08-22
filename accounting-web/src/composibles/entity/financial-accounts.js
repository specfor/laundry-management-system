// @ts-check

import { useAuthorizedFetch } from "../authorized-fetch"
import { whenever } from "@vueuse/core";
import { logicAnd, logicNot } from "@vueuse/math/index.cjs";
import { toValue, ref } from "vue";
import { useNotifications } from "../notification";

/**
 * @typedef {ReturnType<(ReturnType<typeof useNotifications>)['injectNotifications']>} BatchNotificationInjection
 */

/**
 * @param {BatchNotificationInjection} [batchNotificationInjection]
 */
export function useFinancialAccounts(batchNotificationInjection) {

    /** Notification provider has to be inject here, which will most likely be run at setup() function of a Component. (inject() can only be used in setup()) */
    const notificationInjection = batchNotificationInjection ?? useNotifications().injectNotifications()
    
    /**
     * Converts the Raw response from the server into a entity object
     * @param {import("../../types").RawFinancialAccount} rawAccount Raw response from the backend.
     * @returns {import("../../types").FinancialAccount}
     */
    const serialize = ({ locked, archived, deletable, ...rest }) => ({
        ...rest,
        locked: !!locked,
        deletable: !!deletable,
        archived: !!archived
    })

    /**
     * Converts the entity to a object to be sent to the server
     * @param {Partial<import("../../types").FinancialAccount>} accountEntity Financial account Entity
     * @returns {Partial<import("../../types").RawFinancialAccount>}
     */
    const deserialize = ({ locked, archived, deletable, ...rest }) => ({
        ...rest,
        deletable: deletable ? +deletable : undefined,
        locked: locked ? +locked : undefined,
        archived: archived ? +archived : undefined
    })

    /**
     * Get all the financial accounts
     * @returns {Promise<import("../../types").FinancialAccount[]>}
     */
    const getFinanctialAccounts = async () => {
        return new Promise((resolve, reject) => {
            const success = ref(false);
            const { data, isFinished } = useAuthorizedFetch(`/financial-accounts?limit=9999`, 'Get Financial Accounts', success, notificationInjection).json().get();

            whenever(logicAnd(isFinished, success), () => {
                const rawData = /** @type {import("../../types").RawFinancialAccount[] }*/(toValue(data)['financial-accounts'])
                resolve(rawData.map(serialize));
            })
            whenever(logicAnd(isFinished, logicNot(success)), () => reject())
        })
    }

    /**
     * Get a single financial account record
     * @param {number} id
     * @returns {Promise<import("../../types").FinancialAccount | undefined>} Returns undefined if no Financial Account record was found under the given ID
     */
    const getFinancialAccountById = async (id) => {
        return new Promise((resolve, reject) => {
            const success = ref(false);
            const { data, isFinished } = useAuthorizedFetch(`/financial-accounts?account-id=${id}`, 'Get Financial Account', success, notificationInjection).json().get();

            whenever(logicAnd(isFinished, success), () => {
                const rawData = /** @type {import("../../types").RawFinancialAccount[] }*/(toValue(data)['financial-accounts'])
                resolve(rawData.map(serialize).findLast(() => true));
            })
            whenever(logicAnd(isFinished, logicNot(success)), () => reject())
        })
    }

    /**
     * Adds a financial account
     * @param {Omit<import("../../types").FinancialAccount, "account_id">} financialAccount Financial Account to be added
     * @returns {Promise<number>} Returns the created account's ID
     */
    const addFinancialAccount = async (financialAccount) => {
        return new Promise((resolve, reject) => {
            const success = ref(false);
            const { isFinished, data } = useAuthorizedFetch('/financial-accounts/add', 'Add Financial Account', success, notificationInjection, true).json().post(deserialize(financialAccount));

            whenever(logicAnd(isFinished, success), () => resolve(data.value['account_id']))
            whenever(logicAnd(isFinished, logicNot(success)), () => reject())
        })
    }

    /**
     * Update a financial account record
     * @param {import("ts-toolbelt/out/Object/Compulsory").Compulsory<Partial<import("../../types").FinancialAccount>, 'account_id'>} financialAccount Updated financial account record
     * @returns {Promise<void>}
     */
    const updateFinancialAccount = async (financialAccount) => {
        return new Promise((resolve, reject) => {
            const success = ref(false);
            const { isFinished } = useAuthorizedFetch('/financial-accounts/update', 'Update Financial Account', success, notificationInjection, true).json().post(deserialize(financialAccount));

            whenever(logicAnd(isFinished, success), () => resolve())
            whenever(logicAnd(isFinished, logicNot(success)), () => reject())
        })
    }

    /**
     * Removes a financial account record
     * @param {number} id Financial Account to be added
     * @returns {Promise<void>}
     */
    const removeFinancialAccount = async (id) => {
        return new Promise((resolve, reject) => {
            const success = ref(false);
            const { isFinished } = useAuthorizedFetch('/financial-accounts/remove', 'Remove Financial Account', success, notificationInjection, true).json().post({ 'account_id': id });

            whenever(logicAnd(isFinished, success), () => resolve())
            whenever(logicAnd(isFinished, logicNot(success)), () => reject())
        })
    }

    return { getFinancialAccountById, getFinanctialAccounts, addFinancialAccount, updateFinancialAccount, removeFinancialAccount }
}