<template>
    <div class="overflow-x-auto m-4">
        <table class="table table-zebra">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Categories</th>
                    <th>Unit Price</th>
                    <th>Amount</th>
                    <th></th>
                </tr>
            </thead>
            <TransitionGroup name="table-rows" tag="tbody">
                <tr v-for="item in items" :key="item.id">
                    <td>{{ item.name }}</td>
                    <td>
                        <div class="flex flex-row gap-2">
                            <div v-for="(category, index) in item.categories" :key="index" class="badge badge-outline">
                                {{ category }}</div>
                        </div>
                    </td>
                    <td>Rs.{{ toReadable(item.price) }}</td>
                    <td>{{ item.amount }}</td>
                    <td>
                        <icon-mdi-delete class="w-6 h-6 text-base-content" @click="$emit('removeItem', item.id)"/>
                    </td>
                </tr>
            </TransitionGroup>
        </table>
    </div>
</template>

<script setup lang="ts">
import type { Item } from '@/types/entity';
import { toReadable } from '@/util/decimal-util';

const { items } = defineProps<{
    items: (Item & { amount: number })[]
}>();

defineEmits<{
    removeItem: [id: number]
}>();
</script>

<style scoped>
.table-rows-move,
/* apply transition to moving elements */
.table-rows-leave-active {
    transition: all 0.3s ease;
}

.table-rows-enter-active {
    transition: all 0.3s ease;
    transition-delay: 0.3s;
}

.table-rows-enter-from,
.table-rows-leave-to {
    opacity: 0;
    /* transform: translateX(30px); */
}
</style>