<template>
    <ModelTemplate v-slot="{ args: [ items ], resolve, reject }">
        <div class="fixed inset-0 bg-gray-300/30 flex items-center justify-center z-30">
            <div class="card w-2/3 bg-white shadow-lg">
                <div class="card-body items-center text-center">
                    <h2>Select Variation</h2>
                    <div class="flex flex-col gap-2 items-start justify-center">
                        <div class="bg-base-200 text-base-content rounded-lg px-3 py-2" v-for="(item, index) in items"
                            :key="index">
                            <div class="flex flex-row justify-between">
                                <h4>{{ item.name }}</h4>
                                <div>Rs. {{ toReadable(item.price) }}</div>
                            </div>
                            <div class="flex flex-row justify-between items-center">
                                <div class="flex flex-row gap-2">
                                    <div v-for="(category, index) in item.categories" :key="index"
                                        class="badge badge-outline">
                                        {{ category }}</div>
                                </div>
                                <div class="flex gap-2 flex-row items-center">
                                    <ElInputNumber :min="1" size="small" v-model="amounts[item.id]" />
                                    <button class="btn btn-primary btn-sm rounded-full"
                                        @click="resolve({...item, amount: amounts[item.id]})">Add</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="flex flex-row">
                        <button class="btn btn-primary btn-sm rounded-full" @click="reject(false)">Cancel</button>
                    </div>
                </div>
            </div>
        </div>
    </ModelTemplate>
    <button class="btn btn-primary btn-sm rounded-full" @click="showModel">Add</button>
</template>

<script setup lang="ts">
import type { Item } from '@/types/entity';
import { toReadable } from '@/util/decimal-util';
import { createTemplatePromise } from '@vueuse/core';
import { ElInputNumber } from 'element-plus';
import { ref } from 'vue';

type ItemWithAmount = Item & { amount: number };

const { variants } = defineProps<{
    variants: Item[]
}>();

const emit = defineEmits<{
    (event: 'addItem', item: ItemWithAmount): void
}>()

const ModelTemplate = createTemplatePromise<ItemWithAmount, [Item[]]>();

const amounts = ref({} as Record<number, number>);

const showModel = () => {
    ModelTemplate.start(variants)
        .then(item => emit("addItem", item))
        .catch(() => null)
}
</script>

<style scoped></style>