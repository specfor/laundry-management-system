<script setup>
import {ref} from "vue";
import {sendJsonPostRequest} from '../js-modules/base-functions.js'
import {apiBaseUrl} from '../js-modules/website-constants.js'
import {defineProps} from "vue";
import {useRouter} from "vue-router";

const router = useRouter()

let username = ref('')
let password = ref('')
let {loggedIn} = defineProps(['loggedIn'])

if (loggedIn === true)
  router.push('/dashboard')

async function loginUser(event) {
  let response = await sendJsonPostRequest(apiBaseUrl + "/login", {
    username: username.value,
    password: password.value
  })
  if (response.status === 'success') {
    localStorage.setItem('auth-token', response.data.token)
    event.$emit('update:logged-in', true)
    router.push('/dashboard')
  } else {
    window.errorNotification('Login Error', response.data.message)
  }
}
</script>

<template>
  <div class="h-screen">
    <div class="flex min-h-full flex-col align-items-center justify-center px-6 py-12 lg:px-8">
      <div class="sm:mx-auto sm:w-full sm:max-w-sm">
        <img class="mx-auto h-10 w-auto" src="https://tailwindui.com/img/logos/mark.svg?color=indigo&shade=600"
             alt="Your Company">
        <h2 class="mt-10 text-center text-2xl font-bold leading-9 tracking-tight text-gray-900">Laundry Management
          System</h2>
      </div>

      <div class="mt-10 sm:mx-auto sm:w-full sm:max-w-sm">
        <div class="space-y-6">
          <div>
            <label for="username" class="block text-sm font-medium leading-6 text-gray-900">Username</label>
            <div class="mt-2">
              <input v-model="username" name="username" type="text" autocomplete="username" required
                     class="px-2 block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset
                           ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600
                           sm:text-sm sm:leading-6">
            </div>
          </div>

          <div>
            <div class="flex items-center justify-between">
              <label for="password" class="block text-sm font-medium leading-6 text-gray-900">Password</label>
              <div class="text-sm">
                <a href="#" class="font-semibold text-indigo-600 hover:text-indigo-500">Forgot password?</a>
              </div>
            </div>
            <div class="mt-2">
              <input v-model="password" name="password" type="password" autocomplete="current-password" required
                     class="block px-2 w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset
                           ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600
                            sm:text-sm sm:leading-6">
            </div>
          </div>

          <div>
            <button type="submit" @click="loginUser(this)"
                    class="flex w-full justify-center rounded-md bg-indigo-600 px-3 py-1.5 text-sm font-semibold
                        leading-6 text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2
                         focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
              Sign in
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>

</style>