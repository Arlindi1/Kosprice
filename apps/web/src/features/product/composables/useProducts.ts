import { ref } from 'vue'

import { getMarketBasket, getMarkets, getProductsCatalog, isAbortError, toErrorMessage } from '@/lib/api'
import { MemoryCache } from '@/lib/cache/memoryCache'
import type {
  ApiItemResponse,
  MarketBasket,
  ProductCatalogItem,
  ProductCatalogMeta,
  ProductMarketPriceRow,
  ProductTrendPoint,
} from '@/lib/types/api'

const marketPricesCache = new MemoryCache<ProductMarketPriceRow[]>(2 * 60_000)
const marketBasketSnapshotCache = new MemoryCache<ApiItemResponse<MarketBasket>>(5 * 60_000)
const productTrendCache = new MemoryCache<ProductTrendPoint[]>(2 * 60_000)

function createDateRange(endDate: string, days: number): string[] {
  const end = new Date(`${endDate}T00:00:00Z`)
  const dates: string[] = []

  for (let offset = days - 1; offset >= 0; offset--) {
    const date = new Date(end)
    date.setUTCDate(end.getUTCDate() - offset)
    dates.push(date.toISOString().slice(0, 10))
  }

  return dates
}

function resolvePrice(marketBasket: MarketBasket, productId: number): number | null {
  const productItem = marketBasket.items.find((item) => item.product_id === productId)
  return productItem ? productItem.price_eur : null
}

export function useProducts() {
  const catalog = ref<ProductCatalogItem[]>([])
  const catalogMeta = ref<ProductCatalogMeta | null>(null)
  const isLoadingCatalog = ref(false)
  const catalogError = ref<string | null>(null)

  const marketPrices = ref<ProductMarketPriceRow[]>([])
  const isLoadingMarketPrices = ref(false)
  const marketPricesError = ref<string | null>(null)

  const trend = ref<ProductTrendPoint[]>([])
  const isLoadingTrend = ref(false)
  const trendError = ref<string | null>(null)
  let catalogController: AbortController | null = null

  async function fetchMarketBasketSnapshot(
    marketId: number,
    recordedAt?: string,
    force = false,
  ): Promise<ApiItemResponse<MarketBasket>> {
    const cacheKey = `markets:basket:${marketId}:${recordedAt ?? 'latest'}`

    if (force) {
      marketBasketSnapshotCache.delete(cacheKey)
    }

    return marketBasketSnapshotCache.getOrSet(cacheKey, () => getMarketBasket(marketId, recordedAt))
  }

  async function loadCatalog(cityId: number | null, force = false): Promise<void> {
    catalogController?.abort()

    if (cityId === null) {
      catalog.value = []
      catalogMeta.value = null
      catalogError.value = null
      isLoadingCatalog.value = false
      catalogController = null
      return
    }

    const controller = new AbortController()
    catalogController = controller

    isLoadingCatalog.value = true
    catalogError.value = null

    try {
      const response = await getProductsCatalog(cityId, {
        force,
        signal: controller.signal,
      })

      if (catalogController !== controller) {
        return
      }

      catalog.value = response.data
      catalogMeta.value = response.meta ?? null
    } catch (error) {
      if (isAbortError(error)) {
        return
      }

      catalogError.value = toErrorMessage(error)
      catalog.value = []
      catalogMeta.value = null
    } finally {
      if (catalogController === controller) {
        catalogController = null
        isLoadingCatalog.value = false
      }
    }
  }

  async function loadProductMarketPrices(
    cityId: number | null,
    productId: number,
    force = false,
  ): Promise<void> {
    if (cityId === null) {
      marketPrices.value = []
      marketPricesError.value = null
      return
    }

    const cacheKey = `products:markets:${cityId}:${productId}`
    if (force) {
      marketPricesCache.delete(cacheKey)
    }

    isLoadingMarketPrices.value = true
    marketPricesError.value = null

    try {
      const rows = await marketPricesCache.getOrSet(cacheKey, async () => {
        const marketsResponse = await getMarkets(cityId)
        const snapshots = await Promise.all(
          marketsResponse.data.map((market) => fetchMarketBasketSnapshot(market.id, undefined, force)),
        )

        const mapped = marketsResponse.data.map((market, index): ProductMarketPriceRow => {
          const basket = snapshots[index]?.data
          const price = basket ? resolvePrice(basket, productId) : null

          return {
            market_id: market.id,
            market_name: market.name,
            market_address: market.address,
            recorded_at: basket?.recorded_at ?? null,
            price_eur: price,
          }
        })

        return mapped.sort((left, right) => {
          if (left.price_eur === null && right.price_eur === null) {
            return left.market_name.localeCompare(right.market_name)
          }

          if (left.price_eur === null) {
            return 1
          }

          if (right.price_eur === null) {
            return -1
          }

          return left.price_eur - right.price_eur
        })
      })

      marketPrices.value = rows
    } catch (error) {
      marketPricesError.value = toErrorMessage(error)
      marketPrices.value = []
    } finally {
      isLoadingMarketPrices.value = false
    }
  }

  async function loadProductTrend(
    cityId: number | null,
    productId: number,
    marketId: number | null,
    days = 30,
    force = false,
  ): Promise<void> {
    if (cityId === null || marketId === null) {
      trend.value = []
      trendError.value = null
      return
    }

    const endDate = catalogMeta.value?.recorded_at ?? new Date().toISOString().slice(0, 10)
    const cacheKey = `products:trend:${cityId}:${productId}:${marketId}:${days}:${endDate}`

    if (force) {
      productTrendCache.delete(cacheKey)
    }

    isLoadingTrend.value = true
    trendError.value = null

    try {
      const trendPoints = await productTrendCache.getOrSet(cacheKey, async () => {
        const dates = createDateRange(endDate, days)
        const settled = await Promise.allSettled(
          dates.map(async (date): Promise<ProductTrendPoint> => {
            const response = await fetchMarketBasketSnapshot(marketId, date, force)
            return {
              recorded_at: date,
              price_eur: resolvePrice(response.data, productId),
            }
          }),
        )

        return settled.map((result, index) => {
          const recordedAt = dates[index] ?? endDate

          if (result.status === 'fulfilled') {
            return result.value
          }

          return {
            recorded_at: recordedAt,
            price_eur: null,
          }
        })
      })

      trend.value = trendPoints
    } catch (error) {
      trendError.value = toErrorMessage(error)
      trend.value = []
    } finally {
      isLoadingTrend.value = false
    }
  }

  return {
    catalog,
    catalogMeta,
    isLoadingCatalog,
    catalogError,
    marketPrices,
    isLoadingMarketPrices,
    marketPricesError,
    trend,
    isLoadingTrend,
    trendError,
    loadCatalog,
    loadProductMarketPrices,
    loadProductTrend,
  }
}
