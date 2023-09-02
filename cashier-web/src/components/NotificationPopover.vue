<template>
    <el-popover :width="500"
        popper-style="box-shadow: rgb(14 18 22 / 35%) 0px 10px 38px -10px, rgb(14 18 22 / 20%) 0px 10px 20px -15px; padding: 0; border-radius: 10px;">
        <template #reference>
            <el-badge :value="notificationComponents.length" :max="99" class="item">
                <button class="btn btn-square btn-primary">
                    <icon-mdi-bell-outline ref="bellIcon" id="notification-icon"
                        class="w-6 h-6 lg:w-7 lg:h-7 text-gray-800 dark:text-white fill-blue-500 transition-all duration-200 hover:scale-110"></icon-mdi-bell-outline>
                </button>
            </el-badge>
        </template>

        <template #default>
            <div class="p-2">
                <div class="overflow-x-auto">
                    <h3 class="text-2xl m-2 font-medium">Notifications</h3>
                    <table class="table table-xs">
                        <tbody>
                            <template v-if="notificationComponents.length > 0">
                                <tr v-for="(notification, index) in notificationComponents" :key="index">
                                    <component :is="notification"></component>
                                </tr>
                            </template>
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

<script setup lang="ts">
import { useAnimate } from '@vueuse/core';
import { ElPopover, ElBadge } from 'element-plus'
import { ref, type FunctionalComponent } from 'vue';

defineProps<{
    notificationComponents: FunctionalComponent[]
}>()

const bellIcon = ref<HTMLInputElement | null>(null);

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