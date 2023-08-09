import { ref } from 'vue';
import { createSharedComposable } from '@vueuse/core'

function useNotification() {
    const NOTIFICATION_TIMEOUT = 7000;
    const notifications = ref([])
    let count = 0;

    // Adds the notif object to the notification reference
    const pushNotification = (notif) => notifications.value = [...notifications.value, notif];

    // Removes
    const removeNotification = (notif) => notifications.value = [...notifications.value.filter(x => x != notif)];

    const pushSuccessNotification = (title, message) => {
        const notif = { id: count++, value: ['success', title, message] }
        pushNotification(notif);
        setTimeout(removeNotification, NOTIFICATION_TIMEOUT, notif)
    };

    const pushErrorNotification = (title, message) => {
        const notif = { id: count++, value: ['error', title, message] }
        pushNotification({ value: ['error', title, message] })
        setTimeout(removeNotification, NOTIFICATION_TIMEOUT, notif)
    };
    
    return { pushSuccessNotification, pushErrorNotification, removeNotification, notifications };
}

// Creates a composable where multiple Vue components can use it, but internal state remains the same
// https://vueuse.org/shared/createSharedComposable/
export const useNotification = createSharedComposable(useNotification);