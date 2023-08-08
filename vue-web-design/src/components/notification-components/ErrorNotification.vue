<template>
    <div class="flex p-2 bg-rose-400/90 rounded-lg space-x-5 w-max">
        <ExclamationCircleIcon class="fill-rose-800 w-[50px] h-[50px]" />
        <div>
            <div class="text-xl font-semibold text-rose-900">{{ notification['value'][1] }}</div>
            <div class="text-sm text-rose-900">{{ notification['value'][2] }}</div>
        </div>
        <XMarkIcon
            class="stroke-rose-800 stroke-2 w-[25px] h-[25px] hover:border-2 hover:border-rose-600 mt-1 justify-self-end"
            @click="$emit('click')" />
    </div>
</template>

<script setup>
import { XMarkIcon, ExclamationCircleIcon } from '@heroicons/vue/24/solid';

const { notification } = defineProps([notification]);
</script>