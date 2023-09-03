<template>
        <div class="bg-stone-200 rounded-md w-full">
            <h1 class="text-center text-xl text-stone-900 subpixel-antialiased font-bold mt-2 p-2 border-stone-400 border-b-2">Profile</h1>    
            <div class='flex justify-around'>
                <div class="mt-20">
                <div class="flex justify-center">
                    <label for="name" class="text-lg font-semibold">Username:</label>
                    <label for="name" class="text-stone-700 text-lg font-semibold ml-2">{{username}}</label>
                </div>
                <div class="flex justify-center mt-5">
                    <label for="name" class="text-lg font-semibold">First Name:</label>
                    <label for="name" class="text-stone-700 text-lg font-semibold ml-2">{{ firstname }}</label>
                </div>
                <div class="flex justify-center mt-5">
                    <label for="name" class="text-lg font-semibold">Last Name:</label>
                    <label for="name" class="text-stone-700 text-lg font-semibold ml-2">{{ lastname }}</label>
                </div>
                <div class="flex justify-center mt-5">
                    <label for="name" class="text-lg font-semibold">Email:</label>
                    <label for="name" class="text-stone-700 text-lg font-semibold ml-2">{{ email }}</label>
                </div>
                <div class="flex justify-center mt-5">
                    <label for="name" class="text-lg font-semibold">User Id:</label>
                    <label for="name" class="text-stone-700 text-lg font-semibold ml-2">{{ userId }}</label>
                </div>
                <div class="flex justify-center mt-5">
                    <label for="name" class="text-lg font-semibold">Role:</label>
                    <label for="name" class="text-stone-700 text-lg font-semibold ml-2">{{ role }}</label>
                </div>
                <div class="flex justify-center mt-5">
                    <label for="name" class="text-lg font-semibold">Branch Id:</label>
                    <label for="name" class="text-stone-700 text-lg font-semibold ml-2">{{ branchId }}</label>
                </div>
                <div class="block mt-5">
                    <label for="name" class="text-lg font-semibold">Log In History:</label>
                    <table class="able-auto border-collapset border w-full mt-2">
                    <thead>
                        <tr class="border-0 border-y-2 border-t-0 border-slate-500 bg-neutral-300">
                            <th class="text-left px-3 pt-4 pb-2 font-bold">Id</th>
                            <th class="text-left px-3 pt-4 pb-2 font-bold">ip Address</th>
                            <th class="text-left px-3 pt-4 pb-2 font-bold">Logged At</th>                        </tr>
                    </thead>
                    <tbody>
                        <tr class="border-y border-slate-400 bg-neutral-100 hover:bg-neutral-200" v-for="history in o=loginHistory">
                            <td class="px-3 py-1 text-slate-800">{{ history['id'] }}</td>
                            <td class="px-3 py-1 text-slate-800">{{ history['ip_addr'] }}</td>
                            <td class="px-3 py-1 text-slate-800">{{ history['logged_at'] }}</td>
                        </tr>
                    </tbody>
                </table>
                </div>
                </div>
                <div class="w-40 h-40  mt-20 rounded-md border-2 border-stone-400 ">
                    <img src="" alt="profile picture" class="object-contain">
                </div>
            </div>
            <div class="flex justify-around">
                <div>
                    <button class="bg-sky-600 hover:bg-sky-800 text-white rounded-md p-3 mt-6 mb-6" @click="uploadProfilePicture()">Upload Profile Picture</button>
                </div>
                <div>
                    <button class="bg-red-600 hover:bg-red-800 text-white rounded-md p-3 mt-6 mb-6" @click="updatePasswordFunc()">Change Password</button>
                </div>
            </div>
        </div>

  
</template>

<script setup>
import { ref } from "vue";
import { sendGetRequest,sendJsonPostRequest} from "../js-modules/base-functions";
import { apiBaseUrl } from "../js-modules/website-constants";
import {validateInput} from "../js-modules/form-validations.js";

let username = ref('')
let firstname = ref('')
let lastname = ref('')
let role = ref('')
let email = ref('')
let userId = ref('')
let branchId = ref('')
let loginHistory = ref([])



async function updatePasswordFunc() {
  let user = await window.addNewForm('Update User Password', 'Update', [
    {name: 'prevPassword', text: 'Current Password', type: 'password'},
    {name: 'newPassword', text: 'New Password', type: 'password', validate: value => validateInput(value, 'password')},
    {
      name: 'passwordConfirm', text: 'Confirm Password', type: 'password',
      validate: value => validateInput(value, 'password')
    }])
  if (!user['accepted'])
    return

  if (user.data['newPassword'] !== user.data['passwordConfirm']) {
    window.errorNotification('User Password Change', 'Password & Confirm Password should be the same.')
    return
  }

  let response = await sendJsonPostRequest(apiBaseUrl + "/profile/update-password", {
    "current-password":user.data['prevPassword'],
    "new-password": user.data['newPassword']
  })

  if (response.status === 'success') {
    window.successNotification('User Update', response.message)
  } else
    window.errorNotification('User Password Change', response.message)
}



async function uploadProfilePicture(){

    let proPic = await window.addNewForm('Upload Profile Picture', 'Add', [
    {name: 'Profile Picture', text: 'Profile Picture', type: 'file'},
  ])

  if (!proPic['accepted'])
    return


}


getUserInfo()

async function getUserInfo(){

    let response = await sendGetRequest(apiBaseUrl + '/profile')
    
    if(response.status === 'success'){
        console.log(response)
        username.value = response.data['username']
        firstname.value = response.data['firstname']
        lastname.value = response.data['lastname']
        role.value = response.data['role']
        email.value = response.data['email']
        userId.value = response.data['id']
        branchId.value = response.data['branch_id']
        loginHistory.value = response.data['login-history']

    }
}

</script>

<style>

</style>