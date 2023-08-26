// @ts-check
import {createRouter, createWebHistory} from 'vue-router'

const Tax = () => import('../views/Tax.vue');
const GeneralLedger = () => import('../views/GeneralLedger.vue');
const ChartOfAccounts = () => import('../views/ChartOfAccounts.vue');
const LedgerEntry = () => import('../views/LedgerEntry.vue');
const Dashboard = () => import('../views/Dashboard.vue');
const TaxTypes = () => import('../views/TaxTypes.vue');
const LedgerRecords = () => import('../views/LedgerRecords.vue');
const ViewLedgerRecord = () => import('../views/ViewLedgerRecord.vue');

const router = createRouter({
    // @ts-ignore
    history: createWebHistory(import.meta.env.BASE_URL),
    routes: [
        {
            path: '/',
            name: 'Dashboard',
            component: Dashboard
        },
        {
            path: '/tax',
            name: 'Tax',
            component: Tax
        },
        {
            path: '/ledger-records',
            name: 'LedgerRecords',
            component: LedgerRecords,
        },
        {
            path: '/ledger-records/view',
            name: 'ViewLedgerRecord',
            component: ViewLedgerRecord
        },
        {
            path: '/general-ledger',
            name: 'GeneralLedger',
            component: GeneralLedger
        },
        {
            path: '/chart-of-accounts',
            name: 'ChartOfAccounts',
            component: ChartOfAccounts
        },
        {
            path: '/tax-types',
            name: 'TaxTypes',
            component: TaxTypes
        },
        {
            path: '/ledger-entry',
            name: 'LedgerEntry',
            component: LedgerEntry
        }
    ]
})
export default router