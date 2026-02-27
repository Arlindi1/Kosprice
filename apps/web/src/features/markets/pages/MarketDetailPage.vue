<script setup lang="ts">
import { computed, ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'

import { useCityStore } from '@/features/city/store/useCityStore'
import { useMarketDetail } from '@/features/markets/composables/useMarketDetail'
import { getMarketLogo } from '@/features/markets/utils/getMarketLogo'
import { getProductIcon } from '@/features/product/utils/getProductIcon'
import ProductDetailsModal from '@/features/products/components/ProductDetailsModal.vue'
import Badge from '@/shared/ui/Badge.vue'
import Button from '@/shared/ui/Button.vue'
import Card from '@/shared/ui/Card.vue'
import Chip from '@/shared/ui/Chip.vue'
import EmptyState from '@/shared/ui/EmptyState.vue'
import SearchInput from '@/shared/ui/SearchInput.vue'
import Select from '@/shared/ui/Select.vue'
import Skeleton from '@/shared/ui/Skeleton.vue'

const router = useRouter()
const route = useRoute()
const cityStore = useCityStore()

const {
  marketBasket,
  filteredItems,
  categories,
  selectedCategory,
  searchTerm,
  sortMode,
  coveragePercent,
  catalogProductCount,
  isLoadingDetail,
  detailError,
  loadMarketDetail,
} = useMarketDetail()
const selectedProductId = ref<number | null>(null)
const isProductDetailsOpen = ref(false)

const marketId = computed<number | null>(() => {
  const raw = route.params.marketId

  if (typeof raw !== 'string') {
    return null
  }

  const parsed = Number(raw)
  return Number.isInteger(parsed) && parsed > 0 ? parsed : null
})

const sortOptions = [
  { label: 'Price', value: 'price' },
  { label: 'Name', value: 'name' },
  { label: 'Category', value: 'category' },
]

const sortValue = computed({
  get: () => sortMode.value,
  set: (value: string) => {
    if (value === 'price' || value === 'name' || value === 'category') {
      sortMode.value = value
    }
  },
})

const marketLogo = computed(() => getMarketLogo(marketBasket.value?.market.name))
const resolvedCityId = computed(() => cityStore.selectedCityId ?? marketBasket.value?.market.city.id ?? null)

function formatMoney(value: number): string {
  return value.toLocaleString('en-US', {
    style: 'currency',
    currency: 'EUR',
    minimumFractionDigits: 2,
  })
}

function openProductDetails(productId: number): void {
  selectedProductId.value = productId
  isProductDetailsOpen.value = true
}

function onProductDetailsToggle(value: boolean): void {
  isProductDetailsOpen.value = value

  if (!value) {
    selectedProductId.value = null
  }
}

watch(
  marketId,
  (resolvedMarketId) => {
    void loadMarketDetail(resolvedMarketId)
  },
  { immediate: true },
)
</script>

<template>
  <section class="page-stack">
    <Button variant="ghost" size="sm" @click="router.push({ name: 'markets', query: route.query })">
      Back to markets
    </Button>

    <Card variant="default">
      <div v-if="isLoadingDetail" class="status-wrap">
        <Skeleton height="8.5rem" />
      </div>
      <div v-else-if="detailError" class="status-wrap">
        <EmptyState
          title="Market details unavailable"
          :message="detailError"
          cta-label="Retry"
          @retry="loadMarketDetail(marketId, true)"
        />
      </div>
      <div v-else-if="!marketBasket" class="status-wrap">
        <EmptyState
          title="Market not found"
          message="No basket data returned for this market."
        />
      </div>
      <div v-else class="space-y-6">
        <header class="flex flex-wrap items-start justify-between gap-5">
          <div class="flex min-w-0 items-center gap-4">
            <img
              :src="marketLogo"
              :alt="`${marketBasket.market.name} logo`"
              class="h-16 w-16 rounded-xl border border-slate-200 bg-white object-contain p-1.5 shadow-soft"
            />
            <div class="min-w-0 space-y-1">
              <p class="text-label text-brand-700">Market detail</p>
              <h2 class="truncate text-display">
                {{ marketBasket.market.name }}
              </h2>
              <p class="text-body">
                {{ marketBasket.market.city.name }} • {{ marketBasket.market.address ?? 'Address unavailable' }}
              </p>
            </div>
          </div>

          <div class="rounded-xl border border-slate-200 bg-slate-50/70 px-4 py-3 text-right">
            <p class="text-xs uppercase tracking-wide text-slate-500">Recorded</p>
            <p class="text-sm font-semibold text-slate-900">{{ marketBasket.recorded_at ?? 'latest' }}</p>
          </div>
        </header>

        <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_320px]">
          <div class="space-y-4">
            <div class="flex flex-wrap items-center gap-2">
              <Chip
                v-for="category in categories"
                :key="category"
                :active="selectedCategory === category"
                @click="selectedCategory = category"
              >
                {{ category === 'all' ? 'All' : category }}
              </Chip>
            </div>

            <div class="grid gap-3 md:grid-cols-[minmax(0,1fr)_160px]">
              <SearchInput v-model="searchTerm" placeholder="Search products in this market..." />
              <Select v-model="sortValue" :options="sortOptions" />
            </div>

            <div v-if="filteredItems.length === 0" class="status-wrap">
              <EmptyState
                title="No matching products"
                message="Adjust category, search, or sorting to see product cards."
              />
            </div>
            <div v-else class="grid gap-3 sm:grid-cols-2">
              <button
                v-for="item in filteredItems"
                :key="item.product_id"
                type="button"
                class="interactive-subtle rounded-xl border border-slate-200 bg-white p-4 text-left"
                @click="openProductDetails(item.product_id)"
              >
                <div class="flex items-start justify-between gap-3">
                  <div class="flex min-w-0 items-center gap-3">
                    <img
                      :src="getProductIcon(item.image_key, item.category, item.name)"
                      :alt="item.name"
                      class="h-12 w-12 rounded-xl border border-slate-200 bg-slate-50 p-1 object-contain"
                    />
                    <div class="min-w-0">
                      <h3 class="truncate text-lg font-semibold tracking-tight text-slate-900">{{ item.name }}</h3>
                      <p class="truncate text-sm text-slate-500">{{ item.unit_label ?? item.unit }}</p>
                    </div>
                  </div>
                  <Badge variant="neutral">{{ item.category }}</Badge>
                </div>

                <div class="mt-4 flex items-center justify-between gap-2">
                  <p class="text-2xl font-semibold tracking-tight text-slate-900">
                    {{ formatMoney(item.price_eur) }}
                  </p>
                  <Badge variant="accent">Price</Badge>
                </div>
              </button>
            </div>
          </div>

          <aside class="space-y-4 xl:sticky xl:top-6">
            <Card title="Basket summary" subtitle="Current market basket snapshot" variant="subtle">
              <p class="text-3xl font-semibold tracking-tight text-slate-900">
                {{ formatMoney(marketBasket.total_price_eur) }}
              </p>
              <p class="mt-2 text-sm text-slate-600">
                {{ filteredItems.length }} products shown • {{ marketBasket.items.length }} total items
              </p>
              <p class="mt-2 text-sm text-slate-600">
                Coverage {{ marketBasket.items.length }} / {{ catalogProductCount }} ({{ coveragePercent }}%)
              </p>
              <p class="mt-2 text-sm text-slate-600">
                Last updated {{ marketBasket.recorded_at ?? 'today' }}
              </p>
              <p class="mt-4 text-xs text-slate-500">
                Use filters to focus on categories while keeping full-basket context in view.
              </p>
            </Card>
          </aside>
        </div>
      </div>
    </Card>

    <ProductDetailsModal
      v-model="isProductDetailsOpen"
      :product-id="selectedProductId"
      :city-id="resolvedCityId"
      @update:model-value="onProductDetailsToggle"
    />
  </section>
</template>
