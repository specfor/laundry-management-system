import {createRouter, createWebHistory} from 'vue-router'
import Login from '../views/Login.vue'
import Products from '../views/Products.vue'
import Dashboard from '../views/Dashboard.vue'
import _404Page from '../views/_404.vue'
import Users from '../views/Users.vue'
import Branches from '../views/Branches.vue'
import Customers from '../views/Customers.vue'
import Employees from '../views/Employees.vue'
import Payments from '../views/Payments.vue'
import Orders from '../views/Orders.vue'
import OrderInfo from '../views/OrderInfo.vue'
import Profile from '../views/Profile.vue'

const routes = [
    {
        path: '/',
        name: 'Sign In',
        component: Login
    },
    {
        path: '/dashboard',
        name: 'Dashboard',
        component: Dashboard
    },
    {
        path: '/users',
        name: 'Users',
        component: Users
    },
    {
        path: '/branches',
        name: 'Branches',
        component: Branches
    },
    {
        path: '/customers',
        name: 'Customers',
        component: Customers
    },
    {
        path: '/employees',
        name: 'Employees',
        component: Employees
    },
    {
        path: '/payments',
        name: 'Payments',
        component: Payments
    },
    {
        path: '/orders',
        name: 'Orders',
        component: Orders
    },
    {
        path:'/orders/:id',
        name:'OrderInfo',
        component:OrderInfo
    },
    {
        path: '/products',
        name: 'Products',
        component: Products
        // route level code-splitting
        // this generates a separate chunk (about.[hash].js) for this route
        // which is lazy-loaded when the route is visited.
        // component: () => import(/* webpackChunkName: "about" */ '../views/About.vue')
    },
    {
        path:'/profile',
        name:'Profile',
        component:Profile
    },
    {
        path: '/:catchall(.*)*',
        name: 'Page not Found',
        component: _404Page
    }
]
const router = createRouter({
    history: createWebHistory(import.meta.env.BASE_URL),
    routes
})
export default router