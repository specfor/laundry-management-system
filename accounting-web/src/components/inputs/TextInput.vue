<template>
    <div class="form-control w-full max-w-xs m-0">
        <label class="label">
            <span class="label-text">{{ label }}</span>
        </label>
        <input type="text" :placeholder="label" class="input input-sm input-bordered w-full max-w-xs"
            :class="{ 'input-error': error }" v-model="value"/>
        <label class="label">
            <span class="label-text-alt"></span>
            <span v-if="error" class="label-text-alt">{{ error }}</span>
        </label>
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
    error: {
        type: String
    },
    modelValue: {
        type: String
    },
})

const emit = defineEmits(['update:modelValue']);

const value = useVModel(props, 'modelValue', emit);
</script>

<style></style>