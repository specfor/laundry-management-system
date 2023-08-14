<template>
  <div class="flex justify-between mt-5 mb-3">
    <h3 class="text-2xl font-semibold">Branches</h3>
    <button class="bg-slate-600 text-slate-100 rounded-md py-2 px-3 font-semibold" @click="addNewBranch">+ New Branch</button>
  </div>

  <TableComponent :edit="editBtn" :tableColumns="branchesTableCol" :tableRows="branchesTableRows" :actions="branchesTableActions" :deleteMultiple="deleteBtn"
                  @remove-branch="deleteBranch($event)" @edit-branch="editBranch($event)" :search="searchParam" @branch-name="getBranchesWithParams"/>

</template>

<script setup>
import TableComponent from '../components/TableComponent.vue'
import {ref} from 'vue'
import {sendGetRequest, sendJsonPostRequest} from "../js-modules/base-functions.js";
import {apiBaseUrl} from "../js-modules/website-constants.js";
import {validateInput} from "../js-modules/form-validations.js";


let branchesTableCol = ['Select','Id', 'Branch Name', 'Contact Info']
let branchesTableRows = ref([])


let deleteBtn = [{
  onClickEvent:'removeBranch'
}]

let editBtn = [{
  onClickEvent:'editBranch'
}]

let searchParam = [{
  paramNumber:'paramOne',
  searchParameter:'Branch Name',
  searchParamType:'branchName',
  type:'text'
},{
  paramNumber:'paramTwo',
  searchParameter:"phone-number",
  searchParamType:'branchName',
  type:'number',
},{
  paramNumber:'paramThree',
  searchParameter:"Branch Id",
  searchParamType:'branchName',
  type:'number',
}]

let typingTimer;
let doneTypingInterval = 500;

async function getBranchesWithParams(params){
  let branchName = params['paramOne']
  let phoneNumber = parseInt(params['paramTwo'])
  let branchId = parseInt(params['paramThree'])

  clearInterval(typingTimer)
  typingTimer = setTimeout(getBranches(null,null,branchId), doneTypingInterval)

  if(branchName && Number.isInteger(phoneNumber)){
    clearInterval(typingTimer)
    typingTimer = setTimeout(getBranches(branchName,phoneNumber,branchId), doneTypingInterval)
  }else if(branchName){
    clearInterval(typingTimer)
    typingTimer = setTimeout(getBranches(branchName,null,branchId), doneTypingInterval)
  }else if(Number.isInteger(phoneNumber)){
    clearInterval(typingTimer)
    typingTimer = setTimeout(getBranches(null,phoneNumber,branchId), doneTypingInterval)
  }else{
    getBranches()
  }
}

async function getBranches(paramOne=null,paramTwo=null,paramThree=null) {

  let params = {}

  if(paramOne){
    params["branch-name"] = paramOne
  }

  if(paramTwo){
    params['phone-number'] = paramTwo
  }

  if(paramThree){
    params['branch-id'] = paramThree
  }

  let response = await sendGetRequest(apiBaseUrl + "/branches",params)

  if (response.status === "success") {
    branchesTableRows.value = []
    let branches = response.data["branches"];
    for (const branch of branches) {
      branchesTableRows.value.push([branch['branch_id'], branch['name'], branch['phone_num']])
    }
  } else {
    window.errorNotification('Fetch Actions Data', response.message)
  }
}

getBranches()

async function addNewBranch() {
  let branch = await window.addNewForm('New Branch', 'Add', [
    {name: 'name', text: 'Branch Name', type: 'text'},
    {name: 'phone', text: 'Phone Number', type: 'text', validate: value => validateInput(value, 'phone-number')}
  ])

  if (!branch['accepted'])
    return

  let response = await sendJsonPostRequest(apiBaseUrl + "/branches/add", {
    "branch-name": branch['data']['name'],
    "phone-number": branch['data']['phone']
  })

  if (response.status === 'success') {
    getBranches()
    window.successNotification('Add New Branch', response.message)
  } else {
    window.errorNotification('Add New Branch', response.message)
  }
}

async function editBranch(id) {

  id = parseInt(id.toString())

  let branchData = branchesTableRows.value.filter((row) => {
    return row[0] === id
  })[0]

  let branch = await window.addNewForm('Update Branch', 'Update', [
    {name: 'name', text: 'Branch Name', type: 'text', value: branchData[1]},
    {name: 'phone', text: 'Phone Number', type: 'text', value: branchData[2],
      validate: value => validateInput(value, 'phone-number')}
  ])

  if (!branch['accepted'])
    return

  let response = await sendJsonPostRequest(apiBaseUrl + "/branches/update", {
    'branch-id': id,
    "branch-name": branch['data']['name'],
    "phone-number": branch['data']['phone']
  })

  if (response.status === 'success') {
    branchesTableRows.value.filter((row) => {
      if (row[0] === id) {
        row[1] = branch['data']['name']
        row[2] = branch['data']['phone']
        return row
      }
    })
    window.successNotification('Update Branch', response.message)
  } else {
    window.errorNotification('Update Branch', response.message)
  }
}

async function deleteBranch(ids) {

  if(ids.length === 1){
    let confirm = await window.popupConfirmation('Delete Branch',
      'This action is irreversible. Are you sure you want to remove this branch?')
  if (confirm === true) {
    let response = await sendJsonPostRequest(apiBaseUrl + "/branches/delete", {
      'branch-id': ids[0]
    })

    if (response.status === 'success') {
      getBranches()
      window.successNotification('Delete Branch', response.message)
    } else {
      window.errorNotification('Delete Branch', response.message)
    }
  }
  }else{
    let confirm = await window.popupConfirmation('Delete Branch',
      'This action is irreversible. Are you sure you want to remove these branches?')

    if(confirm === true){
      ids.forEach(async(id) => {
        let response = await sendJsonPostRequest(apiBaseUrl + "/branches/delete", {
      'branch-id': id
    })

    if (response.status === 'success') {
      getBranches()
      window.successNotification('Delete Branch', response.message)
    } else {
      window.errorNotification('Delete Branch', response.message)
    }
      });
    }  
  }
  
}
</script>

<style scoped>

</style>