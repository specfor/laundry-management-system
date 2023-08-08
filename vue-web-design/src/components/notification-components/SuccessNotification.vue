<template>
    <div class="flex p-2 bg-green-400/90 rounded-lg space-x-5 w-max">
        <CheckCircleIcon class="fill-green-800 w-[50px] h-[50px]" />
        <div>
            <div class="text-xl font-semibold text-green-900">{{ notification['value'][1] }}</div>
            <div class="text-sm text-green-900">{{ notification['value'][2] }}</div>
        </div>
        <XMarkIcon
            class="stroke-green-800 stroke-2 w-[25px] h-[25px] hover:border-2 hover:border-green-600 mt-1 justify-self-end"
            @click="$emit('click')" />
    </div>
</template>

<script setup>
import { CheckCircleIcon, XMarkIcon } from '@heroicons/vue/24/solid';

const { notification } = defineProps(['notification']);
</script>