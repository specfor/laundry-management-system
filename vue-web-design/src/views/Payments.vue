<script setup>
import TableComponent from '../components/TableComponent.vue'
import {ref} from 'vue'
import {sendGetRequest, sendJsonPostRequest} from "../js-modules/base-functions.js";
import {apiBaseUrl} from "../js-modules/website-constants.js";
import {TrashIcon, PencilSquareIcon} from '@heroicons/vue/24/solid'

let paymentsTableCol = ['Select','Id', 'Order Id', 'Paid Amount', 'Paid Date', 'Refunded', 'Modifications']
let paymentsTableRows = ref([])
let paymentsTableActions = [
  {onClickEvent: 'editPayment', btnText: 'Edit', type: 'icon', icon: PencilSquareIcon, iconColor: 'fill-blue-700'},
]

let deleteBtn = [{
  onClickEvent:'removePayment'
}]

async function getPayments() {
  let response = await sendGetRequest(apiBaseUrl + "/payments")

  if (response.status === 'success') {
    paymentsTableRows.value = []
    let payments = response.data["payments"];
    for (const payment of payments) {
      paymentsTableRows.value.push([payment['payment_id'], payment['order_id'], payment['paid_amount'],
        payment['paid_date'], payment['refunded'] ? 'Yes' : 'No'])
    }
  } else {
    window.errorNotification('Fetch Payment Data', response.message)
  }
}

getPayments()

async function addNewPayment() {
  let employee = await window.addNewForm('New Payment', 'Add', [
    {name: 'order', text: 'Order Id', type: 'number', min: 1},
    {
      name: 'amount', text: 'Paid Amount (LKR)', type: 'number', min: 0, validate: (value) => {
        if (value <= 0)
          return 'Paid amount must be greater than 0'
      }
    }
  ])

  if (!employee['accepted'])
    return

  let response = await sendJsonPostRequest(apiBaseUrl + "/payments/add", {
    "order-id": employee['data']['order'],
    "paid-amount": employee['data']['amount']
  })

  if (response.status === "success") {
    getPayments()
    window.successNotification('Add New Payment', response.message)
  } else {
    window.errorNotification('Add New Payment', response.message)
  }
}

async function editPayment(id) {
  let paymentData = paymentsTableRows.value.filter((row) => {
    return row[0] === id
  })[0]

  let payment = await window.addNewForm('Update Payment', 'Update', [
    {
      name: 'refunded', text: 'Refunded', type: 'select', value: paymentData[4].toLowerCase(),
      options: [{text: 'Yes', value: 'yes'}, {text: 'No', value: 'no'}]
    }
  ])

  if (!payment['accepted'])
    return

  let response = await sendJsonPostRequest(apiBaseUrl + "/payments/update", {
    'payment-id': id,
    "refunded": (payment['data']['refunded'] === 'yes')
  })

  if (response.status === "success") {
    paymentsTableRows.value.filter((row) => {
      if (row[0] === id) {
        row[4] = payment['data']['refunded']
        return row
      }
    })
    window.successNotification('Update Payment', response.message)
  } else {
    window.errorNotification('Update Payment', response.message)
  }
}

async function deletePayment(ids) {

  if(ids.length === 1){
    let confirm = await window.popupConfirmation('Delete Payment',
    'This action is irreversible. Are you sure you want to remove this payment?')
  if (confirm === true) {
    let response = await sendJsonPostRequest(apiBaseUrl + "/payments/delete", {
      'payment-id': ids[0]
    })

    if (response.status === "success") {
      getPayments()
      window.successNotification('Delete Payment', response.message)
    } else {
      window.errorNotification('Delete Payment', response.message)
    }
  }
  }else{
    let confirm = await window.popupConfirmation('Delete Payment',
    'This action is irreversible. Are you sure you want to remove these payments?')

    if (confirm === true) {
      ids.forEach(async(id)=>{
        let response = await sendJsonPostRequest(apiBaseUrl + "/payments/delete", {
      'payment-id': id
    })

    if (response.status === "success") {
      getPayments()
      window.successNotification('Delete Payment', response.message)
    } else {
      window.errorNotification('Delete Payment', response.message)
    }
    
      })    
  }

  }
  
}
</script>

<template>
  <div class="flex justify-between mt-5 mb-3">
    <h3 class="text-2xl font-semibold">Payments</h3>
    <button class="bg-slate-600 text-slate-100 rounded-md py-2 px-3 font-semibold" @click="addNewPayment">+ New
      Payment
    </button>
  </div>

  <TableComponent :tableColumns="paymentsTableCol" :tableRows="paymentsTableRows" :actions="paymentsTableActions" :deleteMultiple="deleteBtn"
                  @remove-payment="deletePayment($event)" @edit-payment="editPayment($event)"/>

</template>

<style scoped>

</style>