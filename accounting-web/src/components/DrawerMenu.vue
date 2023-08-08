<template>
    <router-link :to="{ name: 'Dashboard' }">
        <li>
            <a>Dashboard</a>
        </li>
    </router-link>
    <router-link :to="{ name: 'GeneralLedger' }">
        <li>
            <a>General Ledger</a>
        </li>
    </router-link>
    <router-link :to="{ name: 'Tax' }">
        <li>
            <a>Tax</a>
        </li>
    </router-link>
    <router-link :to="{ name: 'ChartOfAccounts' }">
        <li>
            <a>Chart of Accounts</a>
        </li>
    </router-link>
</template>

<script>
</script>

<style scoped>
.router-link-exact-active {
    @apply bg-slate-300;
}
</style>