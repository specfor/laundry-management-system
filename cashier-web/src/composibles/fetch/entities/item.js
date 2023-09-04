// @ts-check
import Decimal from "decimal.js"
import { useAuthorizedFetch } from "../authorized-fetch"
import { whenever } from "@vueuse/core";
import { ref, toValue } from "vue";
import { logicAnd, logicNot } from "@vueuse/math";
import { useNotifications } from "../../notification";

/**
 * @param {import("../../../types/util").NotificationInjection} [batchNotificationInjection]
 */
export function useItem(batchNotificationInjection) {

    /** Notification provider has to be inject here, which will most likely be run at setup() function of a Component. (inject() can only be used in setup()) */
    const notificationInjection = batchNotificationInjection ?? useNotifications().injectNotifications()
    
    /**
     * Converts the Raw response from the server into a entity object
     * @param {import("../../../types/entity").ItemRaw} raw Raw response from the backend.
     * @returns {import("../../../types/entity").Item}
     */
    const serialize = ({ item_id, price, ...rest }) => ({
        ...rest,
        id: item_id,
        price: new Decimal(price)
    })

    /**
     * Converts the entity to a object to be sent to the server
     * @param {Partial<import("../../../types/entity").Item>} entity Entity
     * @returns {Partial<import("../../../types/entity").ItemRaw>}
     */
    const deserialize = ({ id, price, ...rest }) => ({
        ...rest,
        item_id: id,
        price: price?.toString()
    })

    /**
     * Get customers (Paginated) (30 Per page)
     * @param {number} pageNum
     * @returns {Promise<[import("../../../types/entity").Item[], number]>} [Items, Record count]
     */
    const getItems = async (pageNum) => {
        return new Promise((resolve, reject) => {
            const success = ref(false);
            const { data, isFinished } = useAuthorizedFetch(`/items?page-num=${pageNum}`, 'Get Items', success, notificationInjection).json().get();

            whenever(logicAnd(isFinished, success), () => {
                const raw = /** @type {import("../../../types/entity").ItemRaw[] }*/ (toValue(data).items)
                const count = /** @type {number}*/(toValue(data).record_count)
                resolve([raw.map(serialize), count]);
            })
            whenever(logicAnd(isFinished, logicNot(success)), () => reject())
        })
    }

    /**
     * @typedef SearchItemsOptions
     * @property {string} [name]
     */

    /**
     * Get items matching the given query
     * @param {SearchItemsOptions} options
     * @returns {Promise<import("../../../types/entity").Item[]>}
     */
    const searchItems = async ({ name }) => {
        return new Promise((resolve, reject) => {
            const query = "?" + [
                name ? `item-name=${name}` : undefined,
            ].filter(x => x).join("&")

            const success = ref(false);
            const { data, isFinished } = useAuthorizedFetch(`/items${query}`, 'Search Items', success, notificationInjection).json().get();

            whenever(logicAnd(isFinished, success), () => {
                const raw = /** @type {import("../../../types/entity").ItemRaw[] }*/(toValue(data).items)
                resolve(raw.map(serialize));
            })
            whenever(logicAnd(isFinished, logicNot(success)), () => reject())
        })
    }

    return { getItems, searchItems }
}