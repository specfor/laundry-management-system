<template>
    <div class="mt-4 mb-1">
        <div class="font-semibold text-xl py-0.5 pl-4 pr-1 text-center">
            {{ text }}
        </div>
    </div>
</template>

<script setup>
const { text } = defineProps(['text']);
</script>