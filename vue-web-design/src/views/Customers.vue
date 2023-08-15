<script setup>
import TableComponent from '../components/TableComponent.vue'
import {ref} from 'vue'
import {sendGetRequest, sendJsonPostRequest} from "../js-modules/base-functions.js";
import {apiBaseUrl} from "../js-modules/website-constants.js";
import {validateInput} from "../js-modules/form-validations.js";

let customersTableCol = ['Select','Id', 'Customer Name', 'Phone Number', 'Email', 'Address', 'Joined Date',
  'Banned']
let customersTableRows = ref([])


let deleteBtn = [{
  onClickEvent:'removeCustomer'
}]

let editBtn = [{
  onClickEvent:'editCustomer'
}]

let searchParam = [{
  type:'text',
  paramNumber:'paramOne',
  searchParameter:'Customer Name',
  searchParamType:'customerName'
},{
  type:'number',
  paramNumber:"paramTwo",
  searchParameter:'Phone Number',
  searchParamType:'customerName',
},{
  paramNumber:'paramThree',
  searchParameter:'Email',
  searchParamType:'customerName',
  type:'email'
},{
  paramNumber:'paramFour',
  searchParameter:'Customer Id',
  searchParamType:'customerName',
  type:'number'
},{
  paramNumber:'paramFive',
  searchParameter:'Branch Id',
  searchParamType:'customerName',
  type:'number'
},{
  paramNumber:'paramSix',
  searchParameter:'Address',
  searchParamType:'customerName',
  type:'text'
},{
  paramNumber:'paramSeven',
  searchParameter:'Joined Date',
  searchParamType:'customerName',
  type:'date'
}]

async function getCustomers(paramOne=null,paramTwo=null,paramThree=null,paramFour=null,paramFive=null,paramSix=null,paramSeven=null) {

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
    params['customer-id'] = paramFive
  }
  
  if(paramSix){
    params['branch-id'] = paramSix
  }
  
  if(paramSeven){
    params['join-date'] = paramSeven
  }

  let response = await sendGetRequest(apiBaseUrl + "/customers",params)

  if (response.status === "success") {
    customersTableRows.value = []
    let customers = response.data["customers"];
    for (const customer of customers) {
      customersTableRows.value.push([customer['customer_id'], customer['name'], customer['phone_num'],
        customer['email'], customer['address'], customer['joined_date'], customer['banned'] ? 'Yes' : 'No'])
    }
  } else {
    window.errorNotification('Fetch Customer Data', response.message)
  }
}

getCustomers()

async function addNewCustomer() {
  let customer = await window.addNewForm('New Customer', 'Add', [
    {name: 'name', text: 'Customer Name', type: 'text'},
    {name: 'email', text: 'Email', type: 'email', validate: value => validateInput(value, 'email')},
    {name: 'address', text: 'Address', type: 'textarea'},
    {name: 'phone', text: 'Phone Number', type: 'text', validate: value => validateInput(value, 'phone-number')}
  ])

  if (!customer['accepted'])
    return

  let response = await sendJsonPostRequest(apiBaseUrl + "/customers/add", {
    "customer-name": customer['data']['name'],
    "phone-number": customer['data']['phone'],
    "email": customer['data']['email'],
    "address": customer['data']['address']
  }, window.httpHeaders)

  if (response.status === "success") {
    getCustomers()
    window.successNotification('Add New Customer', response.message)
  } else {
    window.errorNotification('Add New Customer', response.message)
  }
}

let typingTimer;
let doneTypingInterval = 500

async function getCustomersWithParams(params){
  let customerName = params['paramOne']
  let phoneNumber = parseInt(params['paramTwo'])
  let email = params['paramThree']
  let cusId = parseInt(params['paramFour'])
  let branchId = parseInt(params['paramFive'])
  let address  = params['paramSix']
  let joinedDate = params['paramSeven']

  if(customerName == ''){
    customerName = null
  }
  if(phoneNumber == ''){
    phoneNumber = null
  }
  if(email == ''){
    email = null
  }
  if(!Number.isInteger(cusId)){
    cusId = null
  }
  if(!Number.isInteger(branchId)){
    branchId = null
  }
  if(address == ''){
    address = null
  }
  if(joinedDate == ''){
    joinedDate = null
  }

  if(customerName == null && phoneNumber == null && email == null && address == null && cusId == null && branchId == null &&  joinedDate == null){
    getCustomers()
    return
  }

  clearInterval(typingTimer)
  typingTimer = setTimeout(getCustomers(customerName,phoneNumber,email,address,cusId,branchId,joinedDate), doneTypingInterval)
}


async function editCustomer(id) {

  id = parseInt(id.toString())

  let customerData = customersTableRows.value.filter((row) => {
    return row[0] === id
  })[0]

  let customer = await window.addNewForm('Update Customer', 'Update', [
    {name: 'name', text: 'Customer Name', type: 'text', value: customerData[1]},
    {
      name: 'email', text: 'Email', type: 'email', value: customerData[3],
      validate: value => validateInput(value, 'email')
    },
    {
      name: 'phone', text: 'Phone Number', type: 'text', value: customerData[2],
      validate: value => validateInput(value, 'phone-number')
    },
    {name: 'address', text: 'Address', type: 'textarea', value: customerData[4]},
    {
      name: 'banned', text: 'Ban Customer', type: 'select', value: customerData[6].toLowerCase(),
      options: [{value: 'yes', text: 'Yes'}, {value: 'no', text: 'No'}]
    }
  ])

  if (!customer['accepted'])
    return

  let response = await sendJsonPostRequest(apiBaseUrl + "/customers/update", {
    'customer-id': id,
    "customer-name": customer['data']['name'],
    "phone-number": customer['data']['phone'],
    "email": customer['data']['email'],
    "address": customer['data']['address'],
    'banned': ((customer['data']['banned'] === 'yes'))
  }, window.httpHeaders)

  if (response.status === "success") {
    customersTableRows.value.filter((row) => {
      if (row[0] === id) {
        row[1] = customer['data']['name']
        row[2] = customer['data']['phone']
        row[3] = customer['data']['email']
        row[4] = customer['data']['address']
        row[6] = customer['data']['banned']
        return row
      }
    })
    window.successNotification('Update Customer', response.message)
  } else {
    window.errorNotification('Update Customer', response.message)
  }
}

async function deleteCustomer(ids) {
  if(ids.length === 1){
    let confirm = await window.popupConfirmation('Delete Customer',
    'This action is irreversible. Are you sure you want to remove this customer?')
  if (confirm === true) {
    let response = await sendJsonPostRequest(apiBaseUrl + "/customers/delete", {
      'customer-id': ids[0]
    }, window.httpHeaders)

    if (response.status === "success") {
      getCustomers()
      window.successNotification('Delete Customer', response.message)
    } else {
      window.errorNotification('Delete Customer', response.message)
    }
  }
  }else{
    let confirm = await window.popupConfirmation('Delete Customer',
    'This action is irreversible. Are you sure you want to remove these customers?')

    if (confirm === true) {

      ids.forEach(async(id)=>{
        let response = await sendJsonPostRequest(apiBaseUrl + "/customers/delete", {
      'customer-id': id
    }, window.httpHeaders)

    if (response.status === "success") {
      getCustomers()
      window.successNotification('Delete Customer', response.message)
    } else {
      window.errorNotification('Delete Customer', response.message)
    }
      })
    
  }

  }
  
}
</script>

<template>
  <div class="flex justify-between mt-5 mb-3">
    <h3 class="text-2xl font-semibold">Customers</h3>
    <button class="bg-slate-600 text-slate-100 rounded-md py-2 px-3 font-semibold" @click="addNewCustomer">+ New
      Customer
    </button>
  </div>

  <TableComponent :tableColumns="customersTableCol" :tableRows="customersTableRows" :actions="customersTableActions" :deleteMultiple="deleteBtn" :edit="editBtn" :search="searchParam"
                  @remove-customer="deleteCustomer($event)" @edit-customer="editCustomer($event)" @customer-name="getCustomersWithParams($event)"/>

</template>

<style scoped>

</style>