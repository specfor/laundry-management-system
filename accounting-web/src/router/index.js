import {createRouter, createWebHistory} from 'vue-router'

const Tax = () => import('../views/Tax.vue');
const GeneralLedger = () => import('../views/GeneralLedger.vue');
const ChartOfAccounts = () => import('../views/ChartOfAccounts.vue');
const LedgerEntry = () => import('../views/LedgerEntry.vue');
const Dashboard = () => import('../views/Dashboard.vue');
const TaxTypes = () => import('../views/TaxTypes.vue');

const routes = [
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
const router = createRouter({
    history: createWebHistory(import.meta.env.BASE_URL),
    routes
})
export default router