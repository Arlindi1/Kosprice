import { reactive, ref } from 'vue'

import {
  getBasketCheapest,
  getBasketTotal,
  getBasketTrend,
  getMarketBasket,
  isAbortError,
  toErrorMessage,
} from '@/lib/api'
import type {
  BasketSummary,
  BasketTrendMeta,
  BasketTrendPoint,
  MarketBasket,
} from '@/lib/types/api'

export function useBasket() {
  const cheapestBasket = ref<BasketSummary | null>(null)
  const isLoadingCheapest = ref(false)
  const cheapestError = ref<string | null>(null)

  const basketTrend = ref<BasketTrendPoint[]>([])
  const basketTrendMeta = ref<BasketTrendMeta | null>(null)
  const isLoadingTrend = ref(false)
  const trendError = ref<string | null>(null)

  const marketBasket = ref<MarketBasket | null>(null)
  const isLoadingMarketBasket = ref(false)
  const marketBasketError = ref<string | null>(null)

  const basketTotalsByMarket = ref<Record<number, BasketSummary>>({})
  const basketTotalsLoadingByMarket = reactive<Record<number, boolean>>({})
  const basketTotalsErrorByMarket = reactive<Record<number, string | null>>({})

  let cheapestController: AbortController | null = null
  let trendController: AbortController | null = null
  let marketBasketController: AbortController | null = null
  const totalControllers = new Map<number, AbortController>()

  async function loadCheapestBasket(cityId: number | null, force = false): Promise<void> {
    cheapestController?.abort()

    if (cityId === null) {
      cheapestBasket.value = null
      cheapestError.value = null
      isLoadingCheapest.value = false
      cheapestController = null
      return
    }

    const controller = new AbortController()
    cheapestController = controller

    isLoadingCheapest.value = true
    cheapestError.value = null

    try {
      const response = await getBasketCheapest(cityId, {
        force,
        signal: controller.signal,
      })

      if (cheapestController !== controller) {
        return
      }

      cheapestBasket.value = response.data
    } catch (error) {
      if (isAbortError(error)) {
        return
      }

      cheapestError.value = toErrorMessage(error)
      cheapestBasket.value = null
    } finally {
      if (cheapestController === controller) {
        cheapestController = null
        isLoadingCheapest.value = false
      }
    }
  }

  async function loadBasketTrend(cityId: number | null, days: number, force = false): Promise<void> {
    trendController?.abort()

    if (cityId === null) {
      basketTrend.value = []
      basketTrendMeta.value = null
      trendError.value = null
      isLoadingTrend.value = false
      trendController = null
      return
    }

    const controller = new AbortController()
    trendController = controller

    isLoadingTrend.value = true
    trendError.value = null

    try {
      const response = await getBasketTrend(cityId, days, {
        force,
        signal: controller.signal,
      })

      if (trendController !== controller) {
        return
      }

      basketTrend.value = response.data
      basketTrendMeta.value = response.meta ?? null
    } catch (error) {
      if (isAbortError(error)) {
        return
      }

      trendError.value = toErrorMessage(error)
      basketTrend.value = []
      basketTrendMeta.value = null
    } finally {
      if (trendController === controller) {
        trendController = null
        isLoadingTrend.value = false
      }
    }
  }

  async function loadBasketTotal(
    cityId: number | null,
    marketId: number,
    force = false,
  ): Promise<BasketSummary | null> {
    totalControllers.get(marketId)?.abort()

    if (cityId === null) {
      basketTotalsErrorByMarket[marketId] = null
      basketTotalsLoadingByMarket[marketId] = false
      totalControllers.delete(marketId)
      return null
    }

    const controller = new AbortController()
    totalControllers.set(marketId, controller)

    basketTotalsLoadingByMarket[marketId] = true
    basketTotalsErrorByMarket[marketId] = null

    try {
      const response = await getBasketTotal(cityId, marketId, {
        force,
        signal: controller.signal,
      })

      if (totalControllers.get(marketId) !== controller) {
        return null
      }

      basketTotalsByMarket.value = {
        ...basketTotalsByMarket.value,
        [marketId]: response.data,
      }
      return response.data
    } catch (error) {
      if (isAbortError(error)) {
        return null
      }

      basketTotalsErrorByMarket[marketId] = toErrorMessage(error)
      return null
    } finally {
      if (totalControllers.get(marketId) === controller) {
        totalControllers.delete(marketId)
        basketTotalsLoadingByMarket[marketId] = false
      }
    }
  }

  async function loadTotalsForMarkets(
    cityId: number | null,
    marketIds: number[],
    force = false,
  ): Promise<void> {
    const tasks = marketIds.map((marketId) => loadBasketTotal(cityId, marketId, force))
    await Promise.all(tasks)
  }

  async function loadMarketBasket(marketId: number | null, force = false): Promise<void> {
    marketBasketController?.abort()

    if (marketId === null) {
      marketBasket.value = null
      marketBasketError.value = null
      isLoadingMarketBasket.value = false
      marketBasketController = null
      return
    }

    const controller = new AbortController()
    marketBasketController = controller

    isLoadingMarketBasket.value = true
    marketBasketError.value = null

    try {
      const response = await getMarketBasket(marketId, undefined, {
        force,
        signal: controller.signal,
      })

      if (marketBasketController !== controller) {
        return
      }

      marketBasket.value = response.data
    } catch (error) {
      if (isAbortError(error)) {
        return
      }

      marketBasketError.value = toErrorMessage(error)
      marketBasket.value = null
    } finally {
      if (marketBasketController === controller) {
        marketBasketController = null
        isLoadingMarketBasket.value = false
      }
    }
  }

  return {
    cheapestBasket,
    isLoadingCheapest,
    cheapestError,
    basketTrend,
    basketTrendMeta,
    isLoadingTrend,
    trendError,
    basketTotalsByMarket,
    basketTotalsLoadingByMarket,
    basketTotalsErrorByMarket,
    marketBasket,
    isLoadingMarketBasket,
    marketBasketError,
    loadCheapestBasket,
    loadBasketTrend,
    loadBasketTotal,
    loadTotalsForMarkets,
    loadMarketBasket,
  }
}
