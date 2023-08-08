<template>
  <div class="fixed right-4 top-4 z-30">
    <div class="space-y-3 items-end flex flex-col">
      <TransitionGroup name="notifications">
        <div v-for="notification in notifications" :key="notification['id']">
          <SuccessNotification v-if="notification['value'][0] === 'success'" @click="removeNotification(notification)"
            :notification="notification"></SuccessNotification>
          <ErrorNotification v-else-if="notification['value'][0] === 'error'" @click="removeNotification(notification)"
            :notification="notification"></ErrorNotification>
        </div>
      </TransitionGroup>
    </div>
  </div>
</template>

<script setup>
import { notifications, removeNotification } from '../stores/notification-store'
import ErrorNotification from './notification-components/ErrorNotification.vue';
import SuccessNotification from './notification-components/SuccessNotification.vue';
</script>

<style scoped>
.notifications-enter-active,
.notifications-move {
  @apply transition ease-out duration-500;
}

.notifications-leave-active {
  @apply transition absolute ease-in duration-500;
}

.notifications-enter-from,
.notifications-leave-to {
  @apply opacity-0 translate-x-[100px];
}

.notifications-enter-to,
.notifications-leave-from {
  @apply opacity-100;
}
</style>