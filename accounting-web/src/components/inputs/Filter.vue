<template>
    <button class="btn btn-square" @click="open = !open">
        <svg width="25px" height="25px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path
                d="M20.17 3.91C20.1062 3.78712 20.0101 3.68399 19.8921 3.61173C19.774 3.53947 19.6384 3.50084 19.5 3.5H4.5C4.36157 3.50084 4.226 3.53947 4.10792 3.61173C3.98984 3.68399 3.89375 3.78712 3.83 3.91C3.76636 4.03323 3.73915 4.17204 3.75155 4.31018C3.76395 4.44832 3.81544 4.58007 3.9 4.69L9.25 12V19.75C9.25259 19.9481 9.33244 20.1374 9.47253 20.2775C9.61263 20.4176 9.80189 20.4974 10 20.5H14C14.1981 20.4974 14.3874 20.4176 14.5275 20.2775C14.6676 20.1374 14.7474 19.9481 14.75 19.75V12L20.1 4.69C20.1846 4.58007 20.236 4.44832 20.2484 4.31018C20.2608 4.17204 20.2336 4.03323 20.17 3.91Z"
                fill="#000000" />
        </svg>
    </button>
    <div v-on-click-outside="close" :class="{ 'hidden': !open }" class="mt-2 absolute px-2 py-3 shadow-sm shadow-gray-800 rounded-md">
        <slot name="filter-controls" :values="valuesForChildren"></slot>
        <div class="flex justify-around gap-2 mt-4">
            <button class="btn btn-primary btn-sm" @click="closeAndEmit(true)">Filter</button>
            <button class="btn btn-sm" @click="closeAndEmit(false)">Clear</button>
        </div>
    </div>
</template>

<script setup>
// @ts-check
import { ref, toValue, onUnmounted } from "vue";
import { syncRef } from '@vueuse/core';
import { vOnClickOutside } from '@vueuse/components'

const open = ref(false);

/**
 * @typedef {boolean|number|string|{ min: number, max: number }} FilterValue
 * @type {import("vue").Ref<Object.<string, any>>}
 */
const values = ref({});

// A copy of the above ref to be given to the child elements
const valuesForChildren = ref(toValue(values));

// Keep the above 2 refs in sync with each other
// https://vueuse.org/shared/syncRef/
const stop = syncRef(values, valuesForChildren, { direction: 'both', deep: true});

// Stop syncing above 2 refs when this component is destroyed
onUnmounted(() => stop())

/**
 * # Quick sidenote on why the above shenanigans
 * It is not possible to use v-model on an <slot/> element.
 */

const emit = defineEmits(['onFilterClick', 'onClearClick'])

/**
 * Will close the popup and emit either onFilterClick or onClearClick
 * @param {boolean} filtred True if the Filter Button was clicked, False if the Clear button was clicked
 */
const closeAndEmit = (filtred) => {
    if (filtred) {
        emit('onFilterClick')
    } else {
        emit('onClearClick')
        // Clear the values
        values.value = {};
    }
    
    // Close the popup
    open.value = false;
}

// Just closes the popup
const close = () => open.value = false;
</script>

<style scoped lang="scss"></style>