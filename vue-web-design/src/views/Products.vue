<script setup>
import TableComponent from '../components/TableComponent.vue'
import {ref} from 'vue'
import {sendGetRequest, sendJsonPostRequest} from "../js-modules/base-functions.js";
import {apiBaseUrl} from "../js-modules/website-constants.js";
import {PencilSquareIcon, TrashIcon} from "@heroicons/vue/24/solid/index.js";
import {validateInput} from "../js-modules/form-validations.js";

let productTableCol = ['Select','Id', 'Product Name', 'Actions', 'Unit Price']
let productTableRows = ref([])



let actionTableCol = ['Select','Id', 'Action']
let actionTableRows = ref([])


let deleteBtnAction = [{
  onClickEvent:'removeAction'
}]

let deleteBtnProduct = [{
  onClickEvent:'removeProduct'
}]

let editBtnProduct = [{
  onClickEvent:'editProduct'
}]

let editBtnAction = [{
  onClickEvent:'editAction'
}]



let typingTimer;
let doneTypingInterval = 500;

let searchParamProduct = [{
  paramNumber:'paramOne',
  searchParameter:'Product Name',
  searchParamType:'productName',
  type:'text'
},{
  paramNumber:'paramTwo',
  searchParameter:'Item Price',
  searchParamType:'productName',
  type:'number'
},{
  paramNumber:'paramThree',
  searchParameter:'Item Id',
  searchParamType:'productName',
  type:'number'
}]

let searchParamAction = [{
  paramNumber:'paramOne',
  searchParameter:'Action Name',
  searchParamType:'actionId',
  type:'text'
},{
  paramNumber:'paramTwo',
  searchParameter:'Page Number',
  searchParamType:'actionId',
  type:"number"
}]

async function getProductsWithParams(params){
  let name = params['paramOne']
  let price = parseInt(params['paramTwo'])
  let id = parseInt(params['paramThree'])

  if(!Number.isInteger(price)){
    price = null
  }

  if(!Number.isInteger(id)){
    id = null
  }

  if(name == ''){
    name = null
  }

  if(name == null && price == null && id == null){
    getProducts()
    return
  }


  clearInterval(typingTimer)
  typingTimer = setTimeout(getProducts(name,price,id), doneTypingInterval)
}

let typingTimerTwo 

async function getActionsWithParams(params){
  let actionName = params['paramOne']
  let pageNum = parseInt(params['paramTwo'])

  if(actionName == ''){
    actionName = null
  }

  if(!Number.isInteger(pageNum)){
    pageNum = null
  }

  if(actionName == null && pageNum == null){
    getActions()
    return
  }

  clearInterval(typingTimerTwo)
  typingTimerTwo = setTimeout(getActions(actionName,pageNum), doneTypingInterval)
}



async function getActions(paramOne=null,paramTwo=null) {

  let params = {}

  if(paramOne){
    params["category-name"] = paramOne
  }

  if(paramTwo){
    params['page-num'] = paramTwo
  }

  let response = await sendGetRequest(apiBaseUrl + "/category",params)

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

  id = parseInt(id.toString())


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
      window.successNotification('Action Removal', response.message)
    } else {
      window.errorNotification('Action Removal', response.message)
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
      window.successNotification('Action Removal', response.message)
    } else {
      window.errorNotification('Action Removal', response.message)
    }
        })  
  }


    }

}

async function getProducts(paramOne=null,paramTwo=null,paramThree = null) {

  let params = {}

  if(paramOne){
    params["item-name"] = paramOne
  }

  if(paramTwo){
    params['item-price'] = paramTwo
  }

  if(paramThree){
    params['item-id'] = paramThree
  }

  let response = await sendGetRequest(apiBaseUrl + "/items",params)

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
    window.successNotification('Add New Product', response.message)
  } else {
    window.errorNotification('Add New Product', response.message)
  }
}

async function editProduct(id) {


  id = parseInt(id.toString())


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

  let arrayOfCheckedActions = []

  Object.keys(product['data']['actions']).forEach((key)=>{
    if(product['data']['actions'][key] === true){
      arrayOfCheckedActions.push(key)
    }
  })

  if(id === productData[0] && product['data']['name'] === productData[1] && arrayOfCheckedActions.toString() === productData[2].replaceAll(", ",",")){

    
    let response = await sendJsonPostRequest(apiBaseUrl + "/items/update", {
    'item-id': id,
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
  }else if(product['data']['name'] !== productData[1]){

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

  }else{

    let response = await sendJsonPostRequest(apiBaseUrl + "/items/update", {
    'item-id': id,
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
      window.successNotification('Delete Product', response.message)
    } else {
      window.errorNotification('Delete Product', response.message)
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
      window.successNotification('Delete Product', response.message)
    } else {
      window.errorNotification('Delete Product', response.message)
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

  <TableComponent :tableColumns="productTableCol" :tableRows="productTableRows" :actions="productTableActions" :deleteMultiple="deleteBtnProduct" :edit="editBtnProduct"
                  @remove-product="deleteProduct($event)" @edit-product="editProduct($event)" :search="searchParamProduct" @product-name="getProductsWithParams($event)"/> 

  <div class="flex justify-between mt-7 mb-3">
    <h3 class="text-2xl font-semibold">Actions</h3>
    <button class="bg-slate-600 text-slate-100 rounded-md py-2 px-3 font-semibold" @click="addNewAction">+ New Action</button>
  </div>

  <TableComponent :tableColumns="actionTableCol" :tableRows="actionTableRows" :actions="actionTableActions" :deleteMultiple="deleteBtnAction" :edit="editBtnAction"
                  @remove-action="deleteAction($event)" @edit-action="editAction($event)" :search="searchParamAction" @action-id="getActionsWithParams($event)"/>
</template>

<style scoped>

</style>