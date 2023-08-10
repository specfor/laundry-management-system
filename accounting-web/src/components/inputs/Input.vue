<template>
    <div class="form-control w-full max-w-xs">
        <label class="label">
            <span class="label-text">{{ label }}</span>
        </label>
        <input v-model="value" :type="type" placeholder="Type here" class="input input-bordered w-full max-w-xs" />
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
    placeholder: {
        type: String,
        default: ""
    },
    type: {
        type: String,
        default: "text"
    },
    value: {
        type: [Number, String],
        
        /**
         * @param {{type: string}} props 
         */
        default(props) {
            return props.type == 'text' ? '' : 0;
        }
    },
})

const emit = defineEmits(['update:value']);

const value = useVModel(props, 'value', emit);
</script>

<style>
    
</style>