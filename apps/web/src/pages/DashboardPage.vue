<script setup lang="ts">
import { computed, ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'

import { useBasket } from '@/features/basket/composables/useBasket'
import CitySelector from '@/features/city/components/CitySelector.vue'
import { useCityStore } from '@/features/city/store/useCityStore'
import CategoryRail from '@/features/dashboard/components/CategoryRail.vue'
import DashboardBasketTrendSection from '@/features/dashboard/components/DashboardBasketTrendSection.vue'
import MarketRankingList, {
  type MarketRankingRow,
} from '@/features/dashboard/components/MarketRankingList.vue'
import MarketBrandStrip from '@/features/dashboard/components/MarketBrandStrip.vue'
import ProductPickerDrawer from '@/features/dashboard/components/ProductPickerDrawer.vue'
import TodayHighlightsSection from '@/features/dashboard/components/TodayHighlightsSection.vue'
import TrendingItemsStrip from '@/features/dashboard/components/TrendingItemsStrip.vue'
import { useCheapestProductFinder } from '@/features/dashboard/composables/useCheapestProductFinder'
import { useDashboardToday } from '@/features/dashboard/composables/useDashboardToday'
import { useMarkets } from '@/features/market/composables/useMarkets'
import { getMarketLogo } from '@/features/markets/utils/getMarketLogo'
import { getProductImage } from '@/features/product/utils/getProductImage'
import PopularProductsCarousel from '@/features/products/components/PopularProductsCarousel.vue'
import ProductDetailsModal from '@/features/products/components/ProductDetailsModal.vue'
import ProductFinderPanel from '@/features/products/components/ProductFinderPanel.vue'
import { abortApiRequestsForCity } from '@/lib/api/client'
import Badge from '@/shared/ui/Badge.vue'
import Button from '@/shared/ui/Button.vue'
import HeroSection from '@/shared/ui/HeroSection.vue'
import PromoBanner from '@/shared/ui/PromoBanner.vue'
import placeholderImage from '@/shared/assets/products/images/placeholder.png'

const cityStore = useCityStore()
const activeCityId = computed(() => cityStore.activeCityId)
const router = useRouter()
const route = useRoute()

const isPickerDrawerOpen = ref(false)
const isProductDetailsOpen = ref(false)
const detailsProductId = ref<number | null>(null)
const selectedTrendDays = ref<14 | 30>(14)
const selectedCategory = ref('all')

const {
  basketTrend,
  isLoadingTrend,
  trendError,
  basketTotalsByMarket,
  basketTotalsLoadingByMarket,
  basketTotalsErrorByMarket,
  loadBasketTrend,
  loadTotalsForMarkets,
} = useBasket()
const { markets, isLoadingMarkets, marketsError, loadMarkets } = useMarkets()

const {
  allProducts,
  catalog: catalogProducts,
  selectedProductId,
  popularProducts,
  isLoadingPicker,
  pickerError,
  loadPickerData,
  clearSelection,
} = useCheapestProductFinder()
const {
  cityName: todayCityName,
  cheapestBasket: todayCheapestBasket,
  cheapestFuel: todayCheapestFuel,
  biggestPriceDrop,
  biggestPriceDropState,
  trendingItems,
  isLoading: isLoadingToday,
  error: todayError,
  loadTodayInsights,
} = useDashboardToday()

function normalizeCategory(value: string): string {
  return value.trim().toLowerCase()
}

const availableCategories = computed(() => {
  const categorySet = new Set<string>()

  for (const item of popularProducts.value) {
    const normalized = normalizeCategory(item.category)
    if (normalized.length > 0) {
      categorySet.add(normalized)
    }
  }

  for (const item of trendingItems.value) {
    const normalized = normalizeCategory(item.category)
    if (normalized.length > 0) {
      categorySet.add(normalized)
    }
  }

  return Array.from(categorySet).sort((left, right) => left.localeCompare(right))
})

const filteredPopularProducts = computed(() => {
  if (selectedCategory.value === 'all') {
    return popularProducts.value
  }

  return popularProducts.value.filter(
    (item) => normalizeCategory(item.category) === selectedCategory.value,
  )
})

const filteredTrendingItems = computed(() => {
  if (selectedCategory.value === 'all') {
    return trendingItems.value
  }

  return trendingItems.value.filter(
    (item) => normalizeCategory(item.category) === selectedCategory.value,
  )
})

const promoCityLabel = computed(() => {
  return todayCityName.value && todayCityName.value !== 'your city'
    ? todayCityName.value
    : 'your city'
})

const promoProductImages = computed(() => {
  const sourceItems =
    filteredPopularProducts.value.length > 0
      ? filteredPopularProducts.value
      : popularProducts.value
  const topItems = sourceItems.slice(0, 3)

  if (topItems.length === 0) {
    return [placeholderImage]
  }

  return topItems.map((item) => getProductImage(item))
})

const promoBrands = ['Maxi', 'Meridian', 'Interex', 'Viva Fresh']

const lastUpdatedLabel = computed(() => {
  const dates: string[] = []

  if (todayCheapestBasket.value?.recorded_at) {
    dates.push(todayCheapestBasket.value.recorded_at)
  }

  for (const point of basketTrend.value) {
    dates.push(point.recorded_at)
  }

  if (dates.length === 0) {
    return 'No updates yet'
  }

  dates.sort()
  const latestDate = dates[dates.length - 1]

  if (!latestDate) {
    return 'No updates yet'
  }

  return `Updated ${new Date(`${latestDate}T00:00:00Z`).toLocaleDateString('en-US', {
    month: 'short',
    day: 'numeric',
    year: 'numeric',
  })}`
})

async function refreshDashboard(cityId: number | null, force = false): Promise<void> {
  await Promise.allSettled([
    loadPickerData(cityId, force),
    loadBasketTrend(cityId, selectedTrendDays.value, force),
    loadMarkets(cityId, force),
    loadTodayInsights(cityId, force),
  ])

  if (cityId !== null) {
    const marketIds = markets.value.map((market) => market.id)
    await loadTotalsForMarkets(cityId, marketIds, force)
  }
}

function setSelectedProduct(productId: number): void {
  selectedProductId.value = productId
}

function openProductDetails(productId: number): void {
  detailsProductId.value = productId
  isProductDetailsOpen.value = true
}

function onProductDetailsToggle(value: boolean): void {
  isProductDetailsOpen.value = value

  if (!value) {
    detailsProductId.value = null
  }
}

watch(
  activeCityId,
  (cityId, previousCityId) => {
    if (typeof previousCityId === 'number' && previousCityId !== cityId) {
      abortApiRequestsForCity(previousCityId)
    }

    selectedCategory.value = 'all'
    clearSelection()
    void refreshDashboard(cityId)
  },
  { immediate: true },
)

watch(availableCategories, (categories) => {
  if (selectedCategory.value === 'all') {
    return
  }

  if (!categories.includes(selectedCategory.value)) {
    selectedCategory.value = 'all'
  }
})

watch(selectedTrendDays, (days) => {
  void loadBasketTrend(activeCityId.value, days)
})

const rankingRows = computed<MarketRankingRow[]>(() =>
  markets.value
    .map((market) => {
      const summary = basketTotalsByMarket.value[market.id]

      if (!summary) {
        return null
      }

      return {
        market_id: market.id,
        market_name: market.name,
        market_address: market.address,
        total_price_eur: summary.total_price_eur,
        recorded_at: summary.recorded_at,
      }
    })
    .filter((row): row is MarketRankingRow => row !== null)
    .sort((left, right) => left.total_price_eur - right.total_price_eur)
    .slice(0, 5),
)

const isLoadingRanking = computed(() => {
  if (isLoadingMarkets.value) {
    return true
  }

  return markets.value.some((market) => basketTotalsLoadingByMarket[market.id])
})

const rankingError = computed(() => {
  if (marketsError.value) {
    return marketsError.value
  }

  const firstTotalError = markets.value
    .map((market) => basketTotalsErrorByMarket[market.id])
    .find((message): message is string => Boolean(message))

  return firstTotalError ?? null
})

function openMarketDetail(marketId: number): void {
  void router.push({
    name: 'market-detail',
    params: { marketId },
    query: route.query,
  })
}

function scrollToProductFinder(): void {
  const finderSection = document.getElementById('product-finder')
  finderSection?.scrollIntoView({ behavior: 'smooth', block: 'start' })
}
</script>

<template>
  <div class="w-full bg-slate-100/80">
    <section class="w-full pt-6 md:pt-8">
      <div class="section-inner">
        <HeroSection
          eyebrow="Fresh catalog"
          title="Find the best grocery prices in Kosovo"
          subtitle="A city-first dashboard to compare nearby market prices before you buy."
          image-src="/images/shopping-family.jpg"
          image-alt="People shopping at a grocery market"
          :bullets="[
            'Compare nearby market prices in seconds',
            'See ranked alternatives by city and basket total',
            'Track grocery cost movement over time',
          ]"
        >
          <template #actions>
            <div class="flex flex-wrap items-center gap-2.5">
              <Button variant="secondary" size="lg" class="!border-white/30 !bg-white !text-slate-900" @click="scrollToProductFinder">
                Compare prices
              </Button>
              <CitySelector mode="pill" />
              <Badge variant="neutral" class="border border-white/20 bg-white/10 text-slate-100">
                {{ lastUpdatedLabel }}
              </Badge>
              <Button variant="secondary" size="sm" class="!border-white/20 !bg-transparent !text-white hover:!bg-white/10" @click="refreshDashboard(activeCityId, true)">
                Refresh
              </Button>
            </div>
          </template>
        </HeroSection>
      </div>
    </section>

    <section class="section-band w-full">
      <div class="section-inner space-y-8 md:space-y-10">
        <div class="rounded-2xl border border-slate-300 bg-white px-4 py-3.5 md:px-5 md:py-4">
          <div class="flex flex-wrap items-center justify-between gap-2">
            <p class="text-sm font-semibold text-slate-700">
              Spot local discounts faster with category and city filters.
            </p>
            <span class="inline-flex items-center rounded-full border border-slate-900 bg-slate-900 px-2.5 py-1 text-[11px] font-semibold uppercase tracking-wide text-white">
              Updated daily
            </span>
          </div>
        </div>

        <CategoryRail
          v-model="selectedCategory"
          :categories="availableCategories"
        />

        <TodayHighlightsSection
          :city-name="todayCityName"
          :basket="todayCheapestBasket"
          :biggest-drop="biggestPriceDrop"
          :biggest-drop-state="biggestPriceDropState"
          :cheapest-fuel="todayCheapestFuel"
          :is-loading="isLoadingToday"
          :error="todayError"
          @retry="loadTodayInsights(activeCityId, true)"
        />

        <TrendingItemsStrip
          :items="filteredTrendingItems"
          :is-loading="isLoadingToday"
          :error="todayError"
          @retry="loadTodayInsights(activeCityId, true)"
        />
      </div>
    </section>

    <section class="section-band w-full">
      <div class="section-inner space-y-6">
        <div class="grid gap-6 xl:grid-cols-2">
          <PromoBanner
            :title="`Weekly savings in ${promoCityLabel}`"
            subtitle="Track the lowest baskets and snap up everyday staples while prices are down."
            variant="amber"
          >
            <template #illustration>
              <div class="grid h-full grid-cols-2 gap-2">
                <div
                  v-for="(imageSrc, index) in promoProductImages.slice(0, 2)"
                  :key="`${imageSrc}:${index}`"
                  class="overflow-hidden rounded-2xl border border-white/80 bg-white/75"
                >
                  <img
                    :src="imageSrc"
                    alt="Promo product"
                    class="h-full w-full object-cover"
                  />
                </div>
              </div>
            </template>
          </PromoBanner>

          <PromoBanner
            title="Compare prices across Maxi, Meridian, Interex, Viva Fresh"
            subtitle="Browse market leaders in one place and find the best deal before checkout."
            variant="sky"
          >
            <template #illustration>
              <div class="grid h-full grid-cols-2 gap-2">
                <div
                  v-for="brand in promoBrands"
                  :key="brand"
                  class="flex items-center gap-2 rounded-2xl border border-white/80 bg-white/75 px-3 py-2"
                >
                  <img
                    :src="getMarketLogo(brand)"
                    :alt="`${brand} logo`"
                    class="h-8 w-8 rounded-md border border-slate-200 bg-white p-0.5 object-contain"
                  />
                  <span class="text-xs font-semibold text-slate-700">{{ brand }}</span>
                </div>
              </div>
            </template>
          </PromoBanner>
        </div>

        <MarketBrandStrip />
      </div>
    </section>

    <section id="product-finder" class="section-band w-full">
      <div class="section-inner space-y-8 md:space-y-10">
        <div class="mx-auto max-w-3xl space-y-3 text-center">
          <div class="flex flex-wrap items-center justify-center gap-2">
            <Badge variant="neutral" class="border border-slate-200 bg-white">
              City required
            </Badge>
          </div>
          <h3 class="text-heading text-3xl md:text-4xl">
            Find cheapest products near you
          </h3>
          <p class="text-body text-base md:text-lg">
            Start with popular products or search the full catalog.
          </p>
        </div>

        <div class="rounded-2xl border border-slate-300 bg-white px-4 py-3.5 md:px-5 md:py-4">
          <div class="flex flex-wrap items-center justify-between gap-2">
            <p class="text-sm font-semibold text-slate-700">
              Featured items include category accents and cheapest-price spotlights.
            </p>
            <span class="inline-flex rounded-full border border-slate-900 bg-slate-900 px-2.5 py-1 text-[11px] font-semibold uppercase tracking-wide text-white">
              Catalog picks
            </span>
          </div>
        </div>

        <div class="grid gap-5 md:gap-6 xl:grid-cols-[minmax(0,1.2fr)_minmax(0,1fr)]">
          <div class="rounded-3xl border border-slate-300 bg-white p-4 md:p-5">
            <PopularProductsCarousel
              :products="filteredPopularProducts"
              :selected-product-id="selectedProductId"
              :is-loading="isLoadingPicker"
              :error="pickerError"
              :city-id="activeCityId"
              @select="setSelectedProduct"
              @open-details="openProductDetails"
              @retry="refreshDashboard(activeCityId, true)"
              @browse-all="isPickerDrawerOpen = true"
            />
          </div>

          <div class="rounded-3xl border border-slate-300 bg-white p-4 md:p-5">
            <ProductFinderPanel
              :products="catalogProducts"
              :popular-products="filteredPopularProducts"
              :trending-items="filteredTrendingItems"
              :selected-product-id="selectedProductId"
              :city-id="activeCityId"
              :is-loading-products="isLoadingPicker"
              :error="pickerError"
              @select="setSelectedProduct"
              @compare="openProductDetails"
            />
          </div>
        </div>
      </div>
    </section>

    <section class="section-band w-full">
      <div class="section-inner">
        <MarketRankingList
          :rows="rankingRows"
          :is-loading="isLoadingRanking"
          :error="rankingError"
          @retry="refreshDashboard(activeCityId, true)"
          @select="openMarketDetail"
        />
      </div>
    </section>

    <section class="section-band w-full bg-white">
      <div class="section-inner">
        <DashboardBasketTrendSection
          :trend="basketTrend"
          :is-loading="isLoadingTrend"
          :error="trendError"
          :days="selectedTrendDays"
          @update:days="selectedTrendDays = $event"
          @retry="loadBasketTrend(activeCityId, selectedTrendDays, true)"
        />
      </div>
    </section>

    <ProductDetailsModal
      v-model="isProductDetailsOpen"
      :product-id="detailsProductId"
      :city-id="activeCityId"
      @update:model-value="onProductDetailsToggle"
    />

    <ProductPickerDrawer
      v-model="isPickerDrawerOpen"
      :products="allProducts"
      :selected-product-id="selectedProductId"
      :is-loading="isLoadingPicker"
      :error="pickerError"
      @select="setSelectedProduct"
      @retry="loadPickerData(activeCityId, true)"
    />
  </div>
</template>
