// @ts-check
import { useAuthorizedFetch } from "../authorized-fetch"
import { whenever } from "@vueuse/core";
import { ref, toValue } from "vue";
import { logicAnd, logicNot } from "@vueuse/math";
import { useNotifications } from "../../notification";
import Decimal from "decimal.js";

/**
 * @param {import("../../../types/util").NotificationInjection} [batchNotificationInjection]
 */
export function useOrder(batchNotificationInjection) {

    /** Notification provider has to be inject here, which will most likely be run at setup() function of a Component. (inject() can only be used in setup()) */
    const notificationInjection = batchNotificationInjection ?? useNotifications().injectNotifications()

    /**
     * Converts the Raw response from the server into a entity object
     * @param {import("../../../types/entity").OrderRaw} raw Raw response from the backend.
     * @returns {import("../../../types/entity").Order}
     */
    const serialize = ({ added_date, branch_id, customer_id, customer_name, order_id, total_price, payments, items, status, comments }) => ({
        comments,
        branchId: branch_id,
        id: order_id,
        customerId: customer_id,
        customerName: customer_name,
        totalPrice: new Decimal(total_price),
        status: status.map(({ message, time }) => ({ message, time: new Date(time * 1000) })),
        addedDate: new Date(added_date),
        payments: payments.map(({ paid_amount, paid_date, payment_id, refunded }) => ({
            id: payment_id,
            paidAmount: new Decimal(paid_amount),
            paidDate: new Date(paid_date),
            refunded
        })),
        items: items.map(({ categories, item_id, item_name, price, "return-date": returnDate, ...rest }) => ({
            ...rest,
            returnDate: new Date(returnDate),
            id: item_id,
            name: item_name,
            categories: categories.categories.map(({ category_id, name }) => ({
                name,
                id: category_id
            })),
            price: new Decimal(price)
        }))
    })

    // /**
    //  * Converts the entity to a object to be sent to the server
    //  * @param {Partial<import("../../../types/entity").Order>} entity Entity
    //  * @returns {Partial<import("../../../types/entity").OrderRaw>}
    //  */
    // const deserialize = ({ branchId, joinedDate, id, phoneNum, ...rest }) => ({
    //     ...rest,
    //     branch_id: branchId,
    //     joined_date: joinedDate?.toLocaleDateString('en-CA'),
    //     customer_id: id,
    //     phone_num: phoneNum
    // })

    /**
     * Converts the entity to a object to be sent to the server
     * @param {import("../../../types/entity").AddOrderOptions} entity Entity
     * @returns {import("../../../types/entity").AddOrderRequestData}
     */
    const deserializeAddOptions = ({ branchId, customerId, items, totalPrice, comments }) => ({
        "branch-id": branchId,
        "customer-comments": comments,
        "customer-id": customerId,
        "total-price": totalPrice ? totalPrice.toString() : undefined,
        items: Object.fromEntries(Object.entries(items).map(([key, { returnDate, ...rest }]) => ([key, {
            ...rest,
            "return-date": returnDate.toLocaleDateString('en-CA')
        }])))
    })

    /**
     * Get Orders (Paginated) (30 Per page)
     * @param {number} pageNum
     * @returns {Promise<[import("../../../types/entity").Order[], number]>} [Customers, Record Count]
     */
    const getOrders = async (pageNum) => {
        return new Promise((resolve, reject) => {
            const success = ref(false);
            const { data, isFinished } = useAuthorizedFetch(`/customers?page-num=${pageNum}`, 'Get Customers', success, notificationInjection).json().get();

            whenever(logicAnd(isFinished, success), () => {
                const raw = /** @type {import("../../../types/entity").OrderRaw[] }*/(toValue(data).customers)
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
     * @returns {Promise<import("../../../types/entity").Order[]>}
     */
    const searchOrders = async ({ address, email, name, phoneNum }) => {
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
                const raw = /** @type {import("../../../types/entity").OrderRaw[] }*/(toValue(data).customers)
                resolve(raw.map(serialize));
            })
            whenever(logicAnd(isFinished, logicNot(success)), () => reject())
        })
    }

    /**
     * Adds a Order
     * @param {import("../../../types/entity").AddOrderOptions} options
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

    return { getOrders, searchOrders }
}