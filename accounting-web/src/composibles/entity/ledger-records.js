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
    const serialize = ({ date, body, ...rest }) => ({
        ...rest,
        date: new Date(date),
        body: body.map(({ credit, debit, ...remainder }) => ({
            ...remainder,
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
     * @param {Date} [data]
     * @param {string} [narration]
     * @returns {Promise<import("../../types").LedgerRecord[]>}
     */
    const getLedgerRecordsFiltered = async (limit, page, data, narration) => {
        return new Promise((resolve, reject) => {
            const queryString = [`limit=${limit}`, `page`]
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

    return { addLedgerRecord, getLedgerRecords, getLedgerRecordCount, getLedgerRecordsByDate, getLedgerRecordsByNarration }
}