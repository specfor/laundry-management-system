<template>
  <div class="flex justify-between mt-5 mb-3">
    <h3 class="text-2xl font-semibold">Branches</h3>
    <button class="bg-slate-600 text-slate-100 rounded-md py-2 px-3 font-semibold" @click="addNewCustomer">+ New
      Customer
    </button>
  </div>

  <TableComponent :tableColumns="customersTableCol" :tableRows="customersTableRows" :actions="customersTableActions"
                  @remove-customer="deleteCustomer($event)" @edit-customer="editCustomer($event)"/>

</template>

<script setup>
import TableComponent from '../components/TableComponent.vue'
import {ref} from 'vue'
import {sendGetRequest, sendJsonPostRequest} from "../js-modules/base-functions.js";
import {apiBaseUrl} from "../js-modules/website-constants.js";
import {PencilSquareIcon, TrashIcon} from "@heroicons/vue/24/solid/index.js";
import {validateInput} from "../js-modules/form-validations.js";

let customersTableCol = ['Id', 'Customer Name', 'Phone Number', 'Email', 'Address', 'Joined Date',
  'Banned', 'Modifications']
let customersTableRows = ref([])
let customersTableActions = [
  {onClickEvent: 'editCustomer', btnText: 'Edit', type: 'icon', icon: PencilSquareIcon, iconColor: 'fill-blue-700'},
  {onClickEvent: 'removeCustomer', btnText: 'Remove', type: 'icon', icon: TrashIcon, iconColor: 'fill-red-700'}
]

async function getCustomers() {
  let response = await sendGetRequest(apiBaseUrl + "/customers")

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

async function editCustomer(id) {
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
    window.successNotification('Update Branch', response.message)
  } else {
    window.errorNotification('Update Branch', response.message)
  }
}

async function deleteCustomer(id) {
  let confirm = await window.popupConfirmation('Delete Customer',
    'This action is irreversible. Are you sure you want to remove this customer?')
  if (confirm === true) {
    let response = await sendJsonPostRequest(apiBaseUrl + "/customers/delete", {
      'customer-id': id
    }, window.httpHeaders)

    if (response.status === "success") {
      getCustomers()
      window.successNotification('Delete Customer', response.message)
    } else {
      window.errorNotification('Delete Customer', response.message)
    }
  }
}
</script>

<style scoped>

</style>