<template>
  <div v-if="show" class="w-full h-full z-[9999]">

  </div>
</template>

<script setup>
  import {defineProps} from "vue";

  let show = defineProps(['loading'])
</script>

<style scoped>

</style>