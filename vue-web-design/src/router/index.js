import {createRouter, createWebHistory} from 'vue-router'
import Login from '../views/Login.vue'
import Products from '../views/Products.vue'
import Dashboard from '../views/Dashboard.vue'
import _404Page from '../views/_404.vue'
import Users from '../views/Users.vue'

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
        path: '/products',
        name: 'Products',
        component: Products
        // route level code-splitting
        // this generates a separate chunk (about.[hash].js) for this route
        // which is lazy-loaded when the route is visited.
        // component: () => import(/* webpackChunkName: "about" */ '../views/About.vue')
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