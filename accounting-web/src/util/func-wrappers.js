// @ts-check

/**
 * Returns a new function which will return the logical NOT of the given function
 * @template T
 * @param {(a: T, b: T) => boolean} func A function that returns a boolean value
 * @returns {(a: T, b: T) => boolean}
 */
export const NotTakeTwo = (func) => (a, b) => !func(a, b);

/**
 * Returns a new function which will return the logical NOT of the given function
 * @template T
 * @param {(a: T) => boolean} func A function that returns a boolean value
 * @returns {(a: T) => boolean}
 */
export const NotTakeOne = (func) => (a) => !func(a);