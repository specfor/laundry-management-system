<template>
  <div class="grid grid-cols-3">
    <div class="p-2 bg-slate-200 rounded-lg col-span-2">
      <div class="border-b-2 border-black mb-2 pb-1 flex justify-between">
        <h3 class="font-bold">Daily Sales</h3>
        <ChevronUpIcon @click="collapseCard(0)" v-if="cardCollapsed[0]" class="w-6 cursor-pointer hover:bg-white rounded-lg"/>
        <ChevronDownIcon @click="collapseCard(0)" v-if="!cardCollapsed[0]" class="w-6 cursor-pointer hover:bg-white rounded-lg"/>
      </div>
      <div v-if="cardCollapsed[0]" class="h-[300px]">
        <SalesChart/>
      </div>
    </div>
  </div>
</template>

<script setup>
import {ChevronDownIcon, ChevronUpIcon} from "@heroicons/vue/24/solid/index.js";
import {ref} from "vue";
import SalesChart from "../components/dashboard/SalesChart.vue";

let cardCollapsed = ref({0: true})

function collapseCard(id) {
  cardCollapsed.value[id] = !cardCollapsed.value[id]
}
</script>

<style scoped>

</style>