<script setup lang="ts">
import { computed, ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'

import { useBasket } from '@/features/basket/composables/useBasket'
import CitySelector from '@/features/city/components/CitySelector.vue'
import { useCityStore } from '@/features/city/store/useCityStore'
import { useMarkets } from '@/features/market/composables/useMarkets'
import { getMarketLogo } from '@/features/markets/utils/getMarketLogo'
import { abortApiRequestsForCity } from '@/lib/api/client'
import type { Market } from '@/lib/types/api'
import Badge from '@/shared/ui/Badge.vue'
import Button from '@/shared/ui/Button.vue'
import Card from '@/shared/ui/Card.vue'
import Chip from '@/shared/ui/Chip.vue'
import EmptyState from '@/shared/ui/EmptyState.vue'
import Skeleton from '@/shared/ui/Skeleton.vue'

const router = useRouter()
const route = useRoute()
const cityStore = useCityStore()
const activeCityId = computed(() => cityStore.activeCityId)

const { markets, isLoadingMarkets, marketsError, loadMarkets } = useMarkets()
const {
  basketTotalsByMarket,
  basketTotalsLoadingByMarket,
  basketTotalsErrorByMarket,
  loadTotalsForMarkets,
} = useBasket()

type FilterMode = 'all' | 'cheapest' | 'updated_today'
const filterMode = ref<FilterMode>('all')

const rankedMarkets = computed(() =>
  markets.value
    .map((market) => ({
      market,
      total: basketTotalsByMarket.value[market.id]?.total_price_eur ?? Number.POSITIVE_INFINITY,
      recordedAt: basketTotalsByMarket.value[market.id]?.recorded_at ?? null,
    }))
    .sort((left, right) => left.total - right.total),
)

const todayDate = computed(() => new Date().toISOString().slice(0, 10))

const filteredRankedMarkets = computed(() => {
  if (filterMode.value === 'cheapest') {
    const first = rankedMarkets.value.find((item) => Number.isFinite(item.total))
    return first ? [first] : []
  }

  if (filterMode.value === 'updated_today') {
    return rankedMarkets.value.filter((item) => item.recordedAt === todayDate.value)
  }

  return rankedMarkets.value
})

const cheapestTotal = computed(() => {
  const first = rankedMarkets.value.find((item) => Number.isFinite(item.total))
  return first?.total ?? null
})

async function loadMarketRows(force = false): Promise<void> {
  await loadMarkets(activeCityId.value, force)
  const marketIds = markets.value.map((market) => market.id)
  await loadTotalsForMarkets(activeCityId.value, marketIds, force)
}

function formatMoney(value: number): string {
  return value.toLocaleString('en-US', {
    style: 'currency',
    currency: 'EUR',
    minimumFractionDigits: 2,
  })
}

function formatDelta(value: number): string {
  if (!Number.isFinite(value)) {
    return 'Pending'
  }

  if (value <= 0) {
    return 'Best price'
  }

  return `+${value.toFixed(2)} EUR`
}

function isBestValue(total: number, index: number): boolean {
  if (!Number.isFinite(total) || cheapestTotal.value === null) {
    return false
  }

  if (index === 0) {
    return false
  }

  return total <= cheapestTotal.value * 1.04
}

function openMarketDetail(market: Market): void {
  void router.push({
    name: 'market-detail',
    params: { marketId: market.id },
    query: route.query,
  })
}

watch(
  activeCityId,
  (_, previousCityId) => {
    if (typeof previousCityId === 'number') {
      abortApiRequestsForCity(previousCityId)
    }

    void loadMarketRows()
  },
  { immediate: true },
)
</script>

<template>
  <section class="page-stack">
    <Card variant="highlight" compact>
      <div class="flex flex-wrap items-end justify-between gap-4">
        <div class="space-y-1">
          <p class="text-label text-brand-700">Markets</p>
          <h2 class="text-display">Compare markets in your city</h2>
          <p class="text-body">
            Ranked by current basket total. Select any market to open detailed product coverage.
          </p>
        </div>
        <CitySelector mode="pill" />
      </div>
    </Card>

    <Card>
      <template #header>
        <div class="space-y-1">
          <h3 class="text-heading">Ranked market list</h3>
          <p class="text-body">Whole row is clickable. Lower basket total indicates a cheaper market today.</p>
        </div>
      </template>
      <template #actions>
        <Button variant="ghost" size="sm" @click="loadMarketRows(true)">Refresh</Button>
      </template>

      <div class="mb-4 flex flex-wrap items-center gap-2">
        <Chip :active="filterMode === 'all'" @click="filterMode = 'all'">All</Chip>
        <Chip :active="filterMode === 'cheapest'" @click="filterMode = 'cheapest'">Cheapest</Chip>
        <Chip :active="filterMode === 'updated_today'" @click="filterMode = 'updated_today'">Updated today</Chip>
      </div>

      <div v-if="isLoadingMarkets" class="space-y-2">
        <Skeleton v-for="index in 6" :key="index" height="4.1rem" rounded="0.75rem" />
      </div>
      <div v-else-if="marketsError" class="status-wrap">
        <EmptyState
          title="Market list unavailable"
          :message="marketsError"
          cta-label="Retry"
          @retry="loadMarketRows(true)"
        />
      </div>
      <div v-else-if="filteredRankedMarkets.length === 0" class="status-wrap">
        <EmptyState
          title="No matching markets"
          message="No market data matches the selected filter."
          cta-label="Reload"
          @retry="loadMarketRows(true)"
        />
      </div>
      <ol v-else class="divide-y divide-slate-200 rounded-xl border border-slate-200 bg-white">
        <li v-for="(entry, index) in filteredRankedMarkets" :key="entry.market.id">
          <button
            type="button"
            class="interactive-subtle flex w-full items-center justify-between gap-4 px-4 py-3 text-left"
            @click="openMarketDetail(entry.market)"
          >
            <div class="flex min-w-0 items-center gap-3">
              <span class="w-6 text-sm font-semibold text-slate-400">{{ index + 1 }}</span>
              <img
                :src="getMarketLogo(entry.market.name)"
                :alt="`${entry.market.name} logo`"
                class="h-10 w-10 rounded-md border border-slate-200 bg-white p-0.5 object-contain"
              />
              <div class="min-w-0">
                <div class="flex flex-wrap items-center gap-2">
                  <p class="truncate text-base font-semibold text-slate-900">{{ entry.market.name }}</p>
                  <Badge v-if="entry.total === cheapestTotal" variant="accent">Cheapest</Badge>
                  <Badge v-else-if="isBestValue(entry.total, index)" variant="neutral">Best Value</Badge>
                </div>
                <p class="truncate text-xs text-slate-500">{{ entry.market.address ?? 'Address unavailable' }}</p>
              </div>
            </div>

            <div class="flex items-center gap-4">
              <div class="text-right">
                <div v-if="basketTotalsLoadingByMarket[entry.market.id]">
                  <Skeleton width="6.5rem" height="1.25rem" />
                </div>
                <p
                  v-else-if="basketTotalsErrorByMarket[entry.market.id]"
                  class="text-xs text-slate-500"
                >
                  {{ basketTotalsErrorByMarket[entry.market.id] }}
                </p>
                <p v-else-if="entry.total !== Number.POSITIVE_INFINITY" class="text-lg font-semibold text-slate-900">
                  {{ formatMoney(entry.total) }}
                </p>
                <p v-else class="text-sm text-slate-400">Pending</p>
                <p class="text-xs text-slate-500">
                  {{ entry.recordedAt ?? 'latest' }} • {{ formatDelta(entry.total - (cheapestTotal ?? entry.total)) }}
                </p>
              </div>

              <svg class="h-4 w-4 text-slate-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                <path
                  fill-rule="evenodd"
                  d="M7.22 4.22a.75.75 0 011.06 0l5.25 5.25a.75.75 0 010 1.06l-5.25 5.25a.75.75 0 11-1.06-1.06L11.94 10 7.22 5.28a.75.75 0 010-1.06z"
                  clip-rule="evenodd"
                />
              </svg>
            </div>
          </button>
        </li>
      </ol>
    </Card>
  </section>
</template>
