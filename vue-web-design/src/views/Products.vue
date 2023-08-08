<script setup>
import TableComponent from '../components/TableComponent.vue'
import {ref} from 'vue'
import {sendGetRequest, sendJsonPostRequest} from "../js-modules/base-functions.js";
import {apiBaseUrl} from "../js-modules/website-constants.js";
import {PencilSquareIcon, TrashIcon} from "@heroicons/vue/24/solid/index.js";
import {validateInput} from "../js-modules/form-validations.js";
import { pushSuccessNotification, pushErrorNotification } from '../stores/notification-store';

let productTableCol = ['Select','Id', 'Product Name', 'Actions', 'Unit Price', 'Modifications']
let productTableRows = ref([])
let productTableActions = [
    {onClickEvent: 'editProduct', btnText: 'Edit', type: 'icon', icon: PencilSquareIcon, iconColor: 'fill-blue-700'},
  ]



let actionTableCol = ['Select','Id', 'Action', 'Modifications']
let actionTableRows = ref([])
let actionTableActions = [
    {onClickEvent: 'editAction', btnText: 'Edit', type: 'icon', icon: PencilSquareIcon, iconColor: 'fill-blue-700'},
]

let deleteBtnAction = [{
  onClickEvent:'removeAction'
}]

let deleteBtnProduct = [{
  onClickEvent:'removeProduct'
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
    pushErrorNotification('Fetch Actions Data', response.message)
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
    pushSuccessNotification('Add New Action', response.message)
  } else {
    pushErrorNotification('Add New Action', response.message)
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
    pushSuccessNotification('Update Action', response.message)
  } else {
    pushErrorNotification('Update Action', response.message)
  }
}

async function deleteAction(ids) {

  if(ids.length === 1){
    let confirm = await window.popupConfirmation('Delete User',
      'This action is irreversible. Are you sure you want to remove this action?')
  if (confirm) {
    let response = await sendJsonPostRequest(apiBaseUrl + "/category/delete", {
      "category-id": ids[0]
    })

    if (response.status === 'success') {
      getActions()
      pushSuccessNotification('Action Removal', response.message)
    } else {
      pushErrorNotification('Action Removal', response.message)
    }
  }
  }else{
    let confirm = await window.popupConfirmation('Delete User',
      'This action is irreversible. Are you sure you want to remove these actions?')
      if (confirm) {
        ids.forEach(async(id)=>{
          let response = await sendJsonPostRequest(apiBaseUrl + "/category/delete", {
      "category-id": id
    })

    if (response.status === 'success') {
      getActions()
      pushSuccessNotification('Action Removal', response.message)
    } else {
      pushErrorNotification('Action Removal', response.message)
    }
        })  
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
    pushErrorNotification('Fetch Product Data', response.message)
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
    {name: 'price', text: 'Unit Price', type: 'number', validate: value => validateInput(value, 'price')}
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
    pushSuccessNotification('Add New Product', response.message)
  } else {
    pushErrorNotification('Add New Product', response.message)
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
    {name: 'price', text: 'Unit Price', type: 'number', value: productData[3], validate: value => validateInput(value, 'price')}
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
    pushSuccessNotification('Update Product', response.message)
  } else {
    pushErrorNotification('Update Product', response.message)
  }
}

async function deleteProduct(ids) {

  if(ids.length === 1){
    let confirm = await window.popupConfirmation('Delete Product',
      'This action is irreversible. Are you sure you want to remove this product?')
  if (confirm === true) {
    let response = await sendJsonPostRequest(apiBaseUrl + "/items/delete", {
      'item-id': ids[0]
    })

    if (response.status === 'success') {
      getProducts()
      pushSuccessNotification('Delete Product', response.message)
    } else {
      pushErrorNotification('Delete Product', response.message)
    }
  }
  }else{
    let confirm = await window.popupConfirmation('Delete Product',
      'This action is irreversible. Are you sure you want to remove these products?')
    if (confirm === true) {
      ids.forEach(async(id)=>{
        let response = await sendJsonPostRequest(apiBaseUrl + "/items/delete", {
      'item-id': id
    })

    if (response.status === 'success') {
      getProducts()
      pushSuccessNotification('Delete Product', response.message)
    } else {
      pushErrorNotification('Delete Product', response.message)
    }
      })
    
  }

  }

  
}

</script>

<template>
  <div class="flex justify-between mt-5 mb-3">
    <h3 class="text-2xl font-semibold">Products</h3>
    <button class="bg-slate-600 text-slate-100 rounded-md py-2 px-3 font-semibold" @click="addNewProduct">+ New Product</button>
  </div>

  <TableComponent :tableColumns="productTableCol" :tableRows="productTableRows" :actions="productTableActions" :deleteMultiple="deleteBtnProduct"
                  @remove-product="deleteProduct($event)" @edit-product="editProduct($event)"/> 

  <div class="flex justify-between mt-7 mb-3">
    <h3 class="text-2xl font-semibold">Actions</h3>
    <button class="bg-slate-600 text-slate-100 rounded-md py-2 px-3 font-semibold" @click="addNewAction">+ New Action</button>
  </div>

  <TableComponent :tableColumns="actionTableCol" :tableRows="actionTableRows" :actions="actionTableActions" :deleteMultiple="deleteBtnAction"
                  @remove-action="deleteAction($event)" @edit-action="editAction($event)"/>
</template>

<style scoped>

</style>