<script setup>
import TableComponent from "../components/TableComponent.vue";
import {ref} from "vue";
import {sendGetRequest, sendJsonPostRequest} from "../js-modules/base-functions.js";
import {apiBaseUrl} from "../js-modules/website-constants.js";
import {PencilSquareIcon, TrashIcon} from "@heroicons/vue/24/solid/index.js";
import {validateInput} from "../js-modules/form-validations.js";

let tableCol = ['Select','Id', 'Username', 'Email', 'Firstname', 'Lastname', 'Role', 'Branch Id', 'Modifications']
let tableRows = ref([])
let actions = [
  {onClickEvent: 'editUser', btnText: 'Edit', type: 'icon', icon: PencilSquareIcon, iconColor: 'fill-blue-700'},
  {onClickEvent: 'deleteUser', btnText: 'Remove', type: 'icon', icon: TrashIcon, iconColor: 'fill-red-700'},
  {onClickEvent: 'updateUserPass', btnText: 'Update Password'}
]

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
    window.errorNotification('Fetch User Data', response.message)
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
    window.successNotification('User Creation', response.message)
  } else {
    window.errorNotification('User Creation', response.message)
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
    window.successNotification('User Update', response.message)
  } else {
    window.errorNotification('User Update', response.message)
  }
}

async function deleteUser(id) {
  let confirm = await window.popupConfirmation('Delete User',
    'This action is irreversible. Are you sure you want to remove this user?')
  if (confirm) {
    let response = await sendJsonPostRequest(apiBaseUrl + "/users/delete", {
      "user-id": id
    })
    if (response.status === 'success') {
      window.successNotification('User Removal', response.message)
      getUsers()
    } else {
      window.errorNotification('User Removal', response.message)
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
    window.errorNotification('User Password Change', 'Password & Confirm Password should be the same.')
    return
  }

  let response = await sendJsonPostRequest(apiBaseUrl + "/users/update", {
    "user-id": id,
    "password": user['password']
  })

  if (response.status === 'success') {
    window.successNotification('User Update', response.message)
  } else
    window.errorNotification('User Password Change', response.message)
}

</script>

<template>
  <div class="flex justify-between mt-5 mb-3">
    <h3 class="text-2xl font-semibold">Users</h3>
    <button class="bg-slate-600 text-slate-100 rounded-md py-2 px-3 font-semibold" @click="addNewUser">+ New User
    </button>
  </div>

  <TableComponent :tableColumns="tableCol" :tableRows="tableRows" :actions="actions"
                  @delete-user="deleteUser($event)" @edit-user="updateUser($event)"
                  @update-user-pass="updatePasswordFunc($event)"/>
</template>

<style scoped>

</style>