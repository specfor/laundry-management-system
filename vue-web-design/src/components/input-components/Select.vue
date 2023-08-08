<template>
    <div class="grid grid-cols-3 mb-1 relative">
        <div class="font-semibold text-slate-700 py-0.5">
            {{ text }}
        </div>
        <select :disabled="disabled"
            class="col-span-2 border-2 border-slate-400 rounded-md hover:border-slate-700 px-3 py-0.5 hover:bg-slate-200 disabled:border"
            :name="name" :value="value" @input="event => $emit('update:value', event.target.value)">
            <option class="" v-for="option in field['options']" :value="option['value']">{{ option['text'] }}
            </option>
        </select>
        <div v-if="errorMessage" class="absolute top-7 right-0 z-[1000] w-2/3 px-2 rounded-lg bg-red-500/80 text-rose-900">
            {{ errorMessage }}
        </div>
    </div>
</template>

<script setup>
const { text, value, disabled, name, options, errorMessage } = defineProps(['text', 'value', 'disabled', 'name', 'options', 'errorMessage']);
</script>
<style></style>