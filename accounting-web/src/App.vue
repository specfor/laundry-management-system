<script setup>
// @ts-check
import Drawer from './components/Drawer.vue';
import DrawerMenu from './components/DrawerMenu.vue';
import NotificationPopover from './components/notifications/NotificationPopover.vue'
import ErrorNotification from './components/notifications/ErrorNotification.vue'
import SuccessNotification from './components/notifications/SuccessNotification.vue'
import { ref, shallowRef, Teleport, Transition, toValue } from "vue";
import { useConfirmDialog, useFetch, watchThrottled, whenever } from "@vueuse/core";
import { usePersistantState } from "./composibles/persistant-state";
import { useNotifications } from './composibles/notification';
import { logicAnd, logicNot } from '@vueuse/math/index.cjs';
const drawerOpen = ref(false);

// Attempt login
const { data, isFinished } = useFetch('http://laundry-api.localhost/api/v1/login').json().post({
  username: "admin_{342365(_)08",
  password: "rlsjp6)rg_34_)(23as"
})

whenever(logicAnd(isFinished, data), () => {
  const state = usePersistantState();
  state.value = {
    ...state.value,
    isLoggedIn: true,
    token: data.value.body.token,
  }
})

whenever(logicAnd(isFinished, logicNot(data)), () => {
  notificationPopoverElement.value?.animateBell()

  notifications.value = [{
    createdAt: new Date(Date.now()),
    type: "error",
    props: {
      origin: "Auto Login",
      status: 0,
      statusText: "Login Failed"
    }
  }, ...notifications.value]

  showNotification()
  setTimeout(hideNotification, 4000);
})

// NOTIFICATIONS
const { isRevealed: isNotificationShown, reveal: showNotification, cancel: hideNotification } = useConfirmDialog()

const { provideNotifications } = useNotifications()

const notifications = ref( /** @type { { type:"error"|"success", props: import('./types').ShowNotificationOptions, createdAt: Date }[] }  */([]))

const currentlyShownNotification = /** @type { import('vue').Ref<{component: import('vue').ShallowRef<any>, props: import('./types').ShowNotificationOptions} | null> } */ (ref(null))

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

const notificationPopoverElement = /** @type {import('vue').Ref<InstanceType<typeof NotificationPopover> | null>} */ (ref(null));

provideNotifications({
  showError: (options) => {
    notificationPopoverElement.value?.animateBell()

    notifications.value = [{
      createdAt: new Date(Date.now()),
      type: "error",
      props: options
    }, ...notifications.value]
  },
  showSuccess: (options) => {
    notificationPopoverElement.value?.animateBell()

    notifications.value = [{
      createdAt: new Date(Date.now()),
      type: "success",
      props: options
    }, ...notifications.value]
  }
})
</script>

<template>
  <Drawer v-model:open="drawerOpen">
    <template #drawer-content>
      <DrawerMenu @onMenuItemClick="drawerOpen = false" />
    </template>
    <div class="lg:mt-0 mt-[50px] p-5 font-default">
      <router-view></router-view>
    </div>
  </Drawer>

  <Teleport to="body">
    <!-- For Showing Notifications -->
    <div class="fixed w-full bottom-6">
      <Transition name="notification-slide" mode="in-out">
        <div class="flex justify-center" v-if="isNotificationShown && currentlyShownNotification">
          <!-- @vue-ignore -->
          <component :is="currentlyShownNotification.component" v-bind="currentlyShownNotification.props" @close="hideNotification">
          </component>
        </div>
      </Transition>
    </div>

    <!-- Notification List -->
    <div class="absolute top-3 right-7">
      <NotificationPopover :notifications="notifications" ref="notificationPopoverElement"></NotificationPopover>
    </div>
  </Teleport>
</template>

<style scoped>
.notification-slide-enter-active,
.notification-slide-leave-active {
  transition: opacity 0.2s ease, transform 0.2s ease;
}

.notification-slide-enter-from {
  opacity: 0;
  transform: translateY(16rem);
}

.notification-slide-leave-to {
  opacity: 0;
  transform: translateY(16rem);
}
</style>
