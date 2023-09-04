<template>
    <el-popover :width="width" :trigger="'click'"
        popper-style="box-shadow: rgb(14 18 22 / 35%) 0px 10px 38px -10px, rgb(14 18 22 / 20%) 0px 10px 20px -15px; padding: 0; border-radius: 10px; max-height: 500px">
        <template #reference>
            <slot name="reference"></slot>
        </template>

        <template #default>
            <div class="p-3">
                <div class="overflow-x-auto">
                    <h3 class="text-2xl m-2 font-medium">{{ header }}</h3>
                    <slot name="default"></slot>
                </div>
            </div>
        </template>
    </el-popover>
</template>

<script setup lang="ts">
import { ElPopover } from 'element-plus'

defineProps<{
    width: number,
    header: string
}>()
</script>

<style></style>