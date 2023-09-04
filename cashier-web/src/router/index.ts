import { useAuthentication } from '@/composibles/authentication'
import { createRouter, createWebHistory } from 'vue-router'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    // {
    //   path: '/',
    //   name: 'home',
    //   component: HomeView
    // },
    {
      path: '/login',
      name: 'Login',
      // route level code-splitting
      // this generates a separate chunk (About.[hash].js) for this route
      // which is lazy-loaded when the route is visited.
      component: () => import('../views/Login.vue')
    },
    {
      path: '/',
      name: 'Cashier',
      // route level code-splitting
      // this generates a separate chunk (About.[hash].js) for this route
      // which is lazy-loaded when the route is visited.
      component: () => import('../views/Cashier.vue'),
      children: [
        {
          path: "new-order",
          name: "NewOrder",
          alias: "",
          component: () => import('../views/NewOrder.vue')
        },
        {
          path: "view-order",
          name: "ViewOrder",
          component: () => import('../views/ViewOrder.vue')
        }
      ]
    }
  ]
})

router.beforeEach(async (to, from) => {
  const { authToken } = useAuthentication();
  if (
    // make sure the user is authenticated
    !authToken.value &&
    // ❗️ Avoid an infinite redirect
    to.name !== 'Login'
  ) {
    // redirect the user to the login page
    return { name: 'Login' }
  }
})

export default router
