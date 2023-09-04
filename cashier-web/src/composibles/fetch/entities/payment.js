// @ts-check
import { useAuthorizedFetch } from "../authorized-fetch"
import { whenever } from "@vueuse/core";
import { ref, toValue } from "vue";
import { logicAnd, logicNot } from "@vueuse/math";
import { useNotifications } from "../../notification";

/**
 * @param {import("../../../types/util").NotificationInjection} [batchNotificationInjection]
 */
export function usePayment(batchNotificationInjection) {

    /** Notification provider has to be inject here, which will most likely be run at setup() function of a Component. (inject() can only be used in setup()) */
    const notificationInjection = batchNotificationInjection ?? useNotifications().injectNotifications()
    
    // /**
    //  * Converts the Raw response from the server into a entity object
    //  * @param {import("../../../types/entity").PaymentRaw} raw Raw response from the backend.
    //  * @returns {import("../../../types/entity").Payment}
    //  */
    // const serialize = ({ item_id, price, ...rest }) => ({
    //     ...rest,
    //     id: item_id,
    //     price: new Decimal(price)
    // })

    /**
     * Converts the entity to a object to be sent to the server
     * @param {import("../../../types/entity").AddPaymentOptions} entity Entity
     * @returns {import("../../../types/entity").AddPaymentRequestData}
     */
    const deserializeAddOptions = ({ orderId, paidAmount, paidDate }) => ({
        "order-id": orderId,
        "paid-amount": paidAmount.toString(),
        "paid-date": paidDate.toLocaleDateString('en-CA')
    })

    /**
     * Adds an payment
     * @param {import("../../../types/entity").AddPaymentOptions} options
     * @returns {Promise<number>} Return the added payment ID
     */
    const addPayment = async (options) => {
        return new Promise((resolve, reject) => {
            const success = ref(false);
            const { isFinished, data } = useAuthorizedFetch('/payments/add', 'Add New Payment', success, notificationInjection, true).json().post(deserializeAddOptions(options));

            whenever(logicAnd(isFinished, success), () => resolve(/** @type {number}*/(toValue(data)["order-id"])))
            whenever(logicAnd(isFinished, logicNot(success)), () => reject())
        })
    }

    /**
     * Updates an payment
     * @param {number} id
     * @param {boolean} refunded
     * @returns {Promise<void>}
     */
    const updatePayment = async (id, refunded) => {
        return new Promise((resolve, reject) => {
            const success = ref(false);
            const { isFinished, data } = useAuthorizedFetch('/payments/update', 'Update Payment', success, notificationInjection, true).json().post({
                "payment-id": id,
                "refunded": refunded
            });

            whenever(logicAnd(isFinished, success), () => resolve())
            whenever(logicAnd(isFinished, logicNot(success)), () => reject())
        })
    }

    /**
     * Removes an payment
     * @param {number} id
     * @returns {Promise<void>}
     */
    const removePayment = async (id) => {
        return new Promise((resolve, reject) => {
            const success = ref(false);
            const { isFinished, data } = useAuthorizedFetch('/payments/delete', 'Remove Payment', success, notificationInjection, true).json().post({ "payment-id": id });

            whenever(logicAnd(isFinished, success), () => resolve())
            whenever(logicAnd(isFinished, logicNot(success)), () => reject())
        })
    }


    return { addPayment, updatePayment, removePayment }
}