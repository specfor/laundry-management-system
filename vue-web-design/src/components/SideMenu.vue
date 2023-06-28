<template>
  <div class="h-screen bg-gray-500 w-[200px] text-lg font-semibold fixed pl-3">
    <RouterLink to="/dashboard" class="block w-full px-3 py-2 rounded-l-full hover:bg-slate-700 hover:pl-7"
                active-class="active-side-menu-element">Dashboard
    </RouterLink>
    <RouterLink to="/orders" class="block w-full px-3 py-2 rounded-l-full hover:bg-slate-700 hover:pl-7"
                active-class="active-side-menu-element">Orders
    </RouterLink>
    <RouterLink to="/payments" class="block w-full px-3 py-2 rounded-l-full hover:bg-slate-700 hover:pl-7"
                active-class="active-side-menu-element">Payments
    </RouterLink>
    <RouterLink to="/customers" class="block w-full px-3 py-2 rounded-l-full hover:bg-slate-700 hover:pl-7"
                active-class="active-side-menu-element">Customers
    </RouterLink>
    <RouterLink to="/products" class="block w-full px-3 py-2 rounded-l-full hover:bg-slate-700 hover:pl-7"
                active-class="active-side-menu-element">Products
    </RouterLink>
    <RouterLink to="/branches" class="block w-full px-3 py-2 rounded-l-full hover:bg-slate-700 hover:pl-7"
                active-class="active-side-menu-element">Branches
    </RouterLink>
    <RouterLink to="/employees" class="block w-full px-3 py-2 rounded-l-full hover:bg-slate-700 hover:pl-7"
                active-class="active-side-menu-element">Employees
    </RouterLink>
    <RouterLink to="/users" class="block w-full px-3 py-2 rounded-l-full hover:bg-slate-700 hover:pl-7"
                active-class="active-side-menu-element">Users
    </RouterLink>
  </div>
</template>

<script setup>

</script>

<style scoped>
.active-side-menu-element {
  @apply bg-white;
  @apply pl-7;
}
</style>