<template>
    <Suspense>
        <GeneralLedgerTable :ids="ids"></GeneralLedgerTable>
        <template #fallback>
            <div class="fixed top-1/2 left-1/2 flex flex-row justify-center">
                <span class="loading loading-spinner loading-lg"></span>
            </div>
        </template>
    </Suspense>
</template>

<script setup lang="ts">
import { useRouteQuery } from '@vueuse/router';
import GeneralLedgerTable from '../components/GeneralLedgerTable.vue';

const page = useRouteQuery('ids', '0')
const ids = page.value.split(",").map(id => +id);
</script>

<style scoped>
    
</style>