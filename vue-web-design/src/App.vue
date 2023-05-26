<script setup>
import {RouterView} from 'vue-router'
import {ref} from "vue";
import {useRouter} from "vue-router";
import {sendGetRequest} from "./js-modules/base-functions.js";
import {apiBaseUrl} from "./js-modules/website-constants.js";
import Notifications from "./components/Notifications.vue";

// Initializing app and checking whether user is loggedIn
let loggedIn = ref(false)

let headers = {
  'Authorization': null
}

async function init() {
  let storedAuthToken = localStorage.getItem('auth-token') ?? null
  if (storedAuthToken) {
    headers['Authorization'] = 'Bearer ' + storedAuthToken
    let response = await sendGetRequest(apiBaseUrl + '/whoami', '', headers)
    if (response.status === 200) {
      let data = await response.json()
      if (data.body['user-id']) {
        loggedIn.value = true
        return
      }
    }
  }
  useRouter().push('/')
}

init()

</script>

<template>
  <RouterView :loggedIn="loggedIn" @update:logged-in="loggedIn = $event" :headers="headers"/>
  <Notifications/>
</template>

<style scoped>

</style>
