<script setup lang="ts">
import { useStorage } from '@vueuse/core';
import { useI18n } from 'vue-i18n';
import { RouterLink, RouterView } from 'vue-router'

const { locale } = useI18n({
    useScope: 'global'
})

useStorage('app-locale', locale)

</script>

<template>
  <router-view v-slot="{ Component }">
    <keep-alive include="Cashier">
      <component :is="Component" />
    </keep-alive>
  </router-view>
</template>

<style scoped></style>
