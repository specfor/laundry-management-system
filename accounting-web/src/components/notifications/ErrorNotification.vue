<template>
    <div class="alert alert-error shadow-lg w-[500px] lg:w-[70px]">
        <svg xmlns="http://www.w3.org/2000/svg" class="shrink-0 h-9 w-9 stroke-current " fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
        <div>
            <h3 class="font-bold">Error occured on {{ origin }}</h3>
            <div class="text-xs">{{ statusText }} ({{ status }})</div>
        </div>
        <svg @click="$emit('close')" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 cursor-pointer" fill="none" viewBox="0 0 24 24" stroke="currentColor">
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