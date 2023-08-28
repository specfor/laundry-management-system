// @ts-check

import Decimal from "decimal.js"
import { useAuthorizedFetch } from "../authorized-fetch"
import { whenever } from "@vueuse/core";
import { ref, toValue } from "vue";
import { logicAnd, logicNot } from "@vueuse/math/index.cjs";
import { useNotifications } from "../notification";

export function useLedgerRecords() {

    /** Notification provider has to be inject here, which will most likely be run at setup() function of a Component. (inject() can only be used in setup()) */
    const notificationInjection = useNotifications().injectNotifications()

    /**
     * Converts the Raw response from the server into a entity object
     * @param {import("../../types").RawLedgerRecord} rawLedgerRecord Raw response from the backend.
     * @returns {import("../../types").LedgerRecord}
     */
    const serialize = ({ date, body, tot_amount, created_at, ...rest }) => ({
        ...rest,
        createdAt: new Date(created_at * 1000),
        date: new Date(date),
        totalAmount: new Decimal(tot_amount),
        body: body.map(({ credit, debit, ...remainder }) => ({
            ...remainder,
            ...(credit ? { credit: new Decimal(credit) } : { debit: new Decimal(debit ?? "") })
        }))
    })

    /**
     * Converts the Raw response from the server into a entity object
     * @param {import("../../types").RawLedgerRecordWithTaxAndAccountData} rawLedgerRecord Raw response from the backend.
     * @returns {import("../../types").LedgerRecordWithTaxAndAccountData}
     */
    const serializeWithData = ({ date, body, tot_amount, created_at, ...rest }) => ({
        ...rest,
        createdAt: new Date(created_at * 1000),
        date: new Date(date),
        totalAmount: new Decimal(tot_amount),
        body: body.map(({ credit, debit, account_description, account_tax_id, tax_rate, tax_name, tax_id, ...remainder }) => ({
            ...remainder,
            account_description: account_description ?? "",
            account_tax_id,
            tax_id: tax_id ?? account_tax_id,
            tax_name: tax_name ?? "Tax Exempt",
            tax_rate: new Decimal(tax_rate ?? "0"),
            ...(credit ? { credit: new Decimal(credit) } : { debit: new Decimal(debit ?? "") })
        }))
    })

    /**
     * Converts the entity to a object to be sent to the server
     * @param {import("../../types").LedgerRecordAddOptions} ledgerRecordAddOptions Ledger record add options
     * @returns {import("../../types").LedgerRecordAddRequest}
     */
    const deserialize = ({ date, body, taxType, ...rest }) => ({
        ...rest,
        "tax-type": taxType,
        date: date ? /** @type {import("../../types").DateString} */(date.toLocaleDateString('en-CA')) : undefined, // yyyy-mm-dd
        body: body.map(({ credit, debit, ...remainder }) => ({
            ...remainder,
            ...(credit ? { credit: credit.toString() } : { debit: debit.toString() }),
        }))
    })

    /**
     * Get all the ledger records
     * @returns {Promise<import("../../types").LedgerRecord[]>}
     */
    const getLedgerRecords = async () => {
        return new Promise((resolve, reject) => {
            const success = ref(false);
            const { data, isFinished } = useAuthorizedFetch(`/general-ledger`, 'Get Ledger Records', success, notificationInjection).json().get();

            whenever(logicAnd(isFinished, success), () => {
                const rawLedgerRecords = /** @type {import("../../types").RawLedgerRecord[] }*/(toValue(data).records);
                resolve(rawLedgerRecords.map(serialize));
            })
            whenever(logicAnd(isFinished, logicNot(success)), () => reject())
        })
    }

    /**
     * Get ledger records filtred by date or narration
     * @param {number} limit
     * @param {number} page
     * @param {Date} [date]
     * @param {string} [narration]
     * @param {AbortSignal} [abortSignal]
     * @returns {Promise<{records: import("../../types").LedgerRecord[], recordCount: number}>}
     */
    const getLedgerRecordsFiltered = async (limit, page, date, narration, abortSignal) => {
        return new Promise((resolve, reject) => {
            const queryString = "?" + [
                `limit=${limit}`,
                `page-num=${page - 1}`,
                date ? `date=${date.toLocaleDateString('en-CA')}` : "",
                narration ? `narration=${narration}` : ""
            ].filter(x => x != "").join("&");

            const success = ref(false);
            const { data, isFinished } = useAuthorizedFetch(`/general-ledger${queryString}`, 'Get Ledger Records', success, notificationInjection, false, abortSignal).json().get();

            whenever(logicAnd(isFinished, success), () => {
                const rawLedgerRecords = /** @type {import("../../types").RawLedgerRecord[] }*/(toValue(data).records);
                resolve({
                    recordCount: /** @type {number}*/(toValue(data).record_count),
                    records: rawLedgerRecords.map(serialize)
                });
            })

            whenever(logicAnd(isFinished, logicNot(success)), () => reject())
        })
    }

    /**
     * Get a ledger record by ID
     * @param {number} id
     * @returns {Promise<import("../../types").LedgerRecordWithTaxAndAccountData>} 
     */
    const getLedgerRecordById = async (id) => {
        return new Promise((resolve, reject) => {
            const success = ref(false);
            const { data, isFinished } = useAuthorizedFetch(`/general-ledger?record-id=${id}`, 'Get Ledger records by ID', success, notificationInjection).json().get();

            whenever(logicAnd(isFinished, success), () => {
                if((/** @type {number} */ (toValue(data).record_count)) == 0) {
                    // Show an error message
                    notificationInjection.showError({
                        origin: "Get Ledger records by ID",
                        status: 0,
                        statusText: "No such Ledger record exist with ID of " + id
                    })
                    reject()
                }
                const rawLedgerRecords = /** @type {import("../../types").RawLedgerRecordWithTaxAndAccountData[] }*/(toValue(data).records);
                resolve(rawLedgerRecords.map(serializeWithData)[0]);
            })
            whenever(logicAnd(isFinished, logicNot(success)), () => reject())
        })
    }

    /**
     * Get ledger record count
     * @returns {Promise<number>}
     */
    const getLedgerRecordCount = async () => {
        return new Promise((resolve, reject) => {
            const success = ref(false);
            const { data, isFinished } = useAuthorizedFetch(`/general-ledger`, 'Get Ledger Records', success, notificationInjection).json().get();

            whenever(logicAnd(isFinished, success), () => {
                const recordCount = /** @type {number}*/(toValue(data).record_count);
                resolve(recordCount);
            })
            whenever(logicAnd(isFinished, logicNot(success)), () => reject())
        })
    }

    /**
     * Get ledger records matching the given narration
     * @param {number} narration Narration to search for
     * @returns {Promise<import("../../types").LedgerRecord[]>} 
     */
    const getLedgerRecordsByNarration = async (narration) => {
        return new Promise((resolve, reject) => {
            const success = ref(false);
            const { data, isFinished } = useAuthorizedFetch(`/general-ledger?narration=${narration}`, 'Get Ledger Records by Narration', success, notificationInjection).json().get();

            whenever(logicAnd(isFinished, success), () => {
                const rawLedgerRecords = /** @type {import("../../types").RawLedgerRecord[] }*/(toValue(data).records);
                resolve(rawLedgerRecords.map(serialize));
            })
            whenever(logicAnd(isFinished, logicNot(success)), () => reject())
        })
    }

    /**
     * Get a ledger records belonging to the given date
     * @param {Date} date
     * @returns {Promise<import("../../types").LedgerRecord[]>} 
     */
    const getLedgerRecordsByDate = async (date) => {
        return new Promise((resolve, reject) => {
            const success = ref(false);
            const { data, isFinished } = useAuthorizedFetch(`/general-ledger?date=${date.toLocaleDateString('en-CA')}`, 'Get Ledger records by day', success, notificationInjection).json().get();

            whenever(logicAnd(isFinished, success), () => {
                const rawLedgerRecords = /** @type {import("../../types").RawLedgerRecord[] }*/(toValue(data).records);
                resolve(rawLedgerRecords.map(serialize));
            })
            whenever(logicAnd(isFinished, logicNot(success)), () => reject())
        })
    }

    /**
     * Adds a Ledger record
     * @param {import("../../types").LedgerRecordAddOptions} options Financial Account to be added
     * @returns {Promise<void>}
     */
    const addLedgerRecord = async (options) => {
        return new Promise((resolve, reject) => {
            const success = ref(false);
            const { isFinished } = useAuthorizedFetch('/general-ledger/add', 'Add Ledger Record', success, notificationInjection, true).json().post(deserialize(options));

            whenever(logicAnd(isFinished, success), () => resolve())
            whenever(logicAnd(isFinished, logicNot(success)), () => reject())
        })
    }

    return { addLedgerRecord, getLedgerRecords, getLedgerRecordCount, getLedgerRecordsByDate, getLedgerRecordsByNarration, getLedgerRecordsFiltered, getLedgerRecordById }
}