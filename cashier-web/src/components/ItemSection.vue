<template>
	<div>
		<div class="divider">
			<div class="flex flex-row gap-3">
				{{ header }}
				<slot name="icon"></slot>
			</div>
		</div>
		<div class="hover:max-h-96 max-h-40 overflow-y-scroll w-full transition-all duration-150 p-3 flex flex-col gap-2">
			<RecycleScroller class="scroller" :items="items" :item-size="50" key-field="id" v-slot="{ item }">
				<div class="bg-base-200 text-base-content rounded-lg px-3 py-2">
					<div class="flex flex-row justify-between">
						<h4>{{ item.name }}</h4>
						<div>Rs. {{ toReadable(item.price) }}</div>
					</div>
					<div class="flex flex-row justify-between items-center">
						<div class="flex flex-row gap-2">
							<div v-for="(category, index) in item.categories" :key="index" class="badge badge-outline">
								{{ category }}</div>
						</div>
						<div class="flex gap-2 flex-row items-center">
							<icon-mdi-pin-outline v-if="!isPinned(item)"
								class="w-6 h-6 text-secondary cursor-pointer hover:rotate-45 transition-all duration-100"
								@click="pin(item)" />
							<icon-mdi-pin v-else
								class="w-6 h-6 text-secondary cursor-pointer hover:rotate-45 transition-all duration-100"
								@click="unpin(item)" />
							<ElInputNumber :min="1" size="small" />
							<button class="btn btn-primary btn-sm rounded-full">Add</button>
						</div>
					</div>
				</div>
			</RecycleScroller>
		</div>
	</div>
</template>

<script setup lang="ts">
import type { Item } from '@/types/entity';
import { toReadable } from '@/util/decimal-util';
import { RecycleScroller } from "vue-virtual-scroller";

defineProps<{
	header: string,
	items: Item[];
	isPinned: (record: Item) => boolean;
	pin: (record: Item) => number[];
	unpin: (record: Item) => number[];
}>()
</script>
