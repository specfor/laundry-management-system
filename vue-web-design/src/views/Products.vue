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
  let response = await sendGetRequest(apiBaseUrl + "/category", '', window.httpHeaders)

  if (response.status === 200) {
    let data = await response.json()

    if (data.statusMessage === "success") {
      let categories = data["body"]["categories"];
      for (const category of categories) {
        actionTableRows.value.push([category['category_id'], category['name']])
      }
    } else {
      window.errorNotification('Fetch Actions Data', data.body.message)
    }
  } else {
    window.errorNotification('Fetch Actions Data', 'Something went wrong. Can not fetch actions data.')
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
    "category-name": action['data']['action'],
  }, window.httpHeaders)

  if (response.status === 200) {
    let data = await response.json()

    if (data.statusMessage === "success") {
      actionTableRows.value.push([data.body['category-id'], action['data']['action']])
      window.successNotification('Add New Action', data.body.message)
    } else {
      window.errorNotification('Add New Action', data.body.message)
    }
  } else {
    window.errorNotification('Add New Action', 'Something went wrong. Can not fetch product data.')
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
  }, window.httpHeaders)

  if (response.status === 200) {
    let data = await response.json()

    if (data.statusMessage === "success") {
      actionTableRows.value.filter((row) => {
        if (row[0] === id)
          return row[1] = action['data']['action']
      })
      window.successNotification('Update Action', data.body.message)
    } else {
      window.errorNotification('Update Action', data.body.message)
    }
  } else {
    window.errorNotification('Update Action', 'Something went wrong. Can not fetch product data.')
  }
}

async function deleteAction(id) {
  let confirm = await window.popupConfirmation('Delete User',
      'This action is irreversible. Are you sure you want to remove this action?')
  if (confirm) {
    let response = await sendJsonPostRequest(apiBaseUrl + "/category/delete", {
      "category-id": id
    }, window.httpHeaders)
    if (response.status === 200) {
      let data = await response.json()

      if (data.statusMessage === "success") {
        window.successNotification('Action Removal', data.body.message)
        actionTableRows.value = actionTableRows.value.filter(function (tableRow) {
          return tableRow[0] !== id
        })
        window.successNotification('Action Removal', data.body.message)
      } else {
        window.errorNotification('Action Removal', data.body.message)
      }
    } else {
      window.errorNotification('Action Removal', 'Failed to connect to the servers.')
    }
  }
}

async function getProducts() {
  let response = await sendGetRequest(apiBaseUrl + "/items", '', window.httpHeaders)

  if (response.status === 200) {
    let data = await response.json()
    if (data.statusMessage === "success") {
      let products = data["body"]["items"]

      for (const product of products) {
        let actions = product['categories'].join(', ')
        productTableRows.value.push([product["item_id"], product["name"], actions, product["price"]])
      }
    } else {
      window.errorNotification('Fetch Product Data', data.body.message)
    }
  } else {
    window.errorNotification('Fetch Product Data', 'Something went wrong. Can not fetch product data.')
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
  }, window.httpHeaders)

  if (response.status === 200) {
    let data = await response.json()

    if (data.statusMessage === "success") {
      productTableRows.value.push([data.body['item-id'], product['data']['name'], options.join(', '), product['data']['price']])
      window.successNotification('Add New Product', data.body.message)
    } else {
      window.errorNotification('Add New Product', data.body.message)
    }
  } else {
    window.errorNotification('Add New Product', 'Something went wrong. Can not fetch product data.')
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
  }, window.httpHeaders)

  if (response.status === 200) {
    let data = await response.json()

    if (data.statusMessage === "success") {
      productTableRows.value.filter((row) => {
        if (row[0] === id) {
          row[1] = product['data']['name']
          row[2] = options.join(', ')
          row[3] = product['data']['price']
          return row
        }
      })
      window.successNotification('Update Product', data.body.message)
    } else {
      window.errorNotification('Update Product', data.body.message)
    }
  } else {
    window.errorNotification('Update Product', 'Something went wrong. Can not fetch product data.')
  }
}

async function deleteProduct(id) {
  let confirm = await window.popupConfirmation('Delete Product',
      'This action is irreversible. Are you sure you want to remove this product?')
  if (confirm === true) {
    let response = await sendJsonPostRequest(apiBaseUrl + "/items/delete", {
      'item-id': id
    }, window.httpHeaders)

    if (response.status === 200) {
      let data = await response.json()
      if (data.statusMessage === "success") {
        productTableRows.value = productTableRows.value.filter(function (tableRow) {
          return tableRow[0] !== id
        })
        window.successNotification('Delete Product', data.body.message)
      } else {
        window.errorNotification('Delete Product', data.body.message)
      }
    } else {
      window.errorNotification('Delete Product', 'Something went wrong. Can not fetch product data.')
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