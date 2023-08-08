<template>
    <div class="grid grid-cols-3 mb-1 relative">
        <div class="font-semibold text-slate-700 py-0.5">
            {{ text }}
        </div>
        <slot></slot>
        <div v-if="errorMessage"
            class="absolute top-7 right-0 z-[1000] w-2/3 px-2 rounded-b-lg bg-red-500/80 text-rose-900">
            {{ errorMessage }}
        </div>
    </div>
</template>

<script setup>
const { text, errorMessage } = defineProps(['text', 'errorMessage']);
</script>