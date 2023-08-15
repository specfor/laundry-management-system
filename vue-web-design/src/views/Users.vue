<script setup>
import TableComponent from "../components/TableComponent.vue";
import {ref} from "vue";
import {sendGetRequest, sendJsonPostRequest} from "../js-modules/base-functions.js";
import {apiBaseUrl} from "../js-modules/website-constants.js";
import {validateInput} from "../js-modules/form-validations.js";
import { useRoute } from "vue-router";

let tableCol = ['Select','Id', 'Username', 'Email', 'Firstname', 'Lastname', 'Role', 'Branch Id', 'Modifications']
let tableRows = ref([])
let actions = [
  {onClickEvent: 'updateUserPass', btnText: 'Update Password'}
]

let tableColRoles = ['Select','Id', 'Role','Permissions']
let tableRowsRoles = ref([])
let actionRoles = [{}]

let deleteBtn = [{
  onClickEvent:'deleteUser'
}]

let deleteBtnRole = [{
  onClickEvent:'deleteRole'
}]

let editBtn = [{
  onClickEvent:'editUser'
}]

let editBtnRole = [{
  onClickEvent:'editRole'
}]

let searchParamUsers = [{
  paramNumber:'paramOne',
  searchParameter:'Username',
  searchParamType:'userName',
  type:'text'
},{
  paramNumber:'paramTwo',
  searchParameter:'Name',
  searchParamType:'userName',
  type:"text"
},{
  paramNumber:'paramThree',
  searchParameter:'Email',
  searchParamType:'userName',
  type:"email"
},{
  paramNumber:'paramFour',
  searchParameter:'Branch Id',
  searchParamType:'userName',
  type:"number"
}]

let searchParamRoles = [{
  paramNumber:'paramOne',
  searchParameter:'Role Id',
  searchParamType:'roleName',
  type:"number"
},{
  paramNumber:'paramTwo',
  searchParameter:'Role Name',
  searchParamType:'roleName',
  type:"text"
}]


let doneTypingInterval = 500;
let typingTimerTwo;

async function getRolesWithParams(params){
  let roleId = parseInt(params['paramOne'])
  let name = params['paramTwo']

  if(!Number.isInteger(roleId)){
    roleId = null
  }

  if(name == ''){
    name = null
  }

  if(roleId == null && name == null){
    getRoles()
    return
  }  

  clearInterval(typingTimerTwo)
  typingTimerTwo = setTimeout(getRoles(roleId,name), doneTypingInterval)

}


let typingTimer;


async function getUsersWithParams(params){
  let username= params['paramOne']
  let name = params['paramTwo']
  let email = params['paramThree']
  let branchId = params['paramFour']

  if(!Number.isInteger(branchId)){
    branchId = null
  }

  if(username == ""){
    username = null
  }

  if(email == ''){
    email = null
  }

  if(name == ''){
    name = null
  }

  if(username == null && email == null && branchId == null && name == null){
    getUsers()
    return
  }

  clearInterval(typingTimer)
  typingTimer = setTimeout(getUsers(username,name,email,branchId), doneTypingInterval)

}

async function getUsers(paramOne=null,paramTwo=null,paramThree=null,paramFour=null) {

  let params = {}

  if(paramOne){
    params["username"] = paramOne
  }

  if(paramTwo){
    params['name'] = paramTwo
  }

  if(paramThree){
    params['email'] = paramThree
  }

  if(paramFour){
    params['branch-id'] = paramFour
  }

  let response = await sendGetRequest(apiBaseUrl + "/users",params)

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

  id = parseInt(id.toString())


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

async function deleteUser(ids) {

  if(ids.length === 1){
    let confirm = await window.popupConfirmation('Delete User',
    'This action is irreversible. Are you sure you want to remove this user?')
  if (confirm) {
    let response = await sendJsonPostRequest(apiBaseUrl + "/users/delete", {
      "user-id": ids[0]
    })
    if (response.status === 'success') {
      window.successNotification('User Removal', response.message)
      getUsers()
    } else {
      window.errorNotification('User Removal', response.message)
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
      window.successNotification('User Removal', response.message)
      getUsers()
    } else {
      window.errorNotification('User Removal', response.message)
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

getRoles()
getPermissions()

let userRoles = {}

async function deleteRoles(ids){
  if(ids.length === 1){
    let confirm = await window.popupConfirmation('Delete Role',
    'This action is irreversible. Are you sure you want to remove this role?')
  if (confirm) {
    let response = await sendJsonPostRequest(apiBaseUrl + "/user-roles/delete", {
      "role-id": ids[0]
    })
    if (response.status === 'success') {
      window.successNotification('Role Removal', response.message)
      getRoles()
    } else {
      window.errorNotification('Role Removal', response.message)
    }
    }
  }else{
    let confirm = await window.popupConfirmation('Delete Role',
    'This action is irreversible. Are you sure you want to remove these roles?')

    if (confirm) {
      ids.forEach(async(id)=>{
        let response = await sendJsonPostRequest(apiBaseUrl + "/user-roles/delete", {
      "role-id": id
    })
    if (response.status === 'success') {
      window.successNotification('Role Removal', response.message)
      getRoles()
    } else {
      window.errorNotification('Role Removal', response.message)
    }
      })
    
    }
  }
}

async function getPermissions(){

  let response = await sendGetRequest(apiBaseUrl + "/user-roles")

  if(response.status === "success"){
    userRoles = response.data['user-roles'][0]['permissions']
  }else{
    window.errorNotification('Fetch Permission Data', response.message)
  }
}

async function getRoles(paramOne = null ,paramTwo = null){

  let params = {}

if(paramOne){
  params["role-id"] = paramOne
}

if(paramTwo){
  params['name'] = paramTwo
}

  let response = await sendGetRequest(apiBaseUrl + "/user-roles",params)

if (response.status === 'success') {

  tableRowsRoles.value = []
  let roles = response.data["user-roles"]

   for (const role of roles) {

    tableRowsRoles.value.push([role["role_id"], role["name"], role["permissions"]])
   }
 } else {
   window.errorNotification('Fetch User Data', response.message)
}
}

async function updateRoles(id){
  id = parseInt(id.toString())


let roleData = tableRowsRoles.value.filter((row) => {
  return row[0] === id
})[0]

console.log(roleData)
 
let role = await window.addNewForm('Update Role', 'Update', [
    {name: 'role', text: 'Role', type: 'text', value: roleData[2]},
    
  ])
  if (!user['accepted'])
    return
}

async function addNewRole(){

  let tempData = [
    {name:'role', text:'Role',type:'text'}
  ]

  for(const[key,val] of Object.entries(userRoles)){

    let arrayOne = []
    let dictOne = {}
    

    val.forEach(value=>{

      let dictTwo = {}

      dictTwo['name'] = value
      dictTwo['text'] = value
      dictTwo['value'] = value

      arrayOne.push(dictTwo)
    })
    
    dictOne['text'] = `Permissions ${key}`
    dictOne['name'] = key
    dictOne['type'] = 'checkbox'
    dictOne.options = arrayOne


    tempData.push(dictOne)
  }

  console.log(tempData)

  let roles = await window.addNewForm('New Role', 'Add', tempData)

  if (!roles['accepted'])
    return

  let dict_one = {}

  for(const [key,value] of Object.entries(roles.data)){
    
    if(key === 'role'){
    }else{
      let array_one = []
      for(const [key,val] of Object.entries(value)){
        if(val == true){
          array_one.push(key)
        }
      }
      dict_one[key] = array_one
    }
  } 

  let response = await sendJsonPostRequest(apiBaseUrl + "/user-roles/add", {
    "name": roles.data['role'],
    "permissions": dict_one
  })

  if (response.status === "success") {
    getRoles()
    window.successNotification('User Creation', response.message)
  } else {
    window.errorNotification('User Creation', response.message)
  }
}

</script>

<template>
  <div class="flex justify-between mt-5 mb-3">
    <h3 class="text-2xl font-semibold">Users</h3>
    <button class="bg-slate-600 text-slate-100 rounded-md py-2 px-3 font-semibold" @click="addNewUser">+ New User
    </button>
  </div>

  <TableComponent :tableColumns="tableCol" :tableRows="tableRows" :actions="actions" :deleteMultiple="deleteBtn" :edit="editBtn"
                  @delete-user="deleteUser($event)" @edit-user="updateUser($event)"
                  @update-user-pass="updatePasswordFunc($event)" :search="searchParamUsers" @user-name="getUsersWithParams($event)"/>

    <div class="flex justify-between mt-5 mb-3 ">
      <h3 class="text-2xl font-semibold">Roles</h3>
      <button class="bg-slate-600 text-slate-100 rounded-md py-2 px-3 font-semibold" @click="addNewRole">+ New Role
      </button>
      </div>
      <TableComponent :tableColumns="tableColRoles" :tableRows="tableRowsRoles" :actions="actionsRoles" :deleteMultiple="deleteBtnRole" :edit="editBtnRole"
                  @delete-role="deleteRoles($event)" @edit-role="updateRoles($event)"
                  :search="searchParamRoles" @role-name="getRolesWithParams($event)"/>
              


</template>

<style scoped>

</style>