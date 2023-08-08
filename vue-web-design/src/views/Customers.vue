<script setup>
import TableComponent from '../components/TableComponent.vue'
import { ref } from 'vue'
import { sendGetRequest, sendJsonPostRequest } from "../js-modules/base-functions.js";
import { apiBaseUrl } from "../js-modules/website-constants.js";
import { PencilSquareIcon, TrashIcon } from "@heroicons/vue/24/solid/index.js";
import { validateInput } from "../js-modules/form-validations.js";
import { pushSuccessNotification, pushErrorNotification } from '../stores/notification-store';
import { AddNewModel } from "../components/form_modals/AddNewModal.vue";

let customersTableCol = ['Select', 'Id', 'Customer Name', 'Phone Number', 'Email', 'Address', 'Joined Date',
  'Banned', 'Modifications']
let customersTableRows = ref([])
let customersTableActions = [
  { onClickEvent: 'editCustomer', btnText: 'Edit', type: 'icon', icon: PencilSquareIcon, iconColor: 'fill-blue-700' }
]

const modelShow = ref(false);
const modelTitle = ref('');
const modelFields = ref([]);
const modelSuccessButtonText = ref('');
const modelOnClose = ref(null);

let deleteBtn = [{
  onClickEvent: 'removeCustomer'
}]

async function getCustomers() {
  let response = await sendGetRequest(apiBaseUrl + "/customers")

  if (response.status === "success") {
    customersTableRows.value = []
    let customers = response.data["customers"];
    for (const customer of customers) {
      customersTableRows.value.push([customer['customer_id'], customer['name'], customer['phone_num'],
      customer['email'], customer['address'], customer['joined_date'], customer['banned'] ? 'Yes' : 'No'])
    }
  } else {
    pushErrorNotification('Fetch Customer Data', response.message)
  }
}

getCustomers()

async function addNewCustomer() {
  modelTitle.value = 'New Customer';
  modelSuccessButtonText.value = 'Add';
  modelFields.value = [
    { name: 'name', text: 'Customer Name', type: 'text' },
    { name: 'email', text: 'Email', type: 'email', validate: value => validateInput(value, 'email') },
    { name: 'address', text: 'Address', type: 'textarea' },
    { name: 'phone', text: 'Phone Number', type: 'text', validate: value => validateInput(value, 'phone-number') }
  ];

  modelOnClose.value = async ({ accepted, data }) => {
    modelShow.value = false;
    if (!accepted)
      return

    let response = await sendJsonPostRequest(apiBaseUrl + "/customers/add", {
      "customer-name": data['name'],
      "phone-number": data['phone'],
      "email": data['email'],
      "address": data['address']
    }, window.httpHeaders)

    if (response.status === "success") {
      getCustomers()
      pushSuccessNotification('Add New Customer', response.message)
    } else {
      pushErrorNotification('Add New Customer', response.message)
    }
  };
  modelShow.value = true;
}

async function editCustomer(id) {
  let customerData = customersTableRows.value.filter((row) => {
    return row[0] === id
  })[0]

  modelTitle.value = 'Update Customer';
  modelSuccessButtonText.value = 'Update';
  modelFields.value = [
    { name: 'name', text: 'Customer Name', type: 'text', value: customerData[1] },
    {
      name: 'email', text: 'Email', type: 'email', value: customerData[3],
      validate: value => validateInput(value, 'email')
    },
    {
      name: 'phone', text: 'Phone Number', type: 'text', value: customerData[2],
      validate: value => validateInput(value, 'phone-number')
    },
    { name: 'address', text: 'Address', type: 'textarea', value: customerData[4] },
    {
      name: 'banned', text: 'Ban Customer', type: 'select', value: customerData[6].toLowerCase(),
      options: [{ value: 'yes', text: 'Yes' }, { value: 'no', text: 'No' }]
    }
  ];

  modelOnClose.value = async ({ accepted, data }) => {
    modelShow.value = false;
    if (!accepted)
      return

    let response = await sendJsonPostRequest(apiBaseUrl + "/customers/update", {
      'customer-id': id,
      "customer-name": data['name'],
      "phone-number": data['phone'],
      "email": data['email'],
      "address": data['address'],
      'banned': ((data['banned'] === 'yes'))
    }, window.httpHeaders)

    if (response.status === "success") {
      customersTableRows.value.filter((row) => {
        if (row[0] === id) {
          row[1] = data['name']
          row[2] = data['phone']
          row[3] = data['email']
          row[4] = data['address']
          row[6] = data['banned']
          return row
        }

        // I think there is an error here, not sure will check later
      })
      pushSuccessNotification('Update Branch', response.message)
    } else {
      pushErrorNotification('Update Branch', response.message)
    }
  };

  modelShow.value = true;
}

async function deleteCustomer(ids) {
  if (ids.length === 1) {
    let confirm = await window.popupConfirmation('Delete Customer',
      'This action is irreversible. Are you sure you want to remove this customer?')
    if (confirm === true) {
      let response = await sendJsonPostRequest(apiBaseUrl + "/customers/delete", {
        'customer-id': ids[0]
      }, window.httpHeaders)

      if (response.status === "success") {
        getCustomers()
        pushSuccessNotification('Delete Customer', response.message)
      } else {
        pushErrorNotification('Delete Customer', response.message)
      }
    }
  } else {
    let confirm = await window.popupConfirmation('Delete Customer',
      'This action is irreversible. Are you sure you want to remove these customers?')

    if (confirm === true) {

      ids.forEach(async (id) => {
        let response = await sendJsonPostRequest(apiBaseUrl + "/customers/delete", {
          'customer-id': id
        }, window.httpHeaders)

        if (response.status === "success") {
          getCustomers()
          pushSuccessNotification('Delete Customer', response.message)
        } else {
          pushErrorNotification('Delete Customer', response.message)
        }
      })

    }

  }

}

</script>

<template>
  <AddNewModel @onClose="modelOnClose" :show="modelShow" :title="modelTitle" :fields="modelFields" :successBtnText="modelSuccessButtonText">
  </AddNewModel>
  <div class="flex justify-between mt-5 mb-3">
    <h3 class="text-2xl font-semibold">Customers</h3>
    <button class="bg-slate-600 text-slate-100 rounded-md py-2 px-3 font-semibold" @click="addNewCustomer">+ New
      Customer
    </button>
  </div>

  <TableComponent :tableColumns="customersTableCol" :tableRows="customersTableRows" :actions="customersTableActions"
    :deleteMultiple="deleteBtn" @remove-customer="deleteCustomer($event)" @edit-customer="editCustomer($event)" />
</template>

<style scoped></style>