<template> 

<div class="block rounded-md  w-full">
    <h1  class="text-center text-4xl text-stone-900 subpixel-antialiased font-bold mt-2">Order Details</h1>
    <div v-if="!isThereData">
        <h1 class="text-center text-xl text-stone-800 subpixel-antialiased font-semibold mt-6">NO DATA TO DISPLAY.</h1>
    </div>
    <div v-if="isThereData">
        <div class="flex justify-between mt-5 ">
        <div class="block m-4">
            <div class="felx justify-between">
                <label for="orderId" class="text-lg font-semibold">Order Id:</label>
                <label for="orderId" class="text-stone-700 text-lg font-semibold ml-2">{{ order_Id }}</label>
            </div>
            <div class="felx justify-between mt-3">
                <label for="orderId" class="text-lg font-semibold">Branch Id:</label>
                <label for="orderId" class="text-stone-700 text-lg font-semibold ml-2">{{ branchId }}</label>
            </div>
            <div class="felx justify-between mt-3">
                <label for="orderId" class="text-lg font-semibold">Customer Name:</label>
                <label for="orderId" class="text-stone-700 text-lg font-semibold ml-2">{{ customerName }}</label>
            </div>
            <div class="felx justify-between mt-3">
                <label for="orderId" class="text-lg font-semibold">Added On:</label>
                <label for="orderId" class="text-stone-700 text-lg font-semibold ml-2">{{ addedDate }}</label>
            </div>
            <div class="felx justify-between mt-3" >
                <label for="orderId" class="text-lg font-semibold">Value:</label>
                <label for="orderId" class="text-stone-700 text-lg font-semibold ml-2">{{ totalPrice}}</label>
            </div>
            <div class="felx justify-between mt-3" >
                <label for="orderId" class="text-lg font-semibold">Payments:</label>
                <div v-if="payments.length !== 0">
                    <table class="able-auto border-collapset border w-full mt-2">
                    <thead>
                        <tr class="border-0 border-y-2 border-t-0 border-slate-500 bg-neutral-300">
                            <th class="text-left px-3 pt-4 pb-2 font-bold">Id</th>
                            <th class="text-left px-3 pt-4 pb-2 font-bold">Amount</th>
                            <th class="text-left px-3 pt-4 pb-2 font-bold">Date</th>
                            <th class="text-left px-3 pt-4 pb-2 font-bold">Refunded</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="border-y border-slate-400 bg-neutral-100 hover:bg-neutral-200" v-for="payment in payments">
                            <td class="px-3 py-1 text-slate-800">{{ payment['payment_id'] }}</td>
                            <td class="px-3 py-1 text-slate-800">{{ payment['paid_amount'] }}</td>
                            <td class="px-3 py-1 text-slate-800">{{ payment['paid_date'] }}</td>
                            <td class="px-3 py-1 text-slate-800">{{ payment['refunded'] }}</td>
                        </tr>
                    </tbody>
                </table>
                </div>
                <label v-else for="orderId" class="text-stone-700 text-lg font-semibold ml-2">None</label>
                
            </div>
            <div class="felx justify-between mt-3" >
                <label for="orderId" class="text-lg font-semibold">Order Status:</label>
                <label for="orderId" class="text-stone-700 text-lg font-semibold ml-2">{{ orderStatus }}</label>
            </div>
            <div class="felx justify-between mt-3">
                <label for="orderId" class="text-lg font-semibold">Customer Comments:</label>
                <label for="orderId" class="text-stone-700 text-lg font-semibold ml-2">{{ customerComments }}</label>
            </div>
            
        </div>
        <div class="block m-4">
            <table class="able-auto border-collapset border w-full">
                <thead>
                    <tr class="border-0 border-y-2 border-t-0 border-slate-500 bg-neutral-300">
                        <th class="text-left px-3 pt-4 pb-2 font-bold">Name</th>
                        <th class="text-left px-3 pt-4 pb-2 font-bold">Price</th>
                        <th class="text-left px-3 pt-4 pb-2 font-bold">Actions</th>
                        <th class="text-left px-3 pt-4 pb-2 font-bold">Quantity</th>
                        <th class="text-left px-3 pt-4 pb-2 font-bold">Defects</th>
                        <th class="text-left px-3 pt-4 pb-2 font-bold">Return Date</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="item in itemList" class="border-y border-slate-400 bg-neutral-100 hover:bg-neutral-200">
                        <td class="px-3 py-1 text-slate-800">{{ item['item_name'] }}</td>
                        <td class="px-3 py-1 text-slate-800">{{ item['price'] }}</td>
                        <td class="px-3 py-1 text-slate-800">{{
                            formatDict(item)
                         }}</td>
                        <td class="px-3 py-1 text-slate-800">{{ item['amount'] }}</td>
                        <td class="px-3 py-1 text-slate-800">{{ nullValueFix(item['defects']) }}</td>
                        <td class="px-3 py-1 text-slate-800">{{ item['return-date'] }}</td>

                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    </div>
    
</div>
  
</template>

<script setup>
import { useRoute } from "vue-router";
import {sendGetRequest, sendJsonPostRequest} from "../js-modules/base-functions.js";
import {ref} from "vue";
import { apiBaseUrl } from "../js-modules/website-constants";

const router = useRoute()
let isThereData = ref(false)
let order_Id = ref('')
let customerName = ref('')
let addedDate = ref('')
let customerComments = ref('')
let totalPrice = ref('')
let branchId = ref('')
let payments = ref([])
let itemList = ref([])
let orderStatus = ref([])

moreOrderInfo()

function formatDict(dict){
    let dictOne = []
    dict['categories']['categories'].forEach((action)=>{
        dictOne.push(action['name'])
    });
    return dictOne.toString()
}

function nullValueFix(defects){
    if(defects[0] == null){
        return 'None'
    }else{
        return defects.toString()
    }
}

async function moreOrderInfo(){
    let orderId = router.params.id
    if(/^\d+$/.test(orderId)){
        if(parseInt(orderId) === 0){
            isThereData.value = false
            return
        }
        let response = await sendGetRequest(apiBaseUrl + '/orders',{
            'order-id':orderId
        })
        if(response.status === 'success'){
            if(response.data.orders.length === 0){
                isThereData.value = false
                return
            }else{
                order_Id.value = response.data.orders[0]['order_id']
                customerName.value = response.data.orders[0]['customer_name']
                addedDate.value = response.data.orders[0]['added_date']
                totalPrice.value = response.data.orders[0]['total_price']
                customerComments.value = response.data.orders[0]['comments']
                branchId.value = response.data.orders[0]['branch_id']
                payments.value = response.data.orders[0]['payments']
                itemList.value = response.data.orders[0]['items']
                orderStatus.value = response.data.orders[0]['status']
                isThereData.value = true
            }
        }
    }else{
        isThereData.value = false

    }

}


</script>

<style>

</style>