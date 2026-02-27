<script setup lang="ts">
import { computed, ref, watch } from 'vue'

import type { DashboardTrendingItem } from '@/features/dashboard/composables/useDashboardToday'
import { getMarketLogo } from '@/features/markets/utils/getMarketLogo'
import { getProductImage } from '@/features/product/utils/getProductImage'
import { useProductCheapest } from '@/features/products/composables/useProductCheapest'
import { useProductPricesByCity } from '@/features/products/composables/useProductPricesByCity'
import type { ProductCatalogItem } from '@/lib/types/api'
import Button from '@/shared/ui/Button.vue'
import EmptyState from '@/shared/ui/EmptyState.vue'
import SearchInput from '@/shared/ui/SearchInput.vue'
import Skeleton from '@/shared/ui/Skeleton.vue'

const props = withDefaults(
  defineProps<{
    products: ProductCatalogItem[]
    popularProducts: ProductCatalogItem[]
    trendingItems: DashboardTrendingItem[]
    selectedProductId: number | null
    cityId: number | null
    isLoadingProducts: boolean
    error: string | null
  }>(),
  {
    selectedProductId: null,
    cityId: null,
    error: null,
  },
)

const emit = defineEmits<{
  select: [productId: number]
  compare: [productId: number]
}>()

const searchTerm = ref('')
const isPricesExpanded = ref(false)

function normalizeText(value: string): string {
  return value.trim().toLowerCase()
}

const byId = computed(() => {
  const mapping = new Map<number, ProductCatalogItem>()

  for (const item of props.products) {
    mapping.set(item.id, item)
  }

  return mapping
})

const featuredPick = computed(() => {
  const popular = props.popularProducts[0]
  if (popular) {
    return popular
  }

  for (const trending of props.trendingItems) {
    const mapped = byId.value.get(trending.product_id)
    if (mapped) {
      return mapped
    }
  }

  return props.products[0] ?? null
})

const selectedProduct = computed(() => {
  if (props.selectedProductId !== null) {
    const mapped = byId.value.get(props.selectedProductId)
    if (mapped) {
      return mapped
    }
  }

  return featuredPick.value
})

const activeProductId = computed<number | null>(() => selectedProduct.value?.id ?? null)
const selectedCityId = computed<number | null>(() => props.cityId)

const {
  result: cheapestResult,
  isLoading: isLoadingCheapest,
  error: cheapestError,
  refresh: refreshCheapest,
} = useProductCheapest(activeProductId, selectedCityId)

const {
  rows: priceRows,
  isLoading: isLoadingPrices,
  error: pricesError,
  load: loadPrices,
  clear: clearPrices,
} = useProductPricesByCity(activeProductId, selectedCityId)

const searchResults = computed(() => {
  const query = normalizeText(searchTerm.value)

  if (query.length === 0) {
    if (props.popularProducts.length > 0) {
      return props.popularProducts.slice(0, 6)
    }

    return props.products.slice(0, 6)
  }

  return props.products
    .filter((item) => normalizeText(item.name).includes(query))
    .slice(0, 8)
})

const isCityMissing = computed(() => props.cityId === null)

watch([activeProductId, selectedCityId], () => {
  isPricesExpanded.value = false
  clearPrices()
  void refreshCheapest()
}, { immediate: true })

watch(isPricesExpanded, (expanded) => {
  if (!expanded) {
    return
  }

  if (activeProductId.value === null || selectedCityId.value === null) {
    return
  }

  void loadPrices(true)
})

function formatMoney(value: number | null): string {
  if (value === null) {
    return 'No city price'
  }

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

function chooseProduct(productId: number): void {
  emit('select', productId)
}

function compareProduct(productId: number): void {
  emit('select', productId)
  emit('compare', productId)
}

function togglePrices(): void {
  if (isCityMissing.value || activeProductId.value === null) {
    return
  }

  isPricesExpanded.value = !isPricesExpanded.value
}
</script>

<template>
  <section class="space-y-5">
    <div class="space-y-1">
      <h3 class="text-heading">Product Finder</h3>
      <p class="text-body">Search products, lock a featured pick, and compare live market prices.</p>
    </div>

    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
      <SearchInput
        v-model="searchTerm"
        placeholder="Search by product name..."
        :disabled="isCityMissing || props.isLoadingProducts"
      />

      <p v-if="isCityMissing" class="mt-2 text-xs font-semibold text-slate-600">
        Select a city to enable product search and live price actions.
      </p>
    </div>

    <div v-if="props.error" class="status-wrap">
      <EmptyState
        title="Finder data unavailable"
        :message="props.error"
      />
    </div>

    <div v-else class="space-y-4">
      <div class="rounded-2xl border border-slate-200 bg-white p-4">
        <div class="flex items-center justify-between gap-3">
          <p class="text-sm font-bold text-slate-900">Featured pick</p>
          <Button
            v-if="selectedProduct"
            size="sm"
            :disabled="isCityMissing"
            @click="compareProduct(selectedProduct.id)"
          >
            Compare
          </Button>
        </div>

        <div v-if="props.isLoadingProducts" class="mt-3 space-y-2">
          <Skeleton height="6.2rem" rounded="0.85rem" />
        </div>

        <div v-else-if="selectedProduct" class="mt-3 rounded-xl border border-slate-200 bg-slate-50 p-3">
          <div class="flex items-start gap-3">
            <div class="h-20 w-20 shrink-0 overflow-hidden rounded-lg border border-slate-200 bg-white">
              <img
                :src="getProductImage(selectedProduct)"
                :alt="selectedProduct.name"
                class="h-full w-full object-contain p-2"
              />
            </div>
            <div class="min-w-0 flex-1 space-y-1">
              <p class="clamp-2 text-base font-bold text-slate-900">{{ selectedProduct.name }}</p>
              <p class="text-xs text-slate-500">{{ selectedProduct.unit_label ?? 'Unit unavailable' }}</p>
              <span
                class="inline-flex h-6 items-center rounded-full border border-slate-300 bg-slate-100 px-2.5 text-[11px] font-semibold uppercase tracking-wide text-slate-700"
              >
                {{ selectedProduct.category }}
              </span>
              <p class="text-price-display text-2xl text-slate-900">
                {{ formatMoney(selectedProduct.cheapest_price_today) }}
              </p>
            </div>
          </div>
        </div>

        <div v-else class="status-wrap mt-3">
          <EmptyState
            title="No featured pick"
            message="Products will appear here when catalog data loads."
          />
        </div>
      </div>

      <div class="rounded-2xl border border-slate-200 bg-white p-4">
        <p class="text-sm font-bold text-slate-900">Quick select</p>
        <div class="mt-3 grid gap-2">
          <button
            v-for="product in searchResults"
            :key="product.id"
            type="button"
            class="flex items-center justify-between gap-3 rounded-xl border border-slate-200 bg-white px-3 py-2 text-left transition hover:border-slate-300 hover:bg-slate-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-200 disabled:cursor-not-allowed disabled:opacity-60"
            :disabled="isCityMissing"
            @click="chooseProduct(product.id)"
          >
            <span class="truncate text-sm font-semibold text-slate-800">{{ product.name }}</span>
            <span class="text-price-display text-xs text-slate-900">{{ formatMoney(product.cheapest_price_today) }}</span>
          </button>
        </div>
      </div>

      <div class="rounded-2xl border border-slate-200 bg-white p-4">
        <div class="flex items-center justify-between gap-3">
          <p class="text-sm font-bold text-slate-900">Cheapest market highlight</p>
          <Button
            variant="ghost"
            size="sm"
            :disabled="isCityMissing || activeProductId === null"
            @click="refreshCheapest(true)"
          >
            Refresh
          </Button>
        </div>

        <div v-if="isCityMissing" class="mt-3 rounded-xl border border-slate-200 bg-slate-50 p-3">
          <p class="text-sm font-semibold text-slate-800">Pick your city to unlock live market comparison.</p>
        </div>

        <div v-else-if="isLoadingCheapest" class="mt-3 space-y-2">
          <Skeleton height="6rem" rounded="0.85rem" />
        </div>

        <div v-else-if="cheapestError" class="status-wrap mt-3">
          <EmptyState
            title="Cheapest lookup unavailable"
            :message="cheapestError"
            cta-label="Retry"
            @retry="refreshCheapest(true)"
          />
        </div>

        <article
          v-else-if="cheapestResult?.cheapest"
          class="mt-3 rounded-xl border border-slate-200 bg-slate-50 p-3"
        >
          <div class="flex items-start justify-between gap-3">
            <div class="flex min-w-0 items-center gap-3">
              <img
                :src="getMarketLogo(cheapestResult.cheapest.market.name)"
                :alt="`${cheapestResult.cheapest.market.name} logo`"
                class="h-10 w-10 rounded-md border border-slate-200 bg-white p-0.5 object-contain"
              />
            <div class="min-w-0">
              <p class="truncate text-sm font-semibold text-slate-900">{{ cheapestResult.cheapest.market.name }}</p>
              <p class="truncate text-xs text-slate-500">{{ cheapestResult.cheapest.market.address ?? 'Address unavailable' }}</p>
            </div>
          </div>
            <p class="text-price-display text-2xl text-slate-900">{{ formatMoney(cheapestResult.cheapest.price_eur) }}</p>
          </div>
        </article>

        <div v-else class="status-wrap mt-3">
          <EmptyState
            title="No prices found"
            message="No cheapest-market result is available for the selected product in this city."
          />
        </div>

        <div class="mt-4 border-t border-slate-200 pt-4">
          <Button
            variant="secondary"
            size="sm"
            :disabled="isCityMissing || activeProductId === null"
            @click="togglePrices"
          >
            {{ isPricesExpanded ? 'Hide market prices' : 'Show all market prices' }}
          </Button>

          <div v-if="isPricesExpanded" class="mt-3 space-y-2">
            <div v-if="isLoadingPrices" class="space-y-2">
              <Skeleton v-for="index in 5" :key="index" height="3.5rem" rounded="0.75rem" />
            </div>

            <div v-else-if="pricesError" class="status-wrap">
              <EmptyState
                title="Market prices unavailable"
                :message="pricesError"
                cta-label="Retry"
                @retry="loadPrices(true)"
              />
            </div>

            <div v-else-if="priceRows.length === 0" class="status-wrap">
              <EmptyState
                title="No market prices"
                message="No market rows were returned for this product in the selected city."
              />
            </div>

            <ol v-else class="space-y-1.5">
              <li
                v-for="row in priceRows"
                :key="row.market.id"
                class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2"
              >
                <div class="flex items-center justify-between gap-3">
                  <div class="flex min-w-0 items-center gap-2.5">
                    <img
                      :src="getMarketLogo(row.market.name)"
                      :alt="`${row.market.name} logo`"
                      class="h-7 w-7 rounded-md border border-slate-200 bg-white p-0.5 object-contain"
                    />
                    <div class="min-w-0">
                      <p class="truncate text-sm font-semibold text-slate-900">{{ row.market.name }}</p>
                      <p class="text-xs text-slate-500">{{ formatDelta(row.delta_from_cheapest_eur) }}</p>
                    </div>
                  </div>
                  <p class="text-price-display text-sm text-slate-900">{{ formatMoney(row.price_eur) }}</p>
                </div>
              </li>
            </ol>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>
