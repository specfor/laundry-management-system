<template>
  <h4>Add New Orders</h4>
  <button class="bg-slate-400" @click="addNewOrder">+</button>

  <h3 class="text-2xl font-semibold mb-5">Orders</h3>
  <TableComponent :tableColumns="ordersTableCol" :tableRows="ordersTableRows" :actions="ordersTableActions"
                  @remove-order="deleteOrder($event)" @edit-orders="editOrder($event)"
                  @more-info="moreOrderInfo($event)"/>
  <NewOrderModal/>
</template>

<script setup>
import TableComponent from '../components/TableComponent.vue'
import NewOrderModal from '../components/NewOrder.vue'
import {ref} from 'vue'
import {sendGetRequest, sendJsonPostRequest} from "../js-modules/base-functions.js";
import {apiBaseUrl} from "../js-modules/website-constants.js";

let ordersTableCol = ['Id', 'Value (LKR)', 'Customer', 'Products', 'Status', 'Branch', 'Added On', 'Comments',
  'Modifications']
let ordersTableRows = ref([])
let ordersTableActions = [{onClickEvent: 'editOrder', btnText: 'Edit'},
  {onClickEvent: 'removeOrder', btnText: 'Remove'}, {onClickEvent: 'moreInfo', btnText: 'More Info'}]
let productArray = {}

async function getOrders() {
  let response = await sendGetRequest(apiBaseUrl + "/orders")

  if (response.status === 'success') {
    ordersTableRows.value = []
    let orders = response.data["orders"];
    for (const order of orders) {
      ordersTableRows.value.push([order['order_id'], order['total_price'], order['customer_name'], 'See "More Info"',
        order['status'], order['branch_id'], order['added_date'], order['comments']])
      productArray[order['order_id']] = order['items']
    }
    console.log(productArray)
  } else {
    window.errorNotification('Fetch Payment Data', response.message)
  }
}

getOrders()

async function moreOrderInfo(id) {
  let orderData = ordersTableRows.value.filter((row) => {
    return row[0] === id
  })[0]

  let order = await window.dataShowModal('Order Info', '', [
    {name: 'id', text: 'Order Id', type: 'number', value: orderData[0]},
    {name: 'value', text: 'Value (LKR)', type: 'number', value: orderData[1]},
    {name: 'customer_name', text: 'Customer Name', type: 'text', value: orderData[2]},
    {type: 'message', text: 'Product prices are as how they were at placing the order.'},
    {name: 'prod', text: 'Products', type: 'number', value: orderData[0]},
    {name: 'status', text: 'Order Status', type: 'text', value: orderData[4]},
    {name: 'branch_id', text: 'Branch Id', type: 'text', value: orderData[5].toString()},
    {name: 'added', text: 'Added On', type: 'text', value: orderData[6]},
  ], true)
}

async function addNewOrder() {
  let customerResponse = await sendGetRequest(apiBaseUrl + "/customers")
  let customers = []

  if (customerResponse.status === "success") {
    let data = customerResponse.data["customers"];
    for (const customer of data) {
      customers.push({text: customer['name'], value: customer['customer_id']})
    }
  } else {
    window.errorNotification('Fetch Customer Data', customerResponse.message)
    return
  }

  let customer = await window.addNewForm('Select Customer', 'Proceed', [
    {name: 'customer', text: 'Customer', type: 'select', options: customers},
    {text: 'New Customer', type: 'heading'},
    {name: 'name', text: 'Customer Name', type: 'text'},
    {name: 'phone', text: 'Phone Number', type: 'number'},
    {name: 'email', text: 'Email', type: 'email'},
    {name: 'address', text: 'Address', type: 'textarea'}
  ])

  let response = await sendGetRequest(apiBaseUrl + "/items")
  let products = []

  if (response.status === "success") {
    let data = response.data["items"];
    for (const product of data) {
      products.push({text: product['name'], value: product['item_id']})
    }
  } else {
    window.errorNotification('Fetch Product Data', response.message)
  }

  response = await sendGetRequest(apiBaseUrl + "/category")
  let actions = []

  if (response.status === "success") {
    let data = response.data["categories"];
    for (const action of data) {
      actions.push({text: action['name'], name: action['category_id']})
    }
  } else {
    window.errorNotification('Fetch Actions Data', response.message)
  }

  if (!customer['accepted'])
    return

  let order = window.newOrderModal(products, actions, {
    'customer': customerResponse.data['customers'].filter((row) => {
      return row['customer_id'] === parseInt(customer.data['customer'])
    })[0]['name']
  });

  console.log(order)
  if (!order['accepted'])
    return

  response = await sendJsonPostRequest(apiBaseUrl + "/orders/add", {
    "customer-id": customer.data['customer'],
    "customer-comments": order.data['comment']
  })

  if (response.status === "success") {
    getOrders()
    window.successNotification('Add New Payment', response.message)
  } else {
    window.errorNotification('Add New Payment', response.message)
  }
}

async function editOrder(id) {
  let paymentData = ordersTableRows.value.filter((row) => {
    return row[0] === id
  })[0]

  let payment = await window.addNewForm('Update Payment', 'Update', [
    {
      name: 'refunded', text: 'Refunded', type: 'select', value: paymentData[4],
      options: [{text: 'Yes', value: 'yes'}, {text: 'No', value: 'no'}]
    }
  ])

  if (!payment['accepted'])
    return

  let response = await sendJsonPostRequest(apiBaseUrl + "/orders/update", {
    'payment-id': id,
    "refunded": (payment['data']['refunded'] === 'yes')
  })

  if (response.status === "success") {
    ordersTableRows.value.filter((row) => {
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

async function deleteOrder(id) {
  let confirm = await window.popupConfirmation('Delete Order',
      'This action is irreversible. Are you sure you want to remove this order?')
  if (confirm === true) {
    let response = await sendJsonPostRequest(apiBaseUrl + "/orders/delete", {
      'order-id': id
    })

    if (response.status === "success") {
      getOrders()
      window.successNotification('Delete order', response.message)
    } else {
      window.errorNotification('Delete order', response.message)
    }
  }
}
</script>

<style scoped>

</style>