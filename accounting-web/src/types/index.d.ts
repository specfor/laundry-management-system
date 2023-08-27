import { Object, Number } from 'ts-toolbelt'
import { Decimal } from "decimal.js";
import { Ref, ComputedRef } from "vue";

type Float = `${number}.${number}`;
type DateString = `${number}-${number}-${number}`;

// Entities

export interface LedgerRecord {
    record_id: number
    narration: string // "buying some blue berries"
    date: Date,
    totalAmount: Decimal,
    body: Object.Either<{
        account_id: number
        debit: Decimal
        credit: Decimal
        description: string
        tax_id: number
    }, 'credit' | 'debit'>[] // | Either Debit or Credit, having both parameters set will result in an error
}

export interface Tax {
    tax_id: number
    name: string
    description?: string
    tax_rate: Decimal
    deleted: boolean
    locked: boolean
}

export interface FinancialAccountTypes {
    assets: ['Current Asset', 'Fixed Asset', 'Inventory', 'Non-Current Asset', 'Prepayment'],
    equity: ['Equity'],
    expenses: ['Depreciation', 'Direct Costs', 'Expense', 'Overhead'],
    liabilities: ['Current Liability', 'Liability', 'Non-Current Liability'],
    revenue: ['Other Income', 'Revenue', 'Sales']
}

export interface FinancialAccount {
    account_id: number
    name: string
    type: string
    code: string
    description?: string
    tax_id: number
    archived: boolean
    deletable: boolean
    locked: boolean
}

// API Raw Response Objects

export interface RawLedgerRecord {
    record_id: number
    narration: string // "buying some blue berries"
    date: string // yyyy-mm-dd,
    tot_amount: string,
    body: string
}

export type RawLedgerRecordBody = Object.Either<{
    account_id: number
    debit: string
    credit: string
    description: string
    tax_id: number
}, 'credit' | 'debit'>[] // | Either Debit or Credit, having both parameters set will result in an error

export interface RawTax {
    tax_id: number
    name: string
    description?: string
    tax_rate: string
    deleted: number,
    locked: number
}

export interface RawFinancialAccount {
    account_id: number
    name: string
    type: string
    code: string
    description?: string
    tax_id: number
    archived: number
    deletable: number
    locked: number
}

// API Request Interfaces

export interface LedgerRecordAddRequest {
    narration: string // "buying some blue berries"
    "tax-type": 'tax inclusive' | 'no tax' | 'tax exclusive',   // 'no tax' will not add any taxes. use 'tax inclusive' when taxes are already in the credit / debit amount.
    date?: DateString
    body: Object.Either<{
        "account_id": number
        "debit": string
        "credit": string
        "description": string
        "tax_id"?: number // -- - optional.use when need to override the default account tax rate.
    }, 'credit' | 'debit'>[] // | Either Debit or Credit, having both parameters set will result in an error
}

export interface LedgerRecordAddOptions {
    narration: string // "buying some blue berries"
    taxType: 'tax inclusive' | 'no tax' | 'tax exclusive',   // 'no tax' will not add any taxes. use 'tax inclusive' when taxes are already in the credit / debit amount.
    date?: Date
    body: Object.Either<{
        "account_id": number
        "debit": Decimal
        "credit": Decimal
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

export type RefToInner<T> = T extends Ref<infer Inner> ? Inner : never;

export type PromiseAll<T extends unknown[]> = (
    values: readonly [...T],
) => Promise<{ [P in keyof T]: T[P] extends Promise<infer R> ? R : T[P] }>;

export type AllObjectPropsTo<T extends object, P> = { [K in keyof T]: P };

/** Notification */
export type NotificationProvider = {
    showSuccess: (options: ShowNotificationOptions) => void,
    showError: (options: ShowNotificationOptions) => void
}

export interface ShowNotificationOptions {
    origin: string,
    status: number,
    statusText: string
}

import {
    DefineComponent,
    RendererElement,
    RendererNode,
    VNode,
    VNodeProps,
} from "vue";

type NoUndefined<T> = T extends undefined ? never : T;

type Props =
    | (VNodeProps & {
        [key: string]: any;
    })
    | null;

// maybe this can be typed as `SetupContext`
export type Context = {
    props: any;
    attrs: any;
    slots: any;
    emit: any;
    expose: (exposed?: any) => void;
};

export type ComponentReturn = VNode<RendererNode, RendererElement, Props> & {
    __ctx?: Context;
};

export type PseudoComponent<
    T extends (...args: any[]) => ComponentReturn,
    PseudoReturnType extends ComponentReturn = ReturnType<T>,
    PseudoContext extends ComponentReturn["__ctx"] = PseudoReturnType["__ctx"],
    PseudoProps = NoUndefined<PseudoContext>["props"],
    PseudoExposed = Parameters<NoUndefined<PseudoContext>["expose"]>[0]
> = DefineComponent<PseudoProps, PseudoExposed>;

export type GenericComponentInstance<C> = InstanceType<PseudoComponent<C>> | null

// A helper type to get the element type of an array
export type ArrayElement<ArrayType extends readonly unknown[]> = ArrayType extends readonly (infer ElementType)[] ? ElementType : never;

export type TransformerFunc<R, RT extends R> = (arg: R) => RT

export type FilterType<TF> = TF extends TransformerFunc<infer R, infer RT> ? (records: RT[]) => RT[] : never

export type PaginationAdapter<Record> = (
    ...filters: (MaybeRefOrComputedRef<(records: Record[]) => Record[]>)[]
) => Promise<{ currentPage: Ref<number>, pageCount: ComputedRef<number>, currentPageSize: Ref<number>, records: Ref<Record[]> }>

export type MaybeRefOrComputedRef<T> = Ref<T> | ComputedRef<T>

export as namespace Types;