<template>
  <TransitionRoot as="template" :show="show">
    <Dialog as="div" class="relative z-10">
      <TransitionChild as="template" enter="ease-out duration-300" enter-from="opacity-0" enter-to="opacity-100"
                       leave="ease-in duration-200" leave-from="opacity-100" leave-to="opacity-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"/>
      </TransitionChild>

      <div class="fixed inset-0 z-10 overflow-y-auto">
        <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
          <TransitionChild as="template" enter="ease-out duration-300"
                           enter-from="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                           enter-to="opacity-100 translate-y-0 sm:scale-100" leave="ease-in duration-200"
                           leave-from="opacity-100 translate-y-0 sm:scale-100"
                           leave-to="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
            <DialogPanel
                class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all
                 sm:my-8 sm:w-full sm:max-w-6xl">
              <div class="bg-white px-5 pb-4 pt-2 sm:pt-3 sm:pb-4">
                <div class="text-center text-2xl font-bold mb-5 ">Order Details</div>
                <div class="grid grid-cols-3">
                  <div class="pr-3 flex flex-col">
                    <div class="grid grid-cols-3 mb-1">
                      <div class="font-semibold text-slate-700 py-0.5">Order Id</div>
                      <div class="font-semibold text-slate-700 py-0.5 border border-slate-400 rounded-md px-3
                          py-0.5 hover:bg-slate-100 focus:bg-slate-200 col-span-2">{{ orderDetails['order_id'] }}</div>
                    </div>
                    <div class="grid grid-cols-3 mb-1">
                      <div class="font-semibold text-slate-700 py-0.5">Customer Name</div>
                      <div class="font-semibold text-slate-700 py-0.5 border border-slate-400 rounded-md px-3
                          py-0.5 hover:bg-slate-100 focus:bg-slate-200 col-span-2">{{ orderDetails['customer_name'] }}</div>
                    </div>
                    <div class="grid grid-cols-3 mb-1">
                      <div class="font-semibold text-slate-700 py-0.5">Added On</div>
                      <div class="font-semibold text-slate-700 py-0.5 border border-slate-400 rounded-md px-3
                          py-0.5 hover:bg-slate-100 focus:bg-slate-200 col-span-2">{{ orderDetails['added_date'] }}</div>
                    </div>
                    <div class="grid grid-cols-3 mb-1">
                      <div class="font-semibold text-slate-700 py-0.5">Value (LKR)</div>
                      <div class="font-semibold text-slate-700 py-0.5 border border-slate-400 rounded-md px-3
                        py-0.5 hover:bg-slate-100 focus:bg-slate-200 col-span-2">{{ orderDetails['total_price'] }}</div>
                    </div>
                    <div class="grid grid-cols-3 mb-1">
                      <div class="font-semibold text-slate-700 py-0.5">Customer Comments</div>
                      <div class="font-semibold text-slate-700 py-0.5 border border-slate-400 rounded-md px-3
                          py-0.5 hover:bg-slate-100 focus:bg-slate-200 col-span-2">{{ orderDetails['comments'] ?? 'None' }}</div>
                    </div>
                  </div>
                  <div class="pl-2 col-span-2">
                    <div>
                      <h5 class="font-semibold text-slate-700 my-1">Products</h5>
                      <div class="w-full overflow-x-auto">
                        <table class="table-auto border-collapse border w-full">
                          <thead class="bg-slate-300">
                          <tr>
                            <th class="w-[150px]">Name</th>
                            <th class="w-[60px]">Price</th>
                            <th class="w-[170px]">Actions</th>
                            <th class="w-[80px]">Quantity</th>
                            <th>Defects</th>
                            <th class="w-[100px]">Return Date</th>
                          </tr>
                          </thead>
                          <tbody>
                          <tr v-if="orderDetails['items'].length === 0">
                            <td class="text-center" colspan="100%">No products are added.</td>
                          </tr>
                          <tr v-for="(item, i) in orderDetails['items']" :key="i" class="max-h-[400px] overflow-y-auto">
                            <td>{{ item['item_name'] }}</td>
                            <td>{{ item['price'] }}</td>
                            <td>{{ item['actions'] }}</td>
                            <td class="flex justify-center">{{ item['amount'] }}</td>
                            <td>{{ ((item['defects'].join(', ') !== '') ? item['defects'].join(', ') : 'None') }}</td>
                            <td>{{ item['return-date'] }}</td>
                          </tr>
                          </tbody>
                        </table>
                      </div>
                    </div>
                    <div class="font-semibold text-yellow-700 py-0.5">* Product prices are as how they were at placing the order.</div>
                  </div>
                </div>
              </div>
              <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                <button type="button"
                        class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm
                         font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto"
                        @click="show = false" ref="cancelButtonRef">Close
                </button>
              </div>
            </DialogPanel>
          </TransitionChild>
        </div>
      </div>
    </Dialog>
  </TransitionRoot>
</template>

<script setup>
import {Dialog, DialogPanel, TransitionChild, TransitionRoot} from '@headlessui/vue'
import {ref} from "vue";

let show = ref(false)
let success = ref(false)
let orderDetails = ref({})

window.orderDetailsModal = (orderData) => {
  orderDetails.value = orderData
  show.value = true
}
</script>

<style scoped>

</style>