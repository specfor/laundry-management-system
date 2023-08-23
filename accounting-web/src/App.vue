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

// Get auth token from local storage
const authToken = localStorage.getItem('auth-token')
if(!authToken) console.error("No Auth token found")
else {
  const state = usePersistantState();
  state.value = {
    ...state.value,
    isLoggedIn: true,
    token: authToken
  }
}

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
    <div class="lg:mt-0 mt-[50px] p-5 font-default w-full">
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
