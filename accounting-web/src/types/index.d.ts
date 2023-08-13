import { Object, Number } from 'ts-toolbelt'
import { Decimal } from "decimal.js";

type Float = `${number}.${number}`;
type DateString = `${number}-${number}-${number}`;

// Entities

export interface LedgerRecord {
    record_id: number
    account_id: number
    reference: string
    description: string
    credit: Decimal
    debit: Decimal
    tax: Decimal
    timestamp: Date
}

export interface Tax {
    tax_id: number
    name: string
    description: string
    tax_rate: Decimal
}

export interface FinancialAccount {
    account_id: number
    name: string
    type: string
    code: string
    description: string
    tax_id: number
    archived: number
    deletable: number
}

// API Raw Response Objects

export interface RawLedgerRecord {
    record_id: number
    account_id: number
    reference: string
    description: string
    credit: string
    debit: string
    tax: string
    timestamp: string
}

export interface RawTax {
    tax_id: number
    name: string
    description: string
    tax_rate: string
}

// API Request Interfaces

export interface LedgerRecordAddRequest {
    narration: string // "buying some blue berries"
    "tax-type": 'tax inclusive' | 'no tax' | 'tax exclusive',   // 'no tax' will not add any taxes. use 'tax inclusive' when taxes are already in the credit / debit amount.
    date?: DateString
    body: Object.Either<{
        "account_id": number
        "debit"?: Float
        "credit"?: Float
        "description": string
        "tax_id"?: number // -- - optional.use when need to override the default account tax rate.
    }, 'credit' | 'debit'>[] // | Either Debit or Credit, having both parameters set will result in an error
}

export interface GetLedgerRecordsOptions {
    'page-num'?: number
    narration?: string
    date?: DateString
}

export interface GetFinancialAccountsOptions {
    'page-num'?: number
    'account-id'?: number
    'account-name'?: string
    'account-code'?: string
    'account-type'?: string
    'tax-id'?: number
    description?: string
}

export interface GetTaxesOptions {
    'page-num'?: number
    'tax-id'?: number
    'tax-name'?: string
    description?: string
    'rate-min'?: Float
    'rate-max'?: Float
}

export interface AddTaxOptions {
    'tax-name': string
    description?: string
    'tax-rate': Float
}

export interface UpdateTaxOptions {
    'tax-id': number
    'tax-name'?: string
    description?: string
    'tax-rate'?: Float
}

import { DefineComponent } from "vue";

export type ComponentOptions<T> = T extends DefineComponent<infer _A, infer P, infer _B> ? P extends { $props: infer Props extends object } ? Object.Writable<Props> : never : never;

// export type ComponentOptions<T> = T extends DefineComponent<A, infer O, B> ?
//     O extends { $emit: infer E, $props: infer P } ?
//         E extends (event: infer EventName, ...rest: infer Rest) ? never : never

// export type ComponentOptions<T> = T extends DefineComponent<A, infer O, B> ?
//     OptionsType<O>
//     : never;

// type OptionsType<O> = 
//     O extends { $emit: infer E, $props: infer P} ? 
//     "both" : 
//     O extends { $emit: infer E } ? "emit-only" :
//     O extends { $props: infer PP} ? "props-only" :
//     "none";

// type EmitsToObject<E> = E extends Array ? 

// export type ComponentWithTemplateOptions<T> = T extends 

export as namespace Types;