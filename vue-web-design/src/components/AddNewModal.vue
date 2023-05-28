<template>
  <TransitionRoot as="template" :show="show">
    <Dialog as="div" class="relative z-10" @close="show = false">
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
                class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg">
              <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                <div class="text-center text-2xl font-bold mb-5 ">{{ title }}</div>
                <div v-for="field in fields">
                  <div v-if="field['type'] === 'select'" class="grid grid-cols-3 mb-1">
                    <div class="font-semibold text-slate-700 py-0.5">
                      {{ field['text'] }}
                    </div>
                    <select
                        class="col-span-2 border-2 border-slate-400 rounded-md hover:border-slate-700 px-3 py-0.5 hover:bg-slate-200"
                        :name="field['name']" :value="fieldValues[field['name']]"
                        @input="event => fieldValues[field['name']] = event.target.value">
                      <option class=""
                              v-for="option in field['options']" :value="option['value']">{{ option['text'] }}
                      </option>
                    </select>
                  </div>
                  <div v-else-if="field['type'] === 'checkbox'" class="grid grid-cols-3 my-2">
                    <div class="font-semibold text-slate-700 py-0.5">
                      {{ field['text'] }}
                    </div>
                    <div class="col-span-2 grid grid-cols-2">
                      <div v-for="option in field['options']" class="flex mt-1">
                        <input class="w-5 h-5 mt-0.5"
                               type="checkbox" :checked="option['checked']"
                               @input="event => fieldValues[field['name']][option['name']] = event.target.checked">
                        <p class="ml-2 font-semibold "
                               >{{ option['text'] }}</p>
                      </div>
                    </div>
                  </div>
                  <div v-else class="grid grid-cols-3 mb-1">
                    <div class="font-semibold text-slate-700 py-0.5">
                      {{ field['text'] }}
                    </div>
                    <input
                        class="col-span-2 border-2 border-slate-400 rounded-md px-3 hover:border-slate-700 py-0.5 hover:bg-slate-200"
                        :type="field['type']" :value="fieldValues[field['name']]"
                        @input="event => fieldValues[field['name']] = event.target.value">
                  </div>
                </div>
              </div>
              <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                <button type="button"
                        class="inline-flex w-full justify-center rounded-md bg-blue-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-500 sm:ml-3 sm:w-auto"
                        @click="show = false; success = true">{{ successBtnText }}
                </button>
                <button type="button"
                        class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto"
                        @click="show = false" ref="cancelButtonRef">Cancel
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
let title = ref('')
let fields = ref([])
let fieldValues = ref({})
let successBtnText = ref('')

window.addNewForm = (title_, successBtn_, fields_) => {
  fieldValues.value = {}
  success.value = false
  title.value = title_
  fields.value = fields_
  successBtnText.value = successBtn_
  for (const field of fields_) {
    if (field['type'] === 'checkbox') {
      fieldValues.value[field['name']] = {}
      for (const option of field['options']) {
        fieldValues.value[field['name']][option['name']] = !!option['checked'];
      }
    } else {
      if (field['value'])
        fieldValues.value[field['name']] = field['value']
      else
        fieldValues.value[field['name']] = ''
    }
  }
  show.value = true
  return new Promise((resolve) => {
    let id = setInterval(() => {
      if (!show.value) {
        clearInterval(id)
        resolve({data: fieldValues.value, accepted: success.value})
      }
    }, 200)
  })
}
</script>

<style scoped>

</style>