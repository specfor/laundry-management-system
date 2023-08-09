<template>
  <div class="flex justify-between mt-5 mb-3">
    <h3 class="text-2xl font-semibold">Orders</h3>
    <button class="bg-slate-600 text-slate-100 rounded-md py-2 px-3 font-semibold" @click="addNewOrder">+ New Order</button>
  </div>

  <TableComponent :tableColumns="ordersTableCol" :tableRows="ordersTableRows" :actions="ordersTableActions"
                  @remove-order="deleteOrder($event)" @edit-orders="editOrder($event)"
                  @more-info="moreOrderInfo($event)"
                  :deleteMultiple="deleteBtn"
                  :edit="editBtn"/>
  <NewOrderModal/>
  <OrderDetailsModal/>
</template>

<script setup>
import {PencilSquareIcon} from '@heroicons/vue/24/solid'
import TableComponent from '../components/TableComponent.vue'
import NewOrderModal from '../components/form_modals/NewOrder.vue'
import OrderDetailsModal from '../components/form_modals/OrderDetails.vue'
import {ref} from 'vue'
import {sendGetRequest, sendJsonPostRequest} from "../js-modules/base-functions.js";
import {apiBaseUrl} from "../js-modules/website-constants.js";
import { validateInput } from '../js-modules/form-validations'

let ordersTableCol = ['Select','Id', 'Value (LKR)', 'Customer', 'Products', 'Status', 'Branch', 'Added On', 'Comments',
  'Modifications']
let ordersTableRows = ref([])
let ordersTableActions = [
  {onClickEvent: 'moreInfo', btnText: 'More Info'},
]

let editBtn = [{
  onClickEvent:'editOrders'
}]

let deleteBtn = [{
  onClickEvent:'remove-order'
}]

let productArray = {}
let orders = []
let actions = []
let products = []

async function getOrders() {
  actions = []
  let response = await sendGetRequest(apiBaseUrl + "/orders")

  if (response.status === 'success') {
    ordersTableRows.value = []
    orders = response.data["orders"];
    for (const order of orders) {
      ordersTableRows.value.push([order['order_id'], order['total_price'], order['customer_name'], 'See "More Info"',
        order['status'], order['branch_id'], order['added_date'], order['comments']])
      productArray[order['order_id']] = order['items']
    }
  } else {
    window.errorNotification('Fetch Payment Data', response.message)
  }

  response = await sendGetRequest(apiBaseUrl + "/category")

  if (response.status === "success") {
      let data = response.data["categories"];
      for (const action of data) {
          
          actions.push({text: action['name'], name: action['category_id']})
      }
  } else {
      window.errorNotification('Fetch Actions Data', response.message)
  }

  response = await sendGetRequest(apiBaseUrl + "/items")

  if (response.status === "success") {
      let data = response.data["items"];
      for (const product of data) {
          products.push({text: product['name'], value: product['item_id'], actions: product['categories']})
      }
  } else {
      window.errorNotification('Fetch Product Data', response.message)
  }
}

getOrders()

async function moreOrderInfo(id) {
  let orderData = orders.filter((row) => {
    if (row['order_id'] === id){
        for (let item of row['items']) {
            item['actions'] = products.find((product)=>{
                return product['value'] === item['item_id']
            })['actions'].join(', ')
        }
        return row
    }
  })[0]
  window.orderDetailsModal(orderData)
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
    {name: 'phone', text: 'Phone Number', type: 'number',validate:value => validateInput(value,'phone-number')},
    {name: 'email', text: 'Email', type: 'email',validate:value => validateInput(value,'email')},
    {name: 'address', text: 'Address', type: 'textarea'}
  ])



  if (!customer['accepted'])
    return

  if (!customer.data['customer']){

    
    let response = await sendJsonPostRequest(apiBaseUrl + "/customers/add", {
    "customer-name": customer['data']['name'],
    "phone-number": customer['data']['phone'],
    "email": customer['data']['email'],
    "address": customer['data']['address']
  }, window.httpHeaders)

  if (response.status === "success") {
    window.successNotification('Add New Customer', response.message)
  } else {
    window.errorNotification('Add New Customer', response.message)
  }
    
    return
  }

  let order = await window.newOrderModal('New Order',products, actions, {
    'customer': customerResponse.data['customers'].filter((row) => {
      return row['customer_id'] === parseInt(customer.data['customer'])
    })[0]['name']
  });

  if (!order['accepted'])
    return

  let items = []
  order.data.products.forEach((product) => {
    let dict = {}
    dict[product['id']] = {
      'amount': product['quantity'],
      'return-date': product['return_date'],
      'defects': [product['defects']] ?? []
    }
    items.push(dict)
  })

  let response = await sendJsonPostRequest(apiBaseUrl + "/orders/add", {
    "items": items,
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
    {name: 'phone', text: 'Phone Number', type: 'number',validate:value => validateInput(value,'phone-number')},
    {name: 'email', text: 'Email', type: 'email',validate:value => validateInput(value,'email')},
    {name: 'address', text: 'Address', type: 'textarea'}
  ])

  if (!customer['accepted'])
    return


    if (!customer.data['customer']){
      let response = await sendJsonPostRequest(apiBaseUrl + "/customers/add", {
    "customer-name": customer['data']['name'],
    "phone-number": customer['data']['phone'],
    "email": customer['data']['email'],
    "address": customer['data']['address']
  }, window.httpHeaders)

  if (response.status === "success") {
    window.successNotification('Add New Customer', response.message)
  } else {
    window.errorNotification('Add New Customer', response.message)
  }
    return
  }

  let order = await window.newOrderModal('Update Order',products, actions, {
    'customer': customerResponse.data['customers'].filter((row) => {
      return row['customer_id'] === parseInt(customer.data['customer'])
    })[0]['name']
  });

  if (!order['accepted'])
    return
  
    let items = []
  order.data.products.forEach((product) => {
    let dict = {}
    dict[product['id']] = {
      'amount': product['quantity'],
      'return-date': product['return_date'],
      'defects': [product['defects']] ?? []
    }
    items.push(dict)
  })

  let response = await sendJsonPostRequest(apiBaseUrl + "/orders/update", {
    "order-id":id,
    "items": items,
    "customer-id": customer.data['customer'],
    "customer-comments": order.data['comment']
  })

  if (response.status === "success") {
    getOrders()
    window.successNotification('Update Payment', response.message)
  } else {
    window.errorNotification('Update Payment', response.message)
  }
}

async function deleteOrder(ids) {
  if(ids.length === 1){
    let confirm = await window.popupConfirmation('Delete Order',
      'This action is irreversible. Are you sure you want to remove this order?')

      if (confirm === true) {
    let response = await sendJsonPostRequest(apiBaseUrl + "/orders/delete", {
      'order-id': ids[0]
    })

    if (response.status === "success") {
      getOrders()
      window.successNotification('Delete order', response.message)
    } else {
      window.errorNotification('Delete order', response.message)
    }
  }
  }else{
    let confirm = await window.popupConfirmation('Delete Order',
      'This action is irreversible. Are you sure you want to remove these orders?')

    if(confirm === true){
      ids.forEach(async(id)=>{
        let response = await sendJsonPostRequest(apiBaseUrl + "/orders/delete", {
      'order-id': id
    })

    if (response.status === "success") {
      getOrders()
      window.successNotification('Delete order', response.message)
    } else {
      window.errorNotification('Delete order', response.message)
    }
      })
    }  
  } 
}
</script>

<style scoped>

</style>