<template>
    <div class="grid grid-cols-3 mb-1 relative">
        <div class="font-semibold text-slate-700 py-0.5">
            {{ text }}
        </div>
        <textarea :name="name" cols="30" rows="3" v-bind="valueRef" @input="$emit('update:value', valueRef)"
            :disabled="disabled" class="col-span-2 border-2 border-slate-400 rounded-md px-3 hover:border-slate-700
                               py-0.5 hover:bg-slate-100 focus:bg-slate-200 disabled:border"></textarea>
        <div v-if="errorMessage" class="absolute top-7 right-0 z-[1000] w-2/3 px-2 rounded-lg bg-red-500/80 text-rose-900">
            {{ errorMessage }}
        </div>
    </div>
</template>

<script setup>
import { ref } from "vue";

const { text, name, errorMessage, value, disabled } = defineProps(['text', 'name', 'errorMessage', 'value', 'disabled']);
const valueRef = ref(value);
</script>