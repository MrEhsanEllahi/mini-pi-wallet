import { createRouter, createWebHistory } from 'vue-router'
import WalletView from '../views/WalletView.vue'
import AuthView from '../views/AuthView.vue'
import { useAuthStore } from '../stores/auth'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    { path: '/login', name: 'login', component: AuthView },
    { path: '/', name: 'wallet', component: WalletView, meta: { requiresAuth: true } },
  ],
})

// Simple auth guard
router.beforeEach(async (to) => {
  const auth = useAuthStore()
  if (!auth.token && to.meta.requiresAuth) {
    return { name: 'login' }
  }
})

export default router
