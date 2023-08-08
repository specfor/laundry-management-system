<template>
  <ModelBase :show="show">
    <div class="bg-white px-5 pb-4 pt-2 sm:pt-3 sm:pb-4">
      <div class="text-center text-2xl font-bold mb-5 ">{{ typeOfTheOrder }}</div>
      <div class="grid grid-cols-3">
        <div class="pr-3 flex flex-col">
          <div class="border border-slate-400 rounded-md p-2 mb-2">
            <div class="grid grid-cols-3 mb-1">
              <div class="font-semibold text-slate-700 py-0.5">Product</div>
              <select class="col-span-2 border-2 border-slate-400 rounded-md hover:border-slate-700 px-3 py-0.5
                             hover:bg-slate-200 disabled:border" name="product" :value="fieldValues['product']"
                @input="event => fieldValues['product'] = event.target.value">
                <option class="" v-for="product in productNames" :value="product">{{ product }}
                </option>
              </select>
            </div>
            <div class="grid grid-cols-3 mb-1">
              <div class="font-semibold text-slate-700 py-0.5">Quantity</div>
              <input class="col-span-2 border-2 border-slate-400 rounded-md px-3
                            py-0.5 hover:bg-slate-100 focus:bg-slate-200" type="number"
                :value="fieldValues['quantity']" @input="event => fieldValues['quantity'] = event.target.value">
            </div>
            <div class="grid grid-cols-3 my-2">
              <div class="font-semibold text-slate-700 py-0.5">Actions</div>
              <div class="col-span-2 grid grid-cols-2">
                <div v-for="option in actions" class="flex mt-1">
                  <input class="w-5 h-5 mt-0.5" :disabled="option['disabled']" type="checkbox"
                    :checked="option['checked']"
                    @input="event => fieldValues['actions'][option['name']] = event.target.checked">
                  <p class="ml-2 font-semibold ">{{ option['text'] }}</p>
                </div>
              </div>
            </div>
            <div class="grid grid-cols-3 mb-1">
              <div class="font-semibold text-slate-700 py-0.5">Return Date</div>
              <input class="col-span-2 border-2 border-slate-400 rounded-md px-3
                            py-0.5 hover:bg-slate-100 focus:bg-slate-200" type="date"
                :value="fieldValues['return_date']" @input="event => fieldValues['return_date'] = event.target.value">
            </div>
            <div class="grid grid-cols-3 mb-1">
              <div class="font-semibold text-slate-700 py-0.5">Defects</div>
              <input class="col-span-2 border-2 border-slate-400 rounded-md px-3
                            py-0.5 hover:bg-slate-100 focus:bg-slate-200" type="text" :value="fieldValues['defects']"
                @input="event => fieldValues['defects'] = event.target.value">
            </div>
          </div>
          <button class="mb-3 self-end bg-blue-600/90 text-slate-100 w-[150px] p-1 rounded-md hover:bg-blue-500"
            @click="addProduct">Add Product
          </button>
          <div class="grid grid-cols-3 mb-1">
            <div class="font-semibold text-slate-700 py-0.5">Customer Comments</div>
            <textarea class="col-span-2 border-2 border-slate-400 rounded-md px-3
                          py-0.5 hover:bg-slate-100 focus:bg-slate-200 disabled:border"
              :value="fieldValues['comments']" rows="2" @input="event => fieldValues['comments'] = event.target.value" />
          </div>
        </div>
        <div class="pl-2 col-span-2">
          <div class="grid grid-cols-3 mb-1">
            <div class="font-semibold text-slate-700 py-0.5">Customer Name</div>
            <input class="col-span-2 border-2 border-slate-400 rounded-md px-3
                          py-0.5 hover:bg-slate-100 focus:bg-slate-200 disabled:border" disabled type="text"
              :value="fieldValues['customer']" @input="event => fieldValues['customer'] = event.target.value">
          </div>
          <div>
            <h5 class="font-semibold text-slate-700 my-1">Products</h5>
            <div class="w-full overflow-x-auto">
              <table class="table-auto border-collapse border w-full">
                <thead class="bg-slate-300">
                  <tr>
                    <th class="w-[150px]">Name</th>
                    <th class="w-[170px]">Actions</th>
                    <th class="w-[80px]">Quantity</th>
                    <th>Defects</th>
                    <th class="w-[100px]">Return Date</th>
                    <th class="w-7">Modify</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-if="orderProducts['products'].length === 0">
                    <td class="text-center" colspan="100%">No products are added.</td>
                  </tr>
                  <tr v-for="(row, i) in orderProducts['products']" :key="i">
                    <td>{{ row.product_name }}</td>
                    <td>{{ row.actions }}</td>
                    <td class="flex justify-center">{{ row.quantity }}</td>
                    <td>{{ row.defects ?? 'None' }}</td>
                    <td>{{ row.return_date }}</td>
                    <td class="flex">
                      <TrashIcon class="fill-red-700 w-6 h-5 cursor-pointer" @click="removeProduct(i)" />
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
      <button type="button"
        class="inline-flex w-full justify-center rounded-md bg-blue-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-500 sm:ml-3 sm:w-auto"
        @click="closeModel(true)">Place Order
      </button>
      <button type="button"
        class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto"
        @click="closeModel(false)" ref="cancelButtonRef">Cancel
      </button>
    </div>
  </ModelBase>
</template>

<script setup>
import { ref } from "vue";
import { TrashIcon } from '@heroicons/vue/24/solid'
import ModelBase from './ModelBase.vue';

const { show, typeOfTheOrder, actions, values, ...props } = defineProps(['show', 'typeOfTheOrder', 'actions', 'values', 'products']);

let fieldValues = ref(values || {})
let products = ref(props.products)
let productNames = ref([])
let orderProducts = ref({ products: [], comment: null })

function addProduct() {
  let temp_actions = null
  orderProducts.value.products.push({
    id: products.value.filter((row) => {
      if (row['text'] === fieldValues.value['product']) {
        let actions_ = []
        for (const actionId of Object.keys(fieldValues.value['actions'])) {
          actions.value.find((act) => {
            if (fieldValues.value['actions'][actionId] === true)
              if (act['name'] == actionId)
                actions_.push(act['text'])
          })
        }
        if (actions_.toString() === Object.values(row['actions']).toString()) {
          temp_actions = actions_.join(', ')
          return row
        }
      }
    })[0]['value'],
    product_name: fieldValues.value['product'],
    quantity: parseInt(fieldValues.value['quantity']), actions: temp_actions,
    return_date: fieldValues.value['return_date'], defects: fieldValues.value['defects']
  })
  let actions_ = {}
  for (const actionsKey in Object.keys(fieldValues.value['actions'])) {
    actions_[actionsKey] = false
  }
  fieldValues.value = {
    actions: actions_, 
    ...fieldValues.value
  }
}

function removeProduct(index) {
  orderProducts.value.products.splice(index, 1)
}

onMounted(() => {
  for (const product of products) {
    if (!productNames.value.includes(product['text'])) {
      productNames.value.push(product['text'])
    }
  }
});

const closeModel = (accepted) => {
  orderProducts.value.comment = fieldValues.value['comments'];
  this.$emit('onClose', { data: orderProducts.value, accepted });
};
</script>

<style scoped></style>