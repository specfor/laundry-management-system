<template>
  <div class="h-screen bg-gray-500 w-[200px] text-lg font-semibold fixed">
    <RouterLink to="/dashboard" class="block w-full border-2 px-3 py-2 border-slate-700 hover:border-slate-400"
                active-class="text-slate-200">Dashboard</RouterLink>
    <RouterLink to="/orders" class="block w-full border-2 px-3 py-2 border-slate-700 hover:border-slate-400"
                active-class="text-slate-200">Orders</RouterLink>
    <RouterLink to="/payments" class="block w-full border-2 px-3 py-2 border-slate-700 hover:border-slate-400"
                active-class="text-slate-200">Payments</RouterLink>
    <RouterLink to="/customers" class="block w-full border-2 px-3 py-2 border-slate-700 hover:border-slate-400"
                active-class="text-slate-200">Customers</RouterLink>
    <RouterLink to="/products" class="block w-full border-2 px-3 py-2 border-slate-700 hover:border-slate-400"
                active-class="text-slate-200">Products</RouterLink>
    <RouterLink to="/branches" class="block w-full border-2 px-3 py-2 border-slate-700 hover:border-slate-400"
                active-class="text-slate-200">Branches</RouterLink>
    <RouterLink to="/employees" class="block w-full border-2 px-3 py-2 border-slate-700 hover:border-slate-400"
                active-class="text-slate-200">Employees</RouterLink>
    <RouterLink to="/users" class="block w-full border-2 px-3 py-2 border-slate-700 hover:border-slate-400"
                active-class="text-slate-200">Users</RouterLink>
  </div>
</template>

<script setup>

</script>

<style scoped>

</style>