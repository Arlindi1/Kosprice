import { ref, type Ref } from 'vue'

import { getMarkets, isAbortError, toErrorMessage } from '@/lib/api'
import type { Market } from '@/lib/types/api'

export function useMarkets(cityId: Ref<number | null>) {
  const markets = ref<Market[]>([])
  const isLoading = ref(false)
  const error = ref<string | null>(null)
  let loadController: AbortController | null = null

  async function load(force = false): Promise<void> {
    loadController?.abort()

    const resolvedCityId = cityId.value

    if (resolvedCityId === null) {
      markets.value = []
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
      const response = await getMarkets(resolvedCityId, {
        force,
        signal: controller.signal,
      })

      if (loadController !== controller) {
        return
      }

      markets.value = response.data
    } catch (caughtError) {
      if (isAbortError(caughtError)) {
        return
      }

      error.value = toErrorMessage(caughtError)
      markets.value = []
    } finally {
      if (loadController === controller) {
        loadController = null
        isLoading.value = false
      }
    }
  }

  return {
    markets,
    isLoading,
    error,
    refresh: (force = false) => load(force),
  }
}
