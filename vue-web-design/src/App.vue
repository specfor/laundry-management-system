<script setup>
import {RouterView} from 'vue-router'
import {ref, watch} from "vue";
import {useRouter} from "vue-router";
import {sendGetRequest} from "./js-modules/base-functions.js";
import {apiBaseUrl} from "./js-modules/website-constants.js";
import Notifications from "./components/Notifications.vue";
import ConfirmationModal from "./components/ConfirmationModal.vue";
import AddNewModal from "./components/AddNewModal.vue";
import DataShowModal from "./components/DataShowModal.vue";
import Header from "./components/Header.vue";
import SideMenu from "./components/SideMenu.vue";
import LoadingScreen from './components/LoadingScreen.vue'

let router = useRouter()

let isLoading = ref(true)
let showHeader = ref(false)
let leftPadding = ref('')

// Initializing app and checking whether user is loggedIn
if (!window.loggedIn)
  window.loggedIn = ref(false)

watch(window.loggedIn, async ()=>{
  showHeader.value = window.loggedIn.value
  if (window.loggedIn.value === false){
    router.push('/')
    leftPadding.value = ''
  } else{
    leftPadding.value = 'ml-[200px]'
    if (router.currentRoute.value['href'] === '/')
      await router.replace('/dashboard')
  }
})

window.httpHeaders = {
  'Authorization': null
}

async function init() {
  let storedAuthToken = localStorage.getItem('auth-token') ?? null
  if (storedAuthToken) {
    window.httpHeaders['Authorization'] = 'Bearer ' + storedAuthToken
    let response = await sendGetRequest(apiBaseUrl + '/whoami')
    if (response.status === 'success') {
      if (response.data['user-id']) {
        window.loggedIn.value = true
      }
    }
  }
}

init()
</script>

<template>
  <!--  <div class="w-screen h-screen fixed top-0 left-0">-->
  <!--    <LoadingScreen :loading="isLoading"/>-->
  <!--  </div>-->
  <Header v-show="showHeader"/>
  <SideMenu v-show="showHeader" class="pt-12"/>
  <div class="pt-12" :class="leftPadding">
    <div class="container">
      <RouterView/>
    </div>
  </div>
  <Notifications class="pt-6"/>
  <ConfirmationModal/>
  <AddNewModal/>
  <DataShowModal/>
</template>

<style scoped>

</style>
