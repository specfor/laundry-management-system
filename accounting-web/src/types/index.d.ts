// API Response Interfaces

export interface LedgerRecord {
    record_id: number
    account_id: number
    reference: string
    description: string
    credit: number
    debit: number
    tax: number
    timestamp: Date
}

export interface Tax {
    tax_id: number
    name: string
    description: string
    tax_rate: string
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

// API Request Interfaces



import { DefineComponent } from "vue";
import { Object } from 'ts-toolbelt'

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