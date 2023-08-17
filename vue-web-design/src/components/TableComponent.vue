<script setup>
import {defineProps, ref} from "vue";

let selectedIds = ref([])
let isDisabled = ref(true)
let isActive = ref(false)
let isEditDisabled = ref(true)
let isEditActive = ref(false)
let searchInput = ref({})
let isHidden = ref(true)
let filterBgColor = ref('bg-white');

function isMultipleChecked() {

  if (selectedIds.value.length === 1) {
    isEditDisabled.value = false
    isEditActive.value = true
  } else {
    isEditDisabled.value = true
    isEditActive.value = false
  }
}

function isChecked() {

  if (selectedIds.value.length === 0) {
    isActive.value = false
    isDisabled.value = true
  } else {
    isActive.value = true
    isDisabled.value = false
  }
}

let {
  tableColumns,
  tableRows,
  actions,
  deleteMultiple,
  edit,
  search
} = defineProps(['tableColumns', 'tableRows', 'actions', 'deleteMultiple', 'edit', 'search'])

let modificationsColum = tableColumns.pop()
</script>

<template>
  <div class="w-full overflow-x-auto">
    <div class="flex justify-between w-full">
      <div class="flex">
        <button :disabled="isDisabled"
                class="rounded-md px-3 py-1 transition duration-300  mb-4 subpixel-antialiased	font-medium	"
                :class="{'bg-red-600 text-white hover:bg-red-700':isActive,'bg-stone-200 text-stone-600':!isActive}"
                @click="()=>{$emit(deleteMultiple[0]['onClickEvent'],selectedIds);isDisabled = true;isActive=false;isEditDisabled=true;isEditActive= false;selectedIds=[] ;if(selectedIds){isDisabled=true} ;
    selectedIds = []}">Delete
        </button>

        <button :disabled="isEditDisabled"
                class="ml-5 rounded-md px-3 py-1 transition duration-300  mb-4 subpixel-antialiased	font-medium"
                :class="{'bg-blue-600 text-white hover:bg-blue-700':isEditActive,'bg-stone-200 text-stone-600':!isEditActive}"
                @click="()=>{$emit(edit[0]['onClickEvent'],selectedIds);isDisabled=true;isEditActive= false;isActive=false;isDisabled=true;selectedIds = [];if(selectedIds){isEditDisabled=true} }">
          Edit
        </button>

      </div>
      <div
          @click="isHidden = !isHidden; filterBgColor === 'bg-white' ? filterBgColor = 'bg-slate-400' : filterBgColor= 'bg-white'"
          class="flex hover:cursor-pointer p-3 rounded-t-lg items-center" :class="filterBgColor">
        <h4 class="font-semibold mr-2">Filters</h4>
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
             class="w-6 h-6">
          <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"/>
        </svg>
      </div>
    </div>

    <div class="relative">
    <div class="block mb-2 bg-slate-400 rounded-l-lg rounded-b-lg w-1/2 py-3 px-3 fixed right-8 z-[1030]" :class="{'hidden':isHidden}">
      <div v-for="(item,i) in search" :key="i" class="grid grid-cols-3 ml-5">
        <h5 v-text="item['searchParameter']" class="flex items-center"></h5>
        <input :type="item.type" class="rounded-md border-2 border-stone-600 bg-stone-200 px-3 h-8 mt-1 col-span-2"
               v-model="searchInput[item.paramNumber]" @input="$emit(item['searchParamType'],searchInput)">
      </div>
    </div>
    </div>
    <table class="table-auto border-collapse border w-full">

      <thead>
      <tr class="border-0 border-y-2 border-t-0 border-slate-500 bg-neutral-300">
        <th class="text-left px-3 pt-4 pb-2 font-bold"
            v-for="(columnName, i) in tableColumns" :key="i">{{ columnName }}
        </th>
        <th class="text-left px-3 pt-4 pb-2 font-bold sticky right-0 bg-neutral-400 text-center w-[200px] max-w-fit">{{
            modificationsColum
          }}
        </th>
      </tr>
      </thead>

      <tbody class="font-semibold">
      <tr v-if="tableRows.length === 0">
        <td colspan="100%" class="text-center pt-2 text-slate-700">No Data To Display.</td>
      </tr>
      <tr v-for="row in tableRows" :key="row[0]" class="border-y border-slate-400 bg-neutral-100 hover:bg-neutral-200">
        <input type="checkbox" class="ml-3" :value="row[0]" v-model="selectedIds"
               v-on:change="()=>{isChecked();isMultipleChecked()}">
        <td v-for="data in row" class="px-3 py-1 text-slate-800">
          <span v-if="data === null || data === ''">None</span>
          <span v-else>{{ data }}</span>
        </td>
        <td v-if="actions" class="px-3 py-1 sticky right-0 bg-neutral-300 flex items-center justify-center h-full">
          <div v-for="action in actions">
            <Component v-if="action['type'] === 'icon'" :is="action['icon']" class="w-6 cursor-pointer mx-1"
                       :class="action['iconColor']" @click="$emit(action['onClickEvent'], row[0])"/>
            <button v-else class="py-0.5 my-0.5 mx-1 bg-blue-900/60 hover:bg-blue-900/80 rounded-md px-3 text-slate-100"
                    @click="$emit(action['onClickEvent'], row[0])">{{ action['btnText'] }}
            </button>
          </div>
        </td>
      </tr>
      </tbody>
    </table>
  </div>
</template>

<style scoped>

</style>