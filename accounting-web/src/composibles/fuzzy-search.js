// @ts-check
import Fuse from 'fuse.js';

/**
 * Search a list fuzzyly. 
 * Returns the whole array if the search term is ""
 * @template T
 * @param {T[]} list List to be searched in
 * @param {Array<keyof T>} keys Object properties to seach in
 * @param {string} keyword Search term
 */
export const useFuzzySearch = (list, keys, keyword) => {
    if (keyword == "") return list;

    return new Fuse(list, {
        keys: /** @type {string[]} */ (keys)
    }).search(keyword).map(result => result.item)
}