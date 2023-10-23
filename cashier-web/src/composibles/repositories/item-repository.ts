import { useRecordRepository } from '@/composibles/record-repository';
import { useItem } from '../fetch/entities/item';
import type { Item } from '@/types/entity';
import { ITEMS_PER_PAGE } from '@/util/constants';
import { useRecordMemoizer } from '../record-memoizer';
import { computed } from 'vue';

export const useItemRepository = () => {
    const { getItems: getItemsRaw, searchItems: searchItemsRaw } = useItem()

    const getAllItems = async () => {
        const [firstBatch, totalItemCount] = await getItemsRaw(1);
        const pageCount = Math.ceil(totalItemCount / ITEMS_PER_PAGE);

        return Promise.all((new Array(pageCount - 1).fill(0))
            .map((_x, index) =>
                getItemsRaw(index + 2) // Index is 0-indexed so (+1) and we already have 1 page so (+1)
                    .catch(() => [])
                    .then(data => data[0])
            ))
            .then(data =>
                data
                    .reduce((acc, curr) => ([...acc, ...curr]), [] as Item[])
            )
            .then(data => ([...firstBatch, ...data]))
    }

    const { getRecords: getRepositoryRecords, ...repositoryReturns} = useRecordRepository(
        {
            "getAllItems": {
                online() {
                    return getAllItems()
                },
                offline(records) {
                    return records;
                },
            },
            "searchItems": {
                online(term: string) {
                    return searchItemsRaw({ name: term })
                },
                offline(records, term: string) {
                    return records.filter(record => record.name.includes(term)) // TODO: Use Fuse.JS
                },
            }
        },
        (record: Item) => record.id,
        5000,
        () => getAllItems()
    )

    const { pinned: pinnedIds, recents: recentsIds, frequents: frequentsIds, ...restRecordMemoizerReturns } = useRecordMemoizer("cashier-items", (record: Item) => record.id);

    const pinned = computed(() => {
        const items = getRepositoryRecords();
        return pinnedIds.value.map(id => items.find(item => item.id == id)).filter((item): item is Item => !!item)
    })

    const recents = computed(() => {
        const items = getRepositoryRecords();
        return recentsIds.value.map(id => items.find(item => item.id == id)).filter((item): item is Item => !!item)
    })

    const frequents = computed(() => {
        const items = getRepositoryRecords();
        return frequentsIds.value.map(id => items.find(item => item.id == id)).filter((item): item is Item => !!item)
    })

    return { ...repositoryReturns, ...restRecordMemoizerReturns, pinned, recents, frequents }
}