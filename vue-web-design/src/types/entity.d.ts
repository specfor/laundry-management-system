/* RAW API RESPONSES */

import type Decimal from "decimal.js"

export type OrderRaw = {
    order_id: number
    comments?: string,
    total_price: string // "212000.000"
    added_date: string // "2023-08-29 20:08:00"
    branch_id: number
    customer_id: number
    status: {
        time: number // UNIX Timestamp
        message: string
    }[]
    payments: Omit<PaymentRaw, "order_id">[],
    customer_name: string
    items: {
        price: string // "12000.000"
        item_id: number
        amount: number
        "return-date": string // "2023-08-09"
        defects: string[]
        item_name: string
        categories: {
            categories: CategoryRaw[]
        }
    }[]
}

export type ItemRaw = {
    item_id: number
    name: string
    price: string
    blocked: boolean
    categories: string[]
}

export type CustomerRaw = {
    customer_id: number
    email: string
    phone_num: string
    name: string
    address: string
    branch_id: number
    banned: boolean
    joined_date: string // "2023-08-29"
}

export type PaymentRaw = {
    payment_id: number
    order_id: number
    paid_amount: string
    paid_date: string
    refunded: boolean
}

export type CategoryRaw = {
    category_id: number
    name: string
}

/* SERIALIZED TYPES */

export type Category = {
    id: number
    name: string
}

export type Order = {
    id: number
    comments?: string,
    totalPrice: Decimal
    addedDate: Date
    branchId: number
    customerId: number
    status: {
        time: Date
        message: string
    }[]
    payments: Omit<Payment, "orderId">[],
    customerName: string
    items: {
        id: number
        name: string
        price: Decimal
        amount: number
        returnDate: Date
        defects: string[]
        categories: Category[]
    }[]
}

export type Item = {
    id: number
    name: string
    price: Decimal
    blocked: boolean
    categories: string[]
}

export type Customer = {
    id: number
    email: string
    phoneNum: string
    name: string
    address: string
    branchId: number
    banned: boolean
    joinedDate: Date
}

export type Payment = {
    id: number
    orderId: number
    paidAmount: Decimal
    paidDate: Date
    refunded: boolean
}

/* TYPES ACCEPTED BY ENTITY FUNCTIONS */

interface AddOrderOptionsItems {
    [index: string]: {
        amount: number,
        returnDate: Date,
        defects: string[]
    }
}

export type AddOrderOptions = {
    items: AddOrderOptionsItems
    customerId: number
    totalPrice?: Decimal
    branchId?: number // optional - only if user is not someone asigned to a branch, if user is assigned to a branch then send branch - id will not be used.
    comments?: string // optional
}

/* REQUESTS SEND TO SERVER TYPES */

export type AddOrderRequestData = {
    items: {
        [index: string]: {
            amount: number,
            'return-date': string,
            defects: string[]
        }
    }
    "customer-id": number
    "total-price"?: string
    "branch-id"?: number // optional - only if user is not someone asigned to a branch, if user is assigned to a branch then send branch - id will not be used.
    "customer-comments"?: string // optional
}

export as namespace Entity;