<script setup>
// @ts-check
import Drawer from './components/Drawer.vue';
import DrawerMenu from './components/DrawerMenu.vue';
import NotificationPopover from './components/notifications/NotificationPopover.vue'
import { ref, Teleport, Transition } from "vue";
import { useFetch, whenever } from "@vueuse/core";
import { usePersistantState } from "./composibles/persistant-state";
import { logicAnd, logicNot } from '@vueuse/math/index.cjs';
import { useSetupNotification } from './composibles/notification-setup';
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
  console.log("Login Error");
})

// NOTIFICATIONS
const notificationPopoverElement = /** @type {import('vue').Ref<InstanceType<typeof NotificationPopover> | null>} */ (ref(null));

const { notifications, currentlyShownNotification, isNotificationShown, hideNotification } = useSetupNotification({
  onShowError: (ctx) => {
    notificationPopoverElement.value?.animateBell()
  },
  onShowSuccess: (ctx) => {
    notificationPopoverElement.value?.animateBell()
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

.notification-slide-leave-to,
.notification-slide-enter-from {
  opacity: 0;
  transform: translateY(16rem) scale(0.8);
}
</style>
