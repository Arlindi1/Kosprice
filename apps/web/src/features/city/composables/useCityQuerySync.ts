import { watch } from 'vue'
import { useRoute, useRouter, type LocationQueryValue } from 'vue-router'

import { useCityStore } from '@/features/city/store/useCityStore'

function parseCityQueryValue(
  cityQuery: LocationQueryValue | LocationQueryValue[] | undefined,
): number | null {
  const rawValue = Array.isArray(cityQuery) ? cityQuery[0] : cityQuery

  if (rawValue === null || rawValue === undefined || rawValue === '') {
    return null
  }

  const parsedValue = Number(rawValue)
  if (!Number.isInteger(parsedValue) || parsedValue <= 0) {
    return null
  }

  return parsedValue
}

export function useCityQuerySync(): void {
  const route = useRoute()
  const router = useRouter()
  const cityStore = useCityStore()

  watch(
    () => route.query.city,
    (cityQuery) => {
      const parsedCityId = parseCityQueryValue(cityQuery)

      if (parsedCityId !== cityStore.selectedCityId) {
        cityStore.setSelectedCityId(parsedCityId)
      }
    },
    { immediate: true },
  )

  watch(
    () => cityStore.selectedCityId,
    (selectedCityId) => {
      const parsedCityId = parseCityQueryValue(route.query.city)

      if (parsedCityId === selectedCityId) {
        return
      }

      const nextQuery = { ...route.query }

      if (selectedCityId === null) {
        delete nextQuery.city
      } else {
        nextQuery.city = String(selectedCityId)
      }

      void router.replace({ query: nextQuery })
    },
  )
}
