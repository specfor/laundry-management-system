<script setup>
import TableComponent from "../components/TableComponent.vue";
import {ref} from "vue";
import {sendGetRequest, sendJsonPostRequest} from "../js-modules/base-functions.js";
import {apiBaseUrl} from "../js-modules/website-constants.js";
import {PencilSquareIcon, TrashIcon} from "@heroicons/vue/24/solid/index.js";
import {validateInput} from "../js-modules/form-validations.js";
import { pushSuccessNotification, pushErrorNotification } from '../stores/notification-store';

let tableCol = ['Select','Id', 'Username', 'Email', 'Firstname', 'Lastname', 'Role', 'Branch Id', 'Modifications']
let tableRows = ref([])
let actions = [
  {onClickEvent: 'editUser', btnText: 'Edit', type: 'icon', icon: PencilSquareIcon, iconColor: 'fill-blue-700'},
  {onClickEvent: 'updateUserPass', btnText: 'Update Password'}
]

let deleteBtn = [{
  onClickEvent:'deleteUser'
}]

async function getUsers() {
  let response = await sendGetRequest(apiBaseUrl + "/users")

  if (response.status === 'success') {
    tableRows.value = []
    let users = response.data["users"]

    for (const user of users) {
      tableRows.value.push([user["id"], user["username"], user["email"], user["firstname"], user["lastname"],
        user["role"], user["branch_id"]])
    }
  } else {
    pushErrorNotification('Fetch User Data', response.message)
  }
}

getUsers()

async function addNewUser() {
  let user = await window.addNewForm('New User', 'Add', [
    {name: 'username', text: 'Username', type: 'text', validate: value => validateInput(value, 'username')},
    {name: 'password', text: 'Password', type: 'password', validate: value => validateInput(value, 'password')},
    {name: 'email', text: 'Email', type: 'email', validate: value => validateInput(value, 'email')},
    {name: 'firstname', text: 'First Name', type: 'text'},
    {name: 'lastname', text: 'Last Name', type: 'text'},
    {
      name: 'role', text: 'User Role', type: 'select', value: 'cashier',
      options: [{text: 'Administrator', value: 'administrator'}, {text: 'Manager', value: 'manager'},
        {text: 'Cashier', value: 'cashier'}]
    },
    {name: 'branch-id', text: 'Branch Id', type: 'number'}
  ])

  if (!user['accepted'])
    return

  let response = await sendJsonPostRequest(apiBaseUrl + "/users/add", {
    "username": user.data['username'],
    "password": user.data['password'],
    "role": user.data['role'],
    "email": user.data['email'],
    "firstname": user.data['firstname'],
    "lastname": user.data['lastname'],
    "branch-id": user.data['branch-id']
  })

  if (response.status === "success") {
    getUsers()
    pushSuccessNotification('User Creation', response.message)
  } else {
    pushErrorNotification('User Creation', response.message)
  }
}

async function updateUser(id) {
  let userData = tableRows.value.filter((row) => {
    return row[0] === id
  })[0]

  let user = await window.addNewForm('Update User', 'Update', [
    {name: 'email', text: 'Email', type: 'email', value: userData[2], validate: value => validateInput(value, 'email')},
    {name: 'firstname', text: 'First Name', type: 'text', value: userData[3]},
    {name: 'lastname', text: 'Last Name', type: 'text', value: userData[4]},
    {
      name: 'role', text: 'User Role', type: 'select', value: userData[5],
      options: [{text: 'Administrator', value: 'administrator'}, {text: 'Manager', value: 'manager'},
        {text: 'Cashier', value: 'cashier'}]
    },
    {name: 'branch-id', text: 'Branch Id', type: 'number', value: userData[6]}
  ])
  if (!user['accepted'])
    return

  let response = await sendJsonPostRequest(apiBaseUrl + "/users/update", {
    "user-id": id,
    "role": user['data']['role'],
    "email": user['data']['email'],
    "firstname": user['data']['firstname'],
    "lastname": user['data']['lastname'],
    "branch-id": user['data']['branch-id']
  })
  if (response.status === "success") {
    tableRows.value.filter((row) => {
      if (row[0] === id) {
        row[2] = user['data']['email']
        row[3] = user['data']['firstname']
        row[4] = user['data']['lastname']
        row[5] = user['data']['role']
        row[6] = user['data']['branch-id']
        return row
      }
    })
    pushSuccessNotification('User Update', response.message)
  } else {
    pushErrorNotification('User Update', response.message)
  }
}

async function deleteUser(ids) {

  if(ids.length === 1){
    let confirm = await window.popupConfirmation('Delete User',
    'This action is irreversible. Are you sure you want to remove this user?')
  if (confirm) {
    let response = await sendJsonPostRequest(apiBaseUrl + "/users/delete", {
      "user-id": ids[0]
    })
    if (response.status === 'success') {
      pushSuccessNotification('User Removal', response.message)
      getUsers()
    } else {
      pushErrorNotification('User Removal', response.message)
    }
    }
  }else{
    let confirm = await window.popupConfirmation('Delete User',
    'This action is irreversible. Are you sure you want to remove these users?')

    if (confirm) {
      ids.forEach(async(id)=>{
        let response = await sendJsonPostRequest(apiBaseUrl + "/users/delete", {
      "user-id": id
    })
    if (response.status === 'success') {
      pushSuccessNotification('User Removal', response.message)
      getUsers()
    } else {
      pushErrorNotification('User Removal', response.message)
    }
      })
    
    }
  }

  
}

async function updatePasswordFunc(id) {
  let user = await window.addNewForm('Update User Password', 'Update', [
    {name: 'password', text: 'Password', type: 'password', validate: value => validateInput(value, 'password')},
    {
      name: 'passwordConfirm', text: 'Confirm Password', type: 'password',
      validate: value => validateInput(value, 'password')
    }])
  if (!user['accepted'])
    return

  if (user['password'] !== user['passwordConfirm']) {
    pushErrorNotification('User Password Change', 'Password & Confirm Password should be the same.')
    return
  }

  let response = await sendJsonPostRequest(apiBaseUrl + "/users/update", {
    "user-id": id,
    "password": user['password']
  })

  if (response.status === 'success') {
    pushSuccessNotification('User Update', response.message)
  } else
    pushErrorNotification('User Password Change', response.message)
}

</script>

<template>
  <div class="flex justify-between mt-5 mb-3">
    <h3 class="text-2xl font-semibold">Users</h3>
    <button class="bg-slate-600 text-slate-100 rounded-md py-2 px-3 font-semibold" @click="addNewUser">+ New User
    </button>
  </div>

  <TableComponent :tableColumns="tableCol" :tableRows="tableRows" :actions="actions" :deleteMultiple="deleteBtn"
                  @delete-user="deleteUser($event)" @edit-user="updateUser($event)"
                  @update-user-pass="updatePasswordFunc($event)"/>
</template>

<style scoped>

</style>