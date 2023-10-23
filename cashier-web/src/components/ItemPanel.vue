<template>
    <UseWindowSize v-slot="{ width, height }: { [k: string]: number }">
        <div class="grid grid-cols-10 gap-3 overflow-y-scroll" :style="{ height: (height * 0.8) + 'px' }">
            <div class="col-span-7 flex items-center justify-center">
                <Suspense>
                    <ItemSearcher @add-item="addItem"/>
                    <template #fallback>
                        <span class="loading loading-spinner loading-lg"></span>
                    </template>
                </Suspense>
            </div>
            <ItemTable class="col-span-3" :items="items" @remove-item="removeItem"/>
        </div>
    </UseWindowSize>
</template>

<script setup lang="ts">
import { UseWindowSize } from '@vueuse/components';
import ItemSearcher from './ItemSearcher.vue';
import ItemTable from './ItemTable.vue';
import { ref } from 'vue';
import type { Item } from '@/types/entity';

type ItemWithAmount = Item & { amount: number };

const items = ref<ItemWithAmount[]>([]);

const addItem = (item: ItemWithAmount) => items.value = [item, ...items.value];
const removeItem = (id: number) => items.value = [...items.value.filter(item => item.id !== id)];
</script>

<style scoped></style>