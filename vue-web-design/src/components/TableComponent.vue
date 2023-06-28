<template>
  <div class="w-full overflow-x-auto">
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

<script setup>
import {defineProps} from "vue";

let {tableColumns, tableRows, actions} = defineProps(['tableColumns', 'tableRows', 'actions'])

let modificationsColum = tableColumns.pop()
</script>

<style scoped>

</style>