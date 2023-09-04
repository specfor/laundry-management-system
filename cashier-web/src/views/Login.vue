<template>
    <div>
        <input v-model="username" type="text">
        <input v-model="password" type="password">
        <button @click="doLogin">Login</button>
    </div>
</template>

<script setup lang="ts">
import { useAuthentication } from '@/composibles/authentication';
import { ref } from 'vue';
import { useRouteQuery } from '@vueuse/router';
import { useRouter } from 'vue-router';

const username = ref("")
const password = ref("")

const { login } = useAuthentication()
const { back, replace } = useRouter()
const redirect = useRouteQuery<string>('redirect')

const doLogin = () => {
    login(username.value, password.value)
        .then(() => {
            if (redirect.value == "true") {
                back()
            } else replace({
                name: "Cashier"
            })
        })
        .catch((e) => console.log(e))
}
</script>

<i18n locale="en">
{}
</i18n>

<i18n locale="si">
{}
</i18n>

<style lang="">
    
</style>