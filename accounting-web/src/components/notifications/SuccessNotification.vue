<template>
    <div class="alert alert-success shadow-lg w-[500px] flex flex-row flex-nowrap">
        <div class="grow flex justify-start items-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6 mr-2" fill="none" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <div>
                <h3 class="font-bold">Success: {{ origin }}</h3>
                <div class="text-xs">{{ statusText }} ({{ status }})</div>
            </div>
        </div>
        <svg @click="$emit('close')" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
            stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
        </svg>
    </div>
</template>

<script setup>
// @ts-check
defineProps(['origin', 'status', 'statusText'])
defineEmits(['close'])
</script>

<style></style>