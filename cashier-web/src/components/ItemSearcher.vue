<template>
    <div class="w-full p-2">
        <div class="w-full px-2 py-4 rounded-md shadow-sm flex flex-col gap-2">
            <h2>Items & Actions</h2>
            <div class="join">
                <div>
                    <div>
                        <input class="input input-bordered join-item" placeholder="Search ..." />
                    </div>
                </div>
                <div class="indicator">
                    <span class="indicator-item badge badge-error">offline</span>
                    <button class="btn join-item">Search</button>
                </div>
            </div>
            <!-- Recent Items, Pinned Items, All Items -->
            <div class="grow" v-if="!isSearching">
                <div>
                    <div class="divider">
                        <div class="flex flex-row gap-3">
                            Pinned
                            <icon-mdi-pin class="w-6 h-6 text-base-content" />
                        </div>
                    </div>
                    <div
                        class="hover:max-h-96 max-h-40 overflow-y-scroll w-full transition-all duration-150 p-3 flex flex-col gap-2">
                        <div class="bg-base-200 text-base-content rounded-lg px-3 py-2" v-for="index in 5">
                            <div class="flex flex-row justify-between">
                                <h4>{{ testItem.name }}</h4>
                                <div>Rs. {{ toReadable(testItem.price) }}</div>
                            </div>
                            <div class="flex flex-row justify-between items-center">
                                <div class="flex flex-row gap-2">
                                    <div v-for="(item, index) in testItem.categories" :key="index"
                                        class="badge badge-outline">
                                        {{ item }}</div>
                                </div>
                                <div class="flex gap-2 flex-row items-center">
                                    <icon-mdi-pin-outline v-if="!pinned"
                                        class="w-6 h-6 text-secondary cursor-pointer hover:rotate-45 transition-all duration-100"
                                        @click="pinned = !pinned" />
                                    <icon-mdi-pin v-else
                                        class="w-6 h-6 text-secondary cursor-pointer hover:rotate-45 transition-all duration-100"
                                        @click="pinned = !pinned" />
                                    <ElInputNumber :min="1" size="small" />
                                    <button class="btn btn-primary btn-sm rounded-full">Add</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div>
                    <div class="divider">
                        <div class="flex flex-row gap-3">
                            Frequent
                            <icon-mdi-archive-refresh-outline class="w-6 h-6 text-base-content" />
                        </div>
                    </div>
                    <div
                        class="hover:max-h-96 max-h-40 overflow-y-scroll w-full transition-all duration-150 p-3 flex flex-col gap-2">
                        <div class="bg-base-200 text-base-content rounded-lg px-3 py-2" v-for="index in 5">
                            <div class="flex flex-row justify-between">
                                <h4>{{ testItem.name }}</h4>
                                <div>Rs. {{ toReadable(testItem.price) }}</div>
                            </div>
                            <div class="flex flex-row justify-between items-center">
                                <div class="flex flex-row gap-2">
                                    <div v-for="(item, index) in testItem.categories" :key="index"
                                        class="badge badge-outline">
                                        {{ item }}</div>
                                </div>
                                <div class="flex gap-2 flex-row items-center">
                                    <icon-mdi-pin-outline v-if="!pinned"
                                        class="w-6 h-6 text-secondary cursor-pointer hover:rotate-45 transition-all duration-100"
                                        @click="pinned = !pinned" />
                                    <icon-mdi-pin v-else
                                        class="w-6 h-6 text-secondary cursor-pointer hover:rotate-45 transition-all duration-100"
                                        @click="pinned = !pinned" />
                                    <ElInputNumber :min="1" size="small" />
                                    <button class="btn btn-primary btn-sm rounded-full">Add</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div>
                    <div class="divider">
                        <div class="flex flex-row gap-3">
                            Recent
                            <icon-mdi-clock-time-four-outline class="w-6 h-6 text-base-content" />
                        </div>
                    </div>
                    <div
                        class="hover:max-h-96 max-h-40 overflow-y-scroll w-full transition-all duration-150 p-3 flex flex-col gap-2">
                        <div class="bg-base-200 text-base-content rounded-lg px-3 py-2" v-for="index in 5">
                            <div class="flex flex-row justify-between">
                                <h4>{{ testItem.name }}</h4>
                                <div>Rs. {{ toReadable(testItem.price) }}</div>
                            </div>
                            <div class="flex flex-row justify-between items-center">
                                <div class="flex flex-row gap-2">
                                    <div v-for="(item, index) in testItem.categories" :key="index"
                                        class="badge badge-outline">
                                        {{ item }}</div>
                                </div>
                                <div class="flex gap-2 flex-row items-center">
                                    <icon-mdi-pin-outline v-if="!pinned"
                                        class="w-6 h-6 text-secondary cursor-pointer hover:rotate-45 transition-all duration-100"
                                        @click="pinned = !pinned" />
                                    <icon-mdi-pin v-else
                                        class="w-6 h-6 text-secondary cursor-pointer hover:rotate-45 transition-all duration-100"
                                        @click="pinned = !pinned" />
                                    <ElInputNumber :min="1" size="small" />
                                    <button class="btn btn-primary btn-sm rounded-full">Add</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div>
                    <div class="divider">
                        All
                    </div>
                    <div
                        class="hover:max-h-96 max-h-40 overflow-y-scroll w-full transition-all duration-150 p-3 flex flex-col gap-2">
                        <div class="bg-base-200 text-base-content rounded-lg px-3 py-2" v-for="item in items">
                            <div class="flex flex-row justify-between">
                                <h4>{{ item.name }}</h4>
                                <div>{{ item.hasVariants ? `Variant` : `Rs. ${toReadable(item.items[0].price)}` }}
                                </div>
                            </div>
                            <div class="flex flex-row justify-between items-center">
                                <div class="flex flex-row gap-2">
                                    <div v-for="(item, index) in item.categories" :key="index"
                                        class="badge badge-outline">
                                        {{ item }}</div>
                                </div>
                                <div class="flex gap-2 flex-row items-center">
                                    <icon-mdi-pin-outline v-if="!isPinned(item)"
                                        class="w-6 h-6 text-secondary cursor-pointer hover:rotate-45 transition-all duration-100"
                                        @click="pinned = !pinned" />
                                    <icon-mdi-pin v-else
                                        class="w-6 h-6 text-secondary cursor-pointer hover:rotate-45 transition-all duration-100"
                                        @click="pinned = !pinned" />
                                    <ElInputNumber :min="1" size="small" />
                                    <button class="btn btn-primary btn-sm rounded-full">Add</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Search Results -->
            <div class="grow" v-if="isSearching">
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { useItemRepository } from '@/composibles/repositories/item-repository';
import type { Item } from '@/types/entity';
import { toReadable } from '@/util/decimal-util';
import Decimal from 'decimal.js';
import { ElInputNumber } from 'element-plus';
import { ref } from 'vue';

const isSearching = ref(false);

type ItemWithAmount = Item & { amount: number };

const mapToItemVariation = (items: Item[]) => {
    return Object.entries(items
        .reduce((acc, curr) => ({
            ...acc,
            [curr.name]: [...acc[curr.name], curr]
        }), {} as Record<string, Item[]>))
        .map(([name, items]) => ({
            items,
            hasVariants: items.length > 1,
            name
        }))
}

const { getAllItems, searchItems, use, pin, unpin, isPinned, frequents, pinned, recents } = useItemRepository();

const items = mapToItemVariation(await getAllItems());

const addItem = (item: ItemWithAmount) => {
    use([item]);
    emit("addItem", item);
};

const emit = defineEmits<{
    addItem: [item: ItemWithAmount]
}>();
</script>

<style scoped></style>