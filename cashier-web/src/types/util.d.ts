import type { Object } from "ts-toolbelt";
import type { Ref, ComputedRef } from "vue";

export type NotificationInjection = {
    showSuccess: ({ origin, status, statusText }: {
        origin: string;
        status: number;
        statusText: string;
    }) => void;
    showError: ({ origin, status, statusText }: {
        origin: string;
        status: number;
        statusText: string;
    }) => void;
}

type RecordRepositoryMethod<Record, Args> = {
    online: (...args: Args) => Promise<Record[]>
    offline: (records: Record[], ...args: Args) => Record[]
}

export type RecordRepository<Record, T extends Record<string, unknown>> =
    (methods: T, idSelector: (record: Record) => number | string, cacheInvalidateAfter: number, onInvalidateRun: keyof T) => {
        [key in keyof T]: T[key] extends RecordRepositoryMethod<infer R, infer Args> ? (...args: Args) => Record[] : never
        invalidate: () => void
    }

export type RecordRepositoryReturnMethods<Record, T extends Record<string, RecordRepositoryMethod<RecordType, any>>> = {
    [key in keyof T]: T[key] extends RecordRepositoryMethod<infer R, infer Args> ? (...args: Args) => Promise<Record[]> : never
}

export type RecordMemoizer<Record, ID extends string | number | symbol> =
    (localStorageKey: string, idSelector: (record: Record) => ID) => {
        pin: (record: RecordType) => ID[]
        use: (records: RecordType[]) => ID[]
        recents: RemovableRef<ID[]>
        pinned: RemovableRef<ID[]>
        frequents: ComputedRef<ID[]>
    }

export type ActionQueueItem<ID, T> = {
    args: T
    status: "completed" | "failed" | "pending" | "processing"
    resolvedId?: ID
    addedOn: Date
}

export type ActionQueue<ID, T> = (
    action: (args: T) => Promise<ID>,
    onSuccess: (args: T, id: ID) => void,
    onError: (args: T, error: any) => void,
    localStorageKey?: string,
) => {
    add: (args: T) => void,
    completed: ComputedRef<ActionQueueItem<ID, T>[]>,
    failed: ComputedRef<ActionQueueItem<ID, T>[]>,
    pending: ComputedRef<ActionQueueItem<ID, T>[]>
}

export interface ShowNotificationOptions {
    origin: string,
    status: number,
    statusText: string
}

export type ArrayElement<ArrayType extends readonly unknown[]> = ArrayType extends readonly (infer ElementType)[] ? ElementType : never;

export interface AuthData {
    authToken?: string
} 

export interface AuthInjection {
    authToken: ComputedRef<string | undefined>
}

export as namespace Util;