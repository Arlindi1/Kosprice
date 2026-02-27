import { ref } from 'vue'

import {
  getBasketCheapest,
  getFuelLatest,
  getMarketBasket,
  getMarkets,
  getProductsCatalog,
  isAbortError,
  toErrorMessage,
} from '@/lib/api'
import { MemoryCache } from '@/lib/cache/memoryCache'
import type {
  ApiItemResponse,
  BasketSummary,
  FuelPrice,
  MarketBasket,
  ProductCatalogItem,
} from '@/lib/types/api'

type DropState = 'ok' | 'insufficient' | 'no_drop'

export type DashboardPriceDropInsight = {
  product_id: number
  product_name: string
  image_key: string | null
  current_price_eur: number
  previous_price_eur: number
  drop_eur: number
  comparison_date: string
}

export type DashboardFuelHighlight = {
  fuel_type: string
  station_name: string
  station_address: string | null
  price_eur_liter: number
  recorded_at: string | null
}

export type DashboardTrendingItem = {
  product_id: number
  name: string
  category: string
  image_key: string | null
  current_price_eur: number | null
  badge_label: string
  badge_variant: 'accent' | 'neutral'
}

const marketBasketByDateCache = new MemoryCache<ApiItemResponse<MarketBasket>>(2 * 60_000)

function formatMoneyDelta(value: number): string {
  const abs = Math.abs(value)
  const sign = value < 0 ? '-' : '+'
  return `${sign}${abs.toFixed(2)} EUR`
}

function roundCurrency(value: number): number {
  return Math.round((value + Number.EPSILON) * 100) / 100
}

function subtractDays(date: string, days: number): string {
  const parsed = new Date(`${date}T00:00:00Z`)
  parsed.setUTCDate(parsed.getUTCDate() - days)
  return parsed.toISOString().slice(0, 10)
}

function resolveLatestDate(
  catalogRecordedAt: string | null | undefined,
  basketRecordedAt: string | null | undefined,
  fuelRows: FuelPrice[],
): string | null {
  const candidates: string[] = []

  if (catalogRecordedAt) {
    candidates.push(catalogRecordedAt)
  }

  if (basketRecordedAt) {
    candidates.push(basketRecordedAt)
  }

  for (const row of fuelRows) {
    if (row.recorded_at) {
      candidates.push(row.recorded_at)
    }
  }

  if (candidates.length === 0) {
    return null
  }

  candidates.sort()
  return candidates[candidates.length - 1] ?? null
}

export function useDashboardToday() {
  const cityName = ref('your city')

  const cheapestBasket = ref<BasketSummary | null>(null)
  const cheapestFuel = ref<DashboardFuelHighlight | null>(null)
  const biggestPriceDrop = ref<DashboardPriceDropInsight | null>(null)
  const biggestPriceDropState = ref<DropState>('insufficient')

  const trendingItems = ref<DashboardTrendingItem[]>([])

  const isLoading = ref(false)
  const error = ref<string | null>(null)
  let loadController: AbortController | null = null

  async function resolveMinPricesForDate(
    cityId: number,
    marketIds: number[],
    comparisonDate: string,
    force = false,
    signal?: AbortSignal,
  ): Promise<Map<number, number>> {
    const minPriceByProduct = new Map<number, number>()

    const settled = await Promise.allSettled(
      marketIds.map(async (marketId) => {
        const cacheKey = `dashboard:market:basket:${cityId}:${marketId}:${comparisonDate}`
        if (force) {
          marketBasketByDateCache.delete(cacheKey)
        }

        const response = await marketBasketByDateCache.getOrSet(
          cacheKey,
          () => getMarketBasket(marketId, comparisonDate, { signal }),
        )

        return response.data
      }),
    )

    for (const item of settled) {
      if (item.status !== 'fulfilled') {
        continue
      }

      for (const basketItem of item.value.items) {
        const previousMin = minPriceByProduct.get(basketItem.product_id)
        if (previousMin === undefined || basketItem.price_eur < previousMin) {
          minPriceByProduct.set(basketItem.product_id, basketItem.price_eur)
        }
      }
    }

    return minPriceByProduct
  }

  function resolveCheapestFuel(rows: FuelPrice[]): DashboardFuelHighlight | null {
    const candidates = rows.filter((row) => row.fuel_type === 'diesel' || row.fuel_type === 'petrol95')
    if (candidates.length === 0) {
      return null
    }

    const cheapest = candidates.reduce((lowest, current) =>
      current.price_eur_liter < lowest.price_eur_liter ? current : lowest,
    )

    return {
      fuel_type: cheapest.fuel_type,
      station_name: cheapest.station.name,
      station_address: cheapest.station.address,
      price_eur_liter: cheapest.price_eur_liter,
      recorded_at: cheapest.recorded_at,
    }
  }

  function resolveTrendingItems(
    catalogItems: ProductCatalogItem[],
    previousMinByProduct: Map<number, number>,
  ): DashboardTrendingItem[] {
    const withChange = catalogItems
      .map((item) => {
        const current = item.cheapest_price_today
        const previous = previousMinByProduct.get(item.id)
        const change = current !== null && previous !== undefined ? roundCurrency(current - previous) : null

        return {
          item,
          change,
        }
      })
      .filter((entry) => entry.item.cheapest_price_today !== null)

    const sortedByImpact = [...withChange].sort((left, right) => {
      const leftImpact = left.change === null ? -1 : Math.abs(left.change)
      const rightImpact = right.change === null ? -1 : Math.abs(right.change)

      if (leftImpact !== rightImpact) {
        return rightImpact - leftImpact
      }

      return left.item.name.localeCompare(right.item.name)
    })

    return sortedByImpact.slice(0, 6).map((entry) => {
      const change = entry.change

      if (change === null) {
        return {
          product_id: entry.item.id,
          name: entry.item.name,
          category: entry.item.category,
          image_key: entry.item.image_key,
          current_price_eur: entry.item.cheapest_price_today,
          badge_label: 'Popular',
          badge_variant: 'neutral',
        } satisfies DashboardTrendingItem
      }

      return {
        product_id: entry.item.id,
        name: entry.item.name,
        category: entry.item.category,
        image_key: entry.item.image_key,
        current_price_eur: entry.item.cheapest_price_today,
        badge_label: formatMoneyDelta(change),
        badge_variant: change < 0 ? 'accent' : 'neutral',
      } satisfies DashboardTrendingItem
    })
  }

  function resolveBiggestDrop(
    catalogItems: ProductCatalogItem[],
    previousMinByProduct: Map<number, number>,
    comparisonDate: string,
  ): DashboardPriceDropInsight | null {
    const dropCandidates = catalogItems
      .map((item) => {
        const current = item.cheapest_price_today
        const previous = previousMinByProduct.get(item.id)

        if (current === null || previous === undefined) {
          return null
        }

        const drop = roundCurrency(previous - current)
        if (drop <= 0) {
          return null
        }

        return {
          product_id: item.id,
          product_name: item.name,
          image_key: item.image_key,
          current_price_eur: current,
          previous_price_eur: previous,
          drop_eur: drop,
          comparison_date: comparisonDate,
        } satisfies DashboardPriceDropInsight
      })
      .filter((entry): entry is DashboardPriceDropInsight => entry !== null)
      .sort((left, right) => right.drop_eur - left.drop_eur)

    return dropCandidates[0] ?? null
  }

  async function load(cityId: number | null, force = false): Promise<void> {
    loadController?.abort()

    if (cityId === null) {
      cityName.value = 'your city'
      cheapestBasket.value = null
      cheapestFuel.value = null
      biggestPriceDrop.value = null
      biggestPriceDropState.value = 'insufficient'
      trendingItems.value = []
      error.value = null
      isLoading.value = false
      loadController = null
      return
    }

    const controller = new AbortController()
    loadController = controller

    isLoading.value = true
    error.value = null

    try {
      const [basketResponse, fuelResponse, catalogResponse, marketsResponse] = await Promise.all([
        getBasketCheapest(cityId, { force, signal: controller.signal }),
        getFuelLatest(cityId, { force, signal: controller.signal }),
        getProductsCatalog(cityId, { force, signal: controller.signal }),
        getMarkets(cityId, { force, signal: controller.signal }),
      ])

      if (loadController !== controller) {
        return
      }

      cheapestBasket.value = basketResponse.data
      cityName.value =
        basketResponse.data.city.name ??
        marketsResponse.data[0]?.city.name ??
        'your city'

      cheapestFuel.value = resolveCheapestFuel(fuelResponse.data)

      const latestDate = resolveLatestDate(
        catalogResponse.meta?.recorded_at ?? null,
        basketResponse.data.recorded_at ?? null,
        fuelResponse.data,
      )

      const catalogItems = catalogResponse.data
      if (latestDate === null || marketsResponse.data.length === 0 || catalogItems.length === 0) {
        biggestPriceDrop.value = null
        biggestPriceDropState.value = 'insufficient'
        trendingItems.value = resolveTrendingItems(catalogItems, new Map())
        return
      }

      const comparisonDate = subtractDays(latestDate, 7)
      const previousMinByProduct = await resolveMinPricesForDate(
        cityId,
        marketsResponse.data.map((market) => market.id),
        comparisonDate,
        force,
        controller.signal,
      )

      if (loadController !== controller) {
        return
      }

      const dropInsight = resolveBiggestDrop(catalogItems, previousMinByProduct, comparisonDate)
      biggestPriceDrop.value = dropInsight

      if (dropInsight) {
        biggestPriceDropState.value = 'ok'
      } else if (previousMinByProduct.size > 0) {
        biggestPriceDropState.value = 'no_drop'
      } else {
        biggestPriceDropState.value = 'insufficient'
      }

      trendingItems.value = resolveTrendingItems(catalogItems, previousMinByProduct)
    } catch (caughtError) {
      if (isAbortError(caughtError)) {
        return
      }

      error.value = toErrorMessage(caughtError)
      cheapestBasket.value = null
      cheapestFuel.value = null
      biggestPriceDrop.value = null
      biggestPriceDropState.value = 'insufficient'
      trendingItems.value = []
    } finally {
      if (loadController === controller) {
        loadController = null
        isLoading.value = false
      }
    }
  }

  return {
    cityName,
    cheapestBasket,
    cheapestFuel,
    biggestPriceDrop,
    biggestPriceDropState,
    trendingItems,
    isLoading,
    error,
    loadTodayInsights: load,
  }
}
