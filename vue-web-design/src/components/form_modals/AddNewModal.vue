<script setup>
import { ref, computed, onMounted } from "vue";
import ModelBase from "./ModelBase.vue";
import ModelInputs from "./ModelInputs.vue";

const { title, successBtnText, show, fields: propFields } = defineProps(['show', 'title', 'fields', 'successBtnText']);

let fields = ref(propFields)
let fieldValues = ref({})

onMounted(() => {
  let fieldValuesObj = {};
  for (const field of fields.value) {
    if (field['type'] === 'checkbox') {
      fieldValuesObj[field['name']] = {}
      for (const option of field['options']) {
        fieldValuesObj[field['name']][option['name']] = !!option['checked'];
      }
    } else {
      if (field['value'])
        fieldValuesObj[field['name']] = field['value']
      else
        fieldValuesObj[field['name']] = ''
    }
    errorMessages.value[field['name']] = ''
  }

  fieldValues.value = fieldValuesObj;
});

// errorMessages object will automatically be recalculated when the fieldValues and fields reference changes
const errorMessages = computed(() => {
  const ret = {};

  for (const field of fields.value) {
    if (fieldValues.value[field['name']].length === 0)
      ret[field['name']] = ''
    else if (field['validate'])
      ret[field['name']] = field['validate'](fieldValues.value[field['name']]) ?? ''
  }

  return ret;
})

const closeModel = (accepted) => this.$emit('onClose', { accepted, data: fieldValues.value });
</script>

<template>
  <ModelBase :show="show">
    <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
      <div class="text-center text-2xl font-bold mb-5 ">{{ title }}</div>
      <ModelInputs v-bind:fields.sync="fields" v-bind:fieldValues.sync="fieldValues" :errorMessages="errorMessages"></ModelInputs>
    </div>
    <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
      <button v-if="successBtnText" type="button"
        class="inline-flex w-full justify-center rounded-md bg-blue-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-500 sm:ml-3 sm:w-auto"
        @click="closeModel(true)">{{ successBtnText }}
      </button>
      <button type="button"
        class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto"
        @click="closeModel(false)" ref="cancelButtonRef">Cancel
      </button>
    </div>
  </ModelBase>
</template>

<style scoped></style>