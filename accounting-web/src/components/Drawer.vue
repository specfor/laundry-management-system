<template>
    <div class="drawer lg:drawer-open">
        <input :id="drawerId" type="checkbox" class="drawer-toggle" v-model="isOpen" />
        <div class="drawer-content flex flex-col items-center justify-center">
            <label @click="isOpen = !isOpen" class="drawer-button lg:hidden absolute top-1 left-1">
                <button class="btn btn-square btn-outline">
                    <v-icon name="hi-menu" />
                </button>
            </label>
            <slot></slot>
        </div>
        <div class="drawer-side">
            <label @click="isOpen = !isOpen" class="drawer-overlay"></label>
            <div class="menu p-4 w-80 h-full bg-base-200 text-base-content">
                <button @click="isOpen = !isOpen" class="btn btn-square">
                    <v-icon name="md-close-round" />
                </button>
                <slot name="drawer-content"></slot>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';

const isOpen = ref(false);
const drawerId = "drawer" + Math.floor(Math.random() * 100);
</script>

<style></style>