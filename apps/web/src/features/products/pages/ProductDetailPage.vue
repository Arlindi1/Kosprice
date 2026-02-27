<script setup lang="ts">
import { computed, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'

import CitySelector from '@/features/city/components/CitySelector.vue'
import { useCityStore } from '@/features/city/store/useCityStore'
import { getMarketLogo } from '@/features/markets/utils/getMarketLogo'
import { useProductDetail } from '@/features/products/composables/useProductDetail'
import { getProductIcon } from '@/features/product/utils/getProductIcon'
import BarChart from '@/shared/charts/BarChart.vue'
import Badge from '@/shared/ui/Badge.vue'
import Button from '@/shared/ui/Button.vue'
import Card from '@/shared/ui/Card.vue'
import EmptyState from '@/shared/ui/EmptyState.vue'
import Skeleton from '@/shared/ui/Skeleton.vue'

const route = useRoute()
const router = useRouter()
const cityStore = useCityStore()
const selectedCityId = computed(() => cityStore.selectedCityId)

const {
  product,
  productError,
  isLoadingProduct,
  cheapestResult,
  cheapestError,
  isLoadingCheapest,
  loadProduct,
  loadCheapest,
} = useProductDetail()

const productId = computed<number | null>(() => {
  const raw = route.params.productId

  if (typeof raw !== 'string') {
    return null
  }

  const parsed = Number(raw)
  return Number.isInteger(parsed) && parsed > 0 ? parsed : null
})

const chartRows = computed(() => {
  if (!cheapestResult.value?.cheapest) {
    return []
  }

  return [cheapestResult.value.cheapest, ...cheapestResult.value.alternatives.slice(0, 4)]
})

const chartLabels = computed(() =>
  chartRows.value.map((row) => {
    const maxLength = 14
    return row.market.name.length > maxLength
      ? `${row.market.name.slice(0, maxLength - 1)}…`
      : row.market.name
  }),
)

const chartData = computed(() => [
  {
    label: 'Price (EUR)',
    data: chartRows.value.map((row) => row.price_eur),
    backgroundColor: chartRows.value.map((_, index) =>
      index === 0 ? 'rgba(79, 70, 229, 0.9)' : 'rgba(148, 163, 184, 0.7)',
    ),
    borderColor: chartRows.value.map((_, index) =>
      index === 0 ? 'rgba(67, 56, 202, 1)' : 'rgba(100, 116, 139, 1)',
    ),
  },
])

const productIcon = computed(() =>
  getProductIcon(product.value?.image_key, product.value?.category, product.value?.name),
)

function formatMoney(value: number): string {
  return value.toLocaleString('en-US', {
    style: 'currency',
    currency: 'EUR',
    minimumFractionDigits: 2,
  })
}

function formatDelta(value: number): string {
  if (value <= 0) {
    return 'Best'
  }

  return `+${value.toFixed(2)} EUR`
}

watch(
  productId,
  (resolvedProductId) => {
    void loadProduct(resolvedProductId)
  },
  { immediate: true },
)

watch(
  [productId, selectedCityId],
  ([resolvedProductId, cityId]) => {
    void loadCheapest(resolvedProductId, cityId)
  },
  { immediate: true },
)
</script>

<template>
  <section class="page-stack">
    <Button variant="ghost" size="sm" @click="router.push({ name: 'products', query: route.query })">
      Back to products
    </Button>

    <Card>
      <div v-if="isLoadingProduct" class="status-wrap">
        <Skeleton height="7.8rem" />
      </div>
      <div v-else-if="productError" class="status-wrap">
        <EmptyState
          title="Product unavailable"
          :message="productError"
          cta-label="Retry"
          @retry="loadProduct(productId, true)"
        />
      </div>
      <div v-else-if="!product" class="status-wrap">
        <EmptyState
          title="Product not found"
          message="This product is not available in the catalog."
        />
      </div>
      <div v-else class="space-y-6">
        <header class="flex flex-wrap items-center justify-between gap-4">
          <div class="flex min-w-0 items-center gap-4">
            <img
              :src="productIcon"
              :alt="product.name"
              class="h-16 w-16 rounded-xl border border-slate-200 bg-white p-2 object-contain shadow-soft"
            />
            <div class="min-w-0 space-y-1">
              <p class="text-label text-brand-700">Product details</p>
              <h2 class="truncate text-display">{{ product.name }}</h2>
              <p class="text-body">{{ product.unit_label ?? product.unit }}</p>
            </div>
          </div>

          <div class="w-full min-w-[220px] sm:w-auto">
            <CitySelector mode="pill" />
          </div>
        </header>

        <div v-if="isLoadingCheapest" class="status-wrap">
          <Skeleton height="6.8rem" />
          <Skeleton height="8rem" />
        </div>
        <div v-else-if="cheapestError" class="status-wrap">
          <EmptyState
            title="Cheapest lookup failed"
            :message="cheapestError"
            cta-label="Retry"
            @retry="loadCheapest(productId, selectedCityId, true)"
          />
        </div>
        <div v-else-if="!cheapestResult || !cheapestResult.cheapest" class="status-wrap">
          <EmptyState
            title="No market prices"
            message="No market prices are currently available for this product in the selected city."
          />
        </div>
        <div v-else class="space-y-5">
          <Card variant="subtle">
            <div class="flex flex-wrap items-start justify-between gap-3">
              <div class="flex items-start gap-3">
                <img
                  :src="getMarketLogo(cheapestResult.cheapest.market.name)"
                  :alt="`${cheapestResult.cheapest.market.name} logo`"
                  class="h-12 w-12 rounded-xl border border-slate-200 bg-white p-1 object-contain"
                />
                <div class="space-y-1">
                  <div class="flex items-center gap-2">
                    <Badge variant="accent">Cheapest Market</Badge>
                    <span class="text-xs text-slate-500">{{ cheapestResult.recorded_at ?? 'latest' }}</span>
                  </div>
                  <h3 class="text-2xl font-semibold tracking-tight text-slate-900">
                    {{ cheapestResult.cheapest.market.name }}
                  </h3>
                  <p class="text-sm text-slate-600">{{ cheapestResult.cheapest.market.address ?? 'Address unavailable' }}</p>
                </div>
              </div>
              <p class="text-3xl font-semibold tracking-tight text-brand-700">
                {{ formatMoney(cheapestResult.cheapest.price_eur) }}
              </p>
            </div>
          </Card>

          <section class="space-y-2">
            <h3 class="text-sm font-semibold text-slate-900">Alternatives ranked by price</h3>

            <div v-if="cheapestResult.alternatives.length === 0" class="status-wrap">
              <EmptyState
                title="No alternatives yet"
                message="Only one market currently has data for this product."
              />
            </div>
            <ol v-else class="divide-y divide-slate-200 rounded-xl border border-slate-200 bg-white">
              <li v-for="row in cheapestResult.alternatives" :key="row.market.id">
                <div class="flex items-center justify-between gap-3 px-4 py-3">
                  <div class="flex min-w-0 items-center gap-3">
                    <img
                      :src="getMarketLogo(row.market.name)"
                      :alt="`${row.market.name} logo`"
                      class="h-8 w-8 rounded-md border border-slate-200 bg-white p-0.5 object-contain"
                    />
                    <div class="min-w-0">
                      <p class="truncate text-sm font-semibold text-slate-900">{{ row.market.name }}</p>
                      <p class="truncate text-xs text-slate-500">{{ row.market.address ?? 'Address unavailable' }}</p>
                    </div>
                  </div>
                  <div class="text-right">
                    <p class="text-sm font-semibold text-slate-900">{{ formatMoney(row.price_eur) }}</p>
                    <p class="text-xs text-slate-600">{{ formatDelta(row.delta_from_cheapest_eur) }}</p>
                  </div>
                </div>
              </li>
            </ol>
          </section>

          <section
            v-if="chartRows.length > 1"
            class="rounded-2xl border border-slate-200 bg-slate-50/70 p-4"
          >
            <h3 class="text-sm font-semibold text-slate-900">Price comparison snapshot</h3>
            <p class="mt-0.5 text-xs text-slate-500">Cheapest market versus nearby alternatives.</p>
            <div class="mt-3">
              <BarChart
                :labels="chartLabels"
                :datasets="chartData"
                :height="180"
                :show-legend="false"
                :show-axes="false"
              />
            </div>
          </section>
        </div>
      </div>
    </Card>
  </section>
</template>
