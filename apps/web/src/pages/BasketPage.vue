<script setup lang="ts">
import { computed, watch } from 'vue'

import { useBasket } from '@/features/basket/composables/useBasket'
import CitySelector from '@/features/city/components/CitySelector.vue'
import { useCityStore } from '@/features/city/store/useCityStore'
import { useMarkets } from '@/features/market/composables/useMarkets'
import { abortApiRequestsForCity } from '@/lib/api/client'
import LineChart from '@/shared/charts/LineChart.vue'
import Button from '@/shared/ui/Button.vue'
import Card from '@/shared/ui/Card.vue'
import EmptyState from '@/shared/ui/EmptyState.vue'
import Skeleton from '@/shared/ui/Skeleton.vue'

const cityStore = useCityStore()
const activeCityId = computed(() => cityStore.activeCityId)

const { markets, isLoadingMarkets, marketsError, loadMarkets } = useMarkets()

const {
  basketTrend,
  isLoadingTrend,
  trendError,
  basketTotalsByMarket,
  basketTotalsLoadingByMarket,
  basketTotalsErrorByMarket,
  loadBasketTrend,
  loadTotalsForMarkets,
  loadBasketTotal,
} = useBasket()

const trendLabels = computed(() => basketTrend.value.map((point) => point.recorded_at.slice(5)))
const trendDatasets = computed(() => [
  {
    label: 'Average Basket (EUR)',
    data: basketTrend.value.map((point) => point.average_total_eur),
    borderColor: '#4338ca',
    backgroundColor: 'rgba(79, 70, 229, 0.2)',
    fill: true,
  },
])

function formatMoney(value: number): string {
  return value.toLocaleString('en-US', {
    style: 'currency',
    currency: 'EUR',
    minimumFractionDigits: 2,
  })
}

async function loadBasketIndex(force = false): Promise<void> {
  await loadBasketTrend(activeCityId.value, 30, force)
  await loadMarkets(activeCityId.value, force)
  const marketIds = markets.value.map((market) => market.id)
  await loadTotalsForMarkets(activeCityId.value, marketIds, force)
}

function refreshMarketTotal(marketId: number): void {
  void loadBasketTotal(activeCityId.value, marketId, true)
}

watch(
  activeCityId,
  (_, previousCityId) => {
    if (typeof previousCityId === 'number') {
      abortApiRequestsForCity(previousCityId)
    }

    void loadBasketIndex()
  },
  { immediate: true },
)
</script>

<template>
  <section class="page-stack">
    <Card variant="highlight" title="Basket Index" subtitle="Track cost movement of a standard household basket">
      <div class="status-wrap">
        <p class="text-body">
          The basket index summarizes everyday product prices into one comparable total per market.
          Lower values mean lower cost of living pressure for that city snapshot.
        </p>
        <CitySelector />
      </div>
    </Card>

    <Card title="30-Day Basket Index Trend">
      <div v-if="isLoadingTrend" class="status-wrap">
        <Skeleton height="18rem" />
      </div>
      <div v-else-if="trendError" class="status-wrap">
        <EmptyState title="Trend unavailable" :message="trendError" cta-label="Retry" @retry="loadBasketTrend(activeCityId, 30, true)" />
      </div>
      <div v-else-if="basketTrend.length === 0" class="status-wrap">
        <EmptyState title="No trend points" message="No basket trend data available for this city." />
      </div>
      <LineChart v-else :labels="trendLabels" :datasets="trendDatasets" :height="320" />
    </Card>

    <Card title="Current Basket Totals By Market">
      <div v-if="isLoadingMarkets" class="status-wrap">
        <Skeleton height="1.8rem" />
        <Skeleton height="1.8rem" />
      </div>
      <div v-else-if="marketsError" class="status-wrap">
        <EmptyState title="Totals unavailable" :message="marketsError" cta-label="Retry" @retry="loadBasketIndex(true)" />
      </div>
      <div v-else-if="markets.length === 0" class="status-wrap">
        <EmptyState title="No markets found" message="No markets found in this city." />
      </div>
      <ol v-else class="divide-y divide-slate-200 rounded-xl border border-slate-200 bg-white">
        <li v-for="market in markets" :key="market.id">
          <div class="flex items-center justify-between gap-3 px-4 py-3">
            <div class="min-w-0">
              <p class="truncate text-sm font-semibold text-slate-900">{{ market.name }}</p>
              <p class="truncate text-xs text-slate-500">{{ market.address ?? 'Address unavailable' }}</p>
            </div>
            <div class="flex items-center gap-3">
              <div class="text-right">
                <Skeleton v-if="basketTotalsLoadingByMarket[market.id]" width="6rem" />
                <span v-else-if="basketTotalsErrorByMarket[market.id]" class="small-text muted">
                  {{ basketTotalsErrorByMarket[market.id] }}
                </span>
                <strong v-else-if="basketTotalsByMarket[market.id]">
                  {{ formatMoney(basketTotalsByMarket[market.id]?.total_price_eur ?? 0) }}
                </strong>
                <span v-else class="muted">-</span>
              </div>
              <Button variant="ghost" size="sm" @click="refreshMarketTotal(market.id)">Refresh</Button>
            </div>
          </div>
        </li>
      </ol>
    </Card>
  </section>
</template>
