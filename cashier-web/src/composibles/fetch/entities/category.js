// @ts-check
import { useAuthorizedFetch } from "../authorized-fetch"
import { whenever } from "@vueuse/core";
import { ref, toValue } from "vue";
import { logicAnd, logicNot } from "@vueuse/math";
import { useNotifications } from "../../notification";

/**
 * @param {import("../../../types/util").NotificationInjection} [batchNotificationInjection]
 */
export function useCategory(batchNotificationInjection) {

    /** Notification provider has to be inject here, which will most likely be run at setup() function of a Component. (inject() can only be used in setup()) */
    const notificationInjection = batchNotificationInjection ?? useNotifications().injectNotifications()
    
    /**
     * Converts the Raw response from the server into a entity object
     * @param {import("../../../types/entity").CategoryRaw} raw Raw response from the backend.
     * @returns {import("../../../types/entity").Category}
     */
    const serialize = ({ category_id, ...rest }) => ({
        ...rest,
        id: category_id
    })

    /**
     * Converts the entity to a object to be sent to the server
     * @param {Partial<import("../../../types/entity").Category>} entity Entity
     * @returns {Partial<import("../../../types/entity").CategoryRaw>}
     */
    const deserialize = ({ id, ...rest }) => ({
        ...rest,
        category_id: id
    })

    /**
     * Get categories (Paginated) (30 Per page)
     * @param {number} pageNum
     * @returns {Promise<[import("../../../types/entity").Category[], number]>} [Customers, Record Count]
     */
    const getCategories = async (pageNum) => {
        return new Promise((resolve, reject) => {
            const success = ref(false);
            const { data, isFinished } = useAuthorizedFetch(`/category?page-num=${pageNum}`, 'Get Categories', success, notificationInjection).json().get();

            whenever(logicAnd(isFinished, success), () => {
                const raw = /** @type {import("../../../types/entity").CategoryRaw[] }*/(toValue(data).categories)
                const count = /** @type {number} */ (toValue(data).record_count)
                resolve([raw.map(serialize), count]);
            })
            whenever(logicAnd(isFinished, logicNot(success)), () => reject())
        })
    }

    /**
     * @typedef SearchCategoriesOptions
     * @property {string} [name]
     */

    /**
     * Get customers matching the given query
     * @param {SearchCategoriesOptions} options
     * @returns {Promise<import("../../../types/entity").Category[]>}
     */
    const searchCategories = async ({ name }) => {
        return new Promise((resolve, reject) => {
            const query = "?" + [
                name ? `category-name=${name}` : undefined
            ].filter(x => x).join("&")

            const success = ref(false);
            const { data, isFinished } = useAuthorizedFetch(`/category${query}`, 'Search Categories', success, notificationInjection).json().get();

            whenever(logicAnd(isFinished, success), () => {
                const raw = /** @type {import("../../../types/entity").CategoryRaw[] }*/(toValue(data).categories)
                resolve(raw.map(serialize));
            })
            whenever(logicAnd(isFinished, logicNot(success)), () => reject())
        })
    }

    return { getCategories, searchCategories }
}