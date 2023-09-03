<script setup>
import {UserIcon, BellIcon} from '@heroicons/vue/24/solid'
import { ref } from 'vue';

let dropdownShow = ref(true)

</script>

<template>
  <div class="w-full bg-sky-700 px-3 py-1 fixed top-0 z-[9999]">
    <div class="flex justify-between">
      <div class="text-xl font-bold text-slate-200">
        Laundry Management System
      </div>
      <div class="flex space-x-3">
        <BellIcon class="w-7 h-full cursor-pointer hover:bg-sky-900"/>
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-7 h-7 cursor-pointer hover:bg-sky-900" @click="()=>{dropdownShow = !dropdownShow}">
  <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
</svg>

      </div>
    </div>
    <div :class="{'hidden':dropdownShow}" class="absolute right-0 z-10 mt-2 w-56 origin-top-right rounded-md bg-white shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none" role="menu" aria-orientation="vertical" aria-labelledby="menu-button" tabindex="-1">
    <div class="py-1" role="none">
      <!-- Active: "bg-gray-100 text-gray-900", Not Active: "text-gray-700" -->
      <a href="/profile" class="text-gray-700 block px-4 py-2 text-sm hover:bg-sky-900 hover:text-white transition duration-300" role="menuitem" tabindex="-1" id="menu-item-0">Profile</a>
      <button class=" block px-4 py-2 text-sm bg-blue-700 rounded-md ml-3 mb-2 text-white mt-2" role="menuitem" tabindex="-1" id="menu-item-1">Log Out</button>
    </div>
  </div>

  </div>
</template>

<style scoped>

</style>