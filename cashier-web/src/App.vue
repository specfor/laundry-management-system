<script setup lang="ts">
import { useI18n } from 'vue-i18n';
import { RouterLink, RouterView } from 'vue-router'

const { locale } = useI18n({
  useScope: 'global'
})

const { t } = useI18n()

</script>

<i18n locale="en">
{
  "hello": "Hello World"
}
</i18n>

<i18n locale="si">
{
  "hello": "Hello Kohomada"
}
</i18n>

<template>
  <router-view v-slot="{ Component }">
    <keep-alive include="Cashier">
      <component :is="Component" />
    </keep-alive>
  </router-view>
</template>

<style scoped></style>
