import { ref } from 'vue';

const NOTIFICATION_TIMEOUT = 7000;

const notifications = ref([])

let count = 0;

const pushNotification = (notif) => notifications.value = [...notifications.value, notif];

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

const notificationStore = {
    notifications,
    pushSuccessNotification,
    pushErrorNotification,
    removeNotification,
};

export default notificationStore;