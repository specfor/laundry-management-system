<template>
  <div class="flex justify-between mt-5 mb-3">
    <h3 class="text-2xl font-semibold">Employees</h3>
    <button class="bg-slate-600 text-slate-100 rounded-md py-2 px-3 font-semibold" @click="addNewEmployee">+ New Employee</button>
  </div>

  <TableComponent :tableColumns="employeesTableCol" :tableRows="employeesTableRows" :actions="employeesTableActions" :deleteMultiple="deleteBtn" :edit="editBtn"
                  @remove-employee="deleteEmployee($event)" @edit-employee="editEmployee($event)" :search="searchParam" @employee-name="getEmployeesWithParams"/>

</template>

<script setup>
import TableComponent from '../components/TableComponent.vue'
import {ref} from 'vue'
import {sendGetRequest, sendJsonPostRequest} from "../js-modules/base-functions.js";
import {apiBaseUrl} from "../js-modules/website-constants.js";
import {PencilSquareIcon} from "@heroicons/vue/24/solid/index.js";
import {validateInput} from "../js-modules/form-validations.js";

let employeesTableCol = ['Select','Id', 'Customer Name', 'Phone Number', 'Email', 'Address', 'Joined Date',
  'Left Date']
let employeesTableRows = ref([])

let deleteBtn = [{
  onClickEvent:'removeEmployee'
}]

let editBtn = [{
  onClickEvent:'editEmployee'
}]

let typingTimer;
let doneTypingInterval = 500;

let searchParam = [{
  paramNumber:'paramOne',
  searchParameter:'Employee Name',
  searchParamType:'employeeName',
  type:'text'
},{
  paramNumber:'paramTwo',
  searchParameter:'Phone Number',
  searchParamType:'employeeName',
  type:'Number'
},{
  paramNumber:'paramThree',
  searchParameter:'Email',
  searchParamType:'employeeName',
  type:'email'
},{
  paramNumber:'paramFour',
  searchParameter:'Address',
  searchParamType:'employeeName',
  type:'text'
},{
  paramNumber:'paramFive',
  searchParameter:'Branch Id',
  searchParamType:'employeeName',
  type:'number'
}]

async function getEmployeesWithParams(params){
  let empName = params['paramOne']
  let phoneNumber = parseInt(params['paramTwo'])
  let email = params['paramThree']
  let address = params['paramFour']
  let branchId = params['paramFive']


  if(empName == ''){
    empName = null
  }

  if(phoneNumber == ''){
    phoneNumber = null
  }

  if(email == ''){
    email = null
  }

  if(address == ''){
    address = null
  }

  if(branchId == ''){
    branchId = null
  }

  if(empName== null && phoneNumber == null && email == null && address == null && branchId == null){
    getEmployees()
    return
  }

  clearInterval(typingTimer)
  typingTimer = setTimeout(getEmployees(empName,phoneNumber,email,address,branchId), doneTypingInterval)
}


async function getEmployees(paramOne=null,paramTwo=null,paramThree = null ,paramFour=null,paramFive=null) {

  let params = {}

  if(paramOne){
    params["name"] = paramOne
  }

  if(paramTwo){
    params['phone-number'] = paramTwo
  }

  if(paramThree){
    params['email'] = paramThree

  }

  if(paramFour){
    params['address'] = paramFour

  }

  if(paramFive){
    params['branch-id'] = paramFive
  }

  let response = await sendGetRequest(apiBaseUrl + "/employees",params)

  if (response.status === 'success') {
    employeesTableRows.value = []
    let employees = response.data["employees"];
    for (const employee of employees) {
      employeesTableRows.value.push([employee['employee_id'], employee['name'], employee['phone_num'],
        employee['email'], employee['address'], employee['join_date'], employee['left_date']])
    }
  } else {
    window.errorNotification('Fetch Employee Data', response.message)
  }
}

getEmployees()

async function addNewEmployee() {
  let employee = await window.addNewForm('New Employee', 'Add', [
    {name: 'name', text: 'Employee Name', type: 'text'},
    {name: 'email', text: 'Email', type: 'email', validate: value => validateInput(value, 'email')},
    {name: 'address', text: 'Address', type: 'textarea'},
    {name: 'phone', text: 'Phone Number', type: 'text', validate: value => validateInput(value, 'phone-number')},
    {name: 'join', text: 'Join Date', type: 'date'}
  ])

  if (!employee['accepted'])
    return

  let response = await sendJsonPostRequest(apiBaseUrl + "/employees/add", {
    "employee-name": employee['data']['name'],
    "phone-number": employee['data']['phone'],
    "email": employee['data']['email'],
    "address": employee['data']['address'],
    "join-date": employee['data']['join']
  })

  if (response.status === "success") {
    getEmployees()
    window.successNotification('Add New Employee', response.message)
  } else {
    window.errorNotification('Add New Employee', response.message)
  }
}

async function editEmployee(id) {

  id = parseInt(id.toString())

  let employeeData = employeesTableRows.value.filter((row) => {
    return row[0] === id
  })[0]

  let employee = await window.addNewForm('Update Employee', 'Update', [
    {name: 'name', text: 'Employee Name', type: 'text', value: employeeData[1]},
    {name: 'phone', text: 'Phone Number', type: 'text', value: employeeData[2],
      validate: value => validateInput(value, 'phone-number')},
    {name: 'email', text: 'Email', type: 'email', value: employeeData[3],
      validate: value => validateInput(value, 'email')},
    {name: 'address', text: 'Address', type: 'textarea', value: employeeData[4]},
    {name: 'join', text: 'Join Date', type: 'date', value: employeeData[5]},
    {name: 'left', text: 'Left Date', type: 'date', value: employeeData[6]}
  ])

  if (!employee['accepted'])
    return

  let response = await sendJsonPostRequest(apiBaseUrl + "/employees/update", {
    'employee-id': id,
    "employee-name": employee['data']['name'],
    "phone-number": employee['data']['phone'],
    "email": employee['data']['email'],
    "address": employee['data']['address'],
    "join-date": employee['data']['join'],
    "left-date": employee['data']['left']
  })

  if (response.status === "success") {
    employeesTableRows.value.filter((row) => {
      if (row[0] === id) {
        row[1] = employee['data']['name']
        row[2] = employee['data']['phone']
        row[3] = employee['data']['email']
        row[4] = employee['data']['address']
        row[5] = employee['data']['join']
        row[6] = employee['data']['left']
        return row
      }
    })
    window.successNotification('Update Employee', response.message)
  } else {
    window.errorNotification('Update Employee', response.message)
  }
}

async function deleteEmployee(ids) {

  if(ids.length === 1){
    let confirm = await window.popupConfirmation('Delete Employee',
      'This action is irreversible. Are you sure you want to remove this employee?')
  if (confirm === true) {
    let response = await sendJsonPostRequest(apiBaseUrl + "/employees/delete", {
      'employee-id': ids[0]
    })

    if (response.status === "success") {
      getEmployees()
      window.successNotification('Delete Customer', response.message)
    } else {
      window.errorNotification('Delete Customer', response.message)
    }
  }
  }else{
    let confirm = await window.popupConfirmation('Delete Employee',
      'This action is irreversible. Are you sure you want to remove these employees?')
      if (confirm === true) {

        ids.forEach(async(id)=>{
          let response = await sendJsonPostRequest(apiBaseUrl + "/employees/delete", {
      'employee-id': id
    })

    if (response.status === "success") {
      getEmployees()
      window.successNotification('Delete Customer', response.message)
    } else {
      window.errorNotification('Delete Customer', response.message)
    }
        })
    
  }

  }

  
}
</script>

<style scoped>

</style>