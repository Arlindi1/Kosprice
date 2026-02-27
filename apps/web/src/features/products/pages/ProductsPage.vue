<script setup lang="ts">
import { computed, ref, watch } from 'vue'

import CitySelector from '@/features/city/components/CitySelector.vue'
import { useCityStore } from '@/features/city/store/useCityStore'
import { useDashboardToday } from '@/features/dashboard/composables/useDashboardToday'
import { useProducts } from '@/features/product/composables/useProducts'
import CatalogSection from '@/features/products/components/CatalogSection.vue'
import ProductDetailsModal from '@/features/products/components/ProductDetailsModal.vue'
import {
  getCategoryAccent,
  getCategoryChipClass,
} from '@/features/products/utils/getCategoryAccent'
import { abortApiRequestsForCity } from '@/lib/api/client'
import Button from '@/shared/ui/Button.vue'
import Chip from '@/shared/ui/Chip.vue'
import EmptyState from '@/shared/ui/EmptyState.vue'
import SearchInput from '@/shared/ui/SearchInput.vue'
import Skeleton from '@/shared/ui/Skeleton.vue'

const cityStore = useCityStore()
const activeCityId = computed(() => cityStore.activeCityId)

const { catalog, isLoadingCatalog, catalogError, loadCatalog } = useProducts()
const { trendingItems: todayTrendingItems, loadTodayInsights } = useDashboardToday()

const selectedCategory = ref('all')
const searchTerm = ref('')
const selectedProductId = ref<number | null>(null)
const isProductDetailsOpen = ref(false)

const sectionOrder = [
  { id: 'diet-products', label: 'Diet products' },
  { id: 'protein-bars', label: 'Protein bars' },
  { id: 'top-price-drops', label: 'Top price drops' },
  { id: 'most-compared', label: 'Most compared' },
]

const expandedSections = ref<Record<string, boolean>>({
  'diet-products': false,
  'protein-bars': false,
  'top-price-drops': false,
  'most-compared': false,
})

function normalizeText(value: string): string {
  return value.trim().toLowerCase()
}

function normalizeCategory(value: string): string {
  return value.trim().toLowerCase()
}

function formatCategoryLabel(value: string): string {
  if (value === 'all') {
    return 'All categories'
  }

  return value
    .split(/[\s_-]+/)
    .map((token) => token.charAt(0).toUpperCase() + token.slice(1))
    .join(' ')
}

const categories = computed(() => {
  const uniqueCategories = new Set(catalog.value.map((item) => normalizeCategory(item.category)))
  return ['all', ...Array.from(uniqueCategories).sort((left, right) => left.localeCompare(right))]
})

const filteredCatalog = computed(() => {
  const normalizedQuery = normalizeText(searchTerm.value)

  return catalog.value
    .filter((item) => {
      const itemCategory = normalizeCategory(item.category)
      const matchesCategory =
        selectedCategory.value === 'all' || itemCategory === selectedCategory.value
      const searchable = `${item.name} ${item.unit_label ?? ''} ${item.category}`.toLowerCase()
      const matchesSearch =
        normalizedQuery.length === 0 || searchable.includes(normalizedQuery)

      return matchesCategory && matchesSearch
    })
    .sort((left, right) => {
      const leftPrice = left.cheapest_price_today ?? Number.POSITIVE_INFINITY
      const rightPrice = right.cheapest_price_today ?? Number.POSITIVE_INFINITY
      if (leftPrice !== rightPrice) {
        return leftPrice - rightPrice
      }

      return left.name.localeCompare(right.name)
    })
})

const catalogById = computed(() => {
  const mapping = new Map<number, (typeof catalog.value)[number]>()

  for (const item of filteredCatalog.value) {
    mapping.set(item.id, item)
  }

  return mapping
})

const selectedCategoryLabel = computed(() => {
  if (selectedCategory.value === 'all') {
    return 'All categories'
  }

  return selectedCategory.value
    .split(/[\s_-]+/)
    .map((token) => token.charAt(0).toUpperCase() + token.slice(1))
    .join(' ')
})

const categoryHeaderClass = computed(() => {
  const accent = getCategoryAccent(selectedCategory.value)

  if (accent === 'amber') {
    return 'border-amber-200 bg-amber-50'
  }

  if (accent === 'sky') {
    return 'border-sky-200 bg-sky-50'
  }

  if (accent === 'lime') {
    return 'border-lime-200 bg-lime-50'
  }

  return 'border-slate-200 bg-slate-50'
})

const trendingRankByProductId = computed(() => {
  const ranking = new Map<number, number>()

  todayTrendingItems.value.forEach((item, index) => {
    ranking.set(item.product_id, index)
  })

  return ranking
})

const topPriceDropProductIds = computed(() =>
  todayTrendingItems.value
    .filter((item) => item.badge_label.trim().startsWith('-'))
    .map((item) => item.product_id),
)

function filterByKeywords(
  items: typeof filteredCatalog.value,
  keywords: string[],
): typeof filteredCatalog.value {
  return items.filter((item) => {
    const searchable = `${item.name} ${item.category}`.toLowerCase()
    return keywords.some((keyword) => searchable.includes(keyword))
  })
}

const dietProducts = computed(() => {
  const preferred = filterByKeywords(filteredCatalog.value, [
    'diet',
    'light',
    'zero',
    'water',
    'tea',
    'juice',
    'yogurt',
    'produce',
    'beverage',
    'dairy',
  ])

  return preferred.length > 0 ? preferred : filteredCatalog.value
})

const proteinBars = computed(() => {
  const preferred = filterByKeywords(filteredCatalog.value, [
    'protein',
    'bar',
    'chicken',
    'egg',
    'bean',
    'cheese',
    'milk',
    'yogurt',
  ])

  if (preferred.length > 0) {
    return preferred
  }

  const coreFallback = filteredCatalog.value.filter((item) => item.is_core_basket)
  return coreFallback.length > 0 ? coreFallback : filteredCatalog.value
})

const topPriceDrops = computed(() => {
  const resolved = topPriceDropProductIds.value
    .map((productId) => catalogById.value.get(productId) ?? null)
    .filter((item): item is (typeof filteredCatalog.value)[number] => item !== null)

  if (resolved.length > 0) {
    return resolved
  }

  const priced = filteredCatalog.value
    .filter((item) => item.cheapest_price_today !== null)
    .sort((left, right) => (left.cheapest_price_today ?? 0) - (right.cheapest_price_today ?? 0))

  return priced.length > 0 ? priced : filteredCatalog.value
})

const mostCompared = computed(() =>
  [...filteredCatalog.value].sort((left, right) => {
    if (left.is_core_basket !== right.is_core_basket) {
      return left.is_core_basket ? -1 : 1
    }

    const leftTrendRank = trendingRankByProductId.value.get(left.id) ?? Number.POSITIVE_INFINITY
    const rightTrendRank = trendingRankByProductId.value.get(right.id) ?? Number.POSITIVE_INFINITY
    if (leftTrendRank !== rightTrendRank) {
      return leftTrendRank - rightTrendRank
    }

    const leftPrice = left.cheapest_price_today ?? Number.POSITIVE_INFINITY
    const rightPrice = right.cheapest_price_today ?? Number.POSITIVE_INFINITY
    if (leftPrice !== rightPrice) {
      return leftPrice - rightPrice
    }

    return left.name.localeCompare(right.name)
  }),
)

async function refreshCatalog(force = false): Promise<void> {
  await Promise.allSettled([
    loadCatalog(activeCityId.value, force),
    loadTodayInsights(activeCityId.value, force),
  ])
}

function filterChipClass(category: string): string {
  if (category === 'all') {
    return ''
  }

  return getCategoryChipClass(category)
}

function openProduct(productId: number): void {
  selectedProductId.value = productId
  isProductDetailsOpen.value = true
}

function onProductDetailsToggle(value: boolean): void {
  isProductDetailsOpen.value = value

  if (!value) {
    selectedProductId.value = null
  }
}

function seeAll(sectionId: string): void {
  expandedSections.value[sectionId] = true
}

function resetExpandedSections(): void {
  expandedSections.value = {
    'diet-products': false,
    'protein-bars': false,
    'top-price-drops': false,
    'most-compared': false,
  }
}

function scrollToSection(sectionId: string): void {
  const target = document.getElementById(sectionId)
  target?.scrollIntoView({ behavior: 'smooth', block: 'start' })
}

watch(
  activeCityId,
  (_, previousCityId) => {
    if (typeof previousCityId === 'number') {
      abortApiRequestsForCity(previousCityId)
    }

    selectedCategory.value = 'all'
    searchTerm.value = ''
    resetExpandedSections()
    void refreshCatalog()
  },
  { immediate: true },
)

watch([selectedCategory, searchTerm], () => {
  resetExpandedSections()
})
</script>

<template>
  <section class="w-full">
    <div class="sticky top-0 z-30 border-y border-slate-200 bg-white/95 backdrop-blur">
      <div class="section-inner space-y-3 py-3">
        <div class="grid gap-3 lg:grid-cols-[minmax(0,1fr)_auto] lg:items-center">
          <div class="mx-auto w-full max-w-2xl">
            <SearchInput
              v-model="searchTerm"
              placeholder="Search products, categories, or units..."
            />
          </div>

          <div class="flex flex-wrap items-center justify-end gap-2">
            <CitySelector mode="pill" />
            <Button variant="ghost" size="sm" @click="refreshCatalog(true)">
              Refresh
            </Button>
          </div>
        </div>

        <div class="overflow-x-auto pb-1">
          <nav class="flex min-w-max items-center gap-2">
            <button
              v-for="section in sectionOrder"
              :key="section.id"
              type="button"
              class="rounded-xl border border-slate-200 bg-white px-3 py-1.5 text-sm font-semibold text-slate-700 transition hover:border-indigo-200 hover:bg-indigo-50 hover:text-indigo-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-200"
              @click="scrollToSection(section.id)"
            >
              {{ section.label }}
            </button>
          </nav>
        </div>
      </div>
    </div>

    <div class="section-inner space-y-6 py-6 md:space-y-8 md:py-8">
      <header class="rounded-3xl border border-slate-200 bg-white p-6">
        <p class="text-label">Store Catalog</p>
        <h2 class="text-display">Browse product collections</h2>
        <p class="mt-2 max-w-2xl text-body">
          Explore curated sections, compare live prices, and open product details from any card.
        </p>
      </header>

      <div class="overflow-x-auto pb-1">
        <div class="flex min-w-max items-center gap-2">
          <Chip
            v-for="category in categories"
            :key="category"
            :active="selectedCategory === category"
            :class="selectedCategory === category ? '' : filterChipClass(category)"
            @click="selectedCategory = category"
          >
            {{ formatCategoryLabel(category) }}
          </Chip>
        </div>
      </div>

      <section
        v-if="selectedCategory !== 'all'"
        class="rounded-3xl border p-5"
        :class="categoryHeaderClass"
      >
        <p class="text-label">Category focus</p>
        <h3 class="text-2xl font-extrabold tracking-tight text-slate-900 md:text-3xl">
          {{ selectedCategoryLabel }}
        </h3>
        <p class="mt-1 text-sm text-slate-600">
          Showing {{ filteredCatalog.length }} matching products for this category.
        </p>
      </section>

      <div v-if="isLoadingCatalog" class="grid grid-cols-2 gap-3 md:grid-cols-3 xl:grid-cols-5">
        <Skeleton v-for="index in 8" :key="index" height="21rem" rounded="1rem" />
      </div>

      <div v-else-if="catalogError" class="status-wrap">
        <EmptyState
          title="Catalog unavailable"
          :message="catalogError"
          cta-label="Retry"
          @retry="refreshCatalog(true)"
        />
      </div>

      <div v-else-if="filteredCatalog.length === 0" class="status-wrap">
        <EmptyState
          title="No matching products"
          message="Try a different search query or category selection."
          cta-label="Reload catalog"
          @retry="refreshCatalog(true)"
        />
      </div>

      <div v-else class="space-y-8">
        <CatalogSection
          section-id="diet-products"
          title="Diet products"
          subtitle="Lighter grocery picks and hydration essentials for everyday balance."
          :products="dietProducts"
          :expanded="expandedSections['diet-products']"
          empty-message="No diet-focused items match your current filters."
          @compare="openProduct"
          @see-all="seeAll('diet-products')"
        />

        <CatalogSection
          section-id="protein-bars"
          title="Protein bars"
          subtitle="Protein-forward snack alternatives and core energy staples."
          :products="proteinBars"
          :expanded="expandedSections['protein-bars']"
          empty-message="No protein-focused items match your current filters."
          @compare="openProduct"
          @see-all="seeAll('protein-bars')"
        />

        <CatalogSection
          section-id="top-price-drops"
          title="Top price drops"
          subtitle="Products with notable movement from recent city comparison signals."
          :products="topPriceDrops"
          :expanded="expandedSections['top-price-drops']"
          empty-message="No drop signals are available for this city right now."
          @compare="openProduct"
          @see-all="seeAll('top-price-drops')"
        />

        <CatalogSection
          section-id="most-compared"
          title="Most compared"
          subtitle="Frequently evaluated staples that shoppers check across markets."
          :products="mostCompared"
          :expanded="expandedSections['most-compared']"
          empty-message="No comparison-heavy items are available for this city right now."
          @compare="openProduct"
          @see-all="seeAll('most-compared')"
        />
      </div>
    </div>

    <ProductDetailsModal
      v-model="isProductDetailsOpen"
      :product-id="selectedProductId"
      :city-id="activeCityId"
      @update:model-value="onProductDetailsToggle"
    />
  </section>
</template>
