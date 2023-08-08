<template>
    <div class="mt-3 mb-1">
        <div class="font-semibold text-slate-900 py-0.5 pl-4 pr-1 ml-5 bg-slate-300 rounded-l-full">
            {{ text }}
        </div>
    </div>
</template>

<script setup>
const { text } = defineProps(['text']);
</script>