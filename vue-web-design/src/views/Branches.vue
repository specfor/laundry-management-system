<template>
  <div class="flex justify-between mt-5 mb-3">
    <h3 class="text-2xl font-semibold">Branches</h3>
    <button class="bg-slate-600 text-slate-100 rounded-md py-2 px-3 font-semibold" @click="addNewBranch">+ New Branch</button>
  </div>

  <TableComponent :tableColumns="branchesTableCol" :tableRows="branchesTableRows" :actions="branchesTableActions" :deleteMultiple="deleteBtn"
                  @remove-branch="deleteBranch($event)" @edit-branch="editBranch($event)"/>

</template>

<script setup>
import TableComponent from '../components/TableComponent.vue'
import {ref} from 'vue'
import {sendGetRequest, sendJsonPostRequest} from "../js-modules/base-functions.js";
import {apiBaseUrl} from "../js-modules/website-constants.js";
import {PencilSquareIcon} from "@heroicons/vue/24/solid/index.js";
import {validateInput} from "../js-modules/form-validations.js";

let branchesTableCol = ['Select','Id', 'Branch Name', 'Contact Info', 'Modifications']
let branchesTableRows = ref([])
let branchesTableActions = [
    {onClickEvent: 'editBranch', btnText: 'Edit', type: 'icon', icon: PencilSquareIcon, iconColor: 'fill-blue-700'},
]

let deleteBtn = [{
  onClickEvent:'removeBranch'
}]

async function getBranches() {
  let response = await sendGetRequest(apiBaseUrl + "/branches")

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