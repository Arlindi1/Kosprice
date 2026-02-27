import { ref } from 'vue'

import { getCities, isAbortError, toErrorMessage } from '@/lib/api'
import type { City } from '@/lib/types/api'

let activeCitiesController: AbortController | null = null

const cities = ref<City[]>([])
const isLoadingCities = ref(false)
const citiesError = ref<string | null>(null)

export function useCities() {
  async function loadCities(force = false): Promise<void> {
    activeCitiesController?.abort()
    const controller = new AbortController()
    activeCitiesController = controller

    isLoadingCities.value = true
    citiesError.value = null

    try {
      const response = await getCities({
        force,
        signal: controller.signal,
      })

      if (activeCitiesController !== controller) {
        return
      }

      cities.value = response.data
    } catch (error) {
      if (isAbortError(error)) {
        return
      }

      citiesError.value = toErrorMessage(error)
    } finally {
      if (activeCitiesController === controller) {
        activeCitiesController = null
        isLoadingCities.value = false
      }
    }
  }

  return {
    cities,
    isLoadingCities,
    citiesError,
    loadCities,
  }
}
