<template>
    <Drawer>
        <template #drawer-content>
            <DrawerMenu/>
        </template>

        <h2>Welcome to Accounting!</h2>
        <router-link :to="{ name: 'GeneralLedger' }">
            <a href="">Goto General Ledger</a>
        </router-link>
    </Drawer>
</template>

<script setup>
import Drawer from '../components/Drawer.vue';
import DrawerMenu from '../components/DrawerMenu.vue';

</script>

<style scoped>

</style>