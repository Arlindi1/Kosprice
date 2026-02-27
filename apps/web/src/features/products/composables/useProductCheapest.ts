import { ref, type Ref } from 'vue'

import { getProductCheapest, isAbortError, toErrorMessage } from '@/lib/api'
import type { ProductCheapestResult } from '@/lib/types/api'

export function useProductCheapest(
  productId: Ref<number | null>,
  cityId: Ref<number | null>,
) {
  const result = ref<ProductCheapestResult | null>(null)
  const isLoading = ref(false)
  const error = ref<string | null>(null)
  let loadController: AbortController | null = null

  async function load(force = false): Promise<void> {
    loadController?.abort()

    const resolvedProductId = productId.value
    const resolvedCityId = cityId.value

    if (resolvedProductId === null || resolvedCityId === null) {
      result.value = null
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
      const response = await getProductCheapest(resolvedProductId, resolvedCityId, {
        force,
        signal: controller.signal,
      })

      if (loadController !== controller) {
        return
      }

      result.value = response.data
    } catch (caughtError) {
      if (isAbortError(caughtError)) {
        return
      }

      error.value = toErrorMessage(caughtError)
      result.value = null
    } finally {
      if (loadController === controller) {
        loadController = null
        isLoading.value = false
      }
    }
  }

  return {
    result,
    isLoading,
    error,
    refresh: (force = false) => load(force),
  }
}
