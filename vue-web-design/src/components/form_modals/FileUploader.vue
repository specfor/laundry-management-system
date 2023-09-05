<script setup>

import {Dialog, DialogPanel, TransitionChild, TransitionRoot} from "@headlessui/vue";
import {ref} from "vue";
import {apiBaseUrl} from "../../js-modules/website-constants.js";
import {ChevronUpIcon, ChevronDownIcon, XMarkIcon} from "@heroicons/vue/24/solid/index.js";

let windowClosable = ref(false)
let expanded = ref(true)
let files = ref([])
let queue = ref([])
let show = ref(false)
let showManager = ref(false)
let success = ref(false)
let allowAdd = ref(true)
let title = ref('')
let queueId = 0

window.fileUploadForm = (title_, options) => {
  success.value = false
  title.value = title_
  queueId += 1
  show.value = true
  return new Promise((resolve) => {
    let id = setInterval(() => {
      if (!show.value) {
        clearInterval(id)
        if (success.value) {
          queue.value.push({
            id: queueId,
            progress: 0,
            fileName: "Uploading " + files.value[0].name,
            widthCss: 'width: 0%'
          })
          showManager.value = true
          windowClosable.value = false
        }
        resolve({data: handleFileUpload(options.url, options.uploadName, queueId), accepted: success.value})
      }
    }, 200)
  })
}

function handleFileUpload(url, uploadName, queueId) {
  const xhr = new XMLHttpRequest()

  xhr.upload.addEventListener('progress', (p) => {
    queue.value.forEach((item) => {
      if (item.id === queueId) {
        item.progress = ((p.loaded / p.total) * 100).toFixed(2,)
        item.widthCss = "width: " + ((p.loaded / p.total) * 100).toFixed(2,) + '%'
      }
    })
  })

  xhr.open('POST', url)
  for (const [key, value] of Object.entries(window.httpHeaders)) {
    xhr.setRequestHeader(key, value)
  }
  let fd = new FormData()
  fd.append(uploadName, files.value[0], files.value[0].name)
  xhr.send(fd)

  return new Promise((resolve) => {
    let id = setInterval(() => {
      if (xhr.readyState === xhr.DONE) {
        clearInterval(id)
        if (!queue.value.find((item) => item.progress === 100))
          windowClosable.value = true
        if (xhr.status === 200) {
          let data = JSON.parse(xhr.responseText);
          resolve(data);
        } else
          resolve(null)
      }
    }, 100)
  })
}

function closeManager() {
  showManager.value = false
  queue.value = []
}
</script>

<template>
  <div v-show="showManager">
    <div class="fixed right-0 bottom-0 z-[5] bg-blue-800 w-[400px]">
      <div class="flex bg-blue-950 pl-3 text-slate-400 justify-between">
        <h4 class="font-bold">Upload/Download Manager</h4>
        <div class="flex">
          <ChevronDownIcon class="w-6 h-6 cursor-pointer hover:bg-black" @click="expanded = !expanded"
                           v-show="expanded"/>
          <ChevronUpIcon class="w-6 h-6 cursor-pointer hover:bg-black" @click="expanded = !expanded"
                         v-show="!expanded"/>
          <XMarkIcon v-if="windowClosable" class="w-6 h-6 cursor-pointer hover:bg-black" @click="closeManager"/>
        </div>
      </div>
      <div class="px-1 py-1" v-if="expanded">
        <div v-for="queueElement in queue" class="border border-black px-2 mb-1">
          <div class="flex justify-between">
            <h6 class="font-semibold text-black">{{ queueElement.fileName }}</h6>
            <h6 class="font-semibold text-black">{{ queueElement.progress }}%</h6>
          </div>
          <div class="relative h-2 mb-1">
            <div class="w-full h-2 bg-blue-500 rounded-lg absolute"></div>
            <div :style="queueElement.widthCss" class="h-2 bg-black rounded-lg absolute"></div>
          </div>
        </div>
      </div>
    </div>
  </div>

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
                class="transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all
                 sm:my-8 sm:w-full sm:max-w-md">
              <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                <div class="text-center text-2xl font-bold mb-5 ">{{ title }}</div>
                <div class="ml-6">
                  <input
                      class="col-span-2 border-2 border-slate-400 rounded-md px-3 hover:border-slate-700
                         py-0.5 hover:bg-slate-100 focus:bg-slate-200 disabled:border"
                      :disabled="false" type="file" @input="(event) => {files = event.target.files;}">
                </div>
              </div>
              <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                <button type="button" :disabled="allowAdd ? false : 'disabled'"
                        class="inline-flex w-full justify-center rounded-md bg-blue-600 px-3 py-2 text-sm font-semibold
                         text-white shadow-sm hover:bg-blue-500 sm:ml-3 sm:w-auto disabled:hover:bg-blue-200 disabled:bg-blue-200"
                        @click="show = false; success = true;">Upload
                </button>
                <button type="button"
                        class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold
                         text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto"
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

<style scoped>

</style>