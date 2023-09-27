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
                <ItemSection :header="'Pinned'" :items="pinned" :isPinned="isPinned" :pin="pin" :unpin="unpin">
                    <template #icon>
                        <icon-mdi-pin class="w-6 h-6 text-base-content" />
                    </template>
                </ItemSection>
                <ItemSection :header="'Frequent'" :items="frequents" :isPinned="isPinned" :pin="pin" :unpin="unpin">
                    <template #icon>
                        <icon-mdi-archive-refresh-outline class="w-6 h-6 text-base-content" />
                    </template>
                </ItemSection>
                <ItemSection :header="'Recent'" :items="recents" :isPinned="isPinned" :pin="pin" :unpin="unpin">
                    <template #icon>
                        <icon-mdi-clock-time-four-outline class="w-6 h-6 text-base-content" />
                    </template>
                </ItemSection>
                <div>
                    <div class="divider">
                        All
                    </div>
                    <div
                        class="hover:max-h-96 max-h-40 overflow-y-scroll w-full transition-all duration-150 p-3 flex flex-col gap-2">
                        <RecycleScroller class="scroller" :items="items" :item-size="50" key-field="id"
                            v-slot="{ item }: { item: VariationAdjustedItem }">
                            <div class="bg-base-200 text-base-content rounded-lg px-3 py-2">
                                <div class="flex flex-row justify-between">
                                    <h4>{{ item.name }}</h4>
                                    <div>{{ item.hasVariants ? `${item.items.length} Variant${item.items.length > 1 ? 's' :
                                        ''}` : `Rs. ${toReadable(item.items[0].price)}` }}
                                    </div>
                                </div>
                                <div class="flex flex-row justify-between items-center">
                                    <div class="flex flex-row gap-2">
                                        <div v-for="(category, index) in (item.hasVariants ? [] as Item['categories'] : item.items[0].categories)"
                                            :key="index" class="badge badge-outline">
                                            {{ category }}</div>
                                    </div>
                                    <div class="flex gap-2 flex-row items-center">
                                        <template v-if="!item.hasVariants">
                                            <icon-mdi-pin-outline v-if="!isPinned(item.items[0])"
                                                class="w-6 h-6 text-secondary cursor-pointer hover:rotate-45 transition-all duration-100"
                                                @click="pin(item.items[0])" />
                                            <icon-mdi-pin v-else
                                                class="w-6 h-6 text-secondary cursor-pointer hover:rotate-45 transition-all duration-100"
                                                @click="unpin(item.items[0])" />
                                            <ElInputNumber :min="1" size="small" v-model="amounts[item.id]"/>
                                        </template>
                                        <template v-else>
                                            <icon-mdi-pin-outline class="w-6 h-6 text-secondary opacity-50 cursor-not-allowed"/>
                                        </template>
                                        <button v-if="!item.hasVariants" class="btn btn-primary btn-sm rounded-full" @click="addItem({...item.items[0], amount: amounts[item.id]})">Add</button>
                                        <VariantAddButton :variants="item.items" @add-item="addItem"/>
                                    </div>
                                </div>
                            </div>
                        </RecycleScroller>
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
import { RecycleScroller } from "vue-virtual-scroller";
import ItemSection from './ItemSection.vue'
import VariantAddButton from './VariantAddButton.vue';

const isSearching = ref(false);

type ItemWithAmount = Item & { amount: number };
type VariationAdjustedItem = {
    items: Item[];
    hasVariants: boolean;
    name: string;
    id: number;
}

const mapToItemVariation = (items: Item[]) => {
    return Object.entries(items
        .reduce((acc, curr) => ({
            ...acc,
            [curr.name]: [...acc[curr.name], curr]
        }), {} as Record<string, Item[]>))
        .map(([name, items]) => ({
            items,
            hasVariants: items.length > 1,
            name,
            id: items[0].id // An ID Field is necessary for the rendering of the RecycleScroller
        }))
}

const { getAllItems, searchItems, use, pin, unpin, isPinned, frequents, pinned, recents } = useItemRepository();

const items = mapToItemVariation(await getAllItems());

const amounts = ref({} as Record<number, number>);

const addItem = (item: ItemWithAmount) => {
    use([item]);
    emit("addItem", item);
};

const emit = defineEmits<{
    addItem: [item: ItemWithAmount]
}>();
</script>

<style scoped></style>