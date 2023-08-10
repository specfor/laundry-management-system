<template>
    <div class="form-control w-full max-w-xs">
        <label class="label">
            <span class="label-text">{{ label }}</span>
        </label>
        <select v-model="value" class="select select-bordered">
            <option disabled selected value="">{{ placeholder }}</option>
            <option v-for="(item, index) in options" :key="index" :value="item.value">{{ item.text }}</option>
        </select>
    </div>
</template>

<script setup>
// @ts-check
import { useVModel } from '@vueuse/core';

const props = defineProps({
    label: {
        type: String,
        required: true
    },
    options: {
        /** @type {import('vue').PropType<{ value: string; text: string; }[]>} */
        type: Array,
        required: true
    },
    placeholder: {
        type: String,
        default: ""
    },
    value: {
        type: String,
        default: ""
    },
})

const emit = defineEmits(['update:value']);

const value = useVModel(props, 'value', emit);
</script>

<style></style>