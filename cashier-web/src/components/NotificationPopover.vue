<template>
    <el-popover :width="400" :trigger="'click'"
        popper-style="box-shadow: rgb(14 18 22 / 35%) 0px 10px 38px -10px, rgb(14 18 22 / 20%) 0px 10px 20px -15px; padding: 0; border-radius: 10px; max-height: 500px">
        <template #reference>
            <slot name="reference" :count="notificationComponents.length"></slot>
        </template>

        <template #default>
            <div class="p-4">
                <div class="overflow-x-auto">
                    <h3 class="text-2xl font-medium">Notifications</h3>
                    <table class="table table-xs">
                        <tbody>
                            <div class="w-full my-3 flex flex-col gap-3">
                                <slot name="pinned"></slot>
                            </div>
                            <template v-if="notificationComponents.length > 0">
                                <tr class="notification-row" v-for="(notification, index) in notificationComponents" :key="index">
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
import { ElPopover } from 'element-plus'
import { ref, type FunctionalComponent, useSlots } from 'vue';

defineProps<{
    notificationComponents: FunctionalComponent[]
}>()

const { pinned } = useSlots()
</script>

<style>
.notification-row:last-of-type tr {
    @apply border-none;
}
</style>