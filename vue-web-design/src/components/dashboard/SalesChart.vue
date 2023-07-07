<script setup>
import {Chart as ChartJS, CategoryScale, LinearScale, PointElement, LineElement, Title, Tooltip, Legend} from 'chart.js'
import {Line} from 'vue-chartjs'
import {sendGetRequest} from '../../js-modules/base-functions.js'
import {apiBaseUrl} from '../../js-modules/website-constants.js'
import {ref} from "vue";

ChartJS.register(CategoryScale, LinearScale, PointElement, LineElement, Title, Tooltip, Legend)

let label = []
let orderCount = []
let showGraph = ref(false)

async function getOrderCounts() {
  let response = await sendGetRequest(apiBaseUrl + '/orderCount?branch-id=0&no-days-backward=10')
  if (response.status === 'success') {
    for (const key of Object.keys(response.data['order-counts'])) {
      label.push(key)
      orderCount.push(response.data['order-counts'][key])
    }
    showGraph.value = true;
    console.log(orderCount)
  }
}

getOrderCounts()

let data = {
  labels: label,
  datasets: [
    {
      label: 'Orders',
      backgroundColor: '#0f71d5',
      data: orderCount
    }
  ]
}

let options = {
  responsive: true,
  maintainAspectRatio: false,
  scales: {
    y: {
      min: 0
    },
    x: {
      reverse: true
    }
  }
}

</script>

<template>
  <Line :data="data" :options="options" v-if="showGraph"/>
</template>

<style scoped>

</style>