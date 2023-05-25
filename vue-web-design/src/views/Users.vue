<script setup>
import TableComponent from "../components/TableComponent.vue";
import {ref} from "vue";
import {sendGetRequest, sendJsonPostRequest} from "../js-modules/base-functions.js";
import {apiBaseUrl} from "../js-modules/website-constants.js";
import {defineProps} from "vue";
import {Dialog, DialogPanel, DialogTitle, TransitionChild, TransitionRoot} from '@headlessui/vue'
import ConfirmationModal from "../components/ConfirmationModal.vue";
import PopupSuccess from "../components/Notifications.vue";
// import { ExclamationTriangleIcon } from '@heroicons/vue/24/outline'

//Modal open status
let isChangePasswordShow = ref(false)
let isConfirmShow = ref(false)
let isUpdateShow = ref(false)
let isNewShow = ref(false)

let tableCol = ['Id', 'Username', 'Email', 'Firstname', 'Lastname', 'Role', 'Branch Id', 'Actions']
let tableRows = ref([])
let actions = [{onClickEvent: 'editUser', btnText: 'Edit'}, {onClickEvent: 'deleteUser', btnText: 'Remove'},
  {onClickEvent: 'updateUserPass', btnText: 'Update Password'}]

let {headers} = defineProps(['headers'])

async function getUsers() {
  let response = await sendGetRequest(apiBaseUrl + "/users", '', headers)

  if (response.status === 200) {
    let data = await response.json()
    if (data.statusMessage === "success") {
      let users = data["body"]["users"]

      for (const user of users) {
        tableRows.value.push([user["id"], user["username"], user["email"], user["firstname"], user["lastname"],
          user["role"], user["branch_id"]])
      }
    } else (
        alert("Something went wrong.Try again.")
    )
  }
}

getUsers()

let newUsername = ref(null)
let newPassword = ref(null)
let newEmail = ref(null)
let newFirstname = ref(null)
let newLastname = ref(null)
let newRole = ref('cashier')
let newBranchId = ref(null)

async function addNewUser() {
  let response = await sendJsonPostRequest(apiBaseUrl + "/users/add", {
    "username": newUsername.value,
    "password": newPassword.value,
    "role": newRole.value,
    "email": newEmail.value,
    "firstname": newFirstname.value,
    "lastname": newLastname.value,
    "branch-id": newBranchId.value
  }, headers)
  let data = await response.json()

  if (data.statusMessage === "success") {
    isNewShow.value = false
    newUsername.value = null
    newPassword.value = null
    newEmail.value = null
    newFirstname.value = null
    newLastname.value = null
    newRole.value = 'cashier'
    newBranchId.value = null

    tableRows.value.push([data.body['user-id'], newUsername.value, newEmail.value, newFirstname.value,
      newLastname.value, newRole.value, newBranchId.value])
    alert('user added')
  } else {
    alert(data.body.message)
  }
}

let editUserId = null
let editUsername = ref(null)
let editEmail = ref(null)
let editFirstname = ref(null)
let editLastname = ref(null)
let editRole = ref(null)
let editBranchId = ref(null)

function prepareEditUser(id) {
  for (const tableRow of tableRows.value) {
    if (tableRow[0] === id) {
      editUserId = id
      editUsername.value = tableRow[1]
      editEmail.value = tableRow[2]
      editFirstname.value = tableRow[3]
      editLastname.value = tableRow[4]
      editRole.value = tableRow[5]
      editBranchId.value = tableRow[6]
    }
  }
  isUpdateShow.value = true
}

let deleteUserId = null

function confirmDeletion(id) {
  deleteUserId = id
  isConfirmShow.value = true
}

async function deleteUser() {
  let response = await sendJsonPostRequest(apiBaseUrl + "/users/delete", {
    "user-id": deleteUserId
  }, headers)
  let data = await response.json()

  if (data.statusMessage === "success") {
    alert('user removed.')
    tableRows.value = tableRows.value.filter(function (tableRow) {
      return tableRow[0] !== deleteUserId
    })
  } else {
    alert('failed to remove user.')
  }
}

let updatePassword = ref(null)
let updatePasswordConfirm = ref(null)

function prepareUpdatePassword(id) {
  updatePasswordConfirm.value = null
  updatePassword.value = null
  editUserId = id
  isChangePasswordShow.value = true
}

async function updatePasswordFunc() {
  if (updatePassword.value === updatePasswordConfirm.value) {
    let response = await sendJsonPostRequest(apiBaseUrl + "/users/update", {
      "user-id": editUserId,
      "password": updatePassword.value
    }, headers)

    let resJson = await response.json()

    if (resJson.statusMessage === 'success')
      alert('password updated.')
    else
      alert(resJson.body.message)
  } else {
    alert("Passwords don't match.")
  }

}
</script>

<template>
  <div class="container">
    <h4>Add New User</h4>
    <button class="bg-slate-400" @click="isNewShow = true">+</button>

    <h3 class="text-2xl font-semibold mb-5">Users</h3>
    <TableComponent :tableColumns="tableCol" :tableRows="tableRows" :actions="actions"
                    @delete-user="confirmDeletion($event)" @edit-user="prepareEditUser($event)"
                    @update-user-pass="prepareUpdatePassword($event)"/>
    <ConfirmationModal :title="'Confirm Removal'"
                       :message="'Are you sure to remove the user. This action is irreversible.'"
                       :isOpen="isConfirmShow" @confirm="deleteUser" @modal-closed="isConfirmShow = false"/>

    <!-- Add new User modal -->
    <TransitionRoot as="template" :show="isNewShow">
      <Dialog as="div" class="relative z-10" @close="isNewShow = false">
        <TransitionChild as="template" enter="ease-out duration-300" enter-from="opacity-0" enter-to="opacity-100"
                         leave="ease-in duration-200" leave-from="opacity-100" leave-to="opacity-0">
          <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"/>
        </TransitionChild>

        <div class="fixed inset-0 z-10 overflow-y-auto">
          <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
            <TransitionChild as="template" enter="ease-out duration-300"
                             enter-from="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                             enter-to="opacity-100 translate-y-0 sm:scale-100" leave="ease-in duration-200"
                             leave-from="opacity-100 translate-y-0 sm:scale-100"
                             leave-to="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
              <DialogPanel
                  class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg">
                <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                  <div>
                    Username <input type="text" v-model="newUsername">
                  </div>
                  <div>
                    Password <input type="password" v-model="newPassword">
                  </div>
                  <div>
                    Email <input type="email" v-model="newEmail">
                  </div>
                  <div>
                    Firstname <input type="text" v-model="newFirstname">
                  </div>
                  <div>
                    Lastname <input type="text" v-model="newLastname">
                  </div>
                  <div>
                    Role
                    <select name="role" v-model="newRole">
                      <option value="administrator">Administrator</option>
                      <option value="manager">Manager</option>
                      <option value="cashier">Cashier</option>
                    </select>
                  </div>
                  <div>
                    Branch Id <input type="text" v-model="newBranchId">
                  </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                  <button type="button"
                          class="inline-flex w-full justify-center rounded-md bg-red-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-500 sm:ml-3 sm:w-auto"
                          @click="addNewUser()">Create
                  </button>
                  <button type="button"
                          class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto"
                          @click="isNewShow = false" ref="cancelButtonRef">Cancel
                  </button>
                </div>
              </DialogPanel>
            </TransitionChild>
          </div>
        </div>
      </Dialog>
    </TransitionRoot>

    <!-- Edit User modal -->
    <TransitionRoot as="template" :show="isUpdateShow">
      <Dialog as="div" class="relative z-10" @close="isUpdateShow = false">
        <TransitionChild as="template" enter="ease-out duration-300" enter-from="opacity-0" enter-to="opacity-100"
                         leave="ease-in duration-200" leave-from="opacity-100" leave-to="opacity-0">
          <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"/>
        </TransitionChild>

        <div class="fixed inset-0 z-10 overflow-y-auto">
          <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
            <TransitionChild as="template" enter="ease-out duration-300"
                             enter-from="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                             enter-to="opacity-100 translate-y-0 sm:scale-100" leave="ease-in duration-200"
                             leave-from="opacity-100 translate-y-0 sm:scale-100"
                             leave-to="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
              <DialogPanel
                  class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg">
                <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                  <div>
                    Username <input type="text" v-model="editUsername">
                  </div>
                  <div>
                    Email <input type="email" v-model="editEmail">
                  </div>
                  <div>
                    Firstname <input type="text" v-model="editFirstname">
                  </div>
                  <div>
                    Lastname <input type="text" v-model="editLastname">
                  </div>
                  <div>
                    Role
                    <select name="role" v-model="editRole">
                      <option value="administrator">Administrator</option>
                      <option value="manager">Manager</option>
                      <option value="cashier">Cashier</option>
                    </select>
                  </div>
                  <div>
                    Branch Id <input type="text" v-model="editBranchId">
                  </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                  <button type="button"
                          class="inline-flex w-full justify-center rounded-md bg-red-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-500 sm:ml-3 sm:w-auto"
                          @click="isUpdateShow = false; updateUser()">Update
                  </button>
                  <button type="button"
                          class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto"
                          @click="isUpdateShow = false" ref="cancelButtonRef">Cancel
                  </button>
                </div>
              </DialogPanel>
            </TransitionChild>
          </div>
        </div>
      </Dialog>
    </TransitionRoot>

    <!-- Change user password modal -->
    <TransitionRoot as="template" :show="isChangePasswordShow">
      <Dialog as="div" class="relative z-10" @close="isChangePasswordShow = false">
        <TransitionChild as="template" enter="ease-out duration-300" enter-from="opacity-0" enter-to="opacity-100"
                         leave="ease-in duration-200" leave-from="opacity-100" leave-to="opacity-0">
          <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"/>
        </TransitionChild>

        <div class="fixed inset-0 z-10 overflow-y-auto">
          <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
            <TransitionChild as="template" enter="ease-out duration-300"
                             enter-from="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                             enter-to="opacity-100 translate-y-0 sm:scale-100" leave="ease-in duration-200"
                             leave-from="opacity-100 translate-y-0 sm:scale-100"
                             leave-to="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
              <DialogPanel
                  class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg">
                <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                  <div>
                    Password <input type="password" v-model="updatePassword">
                  </div>
                  <div>
                    Confirm Password <input type="password" v-model="updatePasswordConfirm">
                  </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                  <button type="button"
                          class="inline-flex w-full justify-center rounded-md bg-red-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-500 sm:ml-3 sm:w-auto"
                          @click="isChangePasswordShow = false; updatePasswordFunc()">Update Password
                  </button>
                  <button type="button"
                          class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto"
                          @click="isChangePasswordShow = false" ref="cancelButtonRef">Cancel
                  </button>
                </div>
              </DialogPanel>
            </TransitionChild>
          </div>
        </div>
      </Dialog>
    </TransitionRoot>
  </div>
</template>

<style scoped>

</style>