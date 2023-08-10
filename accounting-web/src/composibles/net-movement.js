// @ts-check

/**
 * 
 * @template {Object} T
 * @param {T[]} records Records to be summed up and diffed
 * @param {keyof T} debitKey Key for debit in the object
 * @param {keyof T} creditKey Key for credit in the object
 * @returns 
 */
export function useNetMovement(records, debitKey, creditKey) {
    const debitSum = records.map(row => row[debitKey]).reduce((acc, curr) => acc + curr, 0);
    const creditSum = records.map(row => row[creditKey]).reduce((acc, curr) => acc + curr, 0);

    const diff = Math.max(debitSum, creditSum) - Math.min(debitSum, creditSum);
    const isDebitMore = debitSum > creditSum;
    return { isDebitMore, diff };
}