// @ts-check

import { inject, provide } from "vue"
import { NotificationInjectionKey } from "../util/injection-keys"

export const useNotifications = () => {
    
    /**
     * Provides the specified Notification system to the Children of the component
     * @param {import("../types/util").NotificationInjection} options 
     */
    const provideNotifications = (options) => {
        provide(NotificationInjectionKey, options)
    }

    /**
     * Inject the Notification system
     */
    const injectNotifications = () => {
        return inject(NotificationInjectionKey, () => ({
            /**
             * The Default success notification showing function
             * @param {{ origin: string, status: number, statusText: string }} options 
             * @returns 
             */
            showSuccess: ({ origin, status, statusText }) => window.alert(`${origin} Success. ${status} ${statusText}`),
    
            /**
             * The Default error notification showing function
             * @param {{ origin: string, status: number, statusText: string }} options 
             * @returns 
             */
            showError: ({ origin, status, statusText }) => window.alert(`ERROR! Recieved code ${status} while trying to ${origin}. ${statusText}`)
        }), true)
    }

    return { provideNotifications, injectNotifications };
}