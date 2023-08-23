<template>
  <div class="h-screen bg-gray-500 w-[200px] text-lg font-semibold fixed pl-1">
    <RouterLink to="/dashboard" class="flex w-full px-2 py-1 rounded-l-lg hover:bg-slate-700 items-center"
                active-class="active-side-menu-element">
      <HomeIcon class="w-6 h-6 mr-3"/>
      Dashboard
    </RouterLink>
    <RouterLink to="/orders" class="flex w-full px-2 py-1 rounded-l-lg hover:bg-slate-700 items-center"
                active-class="active-side-menu-element" v-if="ordersShow">
      <RectangleStackIcon class="w-6 h-6 mr-3"/>
      Orders
    </RouterLink>
    <RouterLink to="/payments" class="flex w-full px-2 py-1 rounded-l-lg hover:bg-slate-700 items-center"
                active-class="active-side-menu-element" v-if="paymentsShow">
      <CreditCardIcon class="w-6 h-6 mr-3"/>
      Payments
    </RouterLink>
    <RouterLink to="/customers" class="flex w-full px-2 py-1 rounded-l-lg hover:bg-slate-700 items-center"
                active-class="active-side-menu-element" v-if="customersShow">
      <UserGroupIcon class="w-6 h-6 mr-3"/>
      Customers
    </RouterLink>
    <RouterLink to="/products" class="flex w-full px-2 py-1 rounded-l-lg hover:bg-slate-700 items-center"
                active-class="active-side-menu-element" v-if="productsShow">
      <ArchiveBoxIcon class="w-6 h-6 mr-3"/>
      Products
    </RouterLink>
    <RouterLink to="/branches" class="flex w-full px-2 py-1 rounded-l-lg hover:bg-slate-700 items-center"
                active-class="active-side-menu-element" v-if="branchesShow">
      <BuildingOffice2Icon class="w-6 h-6 mr-3"/>
      Branches
    </RouterLink>
    <RouterLink to="/employees" class="flex w-full px-2 py-1 rounded-l-lg hover:bg-slate-700 items-center"
                active-class="active-side-menu-element" v-if="employeesShow">
      <IdentificationIcon class="w-6 h-6 mr-3"/>
      Employees
    </RouterLink>
    <RouterLink to="/users" class="flex w-full px-2 py-1 rounded-l-lg hover:bg-slate-700 items-center"
                active-class="active-side-menu-element" v-if="usersShow">
      <UsersIcon class="w-6 h-6 mr-3"/>
      Users
    </RouterLink>
    <a class="flex w-full px-2 py-1 rounded-l-lg hover:bg-slate-700 items-center" href="/accounting/"
       target="_blank" rel="noopener noreferrer" v-if="accountingShow">
      <PencilSquareIcon class="w-6 h-6 mr-3"/>
      Accounting</a>
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
  IdentificationIcon,
  PencilSquareIcon
} from "@heroicons/vue/24/solid/index.js";
import {ref} from "vue";

let ordersShow = ref(false)
let paymentsShow = ref(false)
let customersShow = ref(false)
let productsShow = ref(false)
let branchesShow = ref(false)
let employeesShow = ref(false)
let usersShow = ref(false)
let accountingShow = ref(false)

let permissions = JSON.parse(localStorage.getItem('whoami'))['permissions']
let keys = Object.keys(permissions)
if (keys.includes('branches'))
  branchesShow.value = true;
if (keys.includes('customers'))
  customersShow.value = true;
if (keys.includes('employees'))
  employeesShow.value = true;
if (keys.includes('financial_accounts'))
  accountingShow.value = true;
if (keys.includes('orders'))
  ordersShow.value = true;
if (keys.includes('payments'))
  paymentsShow.value = true;
if (keys.includes('products'))
  productsShow.value = true;
if (keys.includes('users'))
  usersShow.value = true;

</script>

<style scoped>
.active-side-menu-element {
  @apply bg-white;
}
</style>