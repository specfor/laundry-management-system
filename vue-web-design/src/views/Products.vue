<script setup>
import TableComponent from '../components/TableComponent.vue'
import {ref} from 'vue'
import {sendGetRequest, sendJsonPostRequest} from "../js-modules/base-functions.js";
import {apiBaseUrl} from "../js-modules/website-constants.js";

let productTableCol = ['Id', 'Product Name', 'Actions', 'Unit Price', 'Modifications']
let productTableRows = ref([])
let productTableActions = [{onClickEvent: 'editProduct', btnText: 'Edit'}, {
  onClickEvent: 'removeProduct',
  btnText: 'Remove'
}]

let actionTableCol = ['Id', 'Action', 'Modifications']
let actionTableRows = ref([])
let actionTableActions = [{onClickEvent: 'editAction', btnText: 'Edit'}, {
  onClickEvent: 'removeAction',
  btnText: 'Remove'
}]

async function getActions() {
  let response = await sendGetRequest(apiBaseUrl + "/category")

  if (response.status === "success") {
    actionTableRows.value = []
    let categories = response.data["categories"];
    for (const category of categories) {
      actionTableRows.value.push([category['category_id'], category['name']])
    }
  } else {
    window.errorNotification('Fetch Actions Data', response.message)
  }
}

getActions()

async function addNewAction() {
  let action = await window.addNewForm('New Action', 'Add', [
    {name: 'action', text: 'Action Name', type: 'text'}
  ])

  if (!action['accepted'])
    return

  let response = await sendJsonPostRequest(apiBaseUrl + "/category/add", {
    "category-name": action['data']['action']
  })

  if (response.status === 'success') {
    getActions()
    window.successNotification('Add New Action', response.message)
  } else {
    window.errorNotification('Add New Action', response.message)
  }
}

async function editAction(id) {
  let action = await window.addNewForm('Update Action', 'Update', [
    {
      name: 'action', text: 'Action Name', type: 'text', value: actionTableRows.value.filter(function (oneAction) {
        return oneAction[0] === id
      })[0][1]
    }
  ])

  if (!action['accepted'])
    return

  let response = await sendJsonPostRequest(apiBaseUrl + "/category/update", {
    "category-id": id,
    "category-name": action['data']['action']
  })

  if (response.status === 'success') {
    actionTableRows.value.filter((row) => {
      if (row[0] === id)
        return row[1] = action['data']['action']
    })
    window.successNotification('Update Action', response.message)
  } else {
    window.errorNotification('Update Action', response.message)
  }
}

async function deleteAction(id) {
  let confirm = await window.popupConfirmation('Delete User',
      'This action is irreversible. Are you sure you want to remove this action?')
  if (confirm) {
    let response = await sendJsonPostRequest(apiBaseUrl + "/category/delete", {
      "category-id": id
    })

    if (response.status === 'success') {
      getActions()
      window.successNotification('Action Removal', response.message)
    } else {
      window.errorNotification('Action Removal', response.message)
    }
  }
}

async function getProducts() {
  let response = await sendGetRequest(apiBaseUrl + "/items")

  if (response.status === "success") {
    productTableRows.value = []
    let products = response.data["items"]

    for (const product of products) {
      let actions = product['categories'].join(', ')
      productTableRows.value.push([product["item_id"], product["name"], actions, product["price"]])
    }
  } else {
    window.errorNotification('Fetch Product Data', response.message)
  }
}

getProducts()

async function addNewProduct() {
  let optionsArray = []
  for (const action of actionTableRows.value) {
    optionsArray.push({text: action[1], name: action[1]})
  }
  let product = await window.addNewForm('New Product', 'Add', [
    {name: 'name', text: 'Product Name', type: 'text'},
    {name: 'actions', text: 'Actions', type: 'checkbox', options: optionsArray},
    {name: 'price', text: 'Unit Price', type: 'number'}
  ])

  if (!product['accepted'])
    return

  let options = []
  for (const [key, value] of Object.entries(product['data']['actions'])) {
    if (value)
      options.push(key)
  }

  let response = await sendJsonPostRequest(apiBaseUrl + "/items/add", {
    "item-name": product['data']['name'],
    "item-price": [options, parseFloat(product['data']['price'])]
  })

  if (response.status === 'success') {
    getProducts()
    window.successNotification('Add New Product', response.message)
  } else {
    window.errorNotification('Add New Product', response.message)
  }
}

async function editProduct(id) {
  let productData = productTableRows.value.filter((row) => {
    return row[0] === id
  })[0]
  let optionsArray = []
  for (const action of actionTableRows.value) {
    if (productData[2].includes(action[1]))
      optionsArray.push({text: action[1], name: action[1], checked: true})
    else
      optionsArray.push({text: action[1], name: action[1]})
  }
  let product = await window.addNewForm('Update Product', 'Update', [
    {name: 'name', text: 'Product Name', type: 'text', value: productData[1]},
    {name: 'actions', text: 'Actions', type: 'checkbox', options: optionsArray},
    {name: 'price', text: 'Unit Price', type: 'number', value: productData[3]}
  ])

  if (!product['accepted'])
    return

  let options = []
  for (const [key, value] of Object.entries(product['data']['actions'])) {
    if (value)
      options.push(key)
  }

  let response = await sendJsonPostRequest(apiBaseUrl + "/items/update", {
    'item-id': id,
    "item-name": product['data']['name'],
    "item-price": [options, parseFloat(product['data']['price'])]
  })

  if (response.status === 'success') {
    productTableRows.value.filter((row) => {
      if (row[0] === id) {
        row[1] = product['data']['name']
        row[2] = options.join(', ')
        row[3] = product['data']['price']
        return row
      }
    })
    window.successNotification('Update Product', response.message)
  } else {
    window.errorNotification('Update Product', response.message)
  }
}

async function deleteProduct(id) {
  let confirm = await window.popupConfirmation('Delete Product',
      'This action is irreversible. Are you sure you want to remove this product?')
  if (confirm === true) {
    let response = await sendJsonPostRequest(apiBaseUrl + "/items/delete", {
      'item-id': id
    })

    if (response.status === 'success') {
      getProducts()
      window.successNotification('Delete Product', response.message)
    } else {
      window.errorNotification('Delete Product', response.message)
    }
  }
}

</script>

<template>
  <h4>Add New Product</h4>
  <button class="bg-slate-400" @click="addNewProduct">+</button>

  <h3 class="text-2xl font-semibold mb-5">Products</h3>
  <TableComponent :tableColumns="productTableCol" :tableRows="productTableRows" :actions="productTableActions"
                  @remove-product="deleteProduct($event)" @edit-product="editProduct($event)"/>

  <h4>Add New Action</h4>
  <button class="bg-slate-400" @click="addNewAction">+</button>

  <h3 class="text-2xl font-semibold mb-5">Actions</h3>
  <TableComponent :tableColumns="actionTableCol" :tableRows="actionTableRows" :actions="actionTableActions"
                  @remove-action="deleteAction($event)" @edit-action="editAction($event)"/>
</template>

<style scoped>

</style>