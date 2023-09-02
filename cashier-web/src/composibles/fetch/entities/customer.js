// @ts-check
import { useAuthorizedFetch } from "../authorized-fetch"
import { whenever } from "@vueuse/core";
import { ref, toValue } from "vue";
import { logicAnd, logicNot } from "@vueuse/math";
import { useNotifications } from "../../notification";

/**
 * @param {import("../../../types/util").NotificationInjection} [batchNotificationInjection]
 */
export function useCustomer(batchNotificationInjection) {

    /** Notification provider has to be inject here, which will most likely be run at setup() function of a Component. (inject() can only be used in setup()) */
    const notificationInjection = batchNotificationInjection ?? useNotifications().injectNotifications()
    
    /**
     * Converts the Raw response from the server into a entity object
     * @param {import("../../../types/entity").CustomerRaw} raw Raw response from the backend.
     * @returns {import("../../../types/entity").Customer}
     */
    const serialize = ({ joined_date, branch_id, customer_id, phone_num, ...rest }) => ({
        ...rest,
        branchId: branch_id,
        id: customer_id,
        phoneNum: phone_num,
        joinedDate: new Date(joined_date)
    })

    /**
     * Converts the entity to a object to be sent to the server
     * @param {Partial<import("../../../types/entity").Customer>} entity Entity
     * @returns {Partial<import("../../../types/entity").CustomerRaw>}
     */
    const deserialize = ({ branchId, joinedDate, id, phoneNum, ...rest }) => ({
        ...rest,
        branch_id: branchId,
        joined_date: joinedDate?.toLocaleDateString('en-CA'),
        customer_id: id,
        phone_num: phoneNum
    })

    /**
     * Get customers (Paginated) (30 Per page)
     * @param {number} pageNum
     * @returns {Promise<[import("../../../types/entity").Customer[], number]>} [Customers, Record Count]
     */
    const getCustomers = async (pageNum) => {
        return new Promise((resolve, reject) => {
            const success = ref(false);
            const { data, isFinished } = useAuthorizedFetch(`/customers?page-num=${pageNum}`, 'Get Customers', success, notificationInjection).json().get();

            whenever(logicAnd(isFinished, success), () => {
                const raw = /** @type {import("../../../types/entity").CustomerRaw[] }*/(toValue(data).customers)
                const count = /** @type {number} */ (toValue(data).record_count)
                resolve([raw.map(serialize), count]);
            })
            whenever(logicAnd(isFinished, logicNot(success)), () => reject())
        })
    }

    /**
     * @typedef SearchCustomersOptions
     * @property {string} [name]
     * @property {string} [phoneNum]
     * @property {string} [address]
     * @property {string} [email]
     */

    /**
     * Get customers matching the given query
     * @param {SearchCustomersOptions} options
     * @returns {Promise<import("../../../types/entity").Customer[]>}
     */
    const searchCustomer = async ({ address, email, name, phoneNum }) => {
        return new Promise((resolve, reject) => {
            const query = "?" + [
                address ? `address=${address}` : undefined,
                email ? `email=${email}` : undefined,
                name ? `name=${name}` : undefined,
                phoneNum ? `phone-number=${phoneNum}` : undefined
            ].filter(x => x).join("&")

            const success = ref(false);
            const { data, isFinished } = useAuthorizedFetch(`/customers${query}`, 'Search Customers', success, notificationInjection).json().get();

            whenever(logicAnd(isFinished, success), () => {
                const raw = /** @type {import("../../../types/entity").CustomerRaw[] }*/(toValue(data).customers)
                resolve(raw.map(serialize));
            })
            whenever(logicAnd(isFinished, logicNot(success)), () => reject())
        })
    }

    return { getCustomers, searchCustomer }
}