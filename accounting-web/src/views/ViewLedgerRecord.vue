<template>
    <ol>
        <li v-for="(id, index) in ids" :key="index">{{ id }}</li>
    </ol>
</template>

<script setup lang="ts">
import { useRouteQuery } from '@vueuse/router';

const page = useRouteQuery('ids', '0')
const ids = page.value.split(",").map(id => +id);
</script>

<style scoped>
    
</style>