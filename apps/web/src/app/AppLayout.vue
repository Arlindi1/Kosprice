<script setup lang="ts">
import { onBeforeUnmount, ref, watch } from 'vue'
import { RouterLink, RouterView } from 'vue-router'

import { useLastUpdatedIndicator } from '@/app/composables/useLastUpdatedIndicator'
import { useCityQuerySync } from '@/features/city/composables/useCityQuerySync'
import { useCityStore } from '@/features/city/store/useCityStore'
import { getMarkets, getProductsCatalog } from '@/lib/api'

useCityQuerySync()
const cityStore = useCityStore()

const navigation = [
  { to: '/', label: 'Dashboard', icon: 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6' },
  { to: '/markets', label: 'Markets', icon: 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4' },
  { to: '/products', label: 'Products', icon: 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4' },
  { to: '/fuel', label: 'Fuel', icon: 'M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z' },
  { to: '/basket', label: 'Basket Index', icon: 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z' },
]

const { label: lastUpdatedLabel } = useLastUpdatedIndicator()
const isScrolled = ref(false)

let idlePrefetchHandle: number | null = null
const supportsIdlePrefetch = typeof window.requestIdleCallback === 'function'

function clearIdlePrefetch(): void {
  if (idlePrefetchHandle === null) {
    return
  }

  if (supportsIdlePrefetch) {
    window.cancelIdleCallback(idlePrefetchHandle)
  } else {
    clearTimeout(idlePrefetchHandle)
  }

  idlePrefetchHandle = null
}

function prefetchCitySections(cityId: number | null): void {
  clearIdlePrefetch()

  if (cityId === null) {
    return
  }

  const runPrefetch = (): void => {
    idlePrefetchHandle = null
    void Promise.allSettled([
      getProductsCatalog(cityId),
      getMarkets(cityId),
    ])
  }

  if (supportsIdlePrefetch) {
    idlePrefetchHandle = window.requestIdleCallback(runPrefetch, { timeout: 900 })
    return
  }

  idlePrefetchHandle = setTimeout(runPrefetch, 300)
}

function handleScroll(): void {
  isScrolled.value = window.scrollY > 10
}

watch(
  () => cityStore.activeCityId,
  (cityId) => {
    prefetchCitySections(cityId)
  },
  { immediate: true },
)

if (typeof window !== 'undefined') {
  window.addEventListener('scroll', handleScroll, { passive: true })
}

onBeforeUnmount(() => {
  clearIdlePrefetch()
  if (typeof window !== 'undefined') {
    window.removeEventListener('scroll', handleScroll)
  }
})
</script>

<template>
  <div class="app-shell">
    <header class="sticky top-0 z-50 border-b border-slate-300 bg-white/95 backdrop-blur">
      <div class="section-inner" :class="isScrolled ? 'py-2.5' : 'py-3.5'">
        <div class="flex items-center justify-between gap-4">
          <RouterLink to="/" class="inline-flex items-center gap-3 text-slate-900">
            <span
              class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-slate-900 text-white transition-transform duration-150 hover:scale-105"
            >
              <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"
                />
              </svg>
            </span>
            <span class="leading-tight">
              <span class="block text-2xl font-bold tracking-tight">KosPrice</span>
              <span class="hidden text-[11px] uppercase tracking-[0.16em] text-slate-500 md:block">
                Grocery and Fuel Intelligence
              </span>
            </span>
          </RouterLink>

          <p class="hidden rounded-full border border-slate-300 bg-slate-50 px-3 py-1.5 text-xs font-semibold text-slate-600 md:inline-flex">
            {{ lastUpdatedLabel }}
          </p>
        </div>

        <nav class="mt-2.5 overflow-x-auto pb-1">
          <div class="flex min-w-max items-center gap-2">
            <RouterLink
              v-for="item in navigation"
              :key="item.to"
              :to="item.to"
              class="inline-flex items-center gap-2 rounded-lg border border-transparent px-3 py-1.5 text-sm font-medium tracking-tight text-slate-600 transition-colors hover:border-slate-300 hover:bg-slate-100 hover:text-slate-900"
              active-class="border-slate-900 bg-slate-900 text-white hover:border-slate-900 hover:bg-slate-900 hover:text-white"
            >
              <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="item.icon" />
              </svg>
              <span>{{ item.label }}</span>
            </RouterLink>
          </div>
        </nav>

        <p class="mt-2 rounded-lg border border-slate-200 bg-slate-50 px-3 py-1.5 text-center text-xs font-medium text-slate-600 md:hidden">
          {{ lastUpdatedLabel }}
        </p>
      </div>
    </header>

    <main class="w-full">
      <RouterView />
    </main>
  </div>
</template>

<style scoped>
header {
  will-change: transform;
}
</style>
