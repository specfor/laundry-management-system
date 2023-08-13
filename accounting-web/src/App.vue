<script setup>
// @ts-check
import Drawer from './components/Drawer.vue';
import DrawerMenu from './components/DrawerMenu.vue';
import { ref } from "vue";
import { useFetch, whenever } from "@vueuse/core";
import { usePersistantState } from "./composibles/persistant-state";

const drawerOpen = ref(false);

// Attempt login
const { data, isFinished } = useFetch('http://laundry-api.localhost/api/v1/login').json().post({
  username: "admin_{342365(_)08",
  password: "rlsjp6)rg_34_)(23as"
})

whenever(isFinished, () => {
  const state = usePersistantState();
  state.value = {
    ...state.value,
    isLoggedIn: true,
    token: data.value.body.token,
  }
})

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
