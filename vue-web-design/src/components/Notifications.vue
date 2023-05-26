<template>
  <div class="fixed right-4 top-4 z-30">
    <TransitionGroup type="transition" enter-from-class="opacity-0" enter-to-class="opacity-100" duration="5">
      <div class="space-y-3 items-end flex flex-col">
        <div v-for="(notification, i) in notifications" :key="i">
          <div v-if="notification[0] === 'success'" class="flex p-2 bg-green-400/80 rounded-lg space-x-5 w-fit">
            <CheckCircleIcon class="fill-green-800 w-[50px] h-[50px]"/>
            <div>
              <div class="text-xl font-semibold text-green-900">{{ notification[1] }}</div>
              <div class="text-sm text-green-900">{{ notification[2] }}</div>
            </div>
            <XMarkIcon class="stroke-green-800 stroke-2 w-[25px] h-[25px] hover:border-2 hover:border-green-600 mt-1 justify-self-end"
                       @click="removeNotification(i)"/>
          </div>
          <div v-else-if="notification[0] === 'error'" class="flex p-2 bg-rose-400/80 rounded-lg space-x-5 w-fit">
            <ExclamationCircleIcon class="fill-rose-800 w-[50px] h-[50px]"/>
            <div>
              <div class="text-xl font-semibold text-rose-900">{{ notification[1] }}</div>
              <div class="text-sm text-rose-900">{{ notification[2] }}</div>
            </div>
            <XMarkIcon class="stroke-rose-800 stroke-2 w-[25px] h-[25px] hover:border-2 hover:border-rose-600 mt-1 justify-self-end"
                       @click="removeNotification(i)"/>
          </div>
        </div>
      </div>
    </TransitionGroup>
  </div>
</template>

<script setup>
import {defineProps, ref} from "vue";
import {CheckCircleIcon, XMarkIcon, ExclamationCircleIcon} from '@heroicons/vue/24/solid';

let {isOpen, title} = defineProps(['isOpen', 'title'])
let notifications = ref([])

function removeNotification(index) {
  notifications.value.splice(index, 1)
}

window.successNotification = (title, message) => {
  notifications.value.push(['success', title, message])
}
window.errorNotification = (title, message) => {
  notifications.value.push(['error', title, message])
}

</script>

<style scoped>

</style>