<script setup>
import {ref} from "vue";
import SalesChart from "../components/dashboard/SalesChart.vue";
import OrdersDispatch from "../components/dashboard/OrdersDispatch.vue"

let cards = ref([
  {topic: "Daily Order Count", body: SalesChart, css: "col-span-2"},
  {topic: "Orders to Dispatch Today", body: OrdersDispatch, css: ""}
])

</script>

<template>
  <div class="grid grid-cols-3 gap-3">
    <div class="p-2 bg-slate-200 rounded-lg" :class="card.css" v-for="card of cards">
      <div class="border-b-2 border-black mb-2 pb-1 flex justify-between">
        <h3 class="font-bold">{{ card.topic }}</h3>
      </div>
      <div class="h-[300px]">
        <Component :is="card.body"/>
      </div>
    </div>
  </div>
</template>

<style scoped>

</style>