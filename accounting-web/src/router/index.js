import {createRouter, createWebHistory} from 'vue-router'
import Dashboard from '../views/Dashboard.vue'
import Tax from '../views/Tax.vue'
import GeneralLedger from '../views/GeneralLedger.vue'
import ChartOfAccounts from '../views/ChartOfAccounts.vue'

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
    }
]
const router = createRouter({
    history: createWebHistory(import.meta.env.BASE_URL),
    routes
})
export default router