import { createRouter, createWebHistory } from 'vue-router'

import MarketDetailPage from '@/features/markets/pages/MarketDetailPage.vue'
import MarketsPage from '@/features/markets/pages/MarketsPage.vue'
import ProductDetailPage from '@/features/products/pages/ProductDetailPage.vue'
import ProductsPage from '@/features/products/pages/ProductsPage.vue'
import BasketPage from '@/pages/BasketPage.vue'
import DashboardPage from '@/pages/DashboardPage.vue'
import FuelPage from '@/pages/FuelPage.vue'
import NotFoundPage from '@/pages/NotFoundPage.vue'

export const router = createRouter({
  history: createWebHistory(),
  routes: [
    {
      path: '/',
      name: 'dashboard',
      component: DashboardPage,
    },
    {
      path: '/markets',
      name: 'markets',
      component: MarketsPage,
    },
    {
      path: '/markets/:marketId',
      name: 'market-detail',
      component: MarketDetailPage,
    },
    {
      path: '/fuel',
      name: 'fuel',
      component: FuelPage,
    },
    {
      path: '/products',
      name: 'products',
      component: ProductsPage,
    },
    {
      path: '/products/:productId',
      name: 'product-detail',
      component: ProductDetailPage,
    },
    {
      path: '/basket',
      name: 'basket',
      component: BasketPage,
    },
    {
      path: '/:pathMatch(.*)*',
      name: 'not-found',
      component: NotFoundPage,
    },
  ],
})
