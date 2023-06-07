<script setup>
import {RouterView} from 'vue-router'
import {ref} from "vue";
import {useRouter} from "vue-router";
import {sendGetRequest} from "./js-modules/base-functions.js";
import {apiBaseUrl} from "./js-modules/website-constants.js";
import Notifications from "./components/Notifications.vue";
import ConfirmationModal from "./components/ConfirmationModal.vue";
import AddNewModal from "./components/AddNewModal.vue";
import DataShowModal from "./components/DataShowModal.vue";
import LoadingScreen from './components/LoadingScreen.vue'

let isLoading = ref(true)

// Initializing app and checking whether user is loggedIn
if (!window.loggedIn)
  window.loggedIn = ref(false)

let router = useRouter()

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
  <div class="container">
    <RouterView/>
  </div>
  <Notifications/>
  <ConfirmationModal/>
  <AddNewModal/>
  <DataShowModal/>
</template>

<style scoped>

</style>
