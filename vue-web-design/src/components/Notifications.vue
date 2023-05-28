<template>
  <div class="fixed right-4 top-4 z-30">
    <div class="space-y-3 items-end flex flex-col">
      <TransitionGroup name="notifications">
        <div v-for="notification in notifications" :key="notification['id']">
          <div v-if="notification['value'][0] === 'success'"
               class="flex p-2 bg-green-400/90 rounded-lg space-x-5 w-max">
            <CheckCircleIcon class="fill-green-800 w-[50px] h-[50px]"/>
            <div>
              <div class="text-xl font-semibold text-green-900">{{ notification['value'][1] }}</div>
              <div class="text-sm text-green-900">{{ notification['value'][2] }}</div>
            </div>
            <XMarkIcon
                class="stroke-green-800 stroke-2 w-[25px] h-[25px] hover:border-2 hover:border-green-600 mt-1 justify-self-end"
                @click="removeNotification(notification['id'])"/>
          </div>
          <div v-else-if="notification['value'][0] === 'error'"
               class="flex p-2 bg-rose-400/90 rounded-lg space-x-5 w-max">
            <ExclamationCircleIcon class="fill-rose-800 w-[50px] h-[50px]"/>
            <div>
              <div class="text-xl font-semibold text-rose-900">{{ notification['value'][1] }}</div>
              <div class="text-sm text-rose-900">{{ notification['value'][2] }}</div>
            </div>
            <XMarkIcon
                class="stroke-rose-800 stroke-2 w-[25px] h-[25px] hover:border-2 hover:border-rose-600 mt-1 justify-self-end"
                @click="removeNotification(notification['id'])"/>
          </div>
        </div>
      </TransitionGroup>
    </div>
  </div>
</template>

<script setup>
import {defineProps, ref} from "vue";
import {CheckCircleIcon, XMarkIcon, ExclamationCircleIcon} from '@heroicons/vue/24/solid';

let {isOpen, title} = defineProps(['isOpen', 'title'])
let notifications = ref([])
let notificationId = 1

function removeNotification(id) {
  notifications.value = notifications.value.filter((notification) => {
    return notification['id'] !== parseInt(id)
  })
}

window.successNotification = (title, message) => {
  notifications.value.push({id: notificationId, value: ['success', title, message]})
  setTimeout(removeNotification, 7000, notificationId)
  notificationId++
}

window.errorNotification = (title, message) => {
  notifications.value.push({id: notificationId, value: ['error', title, message]})
  setTimeout(removeNotification, 7000, notificationId)
  notificationId++
}

</script>

<style scoped>
.notifications-enter-active,
.notifications-move {
  @apply transition;
  @apply ease-out;
  @apply duration-500;
}

.notifications-leave-active {
  @apply transition;
  @apply absolute;
  @apply ease-in;
  @apply duration-500;
}

.notifications-enter-from,
.notifications-leave-to {
  @apply opacity-0;
  @apply translate-x-[100px];
}

.notifications-enter-to,
.notifications-leave-from {
  @apply opacity-100;
}
</style>