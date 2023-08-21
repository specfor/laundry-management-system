<template>
    <el-popover :width="500" popper-style="box-shadow: rgb(14 18 22 / 35%) 0px 10px 38px -10px, rgb(14 18 22 / 20%) 0px 10px 20px -15px; padding: 0; border-radius: 10px;">
        <template #reference>
            <el-badge :value="notifications.length" :max="99" class="item">
                <svg id="notification-icon" ref="bellIcon"
                    class="w-6 h-6 lg:w-7 lg:h-7 text-gray-800 dark:text-white fill-blue-500 transition-all duration-200 hover:scale-110"
                    aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 14 20">
                    <path
                        d="M12.133 10.632v-1.8A5.406 5.406 0 0 0 7.979 3.57.946.946 0 0 0 8 3.464V1.1a1 1 0 0 0-2 0v2.364a.946.946 0 0 0 .021.106 5.406 5.406 0 0 0-4.154 5.262v1.8C1.867 13.018 0 13.614 0 14.807 0 15.4 0 16 .538 16h12.924C14 16 14 15.4 14 14.807c0-1.193-1.867-1.789-1.867-4.175ZM3.823 17a3.453 3.453 0 0 0 6.354 0H3.823Z" />
                </svg>
            </el-badge>
        </template>

        <template #default>
            <div class="p-2">
                <div class="overflow-x-auto">
                    <h3 class="text-2xl m-2 font-medium">Notifications</h3>
                    <table class="table table-xs">
                        <tbody>
                            <!-- row 1 -->
                            <tr v-if="notifications.length > 0" v-for="(notification, index) in notifications" :key="index">
                                <td>
                                    <svg v-if="notification.type == 'success'" xmlns="http://www.w3.org/2000/svg"
                                        class="shrink-0 h-6 w-6 stroke-success" fill="none" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <svg v-else xmlns="http://www.w3.org/2000/svg" class="shrink-0 h-6 w-6 stroke-error"
                                        fill="none" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </td>
                                <td class="text-sm text-ellipsis">
                                    <span class="font-semibold">{{ notification.props.origin }} ({{notification.props.status }}) </span>
                                    <span class="block text-sm">{{ notification.props.statusText }}</span>
                                </td>
                                <UseTimeAgo v-slot="{ timeAgo }" :time="notification.createdAt">
                                    <td class="text-slate-500">{{ timeAgo }}</td>
                                </UseTimeAgo>
                            </tr>
                            <tr v-else class="border-none">
                                <td>
                                    No Notifications
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </template>
    </el-popover>
</template>

<script setup>
// @ts-check
import { UseTimeAgo } from '@vueuse/components';
import { useAnimate } from '@vueuse/core';
import { ElPopover, ElBadge } from 'element-plus'
import { ref } from 'vue';

defineProps({
    notifications: {
        required: true,
        /** @type { import('vue').PropType<Array<{ type:"error"|"success", props: import('../../types').ShowNotificationOptions, createdAt: Date }>> } */
        type: Array
    }
})

const bellIcon = /** @type {import('vue').Ref<HTMLInputElement | null>} */ (ref(null));

const keyframes = [
    { transform: 'rotate(0deg)' },
    { transform: 'rotate(-45deg) scale(1.6)' },
    { transform: 'rotate(0deg)' },
]

const { play } = useAnimate(bellIcon, keyframes, {
    duration: 300,
    iterations: 1,
    immediate: false
})

defineExpose({
    animateBell: () => play()
})
</script>

<style></style>