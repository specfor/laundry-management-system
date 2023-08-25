// @ts-check

/**
 * Returns a elements of the array that gets filtered through all the filters proviced
 * @template T
 * @param {T[]} array 
 * @param  {...(items: T[]) => T[]} filters 
 */
export function filterArray(array, ...filters) {
    let finalArr = /** @type {T[]} */ ([]);
    for (const filter of filters) {
        finalArr = filter(array)
    }

    return finalArr;
}