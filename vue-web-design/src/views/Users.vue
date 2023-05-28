<script setup>
import TableComponent from "../components/TableComponent.vue";
import {ref} from "vue";
import {sendGetRequest, sendJsonPostRequest} from "../js-modules/base-functions.js";
import {apiBaseUrl} from "../js-modules/website-constants.js";

let tableCol = ['Id', 'Username', 'Email', 'Firstname', 'Lastname', 'Role', 'Branch Id', 'Modifications']
let tableRows = ref([])
let actions = [{onClickEvent: 'editUser', btnText: 'Edit'}, {onClickEvent: 'deleteUser', btnText: 'Remove'},
  {onClickEvent: 'updateUserPass', btnText: 'Update Password'}]

async function getUsers() {
  let response = await sendGetRequest(apiBaseUrl + "/users", '', window.httpHeaders)

  if (response.status === 200) {
    let data = await response.json()
    if (data.statusMessage === "success") {
      tableRows.value = []
      let users = data["body"]["users"]

      for (const user of users) {
        tableRows.value.push([user["id"], user["username"], user["email"], user["firstname"], user["lastname"],
          user["role"], user["branch_id"]])
      }
    } else {
      window.errorNotification('Fetch User Data', data.body.message)
    }
  } else {
    window.errorNotification('Fetch User Data', 'Something went wrong. Can not fetch user data.')
  }
}

getUsers()

async function addNewUser() {
  let user = await window.addNewForm('New User', 'Add', [
    {name: 'username', text: 'Username', type: 'text'},
    {name: 'password', text: 'Password', type: 'password'},
    {name: 'email', text: 'Email', type: 'email'},
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
  }, window.httpHeaders)
  if (response.status === 200) {
    let data = await response.json()

    if (data.statusMessage === "success") {
      getUsers()
      window.successNotification('User Creation', data.body.message)
    } else {
      window.errorNotification('User Creation', data.body.message)
    }
  } else {
    window.errorNotification('User Creation', 'Connection error occurred.')
  }
}

async function updateUser(id) {
  let userData = tableRows.value.filter((row) => {
    return row[0] === id
  })[0]

  let user = await window.addNewForm('Update User', 'Update', [
    {name: 'email', text: 'Email', type: 'email', value: userData[2]},
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
  }, window.httpHeaders)
  if (response.status === 200) {
    let data = await response.json()

    if (data.statusMessage === "success") {
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
      window.successNotification('User Update', data.body.message)
    } else {
      window.errorNotification('User Update', data.body.message)
    }
  } else {
    window.errorNotification('User Update', 'Connection error occurred.')
  }
}

async function deleteUser(id) {
  let confirm = await window.popupConfirmation('Delete User',
      'This action is irreversible. Are you sure you want to remove this user?')
  if (confirm) {
    let response = await sendJsonPostRequest(apiBaseUrl + "/users/delete", {
      "user-id": id
    }, window.httpHeaders)
    if (response.status === 200) {
      let data = await response.json()

      if (data.statusMessage === "success") {
        window.successNotification('User Removal', data.body.message)
        getUsers()
      } else {
        window.errorNotification('User Removal', data.body.message)
      }
    } else {
      window.errorNotification('User Removal', 'Failed to connect to the servers.')
    }
  }
}

async function updatePasswordFunc(id) {
  let user = await window.addNewForm('Update User Password', 'Update', [
    {name: 'password', text: 'Password', type: 'password'},
    {name: 'passwordConfirm', text: 'Confirm Password', type: 'password'}])
  if (!user['accepted'])
    return

  if (user['password'] !== user['passwordConfirm']) {
    window.errorNotification('User Password Change', 'Password & Confirm Password should be the same.')
    return
  }

  let response = await sendJsonPostRequest(apiBaseUrl + "/users/update", {
    "user-id": id,
    "password": user['password']
  }, window.httpHeaders)

  if (response.status === 200) {
    let resJson = await response.json()

    if (resJson.statusMessage === 'success') {
      window.successNotification('User Update', resJson.body.message)
    } else
      window.errorNotification('User Password Change', resJson.body.message)
  }
}

</script>

<template>
  <h4>Add New User</h4>
  <button class="bg-slate-400" @click="addNewUser">+</button>

  <h3 class="text-2xl font-semibold mb-5">Users</h3>
  <TableComponent :tableColumns="tableCol" :tableRows="tableRows" :actions="actions"
                  @delete-user="deleteUser($event)" @edit-user="updateUser($event)"
                  @update-user-pass="updatePasswordFunc($event)"/>
</template>

<style scoped>

</style>