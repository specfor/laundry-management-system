<script setup>
import Drawer from './components/Drawer.vue';
import DrawerMenu from './components/DrawerMenu.vue';
import { ref } from "vue";

const drawerOpen = ref(false);
</script>

<template>
  <Drawer v-model:open="drawerOpen">
    <template #drawer-content>
      <DrawerMenu @onMenuItemClick="drawerOpen = false"/>
    </template>
    <div class="mt-[50px] font-default">
      <router-view></router-view>
    </div>
  </Drawer>
</template>

<style scoped></style>
