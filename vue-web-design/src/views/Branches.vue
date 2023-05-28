<template>
  <h4>Add New Branch</h4>
  <button class="bg-slate-400" @click="addNewBranch">+</button>

  <h3 class="text-2xl font-semibold mb-5">Branches</h3>
  <TableComponent :tableColumns="branchesTableCol" :tableRows="branchesTableRows" :actions="branchesTableActions"
                  @remove-branch="deleteBranch($event)" @edit-branch="editBranch($event)"/>

</template>

<script setup>
import TableComponent from '../components/TableComponent.vue'
import {ref} from 'vue'
import {sendGetRequest, sendJsonPostRequest} from "../js-modules/base-functions.js";
import {apiBaseUrl} from "../js-modules/website-constants.js";

let branchesTableCol = ['Id', 'Branch Name', 'Contact Info', 'Modifications']
let branchesTableRows = ref([])
let branchesTableActions = [{onClickEvent: 'editBranch', btnText: 'Edit'}, {
  onClickEvent: 'removeBranch',
  btnText: 'Remove'
}]

async function getBranches() {
  let response = await sendGetRequest(apiBaseUrl + "/branches", '', window.httpHeaders)

  if (response.status === 200) {
    let data = await response.json()

    if (data.statusMessage === "success") {
      branchesTableRows.value = []
      let branches = data["body"]["branches"];
      for (const branch of branches) {
        branchesTableRows.value.push([branch['branch_id'], branch['name'], branch['phone_num']])
      }
    } else {
      window.errorNotification('Fetch Actions Data', data.body.message)
    }
  } else {
    window.errorNotification('Fetch Actions Data', 'Something went wrong. Can not fetch data.')
  }
}

getBranches()

async function addNewBranch() {
  let branch = await window.addNewForm('New Branch', 'Add', [
    {name: 'name', text: 'Branch Name', type: 'text'},
    {name: 'phone', text: 'Phone Number', type: 'text'}
  ])

  if (!branch['accepted'])
    return

  let response = await sendJsonPostRequest(apiBaseUrl + "/branches/add", {
    "branch-name": branch['data']['name'],
    "phone-number": branch['data']['phone']
  }, window.httpHeaders)

  if (response.status === 200) {
    let data = await response.json()

    if (data.statusMessage === "success") {
      getBranches()
      window.successNotification('Add New Branch', data.body.message)
    } else {
      window.errorNotification('Add New Branch', data.body.message)
    }
  } else {
    window.errorNotification('Add New Branch', 'Something went wrong. Can not fetch data.')
  }
}

async function editBranch(id) {
  let branchData = branchesTableRows.value.filter((row) => {
    return row[0] === id
  })[0]

  let branch = await window.addNewForm('Update Branch', 'Update', [
    {name: 'name', text: 'Branch Name', type: 'text', value: branchData[1]},
    {name: 'phone', text: 'Phone Number', type: 'text', value: branchData[2]}
  ])

  if (!branch['accepted'])
    return

  let response = await sendJsonPostRequest(apiBaseUrl + "/branches/update", {
    'branch-id': id,
    "branch-name": branch['data']['name'],
    "phone-number": branch['data']['phone']
  }, window.httpHeaders)

  if (response.status === 200) {
    let data = await response.json()

    if (data.statusMessage === "success") {
      branchesTableRows.value.filter((row) => {
        if (row[0] === id) {
          row[1] = branch['data']['name']
          row[2] = branch['data']['phone']
          return row
        }
      })
      window.successNotification('Update Branch', data.body.message)
    } else {
      window.errorNotification('Update Branch', data.body.message)
    }
  } else {
    window.errorNotification('Update Branch', 'Something went wrong. Can not fetch data.')
  }
}

async function deleteBranch(id) {
  let confirm = await window.popupConfirmation('Delete Branch',
      'This action is irreversible. Are you sure you want to remove this branch?')
  if (confirm === true) {
    let response = await sendJsonPostRequest(apiBaseUrl + "/branches/delete", {
      'branch-id': id
    }, window.httpHeaders)

    if (response.status === 200) {
      let data = await response.json()
      if (data.statusMessage === "success") {
        getBranches()
        window.successNotification('Delete Branch', data.body.message)
      } else {
        window.errorNotification('Delete Branch', data.body.message)
      }
    } else {
      window.errorNotification('Delete Branch', 'Something went wrong. Can not fetch data.')
    }
  }
}
</script>

<style scoped>

</style>