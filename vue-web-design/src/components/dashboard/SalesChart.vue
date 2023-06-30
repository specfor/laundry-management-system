<script setup>
import {
  Chart as ChartJS,
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  Title,
  Tooltip,
  Legend
} from 'chart.js'
import { Line } from 'vue-chartjs'

ChartJS.register(
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  Title,
  Tooltip,
  Legend
)

const data = {
  labels: [
    '',
    '',
    'Wednesday',
    '',
    '',
    'Saturday',
    'Sunday'
  ],
  datasets: [
    {
      label: 'Data One',
      backgroundColor: '#0f71d5',
      data: [40, 20, 12, 39, 10, 40, 39]
    }
  ]
}

const options = {
  responsive: true,
  maintainAspectRatio: false
}

</script>

<template>
  <Line :data="data" :options="options"/>
</template>

<style scoped>

</style>