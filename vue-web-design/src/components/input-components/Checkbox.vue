<template>
    <div class="grid grid-cols-3 my-2 relative">
        <div class="font-semibold text-slate-700 py-0.5">
            {{ text }}
        </div>
        <div class="col-span-2 grid grid-cols-2">
            <div v-for="option in optionsRef" class="flex mt-1">
                <input class="w-5 h-5 mt-0.5" :disabled="disabled" type="checkbox" v-model="option['checked']"
                    @input="$emit('update:options', optionsRef)">
                <p class="ml-2 font-semibold ">{{ option['text'] }}</p>
            </div>
            <div class="absolute top-7 right-0 z-[1000] w-2/3 px-2 rounded-lg bg-red-500/80 text-rose-900"
                v-if="errorMessage">
                {{ errorMessage }}
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from "vue";

const { name, text, options, errorMessage, disabled } = defineProps(['name', 'text', 'options', 'errorMessage', 'disabled']);

const optionsRef = ref(options);
</script>

<style></style>