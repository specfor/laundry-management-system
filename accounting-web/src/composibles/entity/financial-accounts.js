// @ts-check

import { useAuthorizedFetch } from "../authorized-fetch"
import { whenever } from "@vueuse/core";
import { logicAnd } from "@vueuse/math/index.cjs";
import { toValue, ref } from "vue";

export function useTaxes() {
    /**
     * Get all the financial accounts
     * @returns {Promise<import("../../types").FinancialAccount[]>}
     */
    const getFinanctialAccounts = async () => {
        return new Promise((resolve) => {
            const success = ref(false);
            const { data, isFinished } = useAuthorizedFetch(`/financial-accounts`, 'Get Financial Accounts', ref(true)).json().get();
            
            whenever(logicAnd(isFinished, success), () => {
                resolve(/** @type {import("../../types").FinancialAccount[] }*/(toValue(data)['financial-accounts']));
            })
        })
    }

    /**
     * Get a single financial account record
     * @param {number} id
     * @returns {Promise<import("../../types").FinancialAccount | undefined>} Returns undefined if no Financial Account record was found under the given ID
     */
    const getFinancialAccountById = async (id) => {
        return new Promise((resolve) => {
            const success =  ref(false);
            const { data, isFinished } = useAuthorizedFetch(`/financial-accounts?account-id=${id}`, 'Get Financial Account', success).json().get();

            whenever(logicAnd(isFinished, success), () => {
                const financialAccounts = /** @type {import("../../types").FinancialAccount[] }*/(toValue(data)['financial-accounts'])
                resolve(financialAccounts.findLast(() => true));
            })
        })
    }

    /**
     * Adds a financial account
     * @param {import("../../types").FinancialAccount} financialAccount Financial Account to be added
     * @returns {Promise<void>}
     */
    const addFinancialAccount = async (financialAccount) => {
        return new Promise((resolve) => {
            const success =  ref(false);
            const { isFinished } = useAuthorizedFetch('/financial-accounts/add', 'Add Financial Account', success, true).json().post(financialAccount);

            whenever(logicAnd(isFinished, success), () => resolve())
        })
    }

    /**
     * Update a financial account record
     * @param {import("ts-toolbelt/out/Object/Compulsory").Compulsory<Partial<import("../../types").FinancialAccount>, 'account_id'>} financialAccount Updated financial account record
     * @returns {Promise<void>}
     */
    const updateFinancialAccount = async (financialAccount) => {
        return new Promise((resolve) => {
            const success =  ref(false);
            const { isFinished } = useAuthorizedFetch('/financial-accounts/update', 'Update Financial Account', success, true).json().post(financialAccount);

            whenever(logicAnd(isFinished, success), () => resolve())
        })
    }

    /**
     * Removes a financial account record
     * @param {number} id Financial Account to be added
     * @returns {Promise<void>}
     */
    const removeFinancialAccount = async (id) => {
        return new Promise((resolve) => {
            const success =  ref(false);
            const { isFinished } = useAuthorizedFetch('/financial-accounts/remove', 'Remove Financial Account', success, true).json().post({ 'account-id': id });

            whenever(logicAnd(isFinished, success), () => resolve())
        })
    }

    return { getFinancialAccountById, getFinanctialAccounts, addFinancialAccount, updateFinancialAccount, removeFinancialAccount }
}