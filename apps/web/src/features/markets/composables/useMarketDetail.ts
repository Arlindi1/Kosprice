import { computed, ref } from 'vue'

import { getMarketBasket, getProducts, toErrorMessage } from '@/lib/api'
import { MemoryCache } from '@/lib/cache/memoryCache'
import type {
  ApiItemResponse,
  ApiListResponse,
  BasketItemWithCatalog,
  MarketBasket,
  Product,
  ProductMeta,
} from '@/lib/types/api'

export type MarketDetailSortMode = 'price' | 'name' | 'category'

const marketBasketCache = new MemoryCache<ApiItemResponse<MarketBasket>>(90_000)
const productIndexCache = new MemoryCache<ApiListResponse<Product, ProductMeta>>(5 * 60_000)

function normalizeText(value: string): string {
  return value.trim().toLowerCase()
}

export function useMarketDetail() {
  const marketBasket = ref<MarketBasket | null>(null)
  const enrichedItems = ref<BasketItemWithCatalog[]>([])
  const catalogProductCount = ref(0)
  const isLoadingDetail = ref(false)
  const detailError = ref<string | null>(null)

  const selectedCategory = ref('all')
  const searchTerm = ref('')
  const sortMode = ref<MarketDetailSortMode>('price')

  const categories = computed(() => {
    const uniqueCategories = new Set(
      enrichedItems.value.map((item) => item.category).filter((category) => category.length > 0),
    )

    return ['all', ...Array.from(uniqueCategories).sort((left, right) => left.localeCompare(right))]
  })

  const filteredItems = computed(() => {
    const normalizedQuery = normalizeText(searchTerm.value)

    const filtered = enrichedItems.value.filter((item) => {
      const matchesCategory =
        selectedCategory.value === 'all' || item.category === selectedCategory.value
      const searchable = normalizeText(
        `${item.name} ${item.category} ${item.unit_label ?? ''} ${item.unit}`,
      )
      const matchesSearch =
        normalizedQuery.length === 0 || searchable.includes(normalizedQuery)

      return matchesCategory && matchesSearch
    })

    return filtered.sort((left, right) => {
      if (sortMode.value === 'name') {
        return left.name.localeCompare(right.name)
      }

      if (sortMode.value === 'category') {
        const categoryCompare = left.category.localeCompare(right.category)
        if (categoryCompare !== 0) {
          return categoryCompare
        }

        return left.name.localeCompare(right.name)
      }

      if (left.price_eur !== right.price_eur) {
        return left.price_eur - right.price_eur
      }

      return left.name.localeCompare(right.name)
    })
  })

  const coveragePercent = computed(() => {
    if (catalogProductCount.value <= 0) {
      return 0
    }

    const resolved = (enrichedItems.value.length / catalogProductCount.value) * 100
    return Number(Math.min(100, resolved).toFixed(1))
  })

  async function loadMarketDetail(marketId: number | null, force = false): Promise<void> {
    if (marketId === null) {
      marketBasket.value = null
      enrichedItems.value = []
      detailError.value = null
      isLoadingDetail.value = false
      return
    }

    const basketCacheKey = `market:basket:${marketId}`
    if (force) {
      marketBasketCache.delete(basketCacheKey)
      productIndexCache.delete('products:index')
    }

    isLoadingDetail.value = true
    detailError.value = null

    try {
      const [basketResponse, productsResponse] = await Promise.all([
        marketBasketCache.getOrSet(basketCacheKey, () => getMarketBasket(marketId)),
        productIndexCache.getOrSet('products:index', () => getProducts()),
      ])

      marketBasket.value = basketResponse.data
      catalogProductCount.value = productsResponse.data.length
      const productsById = new Map(productsResponse.data.map((item) => [item.id, item]))
      const productsByName = new Map(
        productsResponse.data.map((item) => [normalizeText(item.name), item]),
      )

      enrichedItems.value = basketResponse.data.items.map((item) => {
        const match =
          productsById.get(item.product_id) ??
          productsByName.get(normalizeText(item.name))

        return {
          ...item,
          category: match?.category ?? 'other',
          image_key: match?.image_key ?? null,
          unit_label: match?.unit_label ?? null,
          is_core_basket: match?.is_core_basket ?? false,
        }
      })

      selectedCategory.value = 'all'
      searchTerm.value = ''
      sortMode.value = 'price'
    } catch (error) {
      detailError.value = toErrorMessage(error)
      marketBasket.value = null
      enrichedItems.value = []
      catalogProductCount.value = 0
    } finally {
      isLoadingDetail.value = false
    }
  }

  return {
    marketBasket,
    enrichedItems,
    filteredItems,
    categories,
    selectedCategory,
    searchTerm,
    sortMode,
    catalogProductCount,
    coveragePercent,
    isLoadingDetail,
    detailError,
    loadMarketDetail,
  }
}
