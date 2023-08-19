// @ts-check
import { ref, shallowRef } from 'vue';
import { useNotifications } from './notification'
import { useConfirmDialog, watchThrottled } from '@vueuse/core';


// Just IDE Complaining (https://github.com/vuejs/vue-cli/issues/1198)
// @ts-ignore
import ErrorNotification from '../components/notifications/ErrorNotification.vue'
// @ts-ignore
import SuccessNotification from '../components/notifications/SuccessNotification.vue'

/**
 * Setups the main notification system
 * @param {{ onShowError: (ctx: import('../types').ShowNotificationOptions) => void, onShowSuccess: (ctx: import('../types').ShowNotificationOptions) => void }} options
 */
export const useSetupNotification = ({ onShowError, onShowSuccess }) => {
    const { isRevealed: isNotificationShown, reveal: showNotification, cancel: hideNotification } = useConfirmDialog()

    const { provideNotifications } = useNotifications();

    const notifications = ref( /** @type { { type:"error"|"success", props: import('../types').ShowNotificationOptions, createdAt: Date }[] }  */([]))

    const currentlyShownNotification = /** @type { import('vue').Ref<{component: import('vue').ShallowRef<any>, props: import('../types').ShowNotificationOptions} | null> } */ (ref(null))

    watchThrottled(
        notifications,
        () => {
            if (isNotificationShown) hideNotification()

            currentlyShownNotification.value = {
                component: notifications.value[0].type == "error" ? shallowRef(ErrorNotification) : shallowRef(SuccessNotification),
                props: notifications.value[0].props
            }

            showNotification()
            setTimeout(hideNotification, 4000);
        },
        { throttle: 5000 },
    )

    provideNotifications({
        showError: (options) => {
            onShowError(options)

            notifications.value = [{
                createdAt: new Date(Date.now()),
                type: "error",
                props: options
            }, ...notifications.value]
        },
        showSuccess: (options) => {
            onShowSuccess(options)

            notifications.value = [{
                createdAt: new Date(Date.now()),
                type: "success",
                props: options
            }, ...notifications.value]
        }
    })

    return { notifications, currentlyShownNotification, isNotificationShown, showNotification, hideNotification }
}