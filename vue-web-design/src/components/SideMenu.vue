<template>
  <div class="h-screen bg-gray-500 w-[200px] text-lg font-semibold fixed pl-1">
    <RouterLink to="/dashboard" class="flex w-full px-2 py-1 rounded-l-lg hover:bg-slate-700 items-center"
                active-class="active-side-menu-element">
      <HomeIcon class="w-6 h-6 mr-3"/>
      Dashboard
    </RouterLink>
    <RouterLink to="/orders" class="flex w-full px-2 py-1 rounded-l-lg hover:bg-slate-700 items-center"
                active-class="active-side-menu-element">
      <RectangleStackIcon class="w-6 h-6 mr-3"/>
      Orders
    </RouterLink>
    <RouterLink to="/payments" class="flex w-full px-2 py-1 rounded-l-lg hover:bg-slate-700 items-center"
                active-class="active-side-menu-element">
      <CreditCardIcon class="w-6 h-6 mr-3"/>
      Payments
    </RouterLink>
    <RouterLink to="/customers" class="flex w-full px-2 py-1 rounded-l-lg hover:bg-slate-700 items-center"
                active-class="active-side-menu-element">
      <UserGroupIcon class="w-6 h-6 mr-3"/>
      Customers
    </RouterLink>
    <RouterLink to="/products" class="flex w-full px-2 py-1 rounded-l-lg hover:bg-slate-700 items-center"
                active-class="active-side-menu-element">
      <ArchiveBoxIcon class="w-6 h-6 mr-3"/>
      Products
    </RouterLink>
    <RouterLink to="/branches" class="flex w-full px-2 py-1 rounded-l-lg hover:bg-slate-700 items-center"
                active-class="active-side-menu-element">
      <BuildingOffice2Icon class="w-6 h-6 mr-3"/>
      Branches
    </RouterLink>
    <RouterLink to="/employees" class="flex w-full px-2 py-1 rounded-l-lg hover:bg-slate-700 items-center"
                active-class="active-side-menu-element">
      <IdentificationIcon class="w-6 h-6 mr-3"/>
      Employees
    </RouterLink>
    <RouterLink to="/users" class="flex w-full px-2 py-1 rounded-l-lg hover:bg-slate-700 items-center"
                active-class="active-side-menu-element">
      <UsersIcon class="w-6 h-6 mr-3"/>
      Users
    </RouterLink>
  </div>
</template>

<script setup>
import {
  HomeIcon,
  RectangleStackIcon,
  CreditCardIcon,
  ArchiveBoxIcon,
  UsersIcon,
  BuildingOffice2Icon,
  UserGroupIcon,
  IdentificationIcon
} from "@heroicons/vue/24/solid/index.js";
</script>

<style scoped>
.active-side-menu-element {
  @apply bg-white;
}
</style>