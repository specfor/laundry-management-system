<template>
  <table class="table-auto border-collapse border w-full">
    <thead>
    <tr>
      <th class="border-2 border-slate-500 bg-slate-600 text-left px-3"
          v-for="(columnName, i) in tableColumns" :key="i">{{ columnName }}
      </th>
    </tr>
    </thead>
    <tbody>
    <tr v-if="tableRows.length === 0">
      <td>No Data To Display.</td>
    </tr>
    <tr v-for="row in tableRows" :key="row[0]">
      <td v-for="data in row" class="border border-slate-500 bg-slate-700 px-3">
        <span v-if="data === null">None</span>
        <span v-else>{{ data }}</span>
      </td>
      <td v-if="actions">
        <button v-for="action in actions" @click="$emit(action['onClickEvent'], row[0])">{{ action['btnText'] }}
        </button>
      </td>
    </tr>
    </tbody>
  </table>
</template>

<script setup>
import {defineProps} from "vue";

let {tableColumns, tableRows, actions} = defineProps(['tableColumns', 'tableRows', 'actions'])
</script>

<style scoped>

</style>