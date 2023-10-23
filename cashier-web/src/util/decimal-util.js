// @ts-check

import Decimal from "decimal.js";

/** 
 * Converts to Decimal
 * @param {Decimal.Value} val */
export const toDecimal = (val) => new Decimal(val);

/**
 * Adds all the given Decimal parameters together
 * @param  {...Decimal} args 
 * @returns {Decimal}
 */
export const addDecimal = (...args) => args.reduce((a, b) => a.add(b), new Decimal(0));

/**
 * Reducer for Decimal values
 * @param  {Decimal} dec1 
 * @param  {Decimal} dec2 
 * @returns {Decimal}
 */
export const decimalReducer = (dec1, dec2) => dec1.add(dec2);

/**
 * Converts a Decimal to a more human readable string
 * @param {Decimal} dec 
 * @returns {string}
 */
export const toReadable = (dec) => {
    if (dec.isZero()) return "0";
    
    const negative = dec.isNegative();
    dec = dec.absoluteValue(); 

    const [whole, ...decimal] = dec.toFixed(3).split('.');
    const digitGroups = whole.split("").reverse().join("").match(/.{1,3}/g)
    return digitGroups ? `${negative ? "-" : ""}${digitGroups.reverse().map(item => item.split("").reverse().join("")).join(",")}.${decimal[0]}` : `0.${decimal[0]}`
}

/**
 * Converts a Decimal to a more human readable string (For calculated totals and such)
 * @param {Decimal} dec 
 * @returns {string}
 */
export const toReadableTotal = (dec) => {
    if (dec.isZero()) return "-";
    
    const negative = dec.isNegative();
    dec = dec.absoluteValue(); 

    const [whole, ...decimal] = dec.toFixed(3).split('.');
    const digitGroups = whole.split("").reverse().join("").match(/.{1,3}/g)
    return digitGroups ? `${negative ? "-" : ""}${digitGroups.reverse().map(item => item.split("").reverse().join("")).join(",")}.${decimal[0]}` : `0.${decimal[0]}`
}

/**
 * Checks if the string is a value Decimal constructor can take
 * @type {(text: string | undefined) => boolean}
 */
export const isProperNumberString = (text) => {
    if (text === undefined) return false;

    try {
        new Decimal(text)
        return true; // All good
    } catch (_e) {
        // Constructior threw an error
        return false;
    }
}