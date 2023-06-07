<script setup>
import {UserIcon, BellIcon} from '@heroicons/vue/24/solid'
</script>

<template>
  <div class="w-full bg-sky-700 px-3 py-1">
    <div class="flex justify-between">
      <div class="text-xl font-bold text-slate-200">
        Laundry Management System
      </div>
      <div class="flex space-x-3">
        <BellIcon class="w-7 h-full cursor-pointer hover:bg-sky-900"/>
        <UserIcon class="w-7 h-full cursor-pointer hover:bg-sky-900"/>
      </div>
    </div>
  </div>
</template>

<style scoped>

</style>