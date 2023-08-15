<script setup>
import TableComponent from '../components/TableComponent.vue'
import {ref} from 'vue'
import {sendGetRequest, sendJsonPostRequest} from "../js-modules/base-functions.js";
import {apiBaseUrl} from "../js-modules/website-constants.js";


let paymentsTableCol = ['Select','Id', 'Order Id', 'Paid Amount', 'Paid Date', 'Refunded']
let paymentsTableRows = ref([])

let deleteBtn = [{
  onClickEvent:'removePayment'
}]

let editBtn = [{
  onClickEvent:'editPayment'
}]

let searchParam = [{
  paramNumber:'paramOne',
  searchParameter:'Order Id',
  searchParamType:'orderId',
  type:'text'
},{
  paramNumber:'paramTwo',
  searchParameter:'Payment Id',
  searchParamType:'orderId',
  type:'text'
},{
  paramNumber:'paramThree',
  searchParameter:'Added date',
  searchParamType:'orderId',
  type:'date'
}]

async function getPayments(paramOne=null,paramTwo=null,paramThree=null) {

  let params = {}

  if(paramOne){
    params["order-id"] = paramOne
  }

  if(paramTwo){
    params['payment-id'] = paramTwo
  }

  if(paramThree){
    params['paid-date'] = paramThree
  }

  let response = await sendGetRequest(apiBaseUrl + "/payments",params)

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

let typingTimer;
let doneTypingInterval = 500;

async function searchPayment(params){

  let orderId = parseInt(params['paramOne'])
  let paymentId = parseInt(params['paramTwo'])
  let paidDate = params['paramThree']

  if(!Number.isInteger(orderId)){
    orderId = null
  }

  if(!Number.isInteger(paymentId)){
    paymentId = null
  }

  if(paidDate == ''){
    paidDate = null
  }
  
  if(paidDate == null && orderId == null && paymentId == null){
    getPayments()
  }

  clearInterval(typingTimer)
  typingTimer = setTimeout(getPayments(orderId,paymentId,paidDate), doneTypingInterval)

 
}

async function editPayment(id) {

  id = parseInt(id.toString())

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

  <TableComponent :tableColumns="paymentsTableCol" :tableRows="paymentsTableRows" :actions="paymentsTableActions" :deleteMultiple="deleteBtn" :edit="editBtn" :search="searchParam"
                  @remove-payment="deletePayment($event)" @edit-payment="editPayment($event)" @orderId="searchPayment($event)" />

</template>

<style scoped>

</style>