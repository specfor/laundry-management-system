<template>
    <div class="h-[10%] w-full shadow-md flex flex-row">
        <div class="grow p-2 flex flex-row gap-3">
            <RouterLink :to="{ name: 'NewOrder' }" #default="{ isExactActive }">
                <button class="btn-sm text-base rounded-sm"
                    :class="{ 'btn-neutral': isExactActive, 'btn-ghost': !isExactActive }">New Order</button>
            </RouterLink>
            <RouterLink :to="{ name: 'ViewOrder' }" #default="{ isExactActive }">
                <button class="btn-sm text-base rounded-sm"
                    :class="{ 'btn-neutral': isExactActive, 'btn-ghost': !isExactActive }">{{ t('view-order') }}</button>
            </RouterLink>
        </div>
        <div class="flex flex-row justify-center items-center gap-3 mr-3">
            <div>
                <NotificationPopover :notification-components="notifications">
                    <template #reference="{ count }">
                        <div class="flex flex-row justify-center gap-3">
                            <TransitionGroup name="notification-icons">
                                <div class="badge badge-error gap-2 p-3 font-medium" key="offline" v-if="!isOnline">
                                    <icon-mdi-connection class="w-5 h-5 lg:w-7 lg:h-7 text-warning-content" />
                                    Offline Mode
                                </div>
                                <ElBadge :value="count" :max="99" class="item" key="icon">
                                    <icon-mdi-bell
                                        class="w-6 h-6 lg:w-7 lg:h-7 text-base-content transition-all duration-200 hover:scale-110" />
                                </ElBadge>
                            </TransitionGroup>
                        </div>
                    </template>
                    <template #pinned>
                        <div class="alert alert-error p-2" v-if="!isOnline">
                            <icon-mdi-connection class="w-5 h-5 lg:w-7 lg:h-7 text-warning-content" />
                            <span>{{ t('no-internet-notification') }}</span>
                        </div>
                        <div class="w-full">
                            Processing Orders (2/10)
                            <progress class="progress w-full" value="20" max="100"></progress>
                        </div>
                    </template>
                </NotificationPopover>
            </div>
            <Popover :width="300" header="Settings">
                <template #reference>
                    <icon-mdi-cog
                        class="w-7 h-7 lg:w-7 lg:h-7 text-base-content transition-transform duration-150 hover:-rotate-45 focus:border-none" />
                </template>
                <div class="w-full">
                    <div class="form-control w-full max-w-xs">
                        <label class="label p-1">
                            <span class="label-text">{{ t('select-theme') }}</span>
                        </label>
                        <select class="select select-bordered select-sm" v-model="isDark">
                            <option :value="false">Light</option>
                            <option :value="true">Dark</option>
                        </select>
                    </div>
                    <div class="form-control w-full max-w-xs">
                        <label class="label p-1">
                            <span class="label-text">{{ t('language-select') }}</span>
                        </label>
                        <select class="select select-bordered select-sm" v-model="locale">
                            <option value="en">English</option>
                            <option value="si">සිoහල</option>
                        </select>
                    </div>
                </div>
            </Popover>
        </div>
    </div>
    <RouterView></RouterView>
</template>

<script setup lang="ts">
import NotificationPopover from '@/components/NotificationPopover.vue';
import Popover from '@/components/Popover.vue';
import { useDark } from '@/composibles/dark';
import { useOnline } from '@vueuse/core';
import { ElBadge } from 'element-plus';
import { themeChange } from 'theme-change'
import { onMounted, ref, type FunctionalComponent } from 'vue'
import { useI18n } from 'vue-i18n';
import { useNotifications } from "../composibles/notification";
import { createNotificationComponent } from '@/components/functional/create-notification';

const isDark = useDark();
const isOnline = useOnline()

const { t, locale } = useI18n({
    useScope: 'global'
})

onMounted(() => {
    themeChange(false)
})

const notifications = ref([
    createNotificationComponent({
        createdAt: new Date(Date.now()),
        type: "error",
        props: {
            origin: "Test Notification",
            status: 200,
            statusText: "This is a description for a test Notification"
        }
    }),
    createNotificationComponent({
        createdAt: new Date(Date.now()),
        type: "success",
        props: {
            origin: "Test Notification",
            status: 200,
            statusText: "This is a description for a test Notification"
        }
    })
] as FunctionalComponent[]);

const { provideNotifications } = useNotifications()
provideNotifications({
    showError(props) {
        notifications.value = [
            createNotificationComponent({
                createdAt: new Date(Date.now()),
                type: "error",
                props
            }),
            ...notifications.value
        ]
    },
    showSuccess(props) {
        notifications.value = [
            createNotificationComponent({
                createdAt: new Date(Date.now()),
                type: "success",
                props
            }),
            ...notifications.value
        ]
    }
})
</script>

<style scoped>
.notification-icons-move,
.notification-icons-leave-active {
    transition: all 0.3s ease;
}

.notification-icons-enter-active {
    transition: all 0.3s ease;
    transition-delay: 0.3s;
}

.notification-icons-enter-from,
.notification-icons-leave-to {
    opacity: 0;
}
</style>