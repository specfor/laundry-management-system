// @ts-check

/**
 * This function can be passed to Array<Date>.sort()
 * @param {Date} a 
 * @param {Date} b 
 */
export const dateSorter = (a, b) => a.getTime() - b.getTime()

/**
 * This function can be passed to Array<string>.sort()
 * @param {string} a 
 * @param {string} b 
 */
export const alphabeticalSorter = (a, b) => {
    if (a < b) {
        return -1;
    }
    if (a > b) {
        return 1;
    }
    return 0;
}

/**
 * This function can be passed to Array<string>.sort()
 * @param {import('decimal.js').Decimal} b 
 * @param {import('decimal.js').Decimal} a 
 */
export const decimalSorter = (a, b) => a.cmp(b)