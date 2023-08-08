<template>
  <ModelBase :show="show">
    <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
      <div class="sm:flex sm:items-start">
        <div
          class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
          <ExclamationTriangleIcon class="h-6 w-6 text-red-600" aria-hidden="true" />
        </div>
        <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left">
          <DialogTitle as="h3" class="text-base font-semibold leading-6 text-gray-900">{{ title }}
          </DialogTitle>
          <div class="mt-2">
            <p class="text-sm text-gray-500">{{ message }}</p>
          </div>
        </div>
      </div>
    </div>
    <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
      <button type="button"
        class="inline-flex w-full justify-center rounded-md bg-red-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-500 sm:ml-3 sm:w-auto"
        @click="closeModel(true)">Confirm
      </button>
      <button type="button"
        class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto"
        @click="closeModel(false)" ref="cancelButtonRef">Cancel
      </button>
    </div>
  </ModelBase>
</template>

<script setup>
import { ExclamationTriangleIcon } from '@heroicons/vue/24/solid';
import { DialogTitle } from '@headlessui/vue'
import ModelBase from './ModelBase.vue';

const { show, title, message } = defineProps(['show', 'title', 'message']);

const closeModel = (confirm) => this.$emit('onClose', confirm);
</script>

<style scoped></style>