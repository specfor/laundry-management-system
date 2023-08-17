<template>
    <ElDatePicker
        v-model="value"
        type="daterange"
        unlink-panels
        range-separator="To"
        start-placeholder="Start date"
        end-placeholder="End date"
        :shortcuts="shortcuts"
        size="default"
    />
</template>

<script setup>
// @ts-check
import { ElDatePicker } from "element-plus";
import { ref } from "vue";
import { useDateRangeShortcuts } from "../../composibles/date-range-shortcuts";

const { dateRange } = defineProps({
    dateRange: {
        /** 
         * An Array with 2 Date Objects
         * [0]: Starting Date
         * [1]: Ending Date
         * @type {import('vue').PropType<Date[]>} */
        type: Array,
        default() {
            return [new Date(), new Date()]
        }
    }
})

/** @type {import("vue").Ref<[Date, Date]>} */
const value = ref([dateRange[0], dateRange[1]]);

const shortcuts = useDateRangeShortcuts()
</script>

<style>
    
</style>