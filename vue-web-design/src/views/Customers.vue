<template>
  <h4>Add New Customer</h4>
  <button class="bg-slate-400" @click="addNewCustomer">+</button>

  <h3 class="text-2xl font-semibold mb-5">Customers</h3>
  <TableComponent :tableColumns="customersTableCol" :tableRows="customersTableRows" :actions="customersTableActions"
                  @remove-customer="deleteCustomer($event)" @edit-customer="editCustomer($event)"/>

</template>

<script setup>
import TableComponent from '../components/TableComponent.vue'
import {ref} from 'vue'
import {sendGetRequest, sendJsonPostRequest} from "../js-modules/base-functions.js";
import {apiBaseUrl} from "../js-modules/website-constants.js";

let customersTableCol = ['Id', 'Customer Name', 'Phone Number', 'Email', 'Address', 'Joined Date',
  'Banned', 'Modifications']
let customersTableRows = ref([])
let customersTableActions = [{onClickEvent: 'editCustomer', btnText: 'Edit'}, {
  onClickEvent: 'removeCustomer',
  btnText: 'Remove'
}]

async function getCustomers() {
  let response = await sendGetRequest(apiBaseUrl + "/customers")

  if (response.status === "success") {
    customersTableRows.value = []
    let customers = response.data["customers"];
    for (const customer of customers) {
      customersTableRows.value.push([customer['customer_id'], customer['name'], customer['phone_num'],
        customer['email'], customer['address'], customer['joined_date'], customer['banned']])
    }
  } else {
    window.errorNotification('Fetch Customer Data', response.message)
  }
}

getCustomers()

async function addNewCustomer() {
  let customer = await window.addNewForm('New Customer', 'Add', [
    {name: 'name', text: 'Customer Name', type: 'text'},
    {name: 'email', text: 'Email', type: 'email'},
    {name: 'address', text: 'Address', type: 'textarea'},
    {name: 'phone', text: 'Phone Number', type: 'text'}
  ])

  if (!customer['accepted'])
    return

  let response = await sendJsonPostRequest(apiBaseUrl + "/customers/add", {
    "customer-name": customer['data']['name'],
    "phone-number": customer['data']['phone'],
    "email": customer['data']['email'],
    "address": customer['data']['address']
  }, window.httpHeaders)

  if (response.status === 200) {
    let data = await response.json()

    if (data.statusMessage === "success") {
      getCustomers()
      window.successNotification('Add New Customer', data.body.message)
    } else {
      window.errorNotification('Add New Customer', data.body.message)
    }
  } else {
    window.errorNotification('Add New Customer', 'Something went wrong. Can not fetch data.')
  }
}

async function editCustomer(id) {
  let customerData = customersTableRows.value.filter((row) => {
    return row[0] === id
  })[0]
  let banned = 'no'
  if (customerData[6] === true)
    banned = 'yes'

  let customer = await window.addNewForm('Update Customer', 'Update', [
    {name: 'name', text: 'Customer Name', type: 'text', value: customerData[1]},
    {name: 'phone', text: 'Phone Number', type: 'text', value: customerData[2]},
    {name: 'email', text: 'Email', type: 'email', value: customerData[3]},
    {name: 'address', text: 'Address', type: 'textarea', value: customerData[4]},
    {
      name: 'banned', text: 'Ban Customer', type: 'select', value: banned,
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

  if (response.status === 200) {
    let data = await response.json()

    if (data.statusMessage === "success") {
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
      window.successNotification('Update Branch', data.body.message)
    } else {
      window.errorNotification('Update Branch', data.body.message)
    }
  } else {
    window.errorNotification('Update Branch', 'Something went wrong. Can not fetch data.')
  }
}

async function deleteCustomer(id) {
  let confirm = await window.popupConfirmation('Delete Customer',
      'This action is irreversible. Are you sure you want to remove this customer?')
  if (confirm === true) {
    let response = await sendJsonPostRequest(apiBaseUrl + "/customers/delete", {
      'customer-id': id
    }, window.httpHeaders)

    if (response.status === 200) {
      let data = await response.json()
      if (data.statusMessage === "success") {
        getCustomers()
        window.successNotification('Delete Customer', data.body.message)
      } else {
        window.errorNotification('Delete Customer', data.body.message)
      }
    } else {
      window.errorNotification('Delete Customer', 'Something went wrong. Can not fetch data.')
    }
  }
}
</script>

<style scoped>

</style>