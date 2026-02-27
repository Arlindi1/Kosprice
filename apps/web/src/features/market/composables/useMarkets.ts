import { ref } from 'vue'

import { getMarkets, isAbortError, toErrorMessage } from '@/lib/api'
import type { Market } from '@/lib/types/api'

export function useMarkets() {
  const markets = ref<Market[]>([])
  const isLoadingMarkets = ref(false)
  const marketsError = ref<string | null>(null)
  let activeMarketsController: AbortController | null = null

  async function loadMarkets(cityId: number | null, force = false): Promise<void> {
    activeMarketsController?.abort()

    if (cityId === null) {
      markets.value = []
      marketsError.value = null
      isLoadingMarkets.value = false
      activeMarketsController = null
      return
    }

    const controller = new AbortController()
    activeMarketsController = controller

    isLoadingMarkets.value = true
    marketsError.value = null

    try {
      const response = await getMarkets(cityId, {
        force,
        signal: controller.signal,
      })

      if (activeMarketsController !== controller) {
        return
      }

      markets.value = response.data
    } catch (error) {
      if (isAbortError(error)) {
        return
      }

      marketsError.value = toErrorMessage(error)
      markets.value = []
    } finally {
      if (activeMarketsController === controller) {
        activeMarketsController = null
        isLoadingMarkets.value = false
      }
    }
  }

  return {
    markets,
    isLoadingMarkets,
    marketsError,
    loadMarkets,
  }
}
