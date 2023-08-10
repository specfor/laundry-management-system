<template>
    <router-link :to="{ name: 'Dashboard' }" @click.native="$emit('onMenuItemClick')">
        <li>
            <a>Dashboard</a>
        </li>
    </router-link>
    <router-link :to="{ name: 'GeneralLedger' }" @click.native="$emit('onMenuItemClick')">
        <li>
            <a>General Ledger</a>
        </li>
    </router-link>
    <router-link :to="{ name: 'Tax' }" @click.native="$emit('onMenuItemClick')">
        <li>
            <a>Tax</a>
        </li>
    </router-link>
    <router-link :to="{ name: 'ChartOfAccounts' }" @click.native="$emit('onMenuItemClick')">
        <li>
            <a>Chart of Accounts</a>
        </li>
    </router-link>
</template>

<script setup>
defineEmits(['onMenuItemClick']);
</script>

<style scoped>
.router-link-exact-active {
    @apply bg-slate-300;
}
</style>